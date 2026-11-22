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
 * Strings for component 'local_aitutor', language 'en'.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['aitutor:use'] = 'Use the AI Tutor in a course';
$string['askplaceholder'] = 'Ask AI Tutor';
$string['assignmenthelp_intro'] = 'Paste your assignment brief and tell me where you are stuck. I will give hints and explain the concepts rather than write it for you.';
$string['cachedef_ratelimit'] = 'Recent question times per user (rate limit)';
$string['cardhint'] = 'Tap a card to flip it';
$string['chip_main'] = 'Explain the main idea of {$a}';
$string['chip_plan'] = 'Make me a study plan';
$string['chip_practice'] = 'Create practice questions';
$string['chip_project'] = 'Help me with the project ideas';
$string['chip_quiz'] = 'Generate quiz';
$string['chip_summarize'] = 'Summarize {$a}';
$string['chip_takeaways'] = 'What are the key takeaways from this course?';
$string['conversation'] = 'Conversation';
$string['copy'] = 'Copy';
$string['correct'] = 'Correct!';
$string['dictate'] = 'Dictate';
$string['disabled'] = 'The AI Tutor is disabled on this site.';
$string['downloadsummary'] = 'Download summary';
$string['error_empty'] = 'The AI returned an empty answer. Please try again.';
$string['error_generic'] = 'Something went wrong. Please try again.';
$string['error_hintrefused'] = 'This hint is no longer available. Ask a new question to continue.';
$string['error_norag'] = 'The AI Tutor service is not configured. Ask your administrator to set the RAG endpoint under Site administration > Plugins > Local plugins > AI Tutor.';
$string['error_rag'] = 'The AI Tutor service is not available right now. Please try again in a moment.';
$string['error_ratelimited'] = 'You have reached the limit of {$a} questions per hour. Please try again later.';
$string['explanation'] = 'Explanation';
$string['flashcard'] = 'Card {$a->current} of {$a->total}';
$string['gptdisclaimer'] = 'The AI Tutor can make mistakes. Check important information.';
$string['hintanother'] = 'Another hint';
$string['hintbadge'] = 'Hint {$a->level} of {$a->total}';
$string['hintmode'] = 'Hint mode';
$string['hintreveal'] = 'Show full answer';
$string['includes'] = 'Includes:';
$string['incorrect'] = 'Not quite.';
$string['listening'] = 'Listening...';
$string['micdenied'] = 'Microphone access is blocked. Allow it from the address bar, then try again.';
$string['micunsupported'] = 'Voice input is not supported in this browser. Please use Chrome, Edge or Safari.';
$string['nav_assignmentshelp'] = 'Assignments Help';
$string['nav_courseresources'] = 'Course Resources';
$string['nav_newchat'] = 'New Chat';
$string['nav_practicequiz'] = 'Practice Quiz';
$string['nav_revisionmode'] = 'Revision Mode';
$string['nav_studyplan'] = 'Study Plan';
$string['nav_suggestedtopics'] = 'Suggested Topics';
$string['newchat'] = 'New Chat';
$string['nextquestion'] = 'Next Question';
$string['nochatsfound'] = 'No chats found.';
$string['pluginname'] = 'AI Tutor';
$string['privacy:metadata:chat'] = 'Conversations a user had with the AI Tutor in a course.';
$string['privacy:metadata:chat:courseid'] = 'The course of the conversation.';
$string['privacy:metadata:chat:hintmode'] = 'Whether hint mode is on for the conversation.';
$string['privacy:metadata:chat:summary'] = 'A short running summary of the older messages of the conversation, used as its memory.';
$string['privacy:metadata:chat:timecreated'] = 'When the conversation started.';
$string['privacy:metadata:chat:timemodified'] = 'When the conversation last changed.';
$string['privacy:metadata:chat:title'] = 'The conversation title (the first question).';
$string['privacy:metadata:chat:userid'] = 'The user who owns the conversation.';
$string['privacy:metadata:msg'] = 'Messages exchanged in the conversations.';
$string['privacy:metadata:msg:content'] = 'The message content.';
$string['privacy:metadata:msg:role'] = 'Whether the message was written by the user or the tutor.';
$string['privacy:metadata:msg:timecreated'] = 'When the message was written.';
$string['privacy:metadata:rag'] = 'Questions are sent to the course RAG service configured by the administrator, together with the course id, the most recent exchanges of the conversation and its running summary.';
$string['privacy:metadata:rag:prompt'] = 'The student question (with the name of the selected resource and the instructions of the chosen action).';
$string['prompt_anotherhint'] = 'Give me another hint';
$string['prompt_assignment'] = 'Help me with my assignment';
$string['prompt_chat'] = 'Hello';
$string['prompt_flashcards'] = 'Create flashcards';
$string['prompt_quiz'] = 'Generate a practice quiz';
$string['prompt_reveal'] = 'Show me the full answer';
$string['prompt_studyplan'] = 'Make me a study plan';
$string['prompt_summary'] = 'Summarize this resource';
$string['prompt_topics'] = 'Suggest topics I can ask about';
$string['qa_flashcards'] = 'Create flashcards';
$string['qa_quiz'] = 'Generate Quiz';
$string['qa_summarizecourse'] = 'Summarize course';
$string['questionof'] = 'Question {$a->current} of {$a->total}';
$string['quickactions'] = 'Quick Actions';
$string['ragtest'] = 'Test RAG connection';
$string['ragtest_courseid'] = 'Course id';
$string['ragtest_fail'] = 'The RAG service did not answer correctly (HTTP {$a->status}, {$a->seconds} s).';
$string['ragtest_healthfail'] = 'The service health check failed (HTTP {$a->status}). Check the URL, that the service is running, and the cURL security settings.';
$string['ragtest_healthok'] = 'The service is reachable (HTTP {$a->status}).';
$string['ragtest_link'] = 'Test the connection';
$string['ragtest_ok'] = 'The RAG service answered (HTTP {$a->status}, {$a->seconds} s).';
$string['ragtest_parsed'] = 'Answer the plugin would show';
$string['ragtest_question'] = 'Question';
$string['ragtest_raw'] = 'Raw response';
$string['ragtest_run'] = 'Send test request';
$string['ragtest_sent'] = 'Request sent';
$string['recentchats'] = 'Recent Chats';
$string['recentchats_empty'] = 'No chats yet.';
$string['recents'] = 'Recents';
$string['restart'] = 'Try again';
$string['retry'] = 'Try again';
$string['revision_intro'] = 'Tap a card to reveal the answer!';
$string['scoreis'] = 'You scored {$a->score} out of {$a->total}.';
$string['search'] = 'Search chats';
$string['searchplaceholder'] = 'Search chats...';
$string['seeresults'] = 'See results';
$string['send'] = 'Send';
$string['setting_demo'] = 'Demo mode';
$string['setting_demo_desc'] = 'When no RAG endpoint is set, reply with canned content so the interface can be tested. Do not enable on production sites.';
$string['setting_enabled'] = 'Enable AI Tutor';
$string['setting_enabled_desc'] = 'Show the AI Tutor tab in courses. Every course gets its own tutor, which only uses that course\'s material and keeps that course\'s chats.';
$string['setting_maxquestionsperhour'] = 'Questions per user per hour';
$string['setting_maxquestionsperhour_desc'] = 'Each question or action calls the RAG service, which costs Gemini calls. A user who reaches the limit is asked to wait. 0 means no limit.';
$string['setting_ragheading'] = 'Course RAG service';
$string['setting_ragheading_desc'] = 'The AI Tutor talks only to your course RAG service (the same one local_ragindex indexes the course files into); Moodle AI providers are not used. Each question is sent to POST /query with the course id, so answers come only from that course\'s files. Press the test button after saving.';
$string['setting_ragkey'] = 'RAG API key';
$string['setting_ragkey_desc'] = 'The service\'s SERVICE_API_KEY, sent in the X-API-Key header.';
$string['setting_ragtimeout'] = 'RAG timeout (seconds)';
$string['setting_ragtimeout_desc'] = 'How long to wait for an answer (5 to 300).';
$string['setting_ragurl'] = 'RAG service URL';
$string['setting_ragurl_desc'] = 'Base URL of the service, without /query, for example http://127.0.0.1:8003. Use the same service as local_ragindex. It may run on this server or a private network: Moodle\'s cURL blocked hosts list is not applied to this URL, so keep localhost blocked there.';
$string['showless'] = 'Show less';
$string['sourcepage'] = 'page {$a}';
$string['sources'] = 'Sources';
$string['startrevision'] = 'Start revision with flashcards';
$string['stoprec'] = 'Stop';
$string['tabname'] = 'AI Tutor';
$string['tapreveal'] = 'Tap to reveal';
$string['thinking'] = 'Thinking...';
$string['togglesidebar'] = 'Show or hide the sidebar';
$string['tools'] = 'Tools';
$string['topics_intro'] = 'Here are some things you can ask about:';
$string['tryasking'] = 'Try asking:';
$string['tutorname'] = 'AI Tutor';
$string['understoodas'] = 'Understood as: {$a}';
$string['useresource'] = 'Use this resource in responses';
$string['viewall'] = 'View all';
$string['vieworiginal'] = 'View original';
$string['voicesend'] = 'Record a voice message';
$string['welcome'] = 'Hi! I\'m your AI Tutor for {$a}. I can help you understand concepts, summarize lectures, explain code, and plan your revision.';
$string['welcomehero'] = 'What would you like to learn today?';
$string['yesterday'] = 'Yesterday';
$string['you'] = 'You';
