# TASK4 — Monique: Remaining Admin Records and Reports Work

## Development test accounts

| Role | Username | Email | Password | Web access |
| --- | --- | --- | --- | --- |
| Admin | `admin` | `admin@resqlink.local` | `Admin@12345` | Yes |
| Dispatcher | `Angela` | `a@gmail.com` | `admin123` | Yes |
| Volunteer | `volunteer` | `volunteer@resqlink.local` | `Volunteer@12345` | Yes; verified |

These are development credentials only and can be overridden with the matching `DEFAULT_*` environment variables.

## Seeder data
`DatabaseSeeder` creates `admin`, `dispatcher`, and `volunteer` roles. Admin: `admin` / `admin@resqlink.local` / `Admin@12345`. These credentials are development-only.

## Assignment
- Verify archived and historical records with Task 3 status history.
- Confirm records are read-only and expose no dispatcher status controls.
- Validate search fields, category/status filters, and admin authorization.
- Verify daily, weekly, and monthly date scopes against persisted data.
- Check category, barangay, status, and frequency aggregations, including empty periods.
- Document routes, report definitions, field meanings, and dependencies.

## Definition of done
All Task 4 acceptance criteria are verified and documented for final reconciliation.

## TASK4 verification status

- [x] Completed and historical incidents are treated as archived for viewing
  and reporting; available Task 3 status-history entries show their status,
  dispatcher, timestamp, and notes.
- [x] Admin incident details are read-only and expose no dispatcher status
  controls.
- [x] Search, category/status filters, pagination, and admin-only permissions
  are covered by `AdminRecordsAndReportsTest`.
- [x] Daily, Monday-based weekly, and monthly report windows are verified
  against persisted data using the configured application timezone.
- [x] Category, barangay, status, and frequency summaries, including empty
  periods, are covered by `AdminRecordsAndReportsTest`.
- [x] Routes, field meanings, report definitions, permissions, and Task 2/3
  dependencies are documented in
  `docs/implementation/admin-records-and-reports-feature.md`.
- [x] The approved archive meaning reuses completed and historical incident
  records. No archive marker, database column, or workflow is required.

Verification run on 2026-10-29: the focused Task 4 suite passed (10 tests,
73 assertions). The full suite had 23 passing tests, 2 failures, and 1 error:

- Task 3 queue test expected `Lahug (Pob.)` in the filtered incident results.
- Task 3 map-feed test expected `300.0` but received `300`.
- Task 1 verified-volunteer test omitted the required volunteer-profile
  `barangay` value.
