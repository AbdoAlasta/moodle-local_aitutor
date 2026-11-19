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

namespace local_aitutor\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for local_aitutor.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\core_userlist_provider,
        \core_privacy\local\request\plugin\provider {

    /**
     * Describes the stored data.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_aitutor_chat', [
            'courseid' => 'privacy:metadata:chat:courseid',
            'userid' => 'privacy:metadata:chat:userid',
            'title' => 'privacy:metadata:chat:title',
            'timecreated' => 'privacy:metadata:chat:timecreated',
            'timemodified' => 'privacy:metadata:chat:timemodified',
            'summary' => 'privacy:metadata:chat:summary',
            'hintmode' => 'privacy:metadata:chat:hintmode',
        ], 'privacy:metadata:chat');
        $collection->add_database_table('local_aitutor_msg', [
            'role' => 'privacy:metadata:msg:role',
            'content' => 'privacy:metadata:msg:content',
            'timecreated' => 'privacy:metadata:msg:timecreated',
        ], 'privacy:metadata:msg');
        $collection->add_external_location_link('local_aitutor_rag', [
            'prompt' => 'privacy:metadata:rag:prompt',
        ], 'privacy:metadata:rag');
        return $collection;
    }

    /**
     * Course contexts that hold data for the user.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $contextlist->add_from_sql(
            "SELECT ctx.id
               FROM {context} ctx
               JOIN {local_aitutor_chat} c ON c.courseid = ctx.instanceid AND ctx.contextlevel = :level
              WHERE c.userid = :userid",
            ['level' => CONTEXT_COURSE, 'userid' => $userid]
        );
        return $contextlist;
    }

    /**
     * Users with data in a context.
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_aitutor_chat} WHERE courseid = :courseid',
            ['courseid' => $context->instanceid]);
    }

    /**
     * Exports the user's chats.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_COURSE) {
                continue;
            }
            $chats = $DB->get_records('local_aitutor_chat', ['courseid' => $context->instanceid, 'userid' => $userid]);
            $data = [];
            foreach ($chats as $chat) {
                $messages = [];
                foreach ($DB->get_records('local_aitutor_msg', ['chatid' => $chat->id], 'id ASC') as $m) {
                    $messages[] = [
                        'role' => $m->role,
                        'type' => $m->type,
                        'content' => $m->content,
                        'time' => \core_privacy\local\request\transform::datetime($m->timecreated),
                    ];
                }
                $data[] = [
                    'title' => $chat->title,
                    'created' => \core_privacy\local\request\transform::datetime($chat->timecreated),
                    'summary' => (string) ($chat->summary ?? ''),
                    'hintmode' => \core_privacy\local\request\transform::yesno($chat->hintmode),
                    'messages' => $messages,
                ];
            }
            if ($data) {
                writer::with_context($context)->export_data([get_string('pluginname', 'local_aitutor')], (object) ['chats' => $data]);
            }
        }
    }

    /**
     * Deletes all data in a context.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;
        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }
        $ids = $DB->get_fieldset_select('local_aitutor_chat', 'id', 'courseid = ?', [$context->instanceid]);
        self::delete_chats($ids);
    }

    /**
     * Deletes a user's data in the approved contexts.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_COURSE) {
                continue;
            }
            $ids = $DB->get_fieldset_select('local_aitutor_chat', 'id', 'courseid = ? AND userid = ?',
                [$context->instanceid, $userid]);
            self::delete_chats($ids);
        }
    }

    /**
     * Deletes data of several users in a context.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_COURSE || !$userlist->get_userids()) {
            return;
        }
        [$insql, $inparams] = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $ids = $DB->get_fieldset_select('local_aitutor_chat', 'id', "courseid = :courseid AND userid $insql",
            ['courseid' => $context->instanceid] + $inparams);
        self::delete_chats($ids);
    }

    /**
     * Deletes chats and their messages.
     *
     * @param int[] $chatids
     */
    private static function delete_chats(array $chatids): void {
        global $DB;
        if (!$chatids) {
            return;
        }
        [$insql, $params] = $DB->get_in_or_equal($chatids);
        $DB->delete_records_select('local_aitutor_msg', "chatid $insql", $params);
        $DB->delete_records_select('local_aitutor_chat', "id $insql", $params);
    }
}
