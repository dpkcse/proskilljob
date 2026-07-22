# Admin candidate search and filtering

## Audit and regression assessment

The admin list is `GET /admin/candidate` (`candidate.index`) and is rendered server-side by `Admin\CandidateController@index` into `backend.candidate.index`. It uses Eloquent pagination (10 rows), URL query strings, Select2 controls, and page-local JavaScript selection; it is not a DataTable or an AJAX search endpoint. Candidate records live in `candidates.id`; identity and email are in the related `users` row. The original list eagerly loaded only `user` and `jobRole`, searched the related user name/email using interpolated `LIKE` text, and did not query profession or job-role translations. Thus name worked because it is `users.name`; profession and role were never part of the predicate. Email was present in the predicate, but whitespace was not trimmed and raw wildcard characters were not escaped.

Profession and job role are separate foreign keys: `candidates.profession_id` and `candidates.role_id`; both master models are translated. Skills use `candidate_skill`. The candidate profile’s selected experience is `experience_id`, whose seeded values are labelled ranges such as `3+ Years`; the system has employment-history rows but does not maintain a reliable calculated total. This feature filters the existing experience master range, not an invented stored total. DOB is nullable `birth_date` (cast `Y-m-d`) and candidate account creation uses `candidates.created_at`. Preferred locations are JSON text in `preferred_job_locations`. There is no application-source master or candidate source column; the only source-like candidate data is the professional reference `relation`, so the UI deliberately labels it **Reference relation** rather than misrepresenting it as application source.

The candidate list and export are protected by `candidate.view`; profile/edit/delete continue to use their existing permissions. Candidate list scope is currently permission-based and has no recruiter ownership condition, so the shared query retains exactly that existing scope. Protected modules: candidate create/update/profile and unrelated website/API candidate listings were not changed.

| Area | Risk | Mitigation |
| --- | --- | --- |
| list/name search/pagination/sorting | medium | shared query keeps `keyword`, `sort_by`, pagination and `withQueryString` |
| profile/edit/selection/bulk delete | low | routes, bindings and checkbox values are unchanged |
| selected and filtered exports | high | export now starts from the same filtered query; selected IDs additionally constrain it |
| PDF large exports | high | legacy in-memory PDF is bounded at 500 with a clear 422 response; CSV/XLSX use `FromQuery` |
| RBAC/API/dashboard/mobile | low | existing authorization and response contracts untouched; Bootstrap responsive controls retained |
| DB portability | medium | age uses indexed DOB date bounds; IDs use `EXISTS` relationship predicates to avoid duplicates |

## Search and filter contract

All populated filter groups combine with **AND**. Multi-select profession, job role, skill, reference relation, and preferred location each use **ANY selected value**. Empty inputs are ignored.

| Parameter | Type/example | Validation | Behavior |
| --- | --- | --- | --- |
| `keyword` | string, `Ada` | trimmed, max 120 | partial name, email, translated profession, or translated job role |
| `email` | string, `ada@example.test` | max 255 | trimmed partial related-user email |
| `profession_ids[]` | integer IDs | existing profession IDs | candidate profession IN values |
| `job_role_ids[]` | integer IDs | existing job-role IDs | candidate profile job role IN values |
| `skill_ids[]` | integer IDs | existing skill IDs | candidate has any selected skill |
| `reference_relations[]` | strings | max 100 each | candidate has a professional reference with relation IN values |
| `preferred_locations[]` | strings | max 255 each | JSON location contains any selected exact value |
| `min_age`, `max_age` | integers | 0–120; min ≤ max | DOB date bounds calculated at request time; null DOB excluded only when used |
| `created_from`, `created_to` | ISO date | from ≤ to | inclusive candidate `created_at` range |
| `min_experience`, `max_experience` | decimal years | 0–80; min ≤ max | inclusive numeric prefix of existing experience range labels |
| `ev_status` | `true`/`false` | enum | existing verified/unverified user filter |
| `sort_by` | `latest`/`oldest` | enum | existing candidate creation order |
| `ids` (export only) | comma IDs | numeric IDs | selected current-page candidates, within active filters |

Age is calculated without storing an age: minimum age means DOB on/before today minus minimum years; maximum age means DOB on/after the day after today minus maximum-plus-one years. This correctly observes whether the birthday has occurred.

## Export and performance

The export buttons preserve the current URL filters. “Selected on this page” exports checked rows; “all filtered” has no `ids` and exports the complete matching result set. CSV/XLSX use Laravel Excel `FromQuery`, so candidate rows are fetched incrementally rather than hydrated all at once. The previous export passed an array of IDs to a collection export, which could not produce candidate rows. Relationship filters use `whereHas`/`EXISTS`, so skills and references do not duplicate candidates or corrupt pagination counts. Existing foreign keys cover FK joins; no speculative migration/index was added. `%term%` global matching remains inherently unable to use a standard B-tree index; it is intentionally bounded to 120 characters and no incompatible full-text index was introduced.

## Manual QA and remaining risks

Verify list/profile/edit actions, desktop/mobile layout, saved filter URLs, selected export, all-filtered CSV/XLSX, and PDF’s 500-row limit after deployment with production-like data. The test database currently cannot migrate because an unrelated SQLite migration drops `earnings.plan_id` while a foreign key still references it. Configure a valid `APP_TIMEZONE` as well (the local environment has it blank). A future application-source field/master table should receive a distinct filter instead of overloading references.
