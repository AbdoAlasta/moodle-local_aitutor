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
$PAGE->set_pagelayout('incourse');
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
// Fit the conversation to the window, so the messages scroll inside it and the page itself stays put.
// Boost scrolls the #page element, not the window, and #page also holds the footer below the tutor: the chat is
// first sized to the window, then shrunk by whatever still overflows the scroller.
$PAGE->requires->js_amd_inline("
    var root = document.getElementById('aitutor-root');
    var main = root && root.querySelector('.aitutor-main');
    var page = document.getElementById('page');
    var scroller = page && /auto|scroll/.test(getComputedStyle(page).overflowY) ? page : document.scrollingElement;
    var fit = function() {
        if (!main) {
            return;
        }
        root.style.removeProperty('--aitutor-fit');
        if (window.innerWidth < 768) {
            return; // Phones: stacked layout, the page scrolls.
        }
        scroller.scrollTop = 0;
        var height = Math.max(360, window.innerHeight - main.getBoundingClientRect().top - 16);
        root.style.setProperty('--aitutor-fit', height + 'px');
        if (window.innerWidth >= 1200) {
            // Three columns: nothing else is below the chat, so remove any remaining page scroll.
            var over = scroller.scrollHeight - scroller.clientHeight;
            if (over > 0) {
                root.style.setProperty('--aitutor-fit', Math.max(360, height - over) + 'px');
            }
        }
    };
    fit();
    window.addEventListener('resize', fit);
    window.addEventListener('load', fit);
");

$modes = \local_aitutor\local\ui::modes();

$data = [
    'courseid' => (int) $course->id,
    'coursename' => format_string($course->fullname),
    'modes' => $modes,
    'avatar' => $OUTPUT->image_url('tutor', 'local_aitutor')->out(false),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_aitutor/main', $data);
echo $OUTPUT->footer();
