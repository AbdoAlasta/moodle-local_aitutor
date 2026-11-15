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
 * Lets an administrator test the RAG connection with a sample question (shows status, raw body and parsed answer).
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_aitutor\local\rag_client;

admin_externalpage_setup('local_aitutor_testrag', '', null, new moodle_url('/local/aitutor/testrag.php'));


$courseid = optional_param('courseid', SITEID, PARAM_INT);
$question = optional_param('question', 'Hello, can you hear me?', PARAM_TEXT);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('ragtest', 'local_aitutor'));

if (!rag_client::is_configured()) {
    echo $OUTPUT->notification(get_string('error_norag', 'local_aitutor'), 'warning');
}

if (rag_client::is_configured() && optional_param('go', 0, PARAM_INT) && confirm_sesskey()) {
    // 1. GET /health: is the service reachable at all?
    $health = rag_client::request('GET', '/health');
    $ok = $health['error'] === '' && $health['status'] >= 200 && $health['status'] < 300;
    echo html_writer::tag('h5', 'GET ' . s($health['url']));
    echo $OUTPUT->notification(get_string($ok ? 'ragtest_healthok' : 'ragtest_healthfail', 'local_aitutor',
        (object) ['status' => $health['status']]), $ok ? 'success' : 'error');
    echo html_writer::tag('pre', s($health['error'] !== '' ? $health['error'] : shorten_text($health['body'], 1000)));

    // 2. POST /query: the same request a student question sends.
    $payload = rag_client::query_payload($courseid, $question);
    $start = microtime(true);
    $res = rag_client::request('POST', '/query', $payload);
    $elapsed = round(microtime(true) - $start, 2);

    $ok = $res['error'] === '' && $res['status'] >= 200 && $res['status'] < 300;
    echo html_writer::tag('h5', 'POST ' . s($res['url']));
    echo $OUTPUT->notification(
        get_string($ok ? 'ragtest_ok' : 'ragtest_fail', 'local_aitutor', (object) ['status' => $res['status'],
            'seconds' => $elapsed]),
        $ok ? 'success' : 'error');
    if ($res['error'] !== '') {
        echo html_writer::tag('p', s($res['error']));
    }
    echo html_writer::tag('h5', get_string('ragtest_sent', 'local_aitutor'));
    echo html_writer::tag('pre', s(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    echo html_writer::tag('h5', get_string('ragtest_raw', 'local_aitutor'));
    echo html_writer::tag('pre', s(shorten_text($res['body'], 3000)));
    if ($ok) {
        echo html_writer::tag('h5', get_string('ragtest_parsed', 'local_aitutor'));
        $parsed = rag_client::parse_response($res['body'])['answer'];
        echo html_writer::tag('pre', $parsed !== '' ? s($parsed) : get_string('error_empty', 'local_aitutor'));
    }
}

echo html_writer::start_tag('form', ['method' => 'post', 'action' => $PAGE->url->out(false), 'class' => 'mt-3']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'go', 'value' => 1]);
echo html_writer::div(html_writer::tag('label', get_string('ragtest_courseid', 'local_aitutor'), ['for' => 'courseid'])
    . html_writer::empty_tag('input', ['type' => 'number', 'id' => 'courseid', 'name' => 'courseid',
        'value' => $courseid, 'class' => 'form-control', 'style' => 'max-width:10rem']), 'mb-2');
echo html_writer::div(html_writer::tag('label', get_string('ragtest_question', 'local_aitutor'), ['for' => 'question'])
    . html_writer::empty_tag('input', ['type' => 'text', 'id' => 'question', 'name' => 'question',
        'value' => $question, 'class' => 'form-control']), 'mb-2');
echo html_writer::empty_tag('input', ['type' => 'submit', 'class' => 'btn btn-primary',
    'value' => get_string('ragtest_run', 'local_aitutor')]);
echo html_writer::end_tag('form');

echo $OUTPUT->footer();
