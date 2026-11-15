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
use core_external\external_single_structure;
use core_external\external_value;
use local_aitutor\local\tutor;

/**
 * Sends a message / runs a tutor action and returns the tutor reply.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_message extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course id'),
            'chatid' => new external_value(PARAM_INT, 'Chat id, 0 to start a new chat', VALUE_DEFAULT, 0),
            'mode' => new external_value(PARAM_ALPHA, 'chat, assignment, topics, studyplan, quiz, flashcards or summary',
                VALUE_DEFAULT, 'chat'),
            'message' => new external_value(PARAM_RAW, 'Student message (may be empty for action modes)', VALUE_DEFAULT, ''),
            'cmid' => new external_value(PARAM_INT, 'Selected resource (course module id), 0 for none', VALUE_DEFAULT, 0),
            'useresource' => new external_value(PARAM_BOOL, 'Use the selected resource as context', VALUE_DEFAULT, true),
        ]);
    }

    /**
     * Executes the request.
     *
     * @param int $courseid
     * @param int $chatid
     * @param string $mode
     * @param string $message
     * @param int $cmid
     * @param bool $useresource
     * @return array
     */
    public static function execute(int $courseid, int $chatid = 0, string $mode = 'chat', string $message = '',
            int $cmid = 0, bool $useresource = true): array {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid, 'chatid' => $chatid, 'mode' => $mode, 'message' => $message,
            'cmid' => $cmid, 'useresource' => $useresource,
        ]);

        if (!get_config('local_aitutor', 'enabled')) {
            throw new \moodle_exception('disabled', 'local_aitutor');
        }
        $course = get_course($params['courseid']);
        $context = \context_course::instance($course->id);
        self::validate_context($context);
        require_capability('local/aitutor:use', $context);

        // The RAG service can be slow: allow its timeout plus some margin for saving the reply.
        \core_php_time_limit::raise(max(120, ((int) get_config('local_aitutor', 'ragtimeout') ?: 60) + 30));

        $result = tutor::handle($course, $USER, $params['chatid'], $params['mode'], $params['message'],
            $params['cmid'], $params['useresource']);
        return $result;
    }

    /**
     * Return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'chatid' => new external_value(PARAM_INT, 'Chat id'),
            'title' => new external_value(PARAM_RAW, 'Chat title'),
            'message' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Message id'),
                'role' => new external_value(PARAM_ALPHA, 'tutor'),
                'type' => new external_value(PARAM_ALPHANUMEXT, 'text, error, topics, studyplan, quiz, flashcards, summary'),
                'content' => new external_value(PARAM_RAW, 'Text or JSON string'),
                'cmid' => new external_value(PARAM_INT, 'Resource used'),
                'timecreated' => new external_value(PARAM_INT, 'Time'),
                'resourceurl' => new external_value(PARAM_RAW, 'URL of the resource used'),
                'resourcename' => new external_value(PARAM_RAW, 'Name of the resource used'),
            ]),
        ]);
    }
}
