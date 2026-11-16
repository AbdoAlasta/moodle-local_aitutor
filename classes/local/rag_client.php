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

/**
 * HTTP client for the course RAG service.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aitutor\local;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

/**
 * Talks to the course RAG service (POST /query, GET /health, header X-API-Key; see RAG_API.md).
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rag_client {
    /**
     * Whether a RAG service URL is configured.
     *
     * @return bool
     */
    public static function is_configured(): bool {
        return self::base_url() !== '';
    }

    /**
     * The configured service base URL (http or https only, without a trailing slash or /query), or an empty string.
     *
     * @return string
     */
    private static function base_url(): string {
        $url = rtrim(trim((string) get_config('local_aitutor', 'ragurl')), '/');
        // Accept a URL pasted with the endpoint (older versions stored the full endpoint).
        $url = preg_replace('~/query$~i', '', $url);
        return preg_match('~^https?://~i', $url) ? $url : '';
    }

    /**
     * Performs one HTTP call.
     *
     * @param string $method GET or POST.
     * @param string $path Path below the base URL, for example /query.
     * @param array|null $json Request body for POST, encoded as JSON.
     * @return array ['url' => string, 'status' => int, 'body' => string, 'error' => string]
     * @throws \moodle_exception When no service URL is configured.
     */
    public static function request(string $method, string $path, ?array $json = null): array {
        $base = self::base_url();
        if ($base === '') {
            throw new \moodle_exception('error_norag', 'local_aitutor');
        }
        $url = $base . $path;
        $timeout = (int) get_config('local_aitutor', 'ragtimeout') ?: 60;
        $timeout = max(5, min(300, $timeout));

        $headers = ['Accept: application/json'];
        $key = trim((string) get_config('local_aitutor', 'ragkey'));
        if ($key !== '') {
            $headers[] = 'X-API-Key: ' . $key;
        }
        $options = ['CURLOPT_TIMEOUT' => $timeout, 'CURLOPT_CONNECTTIMEOUT' => 10];

        // The URL can only be set by a site administrator, so Moodle's "cURL blocked hosts / allowed ports" check is
        // skipped for it. That lets the service run on localhost without removing localhost from the blocked list,
        // which would let every other Moodle feature that fetches URLs reach this server's local services.
        // The site proxy settings still apply.
        $curl = new \curl(['ignoresecurity' => true]);
        if ($method === 'POST') {
            $headers[] = 'Content-Type: application/json';
            $curl->setHeader($headers);
            $body = $curl->post($url, json_encode($json ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $options);
        } else {
            $curl->setHeader($headers);
            $body = $curl->get($url, [], $options);
        }
        $info = $curl->get_info();
        return [
            'url' => $url,
            'status' => (int) ($info['http_code'] ?? 0),
            'body' => is_string($body) ? $body : '',
            'error' => $curl->get_errno() ? (string) $curl->error : '',
        ];
    }

    /**
     * The request body of POST /query. The course id is a string: the same value local_ragindex indexes the files under.
     *
     * @param int $courseid
     * @param string $question What the service searches with.
     * @param array[] $history Earlier exchanges of the chat, oldest first: [['question' => ..., 'answer' => ...]].
     * @param string $instructions Answer format for the action; not used for the search.
     * @param string $summary Running summary of the older part of the chat.
     * @return array
     */
    public static function query_payload(int $courseid, string $question, array $history = [],
            string $instructions = '', string $summary = ''): array {
        $payload = ['question' => $question, 'course_id' => (string) $courseid];
        if ($history) {
            $payload['history'] = $history;
        }
        if ($instructions !== '') {
            $payload['instructions'] = $instructions;
        }
        if ($summary !== '') {
            $payload['summary'] = $summary;
        }
        return $payload;
    }

    /**
     * Folds older exchanges of a chat into its running summary (POST /summarize).
     *
     * @param string $summary The summary so far ('' for none).
     * @param array[] $turns [['question' => ..., 'answer' => ...]], oldest first.
     * @return string The updated summary.
     * @throws \moodle_exception When the service cannot be reached or answers with an error.
     */
    public static function summarize(string $summary, array $turns): string {
        $res = self::request('POST', '/summarize', ['summary' => $summary, 'turns' => array_values($turns)]);
        $data = json_decode($res['body'], true);
        if ($res['error'] !== '' || $res['status'] < 200 || $res['status'] >= 300 || !is_string($data['summary'] ?? null)) {
            debugging('local_aitutor: RAG summarize failed (HTTP ' . $res['status'] . '): '
                . ($res['error'] ?: shorten_text($res['body'], 300)), DEBUG_DEVELOPER);
            throw new \moodle_exception('error_rag', 'local_aitutor');
        }
        return trim($data['summary']);
    }

    /**
     * Asks the service a question about one course.
     *
     * @param int $courseid
     * @param string $question
     * @param array[] $history See query_payload().
     * @param string $instructions See query_payload().
     * @param string $summary See query_payload().
     * @return array ['answer' => string, 'references' => array[], 'rewritten' => string]
     * @throws \moodle_exception When the service cannot be reached or answers with an error.
     */
    public static function query(int $courseid, string $question, array $history = [], string $instructions = '',
            string $summary = ''): array {
        $res = self::request('POST', '/query',
            self::query_payload($courseid, $question, $history, $instructions, $summary));

        if ($res['error'] !== '' || $res['status'] < 200 || $res['status'] >= 300) {
            // Details are for the administrator (debugging), the student only sees a generic message.
            debugging('local_aitutor: RAG request failed (HTTP ' . $res['status'] . '): '
                . ($res['error'] ?: shorten_text($res['body'], 300)), DEBUG_DEVELOPER);
            throw new \moodle_exception('error_rag', 'local_aitutor');
        }

        $data = self::parse_response($res['body']);
        if ($data['answer'] === '') {
            throw new \moodle_exception('error_empty', 'local_aitutor');
        }
        return $data;
    }

    /**
     * Reads {"answer": "...", "references": [...], "rewritten": "..."|null} from a /query response body.
     *
     * @param string $body
     * @return array ['answer' => string, 'references' => array[], 'rewritten' => string]
     */
    public static function parse_response(string $body): array {
        $data = json_decode($body, true);
        if (!is_array($data)) {
            return ['answer' => '', 'references' => [], 'rewritten' => ''];
        }
        return [
            'answer' => is_string($data['answer'] ?? null) ? trim($data['answer']) : '',
            'references' => is_array($data['references'] ?? null) ? array_values($data['references']) : [],
            'rewritten' => is_string($data['rewritten'] ?? null) ? trim($data['rewritten']) : '',
        ];
    }
}
