# ResQLink Mobile Application Scope

**Platform:** Expo + React Native + TypeScript, Android first
**Status:** Backend contract and Expo foundation implemented; features pending

## Purpose

The mobile application lets active, verified barangay volunteers notify
ResQLink of incidents. Laravel receives, validates, assesses, and stores each
incident; dispatchers then coordinate response through the existing web module.

## Ownership

| Area | Task | Owner |
| --- | --- | --- |
| Volunteer access and Sanctum authentication | TASK1 | Jassy |
| Incident reporting and severity contract | TASK2 | Adriane |
| Dispatcher coordination and shared map data | TASK3 | Angela |
| Admin records and reports | TASK4 | Monique |

Task identifiers belong in plans, checklists, branches, commits, and handoff
notes—not in routes, database names, or TypeScript/PHP domain names.

## Included initial release

- Volunteer credential entry, token login, session restoration, and logout.
- Authorization limited to active, verified volunteer accounts.
- Incident category, barangay, nearest landmark, affected population,
  latitude, longitude, impact radius, and notes.
- Foreground GPS with clear permission handling.
- Manual incident-location selection and repositionable marker.
- Approved self-hosted map through TileServer GL Light.
- Geographic impact-radius visualization.
- Submission progress, validation errors, API/network errors, and confirmation.
- Preservation of entered data after recoverable failures.

Laravel remains authoritative for reporter identity, validation, severity,
timestamps, initial status, and authorization.

## Implemented API dependency

| Method | Endpoint | Status | Purpose |
| --- | --- | --- | --- |
| POST | `/api/mobile/login` | Implemented | Issue volunteer Sanctum token |
| GET | `/api/mobile/user` | Implemented | Restore authenticated volunteer |
| POST | `/api/mobile/logout` | Implemented | Revoke current token |
| PUT | `/api/mobile/password` | Implemented | Replace a temporary volunteer password |
| POST | `/api/incidents` | Implemented | Submit an authenticated volunteer incident |

The mobile endpoints use Sanctum bearer tokens and remain separate from the
existing web session routes. Volunteers with temporary passwords may restore
their session, change the password, or log out, but cannot submit an incident
until the password is changed.

## Map behavior

The user can accept GPS coordinates or manually choose a location. The UI shows
the selected coordinates, marker, barangay/landmark context, and a real-world
impact-radius polygon. Denied location permission must not block manual entry.
Unavailable tiles require a useful error/retry state. The initial release does
not promise fully offline maps.

## Out of scope

- Dispatcher or admin workflows in the mobile app.
- Direct MySQL access.
- On-device authoritative severity calculation.
- Background location tracking.
- Firebase, Supabase, Google Maps, or hosted Mapbox APIs.
- Reuse or migration of the removed Flutter source.
- iOS delivery in the initial Android-first milestone.

## Acceptance criteria

- A physical Android development build contains MapLibre Native.
- Expo Go is not used to certify map behavior.
- A verified volunteer can authenticate through implemented Sanctum endpoints.
- Axios supplies the bearer token for protected requests.
- The token is stored in SecureStore and removed at logout.
- GPS denial falls back to manual location selection.
- The self-hosted style loads over the configured LAN/HTTPS endpoint.
- Submitted data reaches MySQL only through Laravel.
- Laravel supplies authoritative reporter, severity, timestamps, and status.
- Error states are understandable and form data survives retryable failures.
