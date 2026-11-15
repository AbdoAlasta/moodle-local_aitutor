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
  "history": [{"question": "What is software quality assurance?", "answer": "SQA is ... [1]"}],
  "instructions": "Respond with ONLY valid JSON ..."
}
```

- `course_id` is the Moodle course id **as a string**, the same value `local_ragindex` sends with `/index/file`.
  The service searches only that course's files.
- `history` (optional, sent when the chat has earlier text exchanges): the last 3 question/answer pairs of the
  current chat, oldest first, answers cut to 500 characters. The service uses one small extra Gemini call to rewrite
  a follow-up ("explain more", "what about its function?") into a standalone question before searching.
- `instructions` (optional): the answer format of the action (assignment hints, or the JSON below for topics, study
  plan, quiz, flashcards and summary). The service adds them to the answer prompt only, never to the search.
- `question` is the student's text; a selected resource adds `(course material: <name>)` to steer the search.
- `mode` is not sent, so LightRAG uses its default (`mix`).

**`history` and `instructions` need the updated `native_rag_gemini`.** Services without them ignore both fields:
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

Any non-2xx status is shown to the student as a generic "service not available" message. Details go to the
Moodle debugging log (Developer level).
