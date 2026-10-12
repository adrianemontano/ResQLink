# ResQLink Mobile Application Implementation Guide

**Platform:** Flutter, with Android as the first target  
**Related work:** `TASK1` user access and volunteer management; `TASK2` incident reporting and severity assessment

## Purpose

Follow these phases in order. Complete and verify each phase
before moving to the next so authentication, incident submission, and mapping
problems remain easy to isolate.

Read these supporting documents first:

- [Mobile application scope](mobile-application-scope.md)
- [Mobile technology stack](mobile-technology-stack.md)
- [Mobile application architecture](mobile-application-architecture.md)
- [Development setup](../development-setup.md)
- [Naming conventions](../naming-conventions.md)

## Before starting

### Coordinate task ownership

This implementation crosses two team assignments:

| Work | Task | Owner |
| --- | --- | --- |
| Volunteer mobile authentication and access checks | `TASK1` | Jassy |
| Incident reporting, validation, and severity | `TASK2` | Adriane |

Agree on the JSON authentication contract with the `TASK1` owner before
implementing the final Flutter login service. Preserve the appropriate task
identifier in branches, commits, checklists, and implementation notes.

### Install development tools

Install these tools on the development computer:

- Git.
- VS Code with Flutter and Dart extensions.
- Flutter stable SDK.
- Android Studio.
- Android SDK and command-line tools.
- An Android emulator or physical Android device.
- The existing PHP, Composer, Node.js, and MySQL requirements for Laravel.

Use `flutter --version`, `flutter doctor`, and `flutter devices` to check the
environment. Resolve Android issues before creating the application. Running
lint, test, build, or other testing commands through an AI coding agent requires
the user's explicit approval under the repository rules.

## Phase 1: Create the Flutter application

### 1. Create a task-scoped branch

Use a branch name that reflects the approved work. For example:

```powershell
git switch -c feature/task2-volunteer-mobile-app
```

### 2. Create the project at the repository root

From the ResQLink repository root, create `mobile/` beside `backend/`:

```powershell
flutter create --org com.resqlink --platforms=android mobile
```

The repository should then contain:

```text
ResQLink/
|-- backend/
|-- mobile/
|-- docs/
`-- README.md
```

Do not create the Flutter application inside `backend/` or `docs/mobile/`.
`docs/mobile/` contains documentation; `mobile/` contains application code.

### 3. Add the initial packages

From `mobile/`, add one package for each approved responsibility:

```powershell
flutter pub add provider
flutter pub add http
flutter pub add flutter_secure_storage
flutter pub add geolocator
flutter pub add maplibre_gl
```

Add `permission_handler` only if the final permission implementation needs it.
Commit `pubspec.lock` so all developers use compatible resolved versions.

### 4. Create the source structure

Follow the feature-oriented `mobile/lib/` structure in
[Mobile application architecture](mobile-application-architecture.md). Add
files when their phase begins instead of creating empty classes. Keep every
code file below 500 lines and target 300–400 lines or fewer.

## Phase 2: Prepare the Laravel mobile API

The current application uses browser-session authentication. A mobile client
needs token authentication before it can use protected API operations reliably.

### 1. Install and configure Sanctum

Use Laravel Sanctum for mobile bearer tokens. Laravel 13 can prepare API token
support with:

```powershell
cd backend
php artisan install:api
```

Review every generated or modified file before committing. This operation may
add a dependency and a token migration. Because it changes the database schema,
run migrations and tests only after obtaining the required explicit approval.

Ensure the `User` model supports Sanctum API tokens and protected API routes use
`auth:sanctum`.

### 2. Add mobile authentication operations

Implement these API routes:

```text
POST /api/auth/login
GET  /api/auth/user
POST /api/auth/logout
```

Recommended route behavior:

| Endpoint | Authentication | Result |
| --- | --- | --- |
| Login | Public, rate limited | Validates account and issues a device token |
| Current user | `auth:sanctum` | Returns the volunteer profile summary |
| Logout | `auth:sanctum` | Revokes the current token only |

The login request should accept:

```json
{
  "login": "volunteer@resqlink.local",
  "password": "Volunteer@12345",
  "device_name": "Adriane Android"
}
```

Before issuing a token, Laravel must confirm:

- The credentials are correct.
- The account is active.
- The account has the `volunteer` role.
- The account has a volunteer profile.
- The volunteer profile is verified.

Do not copy all of the web login controller into an API controller. Extract or
reuse focused credential/access logic where that improves consistency.

### 3. Define stable JSON responses

Return a `data` object containing the plain-text token and a safe volunteer user
summary. Use consistent errors:

- `401` for invalid credentials or token.
- `403` for an inactive, unverified, or unauthorized account.
- `422` for malformed or missing request fields.
- `429` for rate limiting.

Do not return password hashes, token database records, volunteer documents, or
internal exception details.

### 4. Protect incident submission

Update the mobile incident endpoint to accept Sanctum bearer authentication:

```text
POST /api/incidents
```

Keep the existing server-side checks in `StoreIncidentRequest`. Laravel must
derive the reporting volunteer from the authenticated token. The request must
not accept a client-provided reporter ID.

### 5. Document and verify backend changes

Update the relevant `TASK1` and `TASK2` documentation with:

- Route names and methods.
- Request and response JSON.
- Authorization rules.
- Token issue and revocation behavior.
- Database migration/setup steps.

After explicit approval, run the appropriate migration and Laravel tests. At a
minimum, verify login, current user, logout, authorization failures, incident
submission, and reporter-association protection.

## Phase 3: Build the Flutter foundation

### 1. Add environment configuration

In `core/config/environment.dart`, read service addresses from compile-time
values:

```dart
abstract final class Environment {
  static const apiBaseUrl = String.fromEnvironment('API_BASE_URL');
  static const mapStyleUrl = String.fromEnvironment('MAP_STYLE_URL');
}
```

Fail clearly during development if a required value is empty. Do not hard-code
one developer's network address throughout the codebase.

Typical development addresses are:

| Client | Laravel host from the client |
| --- | --- |
| Android emulator | `http://10.0.2.2:8000` |
| Physical Android device | Computer's local network IP and port |
| Flutter web/desktop | Usually `http://127.0.0.1:8000` |

