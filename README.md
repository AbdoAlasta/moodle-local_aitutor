# AI Tutor (local_aitutor) for Moodle 5.2

An **AI Tutor** tab inside every course, next to Course / Participants / Grades. It contains:

- a chat with a course tutor that answers **only from that course's files** and cites its sources;
- a side menu: New Chat, Suggested Topics, Course Resources, Study Plan, Practice Quiz, Assignments Help, Revision Mode;
- a **Course Context** card for picking a resource, **Quick Actions** (lecture context, summary, flashcards, MCQs),
  and **Recent Chats**.

Answers come from the [native-rag-gemini](https://github.com/AbdoAlasta/native-rag-gemini) service, which searches the
course files indexed by [local_ragindex](https://github.com/AbdoAlasta/moodle-local_ragindex). Moodle's own AI
providers are not used.

**Installation: see [INSTALL.md](INSTALL.md).** The service request and response format is in [RAG_API.md](RAG_API.md).

## How it works

- Each question goes to `POST /query` of the RAG service with the course id, so it searches only that course's files.
- A selected resource adds its name to the question to steer the search. Cited sources are listed under each answer.
- Quiz, flashcards, study plan, summary and topics ask for JSON and validate it before display. If the service does
  not follow the format, the reply is shown as plain text.
- Each chat remembers the whole conversation: a running summary of older messages plus the latest exchanges.
- Each user can ask a limited number of questions per hour (30 by default).
- Capability `local/aitutor:use` (student, teacher, manager by default). Chats are stored per user and course, and
  the Privacy API exports and deletes them.

## Changelog

### 0.8.1: no need to open localhost in Moodle
- Reaching the RAG service no longer requires removing `127.0.0.0/8` and `localhost` from *cURL blocked hosts list*:
  the plugin skips that check for the service URL only, because only an administrator can set it.
  **Put `127.0.0.0/8` and `localhost` back in the list**, so other Moodle features that fetch URLs cannot reach
  services on the server itself.

### 0.8.0: memory for the whole chat
- The tutor remembers **the whole chat** instead of only the last 3 questions. Each chat keeps a **running summary**
  stored with it in Moodle. Every question sends the summary plus the exchanges not yet in it (4 to 7), word for word,
  so questions like "what did I ask at the start?" work in long chats.
- When 8 exchanges are outside the summary, the oldest are folded into it with one small `POST /summarize` call
  (about once every 4 questions), so the memory size stays roughly constant. If that call fails, the chat continues
  with the previous summary.
- Fewer tokens: `[1]` citation markers are removed from the memory, and the question rewrite step only sees the
  summary and the last 2 exchanges.
- Quizzes, flashcards and plans appear in the memory as a one-line note instead of being ignored.
- Needs the updated `native_rag_gemini` (`summary` field and `/summarize`).

### 0.7.1: questions per user per hour
- New setting **Questions per user per hour** (default 30). Each question or action calls the RAG service and uses
  Gemini, so a student who reaches the limit is asked to wait. 0 means no limit. The limit counts across all courses.
- Works with the updated `native_rag_gemini`, which answers off-topic questions without calling Gemini and retries
  Gemini on temporary errors.

### 0.7.0: short chat memory and a scrolling chat area
- With each question, the last 3 questions and answers of the chat are sent as `history`. The service rewrites a
  follow-up such as "explain more" into a standalone question before searching, and the answer shows
  *Understood as: ...*. New Chat starts without memory.
- The instructions of the action buttons (quiz, flashcards, plan, summary, topics, assignment help) go in a separate
  `instructions` field and are not used for the search.
- The chat box is sized to the window: messages scroll inside it and the input stays visible. On phones the layout
  stays stacked.

### 0.6.0: matching our RAG services (`RAGS` folder)
- Requests now match the services' API: `POST <url>/query` with `{"question": "...", "course_id": "2"}` and the
  `X-API-Key` header. The course id is sent **as a string** (a number was rejected with 422); it is the same id
  `local_ragindex` indexes the files under.
- `mode`, `prompt`, the previous conversation and the resource text are no longer sent.
- The service URL is now the base URL (e.g. `http://127.0.0.1:8003`); the plugin adds `/query`. Settings that were no
  longer needed were removed by `db/upgrade.php`: auth header name, request format, question field name, response
  field, extra instructions, maximum context characters, conversation turns remembered, path to pdftotext.
- Quiz, flashcards, plan, summary and topics buttons add the required JSON format to the request.
- **Sources**: under each text answer, a list of the cited files (`[1] lecture1.pdf, page 3`).
- **Test the connection** checks `GET /health`, then sends a real question to `/query`.

### 0.5.0: RAG only, no Moodle AI provider
- The dependency on `core_ai` was removed. Without a RAG URL the tutor shows "service not configured" (or demo
  replies if demo mode is on).
- Options to match other services: auth header name, request format (full or minimal), question field name and
  response field path (removed again in 0.6.0).
- New **Test the connection** page: sends a sample question and shows the request, status code, raw response and the
  answer as the student would see it.

### 0.4.0: direct connection to the RAG service
- New settings under **Course RAG service**: service URL, optional API key, timeout.
- With a URL set, all requests go to the RAG service with the `course_id`, so it searches only that course's data.
- Request and response format documented in `RAG_API.md`.
- Privacy API: the RAG service is declared as an external location that receives data.

### 0.3.1: the sidebar chat was removed
- The sidebar panel, its button and its menu item were removed (`drawer.js`, its template and settings). The tutor is
  only reached from the **AI Tutor** tab of each course. `db/upgrade.php` removes the old `drawerenabled` and
  `drawerbutton` settings.

### 0.3.0: one tutor per course
- **Every course has its own tutor**: chats, resources and context belong to the current course only; there is no
  course picker. The course name is shown in the **Course Context** card, and the welcome message and suggestions
  use it.
- **Course Context** card: course name, **Selected resource** with a **Change** link, and a collapsible
  **Use this context** option.
- **Quick Actions** (4 buttons): Use lecture context, Summarize resource, Create flashcards, Generate MCQs.
- **Recent Chats** show the time, "Yesterday" or the date, with **View all** for every chat of the course.
- New suggestion: *Help me with the project ideas*.
- The web service `local_aitutor_get_courses` was removed. All functions still check `local/aitutor:use` in the
  course context, and a chat is only returned to its own user in its own course.

### 0.2.2: full screen
- A header button switches the chat to full screen and back; the choice is remembered in the browser.

### 0.2.1: "AI Tutor" item in the sidebar menu
- An **AI Tutor** item in the main navigation (after Dashboard) opened the panel instead of a new page, with a
  floating button as a fallback.

### 0.2.0: sidebar button and course choice
- A sidebar button opened the AI Tutor panel over any page, closed with the close button or `Esc`, keeping its state
  between pages. (Removed in 0.3.1.)

### Upgrading

After copying new files, open *Site administration > Notifications* to finish the upgrade, then
*Purge all caches*.
