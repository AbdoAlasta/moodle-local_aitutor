<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_aitutor\local;

/**
 * Core logic of the AI Tutor: chats, prompt building, RAG calls and reply parsing.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tutor {
    /** @var string[] Modes that return structured JSON. */
    public const STRUCTURED = ['topics', 'studyplan', 'quiz', 'flashcards', 'summary'];

    /** @var string[] All supported modes. */
    public const MODES = ['chat', 'assignment', 'topics', 'studyplan', 'quiz', 'flashcards', 'summary'];

    /** @var int Pairs kept word for word after older ones are folded into the summary. */
    private const RECENT_TURNS = 4;

    /** @var int When this many pairs are not yet in the summary, all but the last RECENT_TURNS are folded into it. So a
     * question carries the summary plus every pair not in it yet (4 to 7), and nothing falls between the two. */
    private const FOLD_AT = 8;

    /** @var int Most pairs sent with a question (the service accepts 10); only reached when folding keeps failing. */
    private const MAX_HISTORY = 10;

    /** @var int Most pairs sent in one /summarize call (older chats from before the summary existed). */
    private const FOLD_MAX = 20;

    /** @var int Longest earlier answer sent as history (characters). */
    private const HISTORY_ANSWER_CHARS = 500;

    /**
     * Returns a chat owned by the user in the course, or throws.
     *
     * @param int $chatid
     * @param int $courseid
     * @param int $userid
     * @return \stdClass
     */
    public static function get_chat_record(int $chatid, int $courseid, int $userid): \stdClass {
        global $DB;
        return $DB->get_record('local_aitutor_chat', ['id' => $chatid, 'courseid' => $courseid, 'userid' => $userid],
            '*', MUST_EXIST);
    }

    /**
     * Handles one tutor request: stores the user message, calls the RAG service, stores and returns the reply.
     *
     * @param \stdClass $course
     * @param \stdClass $user
     * @param int $chatid 0 to start a new chat.
     * @param string $mode One of self::MODES.
     * @param string $message The user text (may be empty for action modes).
     * @param int $cmid Selected resource, 0 for none.
     * @param bool $useresource Whether to use the resource as context.
     * @return array
     */
    public static function handle(\stdClass $course, \stdClass $user, int $chatid, string $mode, string $message,
            int $cmid, bool $useresource): array {
        global $DB;

        // Before anything is stored: a refused request leaves no half-written chat behind.
        if (rag_client::is_configured()) {
            self::check_rate_limit((int) $user->id);
        }

        $context = \context_course::instance($course->id);
        $now = time();
        $message = trim($message);
        if (!in_array($mode, self::MODES, true)) {
            $mode = 'chat';
        }
        if ($message === '') {
            $message = get_string('prompt_' . $mode, 'local_aitutor');
        }
        $message = \core_text::substr($message, 0, 4000);

        // Chat record.
        if ($chatid > 0) {
            $chat = self::get_chat_record($chatid, $course->id, $user->id);
        } else {
            $chat = (object) [
                'courseid' => $course->id,
                'userid' => $user->id,
                'title' => shorten_text($message, 60, true),
                'timecreated' => $now,
                'timemodified' => $now,
            ];
            $chat->id = $DB->insert_record('local_aitutor_chat', $chat);
        }

        // Selected resource: only its name is used, to steer the service's search towards it.
        $resource = ($cmid > 0 && $useresource) ? resources::get_info($course, $cmid) : null;

        // Chat memory: a running summary of the older messages plus the most recent exchanges, so the service
        // understands follow-ups ("explain more") and questions about the whole chat ("what did I ask first?").
        [$summary, $history] = self::chat_memory($chat);

        // Store the user message.
        $DB->insert_record('local_aitutor_msg', (object) [
            'chatid' => $chat->id, 'role' => 'user', 'type' => 'text', 'content' => $message,
            'cmid' => $cmid ?: null, 'timecreated' => $now,
        ]);

        // Ask the external RAG service (it searches only the files indexed for this course id).
        $error = '';
        $raw = '';
        $references = [];
        $rewritten = '';
        if (rag_client::is_configured()) {
            try {
                $res = rag_client::query((int) $course->id, self::build_question($message, $resource), $history,
                    self::mode_instructions($mode), $summary);
                $raw = $res['answer'];
                $references = $res['references'];
                $rewritten = $res['rewritten'];
            } catch (\moodle_exception $e) {
                $error = $e->getMessage();
            }
        } else if (get_config('local_aitutor', 'demomode')) {
            $raw = demo::generate($mode, $resource['name'] ?? $course->shortname);
        } else {
            $error = get_string('error_norag', 'local_aitutor');
        }

        // Parse.
        if ($error !== '') {
            $type = 'error';
            $content = $error;
        } else {
            [$type, $content] = self::parse_reply($mode, $raw);
            if ($type === 'text') {
                if ($rewritten !== '') {
                    $content .= "\n\n*" . get_string('understoodas', 'local_aitutor', $rewritten) . '*';
                }
                $content .= self::format_sources($references);
            }
        }

        $replyid = $DB->insert_record('local_aitutor_msg', (object) [
            'chatid' => $chat->id, 'role' => 'tutor', 'type' => $type, 'content' => $content,
            'cmid' => $cmid ?: null, 'timecreated' => time(),
        ]);
        $DB->set_field('local_aitutor_chat', 'timemodified', time(), ['id' => $chat->id]);

        return [
            'chatid' => (int) $chat->id,
            'title' => $chat->title,
            'message' => [
                'id' => (int) $replyid,
                'role' => 'tutor',
                'type' => $type,
                'content' => $content,
                'cmid' => (int) $cmid,
                'timecreated' => time(),
                'resourceurl' => $resource['url'] ?? '',
                'resourcename' => $resource['name'] ?? '',
            ],
        ];
    }

    /**
     * Counts one RAG request for the user and refuses it when the hourly limit is reached (setting
     * maxquestionsperhour, 0 = no limit). The limit is per user across all courses, because every request costs
     * model calls on the same service.
     *
     * @param int $userid
     * @throws \moodle_exception error_ratelimited
     */
    public static function check_rate_limit(int $userid): void {
        $limit = (int) get_config('local_aitutor', 'maxquestionsperhour');
        if ($limit <= 0) {
            return;
        }
        $cache = \cache::make('local_aitutor', 'ratelimit');
        $key = 'u' . $userid;
        $now = time();
        $recent = array_values(array_filter(json_decode((string) $cache->get($key), true) ?: [],
            fn($t) => $t > $now - HOURSECS));
        if (count($recent) >= $limit) {
            throw new \moodle_exception('error_ratelimited', 'local_aitutor', '', $limit);
        }
        $recent[] = $now;
        $cache->set($key, json_encode($recent));
    }

    /**
     * The question sent to the RAG service: what it searches with (and rewrites when it is a follow-up). The mode's
     * instructions travel separately, so they never dilute the search.
     *
     * @param string $message
     * @param array|null $resource ['name', 'url'] of the selected resource.
     * @return string
     */
    private static function build_question(string $message, ?array $resource): string {
        return $resource ? "{$message} (course material: {$resource['name']})" : $message;
    }

    /**
     * The memory sent with a question: the chat's running summary and its most recent exchanges. When enough
     * exchanges have built up outside the summary, the older ones are folded into it first (one small /summarize
     * call every few questions). If that call fails the chat still works, with the summary it already had.
     *
     * @param \stdClass $chat Chat record (summary, summarizedupto).
     * @return array [string $summary, array[] $history] history: [['question' => ..., 'answer' => ...]], oldest first
     */
    private static function chat_memory(\stdClass $chat): array {
        global $DB;
        $summary = trim((string) ($chat->summary ?? ''));
        $pairs = self::load_pairs((int) $chat->id, (int) ($chat->summarizedupto ?? 0));

        if (count($pairs) >= self::FOLD_AT && rag_client::is_configured()) {
            $old = array_slice($pairs, 0, -self::RECENT_TURNS);
            try {
                $summary = rag_client::summarize($summary, array_map(fn($p) => ['question' => $p['question'],
                    'answer' => $p['answer']], array_slice($old, -self::FOLD_MAX)));
                $DB->update_record('local_aitutor_chat', (object) ['id' => $chat->id, 'summary' => $summary,
                    'summarizedupto' => end($old)['id']]);
                $pairs = array_slice($pairs, -self::RECENT_TURNS);
            } catch (\moodle_exception $e) {
                debugging('local_aitutor: could not update the chat summary: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }
        // Every pair that is not in the summary yet, so nothing is lost between two folds.
        $recent = array_map(fn($p) => ['question' => $p['question'], 'answer' => $p['answer']],
            array_slice($pairs, -self::MAX_HISTORY));
        return [$summary, $recent];
    }

    /**
     * The exchanges of a chat after a message id, oldest first. Each answer is cut down to what the model needs: the
     * plugin's "Understood as" and Sources lines and the [n] citation markers are removed. Structured replies (quiz,
     * flashcards...) are kept as a one-line note so the memory knows they happened; errors are left out.
     *
     * @param int $chatid
     * @param int $afterid Only messages with a larger id (0 for the whole chat).
     * @return array[] [['question' => string, 'answer' => string, 'id' => int (id of the answer message)]]
     */
    private static function load_pairs(int $chatid, int $afterid): array {
        global $DB;
        $rows = array_reverse($DB->get_records_select('local_aitutor_msg', 'chatid = ? AND id > ?', [$chatid, $afterid],
            'id DESC', 'id, role, type, content', 0, 200));
        $pairs = [];
        $question = null;
        foreach ($rows as $row) {
            if ($row->role === 'user') {
                $question = $row->content;
                continue;
            }
            if ($question !== null && $row->type === 'text') {
                $answer = preg_split('/\n\n\*(?:\*' . preg_quote(get_string('sources', 'local_aitutor'), '/') . '\*\*|'
                    . preg_quote(get_string('understoodas', 'local_aitutor', ''), '/') . ')/u', $row->content)[0];
                $answer = preg_replace('/\s*\[\d+(?:\s*,\s*\d+)*\]/u', '', $answer);
                $pairs[] = ['question' => $question, 'answer' => shorten_text(trim($answer), self::HISTORY_ANSWER_CHARS),
                    'id' => (int) $row->id];
            } else if ($question !== null && in_array($row->type, self::STRUCTURED, true)) {
                $what = $row->type === 'studyplan' ? 'study plan' : $row->type;
                $pairs[] = ['question' => $question, 'answer' => "(The tutor replied with a {$what}.)", 'id' => (int) $row->id];
            }
            $question = null; // Errors are not conversation.
        }
        return $pairs;
    }

    /**
     * The cited references of an answer as a Markdown list, numbered as the service numbered them ([1], [2]...).
     *
     * @param array[] $references From the service: ref, source, page, cited...
     * @return string Empty when nothing was cited.
     */
    private static function format_sources(array $references): string {
        $lines = [];
        foreach ($references as $ref) {
            if (!is_array($ref) || empty($ref['cited']) || trim((string) ($ref['source'] ?? '')) === '') {
                continue;
            }
            $line = '- [' . (int) ($ref['ref'] ?? 0) . '] ' . trim((string) $ref['source']);
            if (!empty($ref['page'])) {
                $line .= ', ' . get_string('sourcepage', 'local_aitutor', (int) $ref['page']);
            }
            $lines[] = $line;
        }
        return $lines ? "\n\n**" . get_string('sources', 'local_aitutor') . "**\n" . implode("\n", $lines) : '';
    }

    /**
     * Instructions (and the required JSON schema) added to the question for each mode; none for plain chat.
     *
     * @param string $mode
     * @return string
     */
    private static function mode_instructions(string $mode): string {
        $json = "Respond with ONLY valid JSON (no markdown fences, no commentary, no citation markers such as [1] inside "
            . "the JSON). Write the text in the language of the question. Schema:\n";
        switch ($mode) {
            case 'topics':
                return $json . '{"topics": ["question or topic the student could ask about", "... 6 to 8 short items"]}';
            case 'studyplan':
                return $json . '{"title": "string", "weeks": [{"label": "Week 1", "topics": "short description"}], '
                    . '"includes": ["Weekly topics", "Key resources", "Practice questions", "Milestones / goals"]} '
                    . "Use 4 to 8 weeks unless the student asks otherwise.";
            case 'quiz':
                return $json . '{"title": "string", "questions": [{"question": "string", "options": ["A", "B", "C", "D"], '
                    . '"answer": 0, "explanation": "why the answer is correct"}]} '
                    . "Write exactly 5 multiple-choice questions, 4 options each, \"answer\" is the zero-based index of the "
                    . "correct option. Options must not contain letter prefixes.";
            case 'flashcards':
                return $json . '{"title": "string", "cards": [{"front": "question or term", "back": "answer or definition"}]} '
                    . "Write 8 flashcards.";
            case 'summary':
                return $json . '{"title": "string", "bullets": ["key point", "..."]} Write 5 to 9 key points, each one sentence.';
            case 'assignment':
                return "This is about an assignment. Do NOT write the final solution or the complete answer. "
                    . "Explain the underlying concepts, give hints, ask guiding questions and outline an approach.";
            default:
                // Plain chat: the student's question goes to the service unchanged.
                return '';
        }
    }

    /**
     * Turns the raw answer text into [type, content] and validates structured output.
     *
     * @param string $mode
     * @param string $raw
     * @return array [type, content]
     */
    public static function parse_reply(string $mode, string $raw): array {
        $raw = trim($raw);
        if (!in_array($mode, self::STRUCTURED, true)) {
            return ['text', $raw !== '' ? $raw : get_string('error_empty', 'local_aitutor')];
        }
        $data = self::extract_json($raw);
        $clean = is_array($data) ? self::validate($mode, $data) : null;
        if ($clean === null) {
            // The model did not follow the format: show what it said as plain text.
            return ['text', $raw !== '' ? $raw : get_string('error_empty', 'local_aitutor')];
        }
        return [$mode, json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)];
    }

    /**
     * Extracts the first JSON object found in a string.
     *
     * @param string $raw
     * @return array|null
     */
    private static function extract_json(string $raw): ?array {
        $raw = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($raw));
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }
        $data = json_decode(substr($raw, $start, $end - $start + 1), true);
        return is_array($data) ? $data : null;
    }

    /**
     * Validates and normalises structured output. Strings are kept as plain text (escaped when rendered).
     *
     * @param string $mode
     * @param array $d
     * @return array|null
     */
    private static function validate(string $mode, array $d): ?array {
        $s = fn($v, int $max = 600): string => \core_text::substr(trim(strip_tags((string) $v)), 0, $max);
        $list = fn($v): array => is_array($v) ? array_values($v) : [];

        switch ($mode) {
            case 'topics':
                $items = array_values(array_filter(array_map($s, $list($d['topics'] ?? []))));
                return $items ? ['topics' => array_slice($items, 0, 10)] : null;

            case 'studyplan':
                $weeks = [];
                foreach ($list($d['weeks'] ?? []) as $w) {
                    if (is_array($w) && $s($w['label'] ?? '') !== '') {
                        $weeks[] = ['label' => $s($w['label'], 60), 'topics' => $s($w['topics'] ?? '')];
                    }
                }
                if (!$weeks) {
                    return null;
                }
                return [
                    'title' => $s($d['title'] ?? '', 120),
                    'weeks' => array_slice($weeks, 0, 12),
                    'includes' => array_slice(array_values(array_filter(array_map($s, $list($d['includes'] ?? [])))), 0, 8),
                ];

            case 'quiz':
                $questions = [];
                foreach ($list($d['questions'] ?? []) as $q) {
                    if (!is_array($q)) {
                        continue;
                    }
                    $options = array_values(array_filter(array_map($s, $list($q['options'] ?? []))));
                    $answer = $q['answer'] ?? null;
                    if (is_string($answer) && preg_match('/^[A-Da-d]$/', trim($answer))) {
                        $answer = ord(strtoupper(trim($answer))) - 65;
                    }
                    if ($s($q['question'] ?? '') === '' || count($options) < 2 || !is_numeric($answer)
                            || (int) $answer < 0 || (int) $answer >= count($options)) {
                        continue;
                    }
                    $questions[] = [
                        'question' => $s($q['question']),
                        'options' => array_slice($options, 0, 6),
                        'answer' => (int) $answer,
                        'explanation' => $s($q['explanation'] ?? '', 800),
                    ];
                }
                return $questions ? ['title' => $s($d['title'] ?? '', 120), 'questions' => array_slice($questions, 0, 10)] : null;

            case 'flashcards':
                $cards = [];
                foreach ($list($d['cards'] ?? []) as $c) {
                    if (is_array($c) && $s($c['front'] ?? '') !== '' && $s($c['back'] ?? '') !== '') {
                        $cards[] = ['front' => $s($c['front'], 300), 'back' => $s($c['back'], 800)];
                    }
                }
                return $cards ? ['title' => $s($d['title'] ?? '', 120), 'cards' => array_slice($cards, 0, 20)] : null;

            case 'summary':
                $bullets = array_values(array_filter(array_map($s, $list($d['bullets'] ?? []))));
                return $bullets ? ['title' => $s($d['title'] ?? '', 120), 'bullets' => array_slice($bullets, 0, 15)] : null;
        }
        return null;
    }
}