### 2. Implement `ApiClient`

Give the client focused methods for JSON requests. It should:

- Combine relative paths with `API_BASE_URL`.
- Add `Accept: application/json`.
- Add `Content-Type: application/json` for JSON bodies.
- Read and attach the bearer token for protected requests.
- Apply a reasonable timeout.
- Decode JSON safely.
- Convert non-success responses to `ApiException`.
- Preserve Laravel `422` field errors.

Do not display dialogs or navigate from `ApiClient`; presentation code owns UI.

### 3. Implement secure token storage

Wrap `flutter_secure_storage` in `TokenStorage` with only the operations the
application needs:

```text
readToken()
writeToken(token)
deleteToken()
```

Never store the password. Never print the token in logs.

### 4. Set up the application shell

Implement:

- A single ResQLink `ThemeData` using Material 3.
- A startup/authentication loading screen.
- A login route.
- A volunteer incident form route.
- A submission confirmation route.
- Provider registration near the application root.

Keep routing simple. The initial application does not require a large navigation
package unless approved requirements introduce deep linking or complex nested
navigation.

## Phase 4: Implement mobile authentication

### 1. Create typed authentication models

`AuthenticatedUser` should contain only fields returned by the approved API.
Implement strict JSON parsing so an unexpected response produces a controlled
error instead of a type-cast crash.

### 2. Implement `AuthService`

Add methods corresponding to the API contract:

```text
login(login, password, deviceName)
currentUser()
logout()
```

The service should use `ApiClient`, parse the user, and coordinate token storage.
It should not contain widget state.

### 3. Implement `AuthProvider`

Use explicit states such as:

```text
checking
authenticated
unauthenticated
submitting
failure
```

On application startup:

1. Read the saved token.
2. If absent, show login.
3. If present, request `/api/auth/user`.
4. If valid, restore the authenticated state.
5. If rejected, remove the token and show login.

### 4. Build the login screen

The screen should include:

- Username or email field.
- Password field with show/hide control.
- Sign-in button.
- Loading indicator during the request.
- Inline or summary error feedback.

Disable repeated sign-in taps while the request is running. Do not reveal
whether a particular username exists.

### Authentication checkpoint

Do not begin the full incident form until all of these work:

