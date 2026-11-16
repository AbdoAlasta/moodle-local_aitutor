# AI Tutor (local_aitutor): installation

AI Tutor adds an **AI Tutor** tab to every Moodle course. Students chat with a tutor that answers only from that
course's indexed files, with cited sources. Quick actions generate summaries, flashcards, multiple-choice quizzes,
study plans and suggested topics, and there is an assignment-help mode that gives hints rather than solutions.
Chats are saved per student and per course.

The plugin does not use Moodle's AI providers. Every request goes to the
[native-rag-gemini](https://github.com/AbdoAlasta/native-rag-gemini) service, whose files are indexed by
[local_ragindex](https://github.com/AbdoAlasta/moodle-local_ragindex).

The full changelog (Arabic) is in [README.md](README.md). The service request and response format is in
[RAG_API.md](RAG_API.md).

## Requirements

- Moodle 5.2 or newer
- A running [native-rag-gemini](https://github.com/AbdoAlasta/native-rag-gemini) service and its `SERVICE_API_KEY`
- [local_ragindex](https://github.com/AbdoAlasta/moodle-local_ragindex) installed and pointing at the **same**
  service, so the course files are indexed. Without it, the tutor searches an empty index.

## 1. Copy the plugin

The folder must be named `aitutor` and go into `<moodle>/public/local/`:

```bash
git clone https://github.com/AbdoAlasta/moodle-local_aitutor.git <moodle>/public/local/aitutor
```

## 2. Install

Log in as an administrator and open *Site administration > Notifications*, or run:

```bash
php admin/cli/upgrade.php
```

Then purge the caches (*Site administration > Development > Purge caches*).

## 3. Configure

*Site administration > Plugins > Local plugins > AI Tutor*

| Setting | Value |
| --- | --- |
| Enable AI Tutor | On |
| RAG service URL | Base URL without `/query`, e.g. `http://127.0.0.1:8003` |
| RAG API key | The service's `SERVICE_API_KEY` (sent as `X-API-Key`) |
| RAG timeout | Seconds to wait for an answer (default 60) |
| Demo mode | Canned replies for trying the interface without a service. Leave off on a real site |

## 4. Moodle HTTP security: nothing to change

Since version 0.8.1, AI Tutor reaches the service even when it runs on `localhost`, a private address or a
non-standard port. The service URL can only be set by an administrator, so the plugin skips Moodle's
*cURL blocked hosts / allowed ports* check for that one URL.

**Keep `127.0.0.0/8` and `localhost` in *Site administration > General > Security > HTTP security >
cURL blocked hosts list*.** Removing them, as older versions of these instructions said, lets every Moodle feature
that fetches a URL (RSS feeds, URL downloads...) reach services on the Moodle server itself.

## 5. Test the connection

On the settings page, click **Test the connection**. It calls `GET /health`, then sends a real `POST /query` and
shows the request, status, raw response and the parsed answer. Enter the id of a course whose files are already
indexed. The default, `1`, is the site front page, which normally has nothing indexed.

## 6. Use it

Open any course. The **AI Tutor** tab appears in the course navigation (under **More** if the bar is full).

## Capabilities and privacy

- `local/aitutor:use` (course level) gives access to the tab and its web services. Students, teachers and managers
  have it by default.
- Students only ever see their own chats in the current course.
- The Privacy API exports and deletes a user's chats, and declares the RAG service as an external recipient of the
  questions.
