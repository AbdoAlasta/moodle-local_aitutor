# RAG service contract for local_aitutor

AI Tutor uses the course RAG services in `RAGS/` (native_rag, native_rag_gemini, lightrag_pgvector, the MariaDB
versions...) as they are. Use the **same** service that `local_ragindex` indexes the course files into, otherwise
AI Tutor searches an empty index.

Settings: *Site administration > Plugins > Local plugins > AI Tutor > Course RAG service*

| Setting | Value |
| --- | --- |
| RAG service URL | Base URL without `/query`, e.g. `http://127.0.0.1:8003` |
| RAG API key | The service's `SERVICE_API_KEY` (sent as `X-API-Key`) |
| RAG timeout | Seconds to wait for an answer (default 60) |

*Test the connection* calls `GET /health` and then `POST /query` with a sample question.

## Request

`POST <url>/query` with `Content-Type: application/json` and `X-API-Key: <key>`.

```json
{
  "question": "explain more",
  "course_id": "2",
  "history": [{"question": "What is software quality assurance?", "answer": "SQA is ..."}],
  "summary": "The student first asked about the software life cycle and got a quiz on testing.",
  "instructions": "Respond with ONLY valid JSON ..."
}
```

- `course_id` is the Moodle course id **as a string**, the same value `local_ragindex` sends with `/index/file`.
  The service searches only that course's files.
- `history` (optional, sent when the chat has earlier exchanges): every question/answer pair of the current chat
  that is not in the summary yet (4 to 7, at most 10), oldest first, answers cut to 500 characters, without citation
  markers. Quiz, flashcard and other structured
  replies appear as a one-line note such as `(The tutor replied with a quiz.)`. The service uses one small extra
  Gemini call to rewrite a follow-up ("explain more", "what about its function?") into a standalone question before
  searching; that call only sees the summary and the last 2 pairs.
- `summary` (optional): the chat's running summary of everything older than `history`, so the tutor remembers the
  whole chat. The plugin keeps it with the chat in Moodle and updates it with `POST /summarize` (below).
- `instructions` (optional): the answer format of the action (assignment hints, or the JSON below for topics, study
  plan, quiz, flashcards and summary). The service adds them to the answer prompt only, never to the search.
- `question` is the student's text; a selected resource adds `(course material: <name>)` to steer the search.
- `mode` is not sent, so LightRAG uses its default (`mix`).

**`history`, `instructions`, `summary` and `/summarize` need the updated `native_rag_gemini`.** Services without them ignore both fields:
answers still work, but follow-ups are not understood and the structured actions come back as plain text.

## Response

`200` with `{"answer": "<text>", "references": [{"ref": 1, "source": "lecture1.pdf", "page": 3, "cited": true, ...}], "rewritten": "<standalone question>" | null}`.

References with `"cited": true` are listed under text answers as **Sources** (`[1] lecture1.pdf, page 3`).
When `rewritten` is set, the answer also shows *Understood as: ...*.
For the structured actions the answer must be the JSON below; if it is not, the answer is shown as plain text.

| action | answer format |
| --- | --- |
| topics | `{"topics": ["...", "..."]}` |
| study plan | `{"title": "...", "weeks": [{"label": "Week 1", "topics": "..."}], "includes": ["..."]}` |
| quiz | `{"questions": [{"question": "...", "options": ["A", "B", "C", "D"], "answer": 0, "explanation": "..."}]}` |
| flashcards | `{"title": "...", "cards": [{"front": "...", "back": "..."}]}` |
| summary | `{"title": "...", "bullets": ["..."]}` |

## Chat memory: `POST /summarize`

When 8 pairs of a chat are outside the summary, the plugin folds all but the last 4 into it (one call every 4
questions or so) and stores the result with the chat:

```json
{"summary": "<summary so far, or empty>", "turns": [{"question": "...", "answer": "..."}]}
```

The answer is `{"summary": "<updated summary, at most 1000 characters>"}`. If the call fails, the question is still
answered with the summary the chat already had.

Any non-2xx status is shown to the student as a generic "service not available" message. Details go to the
Moodle debugging log (Developer level).
