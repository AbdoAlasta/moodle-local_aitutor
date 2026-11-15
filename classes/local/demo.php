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
 * Canned replies used when no RAG endpoint is configured and demo mode is on (for UI testing only).
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class demo {
    /**
     * Returns a canned reply (JSON text for structured modes).
     *
     * @param string $mode
     * @param string $topic Resource or course name.
     * @return string
     */
    public static function generate(string $mode, string $topic): string {
        switch ($mode) {
            case 'topics':
                return json_encode(['topics' => [
                    "Explain the main idea of {$topic}", "Summarize {$topic}", 'Create practice questions',
                    'Generate quiz', 'Make me a study plan', 'What are the key takeaways from this course?',
                ]]);
            case 'studyplan':
                return json_encode(['title' => '6-Week Study Plan', 'weeks' => [
                    ['label' => 'Week 1', 'topics' => 'Introduction and core concepts'],
                    ['label' => 'Week 2', 'topics' => 'Requirements and analysis'],
                    ['label' => 'Week 3', 'topics' => 'Design'],
                    ['label' => 'Week 4', 'topics' => 'Implementation and testing'],
                    ['label' => 'Week 5', 'topics' => 'Deployment and maintenance'],
                    ['label' => 'Week 6', 'topics' => 'Revision and mini-project'],
                ], 'includes' => ['Weekly topics', 'Key resources', 'Practice questions', 'Milestones / goals']]);
            case 'quiz':
                return json_encode(['title' => "Quiz: {$topic}", 'questions' => [
                    ['question' => "Which option best describes the main goal of {$topic}?",
                        'options' => ['A structured way to learn the topic', 'A hardware component', 'A database engine',
                            'A testing tool'], 'answer' => 0, 'explanation' => 'Demo question: replace with real AI output.'],
                    ['question' => 'Which activity is a good way to revise?',
                        'options' => ['Practice questions', 'Skipping lectures', 'Ignoring feedback', 'Memorising nothing'],
                        'answer' => 0, 'explanation' => 'Active recall improves retention.'],
                ]]);
            case 'flashcards':
                return json_encode(['title' => "Flashcards: {$topic}", 'cards' => [
                    ['front' => "What is {$topic} about?", 'back' => 'Demo answer: connect the RAG service for real content.'],
                    ['front' => 'Why does documentation matter?', 'back' => 'It keeps knowledge shared and maintainable.'],
                    ['front' => 'What is active recall?', 'back' => 'Retrieving information from memory without looking.'],
                ]]);
            case 'summary':
                return json_encode(['title' => "{$topic} - Summary", 'bullets' => [
                    'Demo mode is on: this is a canned summary.', 'Set the RAG endpoint under Site administration > Plugins > Local plugins > AI Tutor.',
                    'Then summaries are generated from the real resource text.',
                ]]);
            default:
                return "This is a **demo reply**. The RAG service is not configured yet.\n\n- Go to *Site administration > Plugins > Local plugins > AI Tutor*\n- Set the *RAG endpoint URL*";
        }
    }
}
