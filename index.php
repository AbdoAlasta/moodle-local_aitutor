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
 * AI Tutor page inside a course. Every course has its own tutor (its own chats, resources and context).
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
require_login($course);

$context = context_course::instance($course->id);
require_capability('local/aitutor:use', $context);

if (!get_config('local_aitutor', 'enabled')) {
    throw new moodle_exception('disabled', 'local_aitutor');
}

$PAGE->set_url('/local/aitutor/index.php', ['id' => $course->id]);
$PAGE->set_context($context);
// Same layout as the Grades tab: the normal Moodle header, course navigation and tabs stay around the tutor.
$PAGE->set_pagelayout('report');
$PAGE->set_secondary_active_tab('localaitutor');
$PAGE->set_title(get_string('pluginname', 'local_aitutor') . ': ' . format_string($course->shortname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->add_body_class('local-aitutor-page');

$config = [
    'courseid' => (int) $course->id,
    'coursename' => format_string($course->fullname),
    'strings' => \local_aitutor\local\ui::strings(),
    'rtl' => right_to_left(),
];

$PAGE->requires->js_call_amd('local_aitutor/app', 'init', [$config]);
// Fit the tutor card to the window on desktop, so the messages and Recents scroll inside it and the page itself
// stays put. Boost scrolls the #page element, not the window, and #page also holds the footer below the tutor: the
// card is first sized to the window, then shrunk by whatever still overflows the scroller. It is fitted again when
// the window is resized or the card's width changes (course index drawer opened or closed).
$PAGE->requires->js_amd_inline("
    var root = document.getElementById('aitutor-root');
    var page = document.getElementById('page');
    var scroller = page && /auto|scroll/.test(getComputedStyle(page).overflowY) ? page : document.scrollingElement;
    var fit = function() {
        if (!root) {
            return;
        }
        root.style.removeProperty('--aitutor-fit');
        if (window.innerWidth < 768) {
            return; // Phones: stacked layout, the page scrolls.
        }
        scroller.scrollTop = 0;
        var gap = parseFloat(getComputedStyle(root).marginBottom) || 0;
        var height = Math.max(360, window.innerHeight - root.getBoundingClientRect().top - gap);
        root.style.setProperty('--aitutor-fit', height + 'px');
        var over = scroller.scrollHeight - scroller.clientHeight;
        if (over > 0) {
            root.style.setProperty('--aitutor-fit', Math.max(360, height - over) + 'px');
        }
    };
    fit();
    window.addEventListener('resize', fit);
    window.addEventListener('load', fit);
    if (root && window.ResizeObserver) {
        var width = root.offsetWidth;
        new ResizeObserver(function() {
            if (root.offsetWidth !== width) {
                width = root.offsetWidth;
                fit();
            }
        }).observe(root);
    }
");

$modes = \local_aitutor\local\ui::modes();

$data = [
    'courseid' => (int) $course->id,
    'coursename' => format_string($course->fullname),
    'modes' => $modes,
    'avatar' => $OUTPUT->image_url('tutor', 'local_aitutor')->out(false),
    'courseurl' => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
    'username' => fullname($USER),
    'initials' => core_user::get_initials($USER),
    'courseshort' => format_string($course->shortname),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_aitutor/main', $data);
echo $OUTPUT->footer();
