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
 * Web service functions for local_aitutor.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_aitutor_send_message' => [
        'classname' => \local_aitutor\external\send_message::class,
        'description' => 'Send a message (or run a tutor action) and get the tutor reply.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/aitutor:use',
    ],
    'local_aitutor_get_resources' => [
        'classname' => \local_aitutor\external\get_resources::class,
        'description' => 'List the course resources the tutor can use as context.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/aitutor:use',
    ],
    'local_aitutor_get_chats' => [
        'classname' => \local_aitutor\external\get_chats::class,
        'description' => 'List the recent chats of the current user in a course.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/aitutor:use',
    ],
    'local_aitutor_get_chat' => [
        'classname' => \local_aitutor\external\get_chat::class,
        'description' => 'Get the messages of one chat.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/aitutor:use',
    ],
];
