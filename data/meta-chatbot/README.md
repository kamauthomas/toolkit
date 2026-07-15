# Meta Admissions Chatbot Knowledge Package

Version: `2026-07-14.1`

## Upload now

Upload `meta-training-current.txt` for a document-based Meta assistant, or integrate `knowledge-current.json` together with `programme-facts-current.json` for a structured implementation. The facts file contains no prices; the current knowledge file contains prices approved for use before 1 September 2026.

Do **not** upload `scheduled-2026-09-01.json` to a production chatbot before its activation review. It contains later catalog prices that customers must not be quoted yet.

## Price precedence

1. A current, explicitly approved override in `knowledge-current.json`.
2. If no current price exists, tell the customer to contact admissions. Do not fall back to the 2026 catalog.
3. Before 1 September 2026, say only that a revised schedule is expected from September when a customer asks about future prices.
4. On or after 1 September 2026, the scheduled file still requires an admissions approval flag before activation. The date alone must not publish it.

All fees are stored and communicated as whole Kenya shillings. When a source contains cents, round upward to the next shilling: for example, `30,683.02` becomes `30,684`.

## Required runtime inputs

- `current_date` in `YYYY-MM-DD` format.
- `future_schedule_approved`, default `false`.
- A human admissions escalation channel.

## Updating the data

1. Duplicate the current JSON as a new dated version.
2. Change only values confirmed in writing by admissions.
3. Record `approved_by`, `approved_at`, `source`, and `effective_from`.
4. Run `php scripts/validate-meta-chatbot-data.php`.
5. Regenerate or edit the training text so it matches the JSON exactly.
6. Test direct price questions, comparisons, ambiguous course names, future-price questions, and escalation responses.
7. Keep the previous version for rollback.

## Interpretation notes requiring admissions confirmation

- “German A1 and A2, 6 months, KES 110,000” is represented as one combined pathway.
- “French A1 and A2, 3 months, KES 36,250” is represented as one combined pathway because that is how the supplied instruction is phrased. Confirm whether the amount is combined or per level before changing the record.
- Tuition amounts do not include exam, registration, accommodation, equipment, or other charges unless a record explicitly says so.
