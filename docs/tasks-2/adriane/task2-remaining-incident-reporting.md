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

## Implemented by code inspection

- The volunteer web form and `POST /api/incidents` share validation and incident
  submission services.
- Active, verified Volunteer authorization is enforced by middleware and the
  request authorization layer.
- Category, coordinates, radius, reporter, timestamp, identifier, preliminary
  severity, and initial `Reported` status are persisted.
- The volunteer map uses bundled MapLibre assets, a locally served TileServer
  style, and local Cebu City GeoJSON datasets without a CDN or remote geocoder.
- Dispatcher details and the authenticated GeoJSON map feed consume the stored
  TASK2 incident data.

## Remaining assignment

- Run focused automated tests for valid submission and all important invalid or
  unauthorized paths.
- Manually verify field-level errors, the confirmation state, map interaction,
  and behavior when the local TileServer is unavailable.
- Record test evidence and reconcile any defects with Tasks 1, 3, and 4.

## Definition of done
All TASK2 acceptance criteria have recorded automated and manual verification,
with any cross-task defects resolved or assigned.
