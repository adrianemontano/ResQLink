# TASK2 — Adriane: Remaining Incident Reporting Work

## Development test accounts

| Role | Username | Email | Password | Web access |
| --- | --- | --- | --- | --- |
| Admin | `admin` | `admin@resqlink.local` | `Admin@12345` | Yes |
| Dispatcher | `Angela` | `a@gmail.com` | `admin123` | Yes |
| Volunteer | `volunteer` | `volunteer@resqlink.local` | `Volunteer@12345` | Yes; verified |

These are development credentials only and can be overridden with the matching `DEFAULT_*` environment variables.

## Seeder data
`DatabaseSeeder` creates `admin`, `dispatcher`, and `volunteer` roles. Admin: `admin` / `admin@resqlink.local` / `Admin@12345`. Create and verify a volunteer through Admin before reporting. Credentials are development-only.

## Assignment
- Verify the complete volunteer submission page/API contract and error responses.
- Confirm only active, verified volunteers can submit incidents.
- Validate fields, categories, coordinates, radius, reporter, timestamp, identifier, and `Reported` status.
- Document and verify Low, Moderate, High, and Critical severity thresholds.
- Confirm incident data is available to dispatcher map/details views.
- Provide a local offline map or coordinate-grid fallback with no remote tiles/CDNs.
- Coordinate incident fields and severity rules with Tasks 3 and 4.

## Definition of done
All Task 2 acceptance criteria are verified and documented for dispatcher and admin handoff.
