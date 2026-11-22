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
 * Shared UI definitions (navigation modes and JavaScript string ids).
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aitutor\local;

/**
 * Definitions used by the AI Tutor page.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ui {
    /**
     * The navigation modes shown to the student.
     *
     * @return array[] list of ['mode', 'label', 'icon']
     */
    public static function modes(): array {
        $modes = [];
        foreach ([
            ['newchat', 'chat', 'i/new'],
            ['suggestedtopics', 'topics', 'i/info'],
            ['courseresources', 'resources', 'i/folder'],
            ['studyplan', 'studyplan', 'i/calendar'],
            ['practicequiz', 'quiz', 'i/questions'],
            ['assignmentshelp', 'assignment', 'i/edit'],
            ['revisionmode', 'revision', 'i/reload'],
        ] as [$label, $mode, $icon]) {
            $modes[] = [
                'mode' => $mode,
                'label' => get_string('nav_' . $label, 'local_aitutor'),
                'icon' => $icon,
            ];
        }
        return $modes;
    }

    /**
     * Language string ids used by the JavaScript app.
     *
     * @return string[]
     */
    public static function string_ids(): array {
        return [
            'welcome', 'tryasking', 'thinking', 'error_generic', 'you', 'tutorname', 'recentchats_empty',
            'nextquestion', 'seeresults', 'correct', 'incorrect', 'explanation', 'scoreis', 'tapreveal',
            'startrevision', 'vieworiginal', 'downloadsummary', 'includes', 'assignmenthelp_intro', 'topics_intro',
            'revision_intro', 'chip_main', 'chip_summarize', 'chip_practice', 'chip_quiz', 'chip_plan',
            'chip_takeaways', 'newchat', 'retry', 'flashcard', 'restart', 'cardhint', 'questionof', 'prompt_flashcards',
            'prompt_quiz', 'prompt_studyplan', 'chip_project', 'viewall', 'showless', 'yesterday', 'hintbadge',
            'hintanother', 'hintreveal', 'prompt_anotherhint', 'prompt_reveal', 'copy', 'send', 'dictate', 'voicesend',
            'stoprec', 'listening', 'micdenied', 'micunsupported', 'nochatsfound', 'tools', 'hintmode',
        ];
    }

    /**
     * Resolves the language strings listed by {@see self::string_ids()}.
     *
     * @return string[] id => text
     */
    public static function strings(): array {
        $strings = [];
        foreach (self::string_ids() as $id) {
            $strings[$id] = get_string($id, 'local_aitutor');
        }
        return $strings;
    }
}
