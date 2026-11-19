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

namespace local_aitutor\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_aitutor\local\tutor;

/**
 * Returns the messages of one chat of the current user.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_chat extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course id'),
            'chatid' => new external_value(PARAM_INT, 'Chat id'),
        ]);
    }

    /**
     * Executes the request.
     *
     * @param int $courseid
     * @param int $chatid
     * @return array
     */
    public static function execute(int $courseid, int $chatid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid, 'chatid' => $chatid]);
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('local/aitutor:use', $context);

        $chat = tutor::get_chat_record($params['chatid'], $params['courseid'], $USER->id);
        $rows = $DB->get_records('local_aitutor_msg', ['chatid' => $chat->id], 'id ASC');
        $messages = [];
        $asked = 'text';
        $hint = 0;
        foreach ($rows as $row) {
            if ($row->role === 'user') {
                $asked = $row->type;
            } else if ($row->type === 'hint') {
                $hint = $asked === 'anotherhint' ? $hint + 1 : 1;
            }
            $messages[] = [
                'id' => (int) $row->id,
                'role' => $row->role,
                'type' => $row->type,
                'content' => $row->content,
                'cmid' => (int) $row->cmid,
                'timecreated' => (int) $row->timecreated,
                'hintlevel' => $row->type === 'hint' ? $hint : 0,
            ];
        }
        return ['chatid' => (int) $chat->id, 'title' => $chat->title, 'hintmode' => (bool) $chat->hintmode,
            'messages' => $messages];
    }

    /**
     * Return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'chatid' => new external_value(PARAM_INT, 'Chat id'),
            'title' => new external_value(PARAM_RAW, 'Title'),
            'hintmode' => new external_value(PARAM_BOOL, 'Whether hint mode is on for this chat'),
            'messages' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Message id'),
                'role' => new external_value(PARAM_ALPHA, 'user or tutor'),
                'type' => new external_value(PARAM_ALPHANUMEXT, 'Message type'),
                'content' => new external_value(PARAM_RAW, 'Text or JSON string'),
                'cmid' => new external_value(PARAM_INT, 'Resource used'),
                'timecreated' => new external_value(PARAM_INT, 'Time'),
                'hintlevel' => new external_value(PARAM_INT, 'Hint number, 0 when the message is not a hint'),
            ])),
        ]);
    }
}
