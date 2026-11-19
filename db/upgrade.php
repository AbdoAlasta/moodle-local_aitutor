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
 * Upgrade steps for local_aitutor.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for local_aitutor.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_aitutor_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2026102801) {
        // The sidebar panel was removed: every course only has its own AI Tutor tab now.
        unset_config('drawerenabled', 'local_aitutor');
        unset_config('drawerbutton', 'local_aitutor');
        upgrade_plugin_savepoint(true, 2026102801, 'local', 'aitutor');
    }
    if ($oldversion < 2026102901) {
        // Moodle AI providers are no longer used: the external RAG service is the only backend. New settings
        // (auth header, request format, question and response field) have defaults, nothing to migrate.
        upgrade_plugin_savepoint(true, 2026102901, 'local', 'aitutor');
    }
    if ($oldversion < 2026102902) {
        // The plugin now speaks the course RAG services' own API (POST /query with question and course_id, X-API-Key):
        // the options that adapted it to other services, and the prompt/context options, are gone.
        foreach (['ragauthheader', 'ragpayload', 'ragquestionfield', 'ragresponsefield', 'systemprompt', 'maxcontextchars',
                'maxhistory', 'pdftotextpath'] as $name) {
            unset_config($name, 'local_aitutor');
        }
        // The URL is now the service base; drop a trailing /query stored by older versions.
        $url = rtrim(trim((string) get_config('local_aitutor', 'ragurl')), '/');
        if ($url !== '') {
            set_config('ragurl', preg_replace('~/query$~i', '', $url), 'local_aitutor');
        }
        upgrade_plugin_savepoint(true, 2026102902, 'local', 'aitutor');
    }
    if ($oldversion < 2026111600) {
        // New per-user hourly question limit: switch it on for existing sites too.
        if (get_config('local_aitutor', 'maxquestionsperhour') === false) {
            set_config('maxquestionsperhour', 30, 'local_aitutor');
        }
        upgrade_plugin_savepoint(true, 2026111600, 'local', 'aitutor');
    }
    if ($oldversion < 2026111601) {
        // Chat memory: each chat keeps a running summary of its older messages.
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_aitutor_chat');
        $fields = [
            new xmldb_field('summary', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timemodified'),
            new xmldb_field('summarizedupto', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'summary'),
        ];
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_plugin_savepoint(true, 2026111601, 'local', 'aitutor');
    }
    if ($oldversion < 2026111800) {
        // Hint mode: each chat remembers the student's toggle.
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_aitutor_chat');
        $field = new xmldb_field('hintmode', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'summarizedupto');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2026111800, 'local', 'aitutor');
    }
    return true;
}
