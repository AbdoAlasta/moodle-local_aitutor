# Hint mode: design

Status: implemented in 0.9.0, 18 Nov 2026.

## Decisions already made

- **The student controls it** with a toggle in the chat.
- **The full answer is given only when the student asks for it.**
- **Every question gets a hint**, short factual ones too ("What is a primary key?").
- **Hints do not count toward the hourly limit.** Only the question the student types counts. **Another hint** and
  **Show full answer** are free, within the cap below.

## What the student sees

1. A **Hint mode** toggle next to the message box. When it is on, a "Hint mode" label shows on the chat.
2. The student asks a question. The tutor replies with **one hint and one guiding question**, not the answer.
3. Under each hint there are two buttons: **Another hint** and **Show full answer**.
4. Each new hint on the same problem is more specific than the last (the hint ladder):

   | Level | What the tutor gives |
   | --- | --- |
   | 1. Nudge | The concept involved and where it is in the course material (with the source). |
   | 2. Pointer | The specific rule, step or part of the material that solves it. |
   | 3. Worked step | One worked step on a similar example, not on the student's own problem. |
   | Full answer | Only after **Show full answer**, or when the student types that they want the full answer. |

5. After hint 3, only **Show full answer** is offered. A new typed question starts again at hint 1.
6. The toggle is saved per chat: reopening a chat shows it as it was left. A new chat starts with the toggle as it
   currently is on screen (off the first time).

## The hint cap (why free hints stay bounded)

Free hints must not become a way around the hourly limit, so Moodle, not the browser, decides what is allowed:

- **Moodle counts the level.** The level is the number of hint replies at the end of the chat since the last typed
  question. It is sent to the model ("This is hint 2 of 3"), so the ladder no longer relies on the model guessing it.
- **Another hint** is accepted only when the last tutor reply is a hint at level 1 or 2. Otherwise it is refused.
- **Show full answer** is accepted only when the last tutor reply is a hint.
- **The limit:** one typed question, which counts, costs at most 4 Gemini calls: hint 1, two more hints, and the full
  answer. A request sent outside these rules is refused, so it cannot be repeated for free.

## Where the changes go

### RAG service (native_rag_gemini)

| File | Change |
| --- | --- |
| `app/main.py` | `AskRequest` gains `style: str` (max 2000 chars): how to answer (tone, hint mode). |
| `app/service.py` | `style` is added to the prompt as "Answer style: ...". Unlike `instructions`, it does **not** skip the relevance cut-off. |
| `app/llm.py` | `SUMMARY_SYSTEM` also keeps "which problem the student is working on and how many hints were given". |
| `tests/` | Off-topic question with `style` gets the not-found reply without a Gemini call; `style` reaches the prompt. |

### Moodle plugin (local_aitutor)

| File | Change |
| --- | --- |
| `db/install.xml`, `db/upgrade.php`, `version.php` | Chat table gains `hintmode` (int 1, default 0). |
| `classes/external/send_message.php` | New parameter `hintmode` (bool, default false), saved on the chat. |
| `classes/external/get_chat.php` | Returns `hintmode`, so the toggle shows the saved state. |
| `classes/local/tutor.php` | `MODES` gains `anotherhint` and `reveal`. The hint level is counted from the last replies, and the cap is checked. The rate limit is skipped for these two modes only. A hint style text with the level is sent as `style`. Hint replies are stored with message type `hint`. `load_pairs` labels them "(Hint n)" in the memory. |
| `classes/local/rag_client.php` | `query()` sends `style`. |
| `amd/src/app.js`, `templates/main.mustache` | Toggle, "Hint mode" label, a "Hint n of 3" badge, **Another hint** (hidden after hint 3) and **Show full answer** buttons under hint replies. |
| `lang/en`, `lang/ar` | New strings: toggle, label, badge, the two buttons, `prompt_anotherhint`, `prompt_reveal`, the refusal message, privacy field. |
| `classes/privacy/provider.php` | `hintmode` in metadata and export. |

### How each mode behaves with hint mode on

| Mode | Hint mode effect | Counts toward the limit |
| --- | --- | --- |
| chat (a typed question) | Hint style at level 1 is sent. Reply stored as `hint`. | Yes |
| anotherhint (new) | Sends "Give me another hint" with the next level. Accepted only after hint 1 or 2. | No |
| reveal (new) | Sends "Show me the full answer". Accepted only after a hint. Hint style is not sent. | No |
| assignment | No change: its instructions already forbid the full solution. | Yes |
| topics, studyplan, quiz, flashcards, summary | Ignored: these return JSON and must not be turned into hints. | Yes |

