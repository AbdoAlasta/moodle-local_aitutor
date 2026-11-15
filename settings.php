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
 * Admin settings for local_aitutor.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_aitutor', get_string('pluginname', 'local_aitutor'));
    $ADMIN->add('localplugins', $settings);
    $ADMIN->add('localplugins', new admin_externalpage('local_aitutor_testrag',
        get_string('ragtest', 'local_aitutor'), new moodle_url('/local/aitutor/testrag.php'),
        'moodle/site:config', true));

    $settings->add(new admin_setting_configcheckbox('local_aitutor/enabled',
        get_string('setting_enabled', 'local_aitutor'), get_string('setting_enabled_desc', 'local_aitutor'), 1));

    $settings->add(new admin_setting_heading('local_aitutor/ragheading',
        get_string('setting_ragheading', 'local_aitutor'), get_string('setting_ragheading_desc', 'local_aitutor')));

    $settings->add(new admin_setting_configtext('local_aitutor/ragurl',
        get_string('setting_ragurl', 'local_aitutor'), get_string('setting_ragurl_desc', 'local_aitutor'),
        'http://127.0.0.1:8003', PARAM_RAW_TRIMMED));

    $settings->add(new admin_setting_configpasswordunmask('local_aitutor/ragkey',
        get_string('setting_ragkey', 'local_aitutor'), get_string('setting_ragkey_desc', 'local_aitutor'), ''));

    $settings->add(new admin_setting_description('local_aitutor/ragtest',
        get_string('ragtest', 'local_aitutor'),
        html_writer::link(new moodle_url('/local/aitutor/testrag.php'), get_string('ragtest_link', 'local_aitutor'),
            ['class' => 'btn btn-secondary'])));

    $settings->add(new admin_setting_configtext('local_aitutor/ragtimeout',
        get_string('setting_ragtimeout', 'local_aitutor'), get_string('setting_ragtimeout_desc', 'local_aitutor'),
        60, PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('local_aitutor/demomode',
        get_string('setting_demo', 'local_aitutor'), get_string('setting_demo_desc', 'local_aitutor'), 0));
}