- Valid verified-volunteer login.
- Incorrect-password response.
- Inactive-volunteer rejection.
- Unverified-volunteer rejection.
- Admin and dispatcher rejection.
- Session restoration after application restart.
- Logout and server token revocation.

## Phase 5: Implement incident reporting without the map

### 1. Create request and response models

`IncidentSubmission` should serialize exactly these API fields:

```text
category
barangay
nearest_landmark
affected_population
latitude
longitude
impact_radius
notes
```

`SubmittedIncident` should parse the response fields needed by confirmation,
including identifier, reported time, status, severity, category, and location.

Do not send `volunteer_id`, `reported_by`, severity, status, or `reported_at`.

### 2. Implement `IncidentService`

Add a focused submission method that sends:

```http
POST /api/incidents
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

Return `SubmittedIncident` after a `201` response. Pass validation errors back
to the form through the common error model.

### 3. Build the basic form

Begin with ordinary inputs and temporary coordinate fields so API integration
can be completed before adding MapLibre:

- Category dropdown.
- Barangay field.
- Nearest landmark field.
- Affected population numeric field.
- Temporary latitude and longitude fields.
- Impact radius field in metres.
- Optional notes field.
- Submit button.

Use the same limits documented for `POST /api/incidents`, but treat Laravel as
the final authority.

### 4. Preserve user input on failure

If submission fails because of validation, timeout, or connectivity:

- Keep the form values.
- Show field-specific validation where available.
- Show a general retryable message for connectivity failures.
- Avoid submitting again automatically.
- Re-enable the submit button after the request finishes.

### Basic submission checkpoint

Confirm the no-map version can create an incident and display:

- Incident identifier.
- Server timestamp.
- `Reported` status.
- Preliminary severity.
- Submitted category and location summary.

Also confirm the incident appears correctly in existing dispatcher/admin views.

## Phase 6: Add GPS and MapLibre

### 1. Configure Android permissions

Add only the required Android permissions:

- Internet access for API and map communication.
- Fine/coarse foreground location for GPS assistance.

Do not request background location. Include user-facing explanations for why
location helps report an incident.

### 2. Add location handling

Implement a focused location service or helper that:

1. Checks whether device location services are enabled.
2. Checks the existing permission state.
3. Requests permission when appropriate.
4. Retrieves the current position.
5. Returns understandable denied, permanently denied, disabled, and timeout
   results.

GPS is an aid, not a requirement. Always retain manual map selection.

### 3. Add the MapLibre widget

Build `IncidentMap` as a reusable widget. It should:

- Load the configured approved style.
- Start over Cebu City.
- Display the selected incident marker.
- Allow tap-to-place and, where supported, drag-to-adjust behavior.
- Display the impact-radius circle.
- Report location changes to the form.
- Show a useful state when map data cannot load.

Do not put HTTP submission or authentication logic in the map widget.

### 4. Integrate approved local data

Reuse the approved Cebu City barangay GeoJSON and local landmark data where
their size, format, and license allow mobile packaging. Implement:

- Barangay search and selection.
- Map navigation to the selected barangay.
- Resolution of the pin to a containing barangay boundary.
- Landmark options restricted to the selected barangay where feasible.
- Selection or emphasis of the nearest approved landmark.

Move dataset parsing and spatial lookup out of the main screen widget. Large
data files are not subject to the code line limit, but Dart logic remains under
500 lines per file.

### 5. Decide how mobile map tiles are delivered

For local development, the Android emulator can commonly reach a host tile
server through `10.0.2.2`. A physical device needs the computer's reachable
local network IP.

Before field deployment, document and approve one approach:

- A securely hosted ResQLink tile service reachable by the device; or
- Approved offline map data packaged or downloaded for MapLibre.

Do not silently replace the approved local approach with remote public tile,
geocoding, or map APIs.

### Map checkpoint

Verify:

- Permission allowed, denied, and permanently denied behavior.
- Manual selection without GPS.
- Correct marker coordinates.
- Radius changes reflected on the map and in the request.
- Barangay and landmark synchronization.
- Graceful map/tile failure.

## Phase 7: Verify and hand off the implementation

Continue with the detailed
[Mobile verification and handoff guide](mobile-verification-and-handoff-guide.md).
It covers reliability, security, automated and manual checks, documentation,
commit sequencing, and the recommended first-session stopping point.