## Hint style text

The text sent to the model is in `tutor::answer_style()`. It tells the model not to state the answer, not to describe
either side of a comparison, to add something new in each hint, not to write a hint number, and not to talk about the
course materials, pages or sections (the [n] citations and the Sources list show where a hint comes from). Only the
current level's step is sent. Level 1: point to the right idea to think about (a related concept, or what to compare it
with). Level 2: give one new clue without explaining it. Level 3: show one worked step on an everyday example from a
different subject.

## The hidden answer (ANSWER/HINT split)

Asked only for a hint, the model often gave the definition anyway on short "what is X" questions. So, in hint mode, the
style text asks for a reply in two labelled parts, in one Gemini call:

- `ANSWER:` the complete answer, with citations. The student never sees it.
- `HINT:` a hint towards that answer that must not repeat its key facts.

Writing the answer first gives the model something concrete to hold back. `tutor::split_hint()` then keeps only the
text after the last `HINT:` label (bold or Markdown labels are accepted), and marks as cited only the sources the hint
cites, so the Sources list does not point at the hidden answer. If the reply has an `ANSWER:` but no `HINT:`, nothing is
shown and the student gets the "empty reply" error, so a missing label never shows the answer. A reply with no labels
is shown as it is. The labels stay in English in every language. **Show full answer** does not use the split.

The cost is a longer reply: each hint call writes about twice as much text. In the real check hints still took 2 to 5
seconds.

## Issues and how the design avoids them

| Issue | Why it happens | How we avoid it |
| --- | --- | --- |
| Off-topic questions reach Gemini again | Today any request with `instructions` skips the 0.60 relevance cut-off (action prompts score low on purpose). Hint mode on every question would disable the guard. | Hint text travels in the new `style` field, which keeps the cut-off. Only real actions use `instructions`. Covered by a test. |
| The tutor gives the answer away | The model may ignore the rule, especially on short factual questions. | The hidden answer (ANSWER/HINT split), strict style text at temperature 0.2, and the full answer only through `reveal`. A check with 14 course questions (English and Arabic) on 19 Nov 2026: hint 1 no longer gives the definition on "what is X" questions, and no hidden answer was shown, but 4 of 22 hints at levels 1 and 2 still named key facts. A model can still slip; the check measures how often. |
| Free hints bypass the hourly limit | Hints do not count, so repeated hint requests could cost unlimited Gemini calls. | Moodle checks the cap on the server: at most 2 extra hints and 1 full answer per typed question, so at most 4 calls for each counted question. |
| Hint level is lost in long chats | Older exchanges are folded into the summary. | Moodle counts the level from the stored replies and sends it, so it never depends on the memory. Hint replies are labelled "(Hint n)" in the memory too. |
| "Show full answer" is misread | Guessing intent from free text is unreliable. | The button sends an explicit `reveal` mode. A typed request ("just tell me the answer") is honoured by the style text as a second path. |
| "Another hint" is searched badly | "Give me another hint" says nothing about the topic, and a rewrite drifts to the tutor's last guiding question. | **Another hint** and **Show full answer** send the question the student typed, stored in the chat. |
| Quizzes and flashcards break | Hint text would conflict with the JSON format. | Hint mode is ignored for structured actions. |
| Privacy | A new stored field. | `hintmode` is declared in the Privacy API and exported. |

## Build order (each step checked before the next)

1. RAG `style` field and tests. Check: all tests pass, including off-topic with `style`.
2. Moodle database field and upgrade. Check: the upgrade runs, and the field exists.
3. `tutor.php`, `rag_client.php`, web services. Check: a scripted request returns a stored hint; a 3rd `anotherhint`
   and a `reveal` without a hint are refused; `anotherhint` and `reveal` do not use up the hourly limit.
4. Toggle and buttons in the chat. Check in the browser: toggle, badge, Another hint hidden after hint 3, Show full
   answer, reopening a chat.
5. Strings and privacy. Check: English and Arabic pages, privacy export.
6. End-to-end check with 10 questions in English and Arabic: no answer in hints 1 to 2, the full answer after reveal,
   off-topic questions still refused without a Gemini call.

## Open questions

None. All decisions are made.
