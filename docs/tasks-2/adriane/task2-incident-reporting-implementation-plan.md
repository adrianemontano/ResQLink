# TASK2 — Adriane: Incident Reporting Implementation Plan

## Seeder data

The current `DatabaseSeeder` creates `admin`, `dispatcher`, and `volunteer`
roles. The development Admin account is `admin` / `admin@resqlink.local` /
`Admin@12345`. Create and verify a volunteer through Admin before reporting.
These credentials are development-only.

## Objective

Complete the server-side incident submission workflow and preliminary severity
classification for an active, verified volunteer.

## Implementation steps

1. Review the incident migration, model, relationships, identifier format,
   status values, and persisted location fields.
2. Confirm the submission contract includes category, barangay, landmark,
   affected persons, latitude, longitude, impact radius, and optional notes.
3. Enforce authentication, active-account checks, volunteer role checks, and
   verified volunteer-profile checks.
4. Validate required fields, allowed categories, coordinate ranges, radius,
   affected-person values, and safe optional notes.
5. Centralize and document severity thresholds for Low, Moderate, High, and
   Critical; label the result as preliminary decision support.
6. Persist the authenticated reporter, identifier, timestamp, coordinates,
   severity, and initial `Reported` status.
7. Return consistent success, validation, unauthorized, and not-found
   responses for the approved web/API client.
8. Confirm the dispatcher map/details handoff and document the local offline map
   contract without implementing mobile screens.

## Acceptance criteria

- Only active, verified volunteers can submit complete reports.
- Invalid fields and unsupported categories are rejected with useful errors.
- Accepted incidents contain the required reporter, location, severity, time,
  identifier, and `Reported` data.
- Severity rules are documented and remain separate from dispatcher judgment.
- Incident data is consumable by Tasks 3 and 4.
