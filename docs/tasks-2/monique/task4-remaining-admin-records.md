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
