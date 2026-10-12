# ResQLink Mobile Application Scope

**Platform:** Flutter mobile application  
**Primary user:** Verified barangay volunteer  
**Related work:** `TASK1` user access and volunteer management; `TASK2` incident reporting and severity assessment

## Purpose

The ResQLink mobile application gives verified barangay volunteers a focused
way to report an incident from an Android or iOS device. It communicates with
the existing Laravel application through a JSON API. Laravel remains
responsible for authentication, authorization, validation, severity
classification, timestamps, and database access.

The initial mobile release should be intentionally small. Its primary goal is
to let an authorized volunteer sign in, describe an incident, select its
location, submit it, and receive a clear confirmation.

## Ownership and coordination

The mobile workflow crosses two existing team tasks:

| Area | Task | Owner |
| --- | --- | --- |
| Volunteer login, activation, role, and verification | `TASK1` | Jassy |
| Incident form, submission, and preliminary severity | `TASK2` | Adriane |

Task identifiers and ownership must remain visible in implementation notes,
checklists, branches, and commits. They must not be added to PHP or Dart class
names, routes, database tables, or user-facing labels.

## Initial release goals

The first usable release should support the following workflow:

1. A volunteer opens the application.
2. The application restores a valid saved session or displays the login screen.
3. The volunteer signs in with a username or email and password.
4. Laravel confirms that the account is active, has the `volunteer` role, and
   has a verified volunteer profile.
5. The volunteer opens the incident report form.
6. The volunteer supplies the required incident information.
7. The volunteer selects a location using GPS or the map.
8. The volunteer reviews and submits the report.
9. Laravel validates and stores the report, assigns the authenticated volunteer,
   sets the status to `Reported`, and calculates preliminary severity.
10. The application displays the server-generated incident confirmation.

## Included features

### Volunteer authentication

The mobile application will provide:

- Login using a username or email and password.
- Secure storage of a Laravel API token.
- Restoration of a valid login when the application restarts.
- Retrieval of the current volunteer's basic profile.
- Logout and revocation of the current device token.
- Clear messages for incorrect credentials, inactive accounts, incorrect roles,
  and unverified volunteer profiles.

Volunteer accounts continue to be created and verified by an admin. Public
self-registration is not part of the mobile application.

### Incident reporting

The incident form will collect:

- Category: Flood, Earthquake, Landslide, or Fire.
- Barangay.
- Nearest landmark.
- Estimated affected population.
- Latitude and longitude.
- Impact radius in metres.
- Optional notes.

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
- Uploading incident photos or videos.
- Public incident browsing.
- Volunteer-to-volunteer messaging.
- A separate mobile database that duplicates the Laravel database.
- Advanced offline submission synchronization.

Offline maps may be investigated as a later reliability improvement. The first
release must document whether its map requires access to a locally hosted tile
server.

## API dependency

The existing `POST /api/incidents` operation contains the main incident
submission behavior, but mobile token authentication is not yet implemented.
Before the Flutter login flow is considered complete, Laravel should provide:

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `POST` | `/api/auth/login` | Validate the volunteer and issue a device token |
| `GET` | `/api/auth/user` | Return the authenticated volunteer profile |
| `POST` | `/api/auth/logout` | Revoke the current device token |
| `POST` | `/api/incidents` | Submit an authenticated incident report |

Protected mobile endpoints should use Laravel Sanctum bearer-token
authentication rather than relying on the browser session used by Blade views.

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

### Milestone 1: Mobile foundation

- Create the Flutter application under the repository root `mobile/` directory.
- Add application theme, configuration, navigation, and shared error handling.
- Document Android development prerequisites and API URL configuration.

### Milestone 2: Mobile authentication

- Add Laravel Sanctum mobile endpoints.
- Implement login, secure token storage, session restoration, and logout.
- Enforce active, volunteer-role, and verified-profile requirements server-side.

### Milestone 3: Basic incident submission

- Build the incident form without the map.
- Submit valid JSON to Laravel.
- Display field errors and successful confirmation.

### Milestone 4: Map and GPS

- Add MapLibre.
- Add location permission handling and GPS capture.
- Add pin placement, radius display, barangay boundaries, and landmarks.

### Milestone 5: Reliability and handoff

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

