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
 * Strings for component 'local_aitutor', language 'ar'.
 *
 * @package    local_aitutor
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['aitutor:use'] = 'استخدام المعلّم الذكي في المقرر';
$string['askplaceholder'] = 'اكتب سؤالك...';
$string['assignmenthelp_intro'] = 'الصق نص الواجب وأخبرني أين تعثّرت. سأعطيك تلميحات وأشرح المفاهيم بدلاً من كتابة الحل لك.';
$string['cachedef_ratelimit'] = 'أوقات الأسئلة الأخيرة لكل مستخدم (حد الأسئلة)';
$string['cardhint'] = 'اضغط على البطاقة لقلبها';
$string['chip_main'] = 'اشرح الفكرة الرئيسية في {$a}';
$string['chip_plan'] = 'اعمل لي خطة مذاكرة';
$string['chip_practice'] = 'أنشئ أسئلة تدريبية';
$string['chip_project'] = 'ساعدني في أفكار المشروع';
$string['chip_quiz'] = 'أنشئ اختباراً';
$string['chip_summarize'] = 'لخّص {$a}';
$string['chip_takeaways'] = 'ما أهم النقاط المستفادة من هذا المقرر؟';
$string['conversation'] = 'المحادثة';
$string['copy'] = 'نسخ';
$string['correct'] = 'إجابة صحيحة!';
$string['dictate'] = 'إملاء صوتي';
$string['disabled'] = 'المعلّم الذكي معطّل في هذا الموقع.';
$string['downloadsummary'] = 'تنزيل الملخص';
$string['error_empty'] = 'أعاد الذكاء الاصطناعي إجابة فارغة. حاول مرة أخرى.';
$string['error_generic'] = 'حدث خطأ ما. حاول مرة أخرى.';
$string['error_hintrefused'] = 'لم يعد هذا التلميح متاحاً. اطرح سؤالاً جديداً للمتابعة.';
$string['error_norag'] = 'خدمة المعلّم الذكي غير مُعدّة. اطلب من المسؤول تحديد عنوان خدمة RAG من إدارة الموقع > الإضافات > الإضافات المحلية > AI Tutor.';
$string['error_rag'] = 'خدمة المعلّم الذكي غير متاحة حالياً. حاول مرة أخرى بعد قليل.';
$string['error_ratelimited'] = 'وصلت إلى الحد المسموح وهو {$a} سؤالاً في الساعة. حاول مرة أخرى لاحقاً.';
$string['explanation'] = 'الشرح';
$string['flashcard'] = 'بطاقة {$a->current} من {$a->total}';
$string['gptdisclaimer'] = 'قد يرتكب المعلّم الذكي أخطاء. تحقّق من المعلومات المهمة.';
$string['hintanother'] = 'تلميح آخر';
$string['hintbadge'] = 'تلميح {$a->level} من {$a->total}';
$string['hintmode'] = 'وضع التلميحات';
$string['hintreveal'] = 'إظهار الإجابة كاملة';
$string['includes'] = 'تشمل:';
$string['incorrect'] = 'ليست الإجابة الصحيحة.';
$string['listening'] = 'جارٍ الاستماع...';
$string['micdenied'] = 'تم حظر الوصول إلى الميكروفون. اسمح به من شريط العنوان ثم حاول مرة أخرى.';
$string['micunsupported'] = 'الإدخال الصوتي غير مدعوم في هذا المتصفح. استخدم Chrome أو Edge أو Safari.';
$string['nav_assignmentshelp'] = 'مساعدة في الواجبات';
$string['nav_courseresources'] = 'موارد المقرر';
$string['nav_newchat'] = 'محادثة جديدة';
$string['nav_practicequiz'] = 'اختبار تدريبي';
$string['nav_revisionmode'] = 'وضع المراجعة';
$string['nav_studyplan'] = 'خطة المذاكرة';
$string['nav_suggestedtopics'] = 'مواضيع مقترحة';
$string['newchat'] = 'محادثة جديدة';
$string['nextquestion'] = 'السؤال التالي';
$string['nochatsfound'] = 'لا توجد محادثات مطابقة.';
$string['pluginname'] = 'المعلّم الذكي';
$string['privacy:metadata:chat'] = 'Conversations a user had with the AI Tutor in a course.';
$string['privacy:metadata:chat:courseid'] = 'The course of the conversation.';
$string['privacy:metadata:chat:hintmode'] = 'ما إذا كان وضع التلميحات مفعّلاً في المحادثة.';
$string['privacy:metadata:chat:summary'] = 'ملخص قصير متجدد للرسائل الأقدم في المحادثة، يُستخدم كذاكرة لها.';
$string['privacy:metadata:chat:timecreated'] = 'When the conversation started.';
$string['privacy:metadata:chat:timemodified'] = 'When the conversation last changed.';
$string['privacy:metadata:chat:title'] = 'The conversation title (the first question).';
$string['privacy:metadata:chat:userid'] = 'The user who owns the conversation.';
$string['privacy:metadata:msg'] = 'Messages exchanged in the conversations.';
$string['privacy:metadata:msg:content'] = 'The message content.';
$string['privacy:metadata:msg:role'] = 'Whether the message was written by the user or the tutor.';
$string['privacy:metadata:msg:timecreated'] = 'When the message was written.';
$string['privacy:metadata:rag'] = 'تُرسل الأسئلة مع رقم المقرر وآخر رسائل المحادثة وملخصها إلى خدمة RAG الخاصة بالمقررات التي يحددها المسؤول.';
$string['privacy:metadata:rag:prompt'] = 'سؤال الطالب (مع اسم المورد المحدد وتعليمات الإجراء المختار).';
$string['prompt_anotherhint'] = 'أعطني تلميحاً آخر';
$string['prompt_assignment'] = 'ساعدني في واجبي';
$string['prompt_chat'] = 'مرحباً';
$string['prompt_flashcards'] = 'أنشئ بطاقات مراجعة';
$string['prompt_quiz'] = 'أنشئ اختباراً تدريبياً';
$string['prompt_reveal'] = 'أظهر لي الإجابة كاملة';
$string['prompt_studyplan'] = 'اعمل لي خطة مذاكرة';
$string['prompt_summary'] = 'لخّص هذا المورد';
$string['prompt_topics'] = 'اقترح مواضيع يمكنني السؤال عنها';
$string['qa_flashcards'] = 'إنشاء بطاقات';
$string['qa_quiz'] = 'إنشاء اختبار';
$string['qa_summarizecourse'] = 'تلخيص المقرر';
$string['questionof'] = 'السؤال {$a->current} من {$a->total}';
$string['quickactions'] = 'إجراءات سريعة';
$string['ragtest'] = 'اختبار الاتصال بخدمة RAG';
$string['ragtest_courseid'] = 'رقم المقرر';
$string['ragtest_fail'] = 'لم ترد خدمة RAG بشكل صحيح (HTTP {$a->status}، {$a->seconds} ث).';
$string['ragtest_healthfail'] = 'فشل فحص حالة الخدمة (HTTP {$a->status}). تأكد من العنوان وأن الخدمة تعمل ومن إعدادات أمان cURL.';
$string['ragtest_healthok'] = 'الخدمة متاحة (HTTP {$a->status}).';
$string['ragtest_link'] = 'اختبر الاتصال';
$string['ragtest_ok'] = 'ردّت خدمة RAG (HTTP {$a->status}، {$a->seconds} ث).';
$string['ragtest_parsed'] = 'الإجابة التي ستعرضها الإضافة';
$string['ragtest_question'] = 'السؤال';
$string['ragtest_raw'] = 'الرد الخام';
$string['ragtest_run'] = 'إرسال طلب اختبار';
$string['ragtest_sent'] = 'الطلب المُرسل';
$string['recentchats'] = 'المحادثات الأخيرة';
$string['recentchats_empty'] = 'لا توجد محادثات بعد.';
$string['recents'] = 'الأخيرة';
$string['restart'] = 'إعادة المحاولة';
$string['retry'] = 'حاول مرة أخرى';
$string['revision_intro'] = 'اضغط على البطاقة لإظهار الإجابة!';
$string['scoreis'] = 'نتيجتك {$a->score} من {$a->total}.';
$string['search'] = 'بحث في المحادثات';
$string['searchplaceholder'] = 'ابحث في المحادثات...';
$string['seeresults'] = 'عرض النتيجة';
$string['send'] = 'إرسال';
$string['setting_demo'] = 'Demo mode';
$string['setting_demo_desc'] = 'عند عدم تحديد عنوان خدمة RAG يرد المعلّم بمحتوى جاهز لتجربة الواجهة فقط. لا تفعّله على موقع حقيقي.';
$string['setting_enabled'] = 'Enable AI Tutor';
$string['setting_enabled_desc'] = 'Show the AI Tutor tab in courses.';
$string['setting_maxquestionsperhour'] = 'عدد الأسئلة لكل مستخدم في الساعة';
$string['setting_maxquestionsperhour_desc'] = 'كل سؤال أو إجراء يستدعي خدمة RAG ويستهلك طلبات من Gemini. عند بلوغ الحد يُطلب من المستخدم الانتظار. القيمة 0 تعني بلا حد.';
$string['setting_ragheading'] = 'خدمة RAG للمقررات';
$string['setting_ragheading_desc'] = 'المعلّم الذكي يتصل فقط بخدمة RAG الخاصة بالمقررات (نفس الخدمة التي تفهرس فيها إضافة local_ragindex ملفات المقرر)، ولا يستخدم مزوّدات Moodle AI. يُرسل كل سؤال إلى POST /query مع رقم المقرر، فتأتي الإجابات من ملفات هذا المقرر فقط. اضغط زر الاختبار بعد الحفظ.';
$string['setting_ragkey'] = 'مفتاح API لخدمة RAG';
$string['setting_ragkey_desc'] = 'قيمة SERVICE_API_KEY الخاصة بالخدمة، وتُرسل في الترويسة X-API-Key.';
$string['setting_ragtimeout'] = 'مهلة خدمة RAG (بالثواني)';
$string['setting_ragtimeout_desc'] = 'مدة انتظار الرد (من 5 إلى 300).';
$string['setting_ragurl'] = 'عنوان خدمة RAG';
$string['setting_ragurl_desc'] = 'العنوان الأساسي للخدمة بدون /query، مثل http://127.0.0.1:8003. استخدم نفس خدمة local_ragindex. يمكن أن تعمل على نفس الخادم أو شبكة داخلية: قائمة cURL blocked hosts في Moodle لا تُطبَّق على هذا العنوان، فأبقِ localhost محظوراً هناك.';
$string['showless'] = 'عرض أقل';
$string['sourcepage'] = 'صفحة {$a}';
$string['sources'] = 'المصادر';
$string['startrevision'] = 'ابدأ المراجعة بالبطاقات';
$string['stoprec'] = 'إيقاف';
$string['tabname'] = 'المعلّم الذكي';
$string['tapreveal'] = 'اضغط للإظهار';
$string['thinking'] = 'جارٍ التفكير...';
$string['togglesidebar'] = 'إظهار أو إخفاء الشريط الجانبي';
$string['tools'] = 'الأدوات';
$string['topics_intro'] = 'هذه بعض الأشياء التي يمكنك السؤال عنها:';
$string['tryasking'] = 'جرّب أن تسأل:';
$string['tutorname'] = 'المعلّم الذكي';
$string['understoodas'] = 'فُهم السؤال على أنه: {$a}';
$string['useresource'] = 'استخدام هذا المورد في الإجابات';
$string['viewall'] = 'عرض الكل';
$string['vieworiginal'] = 'عرض الأصل';
$string['voicesend'] = 'تسجيل رسالة صوتية';
$string['welcome'] = 'مرحباً! أنا معلّمك الذكي لمقرر {$a}. أستطيع مساعدتك في فهم المفاهيم وتلخيص المحاضرات وشرح الأكواد وتخطيط مراجعتك.';
$string['welcomehero'] = 'ماذا تريد أن تتعلّم اليوم؟';
$string['yesterday'] = 'أمس';
$string['you'] = 'أنت';
