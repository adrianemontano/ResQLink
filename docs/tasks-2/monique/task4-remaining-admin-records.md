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

## Verification result

All items above were verified against the implementation on the `jassy` branch
and three defects were corrected:

1. The records search and table read the legacy `landmark` and `persons_count`
   columns, which volunteer submissions do not populate. Search now covers
   `nearest_landmark`, and the table prefers `affected_population` and
   `nearest_landmark` with legacy fallbacks, matching the dispatcher views.
2. The records table emitted `status-Reported` badge classes while the shared
   theme defines lowercase `.badge.status-reported`, so admin status badges
   rendered without colour. The class is now lowercased like the dispatcher
   views.
3. The report status chart mapped retired lowercase statuses (`pending`,
   `received`, `dispatched`), so every bar fell back to the default colour and
   `Completed` had none. The map now uses the canonical statuses and shares
   their colours with the badge theme.

Archived access is delivered as a read-only `active` / `archived` / `all` scope
on `admin.incidents.index`, where `Completed` marks archived and historical
records. No data is changed and no migration is required.

Report summary cards now expose the status and frequency totals required by
`TASK4-008`: total incidents, one card per workflow status, and the number of
days containing incidents.

Routes, report definitions, field meanings, and dependencies are documented in
`docs/implementation/admin-records-and-reports-feature.md`.

## Definition of done
All Task 4 acceptance criteria are verified and documented for final reconciliation.
