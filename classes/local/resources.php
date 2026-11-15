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
 * Lists the course resources the student can pick as context.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class resources {
    /** @var string[] Module types offered as course context. */
    private const SUPPORTED = ['page', 'book', 'label', 'resource', 'url', 'assign', 'forum', 'folder', 'lesson', 'wiki', 'glossary'];

    /**
     * Returns the visible resources of a course.
     *
     * @param \stdClass $course
     * @return array[] list of ['id', 'name', 'modname', 'url', 'section']
     */
    public static function list_for_course(\stdClass $course): array {
        $modinfo = get_fast_modinfo($course);
        $result = [];
        foreach ($modinfo->get_cms() as $cm) {
            if (!$cm->uservisible || !in_array($cm->modname, self::SUPPORTED, true)) {
                continue;
            }
            if ($cm->modname === 'label') {
                // Labels have no name worth showing; use their text snippet.
                $name = shorten_text(trim(strip_tags((string) $cm->content)), 60);
                if ($name === '') {
                    continue;
                }
            } else {
                $name = format_string($cm->name, true, ['context' => $cm->context]);
            }
            $section = $modinfo->get_section_info($cm->sectionnum);
            $result[] = [
                'id' => (int) $cm->id,
                'name' => $name,
                'modname' => $cm->modname,
                'url' => $cm->url ? $cm->url->out(false) : '',
                'section' => get_section_name($course, $section),
            ];
        }
        return $result;
    }

    /**
     * Returns the name and URL of a course module the user can see, or null.
     *
     * @param \stdClass $course
     * @param int $cmid
     * @return array|null ['name' => string, 'url' => string]
     */
    public static function get_info(\stdClass $course, int $cmid): ?array {
        $cm = get_fast_modinfo($course)->cms[$cmid] ?? null;
        if (!$cm || !$cm->uservisible || !in_array($cm->modname, self::SUPPORTED, true)) {
            return null;
        }
        return [
            'name' => format_string($cm->name, true, ['context' => $cm->context]),
            'url' => $cm->url ? $cm->url->out(false) : '',
        ];
    }
}
