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

namespace local_aitutor;

use core\hook\navigation\secondary_extend;

/**
 * Hook callbacks for local_aitutor.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Adds the "AI Tutor" tab to the course secondary navigation.
     *
     * @param secondary_extend $hook
     */
    public static function secondary_extend(secondary_extend $hook): void {
        global $PAGE;

        if (!get_config('local_aitutor', 'enabled')) {
            return;
        }
        $course = $PAGE->course;
        if (empty($course->id) || (int) $course->id === SITEID) {
            return;
        }
        // Only on course level pages (course, participants, grades...), not on activity or user pages.
        if (!empty($PAGE->cm) || $PAGE->context->contextlevel !== CONTEXT_COURSE) {
            return;
        }
        $context = \context_course::instance($course->id);
        if (!has_capability('local/aitutor:use', $context)) {
            return;
        }

        $url = new \moodle_url('/local/aitutor/index.php', ['id' => $course->id]);
        $node = \navigation_node::create(
            get_string('tabname', 'local_aitutor'),
            $url,
            \navigation_node::TYPE_CUSTOM,
            null,
            'localaitutor',
            new \pix_icon('i/course', '')
        );
        $node->showinsecondarynavigation = true;
        $hook->get_secondaryview()->add_node($node);

        if ($PAGE->has_set_url() && $PAGE->url->compare(new \moodle_url('/local/aitutor/index.php'), URL_MATCH_BASE)) {
            $node->make_active();
        }
    }
}
