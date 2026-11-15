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

/**
 * Lists the recent chats of the current user in a course.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_chats extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course id'),
            'limit' => new external_value(PARAM_INT, 'Max chats', VALUE_DEFAULT, 8),
        ]);
    }

    /**
     * Executes the request.
     *
     * @param int $courseid
     * @param int $limit
     * @return array
     */
    public static function execute(int $courseid, int $limit = 8): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid, 'limit' => $limit]);
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('local/aitutor:use', $context);

        $limit = max(1, min(30, $params['limit']));
        $rows = $DB->get_records('local_aitutor_chat', ['courseid' => $params['courseid'], 'userid' => $USER->id],
            'timemodified DESC, id DESC', 'id, title, timemodified', 0, $limit);
        $out = [];
        foreach ($rows as $row) {
            $out[] = ['id' => (int) $row->id, 'title' => $row->title, 'timemodified' => (int) $row->timemodified];
        }
        return $out;
    }

    /**
     * Return structure.
     *
     * @return external_multiple_structure
     */
    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Chat id'),
            'title' => new external_value(PARAM_RAW, 'Title'),
            'timemodified' => new external_value(PARAM_INT, 'Last activity'),
        ]));
    }
}
