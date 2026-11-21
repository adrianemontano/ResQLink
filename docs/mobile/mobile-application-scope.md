# ResQLink Mobile Application Scope

**Platform:** Expo + React Native + TypeScript, Android first
**Status:** Approved scope; fresh implementation planned

## Purpose

The mobile application lets active, verified barangay volunteers notify
ResQLink of incidents. Laravel receives, validates, assesses, and stores each
incident; dispatchers then coordinate response through the existing web module.

<<<<<<< HEAD
## Ownership
=======
The initial mobile foundation supports volunteer sign-in, forced temporary
password change, structured incident submission, and optional compressed photo
upload. GPS/map location picking and the richer submission-confirmation workflow
remain planned work.

## Ownership and coordination

The mobile workflow crosses two existing team tasks:
>>>>>>> origin/jassy

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

## Planned API dependency

| Method | Endpoint | Status | Purpose |
| --- | --- | --- | --- |
| POST | `/api/mobile/login` | Planned | Issue volunteer Sanctum token |
| GET | `/api/mobile/user` | Planned | Restore authenticated volunteer |
| POST | `/api/mobile/logout` | Planned | Revoke current token |
| POST | `/api/incidents` | Partly implemented | Submit incident; mobile Sanctum middleware/JSON contract still required |

Endpoint paths for planned operations remain proposals until TASK1 implements
and documents them. Existing web session routes are implemented and must not be
presented as mobile token endpoints.

<<<<<<< HEAD
## Map behavior
=======
- Login using a username or email and password.
- Secure storage of a Laravel API token.
- Required change of an Admin-issued temporary password before incident access.
- Restoration of a valid login when the application restarts.
- Retrieval of the current volunteer's basic profile.
- Logout and revocation of the current device token.
- Clear messages for incorrect credentials, inactive accounts, incorrect roles,
  and unverified volunteer profiles.
>>>>>>> origin/jassy

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

<<<<<<< HEAD
## Acceptance criteria
=======
- Category: Flood, Earthquake, Landslide, or Fire.
- Barangay.
- Nearest landmark.
- Estimated affected population.
- Latitude and longitude.
- Impact radius in metres.
- Optional notes.
- Optional camera/gallery JPG or PNG photo (resized/compressed, maximum 5 MB).

The mobile application may validate fields immediately for usability, but
Laravel is the authoritative validator. The authenticated volunteer must be
assigned by Laravel; the mobile client must never submit or choose a reporter
identifier.

### Location selection

The mobile workflow will provide:

- Permission-aware GPS location capture.
- Manual map navigation when GPS is unavailable or denied.
- Tap or drag interaction for placing the incident pin.
- A visible circle for the selected impact radius.
- Synchronization between the pin and latitude/longitude values.
- Barangay selection based on the approved local Cebu City dataset.
- Landmark selection based on the approved local landmark dataset.

The application must still allow manual location selection when the volunteer
does not grant location permission.

### Submission confirmation

After a successful submission, the application will display:

- Incident identifier.
- Reported date and time returned by Laravel.
- `Reported` status.
- Category and location summary.
- Preliminary severity returned by Laravel.

The confirmation must describe severity as preliminary decision support. It
must not imply an official emergency assessment.

## Roles and permissions

Only an authenticated user meeting all of these conditions can use the mobile
incident submission workflow:

- The account is active.
- The account has the `volunteer` role.
- A related volunteer profile exists.
- The volunteer profile has `verification_status` set to `verified`.

Admin and dispatcher accounts must not gain access to volunteer mobile
functions merely because their credentials are valid.

## Out of scope for the initial release

The following features should be deferred until the core reporting flow is
stable and approved:

- Admin and dispatcher mobile dashboards.
- Volunteer self-registration or document verification.
- Incident dispatch and status updates by volunteers.
- Live responder tracking.
- Continuous background location tracking.
- Chat, voice calls, or video calls.
- Push notifications.
- Uploading incident videos.
- Public incident browsing.
- Volunteer-to-volunteer messaging.
- A separate mobile database that duplicates the Laravel database.
- Advanced offline submission synchronization.

Offline maps may be investigated as a later reliability improvement. The first
release must document whether its map requires access to a locally hosted tile
server.

## API dependency

The implemented mobile API endpoints are:

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `POST` | `/api/login` | Validate the volunteer and issue a 30-day device token |
| `GET` | `/api/user` | Validate a restored token and return the volunteer profile |
| `PUT` | `/api/password/change` | Change a temporary password and clear the forced-change flag |
| `POST` | `/api/logout` | Revoke the current device token |
| `POST` | `/api/incidents` | Submit an authenticated incident report and optional photo |

Protected mobile endpoints use random bearer tokens stored as SHA-256 hashes in
`mobile_access_tokens`, separate from browser sessions. Tokens expire after 30
days and are invalidated after an Admin password reset. This lightweight
first-party token implementation is a project-specific alternative to Sanctum;
review it as security-sensitive code before production deployment.

## Error and recovery expectations

The mobile application should handle these conditions without losing entered
form data or crashing:

- No internet or server connection.
- Request timeout.
- Invalid or expired authentication token.
- Account deactivated after login.
- Volunteer verification removed after login.
- Laravel field validation errors.
- GPS permission denied.
- GPS unavailable or inaccurate.
- Map tiles unavailable.
- Duplicate submit taps.
- Unexpected server error.

The submit button should be disabled while a submission is in progress. If a
request fails safely, the volunteer should be able to correct or retry it.

## Privacy and security boundaries

- Passwords must never be stored on the device.
- API tokens must be stored with secure platform storage.
- Production communication must use HTTPS.
- Logs must not expose passwords, tokens, or sensitive volunteer information.
- Flutter must never connect directly to MySQL.
- Laravel must validate every request even when Flutter has already validated it.
- The application should request only permissions needed by the active feature.
- Location should be captured for the report, not continuously in the background.

## Delivery milestones

### Milestone 1: Mobile foundation — implemented

- Create the Flutter application under the repository root `mobile/` directory.
- Add application theme, configuration, navigation, and shared error handling.
- Document Android development prerequisites and API URL configuration.

### Milestone 2: Mobile authentication — initial implementation

- Implement `/api/login`, `/api/password/change`, and `/api/logout` with hashed
   expiring mobile access tokens and secure device storage.
- Implement login, forced password update, token restoration, and logout.
- Enforce active, volunteer-role, and verified-profile requirements server-side.

### Milestone 3: Basic incident submission and optional photo — initial implementation

- Build the incident form without the map.
- Submit valid JSON to Laravel.
- Display field errors and successful confirmation.
- Support optional compressed photo upload and private server storage.

### Milestone 4: Map and GPS — planned

- Add MapLibre.
- Add location permission handling and GPS capture.
- Add pin placement, radius display, barangay boundaries, and landmarks.

### Milestone 5: Reliability and handoff — ongoing

- Verify failure and recovery behavior.
- Confirm submitted incidents appear in dispatcher and admin workflows.
- Complete setup, API, permission, and map documentation.

## Initial acceptance criteria

- An active, verified volunteer can log in through the mobile application.
- Invalid, inactive, unverified, admin, and dispatcher accounts are rejected.
- A login token is stored securely and can restore a session.
- A volunteer can complete all required incident fields.
- A volunteer can select a location manually or through available GPS.
- Laravel associates the incident with the authenticated volunteer.
- Laravel assigns the `Reported` status and server timestamp.
- Laravel calculates and returns preliminary severity.
- The application displays a clear success confirmation.
- Validation and network failures do not crash the application.
- Implementation and setup documentation are updated with approved behavior.
>>>>>>> origin/jassy

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
