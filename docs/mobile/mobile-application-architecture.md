# ResQLink Mobile Application Architecture

**Architecture style:** Feature-oriented Flutter client with a Laravel REST API  
**Primary goal:** Keep mobile development simple without mixing UI, networking,
security, and backend business rules

## System context

The Flutter application is a client of the existing Laravel system. It does not
replace the Laravel application or access MySQL directly.

```text
+-------------------------+
| Flutter mobile app      |
| Barangay volunteers     |
+------------+------------+
             |
             | HTTPS + JSON
             | Bearer token
             v
+------------+------------+
| Laravel API             |
| Auth, validation, rules |
+------------+------------+
             |
             | Eloquent / SQL
             v
+------------+------------+
| MySQL                   |
| Central system records  |
+-------------------------+

Flutter map ----> approved local/self-hosted MapLibre data
```

The Blade web application continues to serve admin, dispatcher, and current web
volunteer workflows. Mobile API authentication should coexist with, rather than
replace, browser session authentication.

## Responsibility boundaries

### Flutter responsibilities

- Render mobile screens and controls.
- Collect incident form values.
- Perform immediate usability validation.
- Request foreground location permission.
- Capture GPS coordinates when allowed.
- Display the map, marker, barangay data, landmarks, and radius.
- Store the API token securely.
- Send authenticated JSON requests.
- Display server validation, authorization, and network errors.
- Display the server-generated incident confirmation.

### Laravel responsibilities

- Verify credentials.
- Issue and revoke mobile API tokens.
- Enforce account activation, role, and volunteer verification.
- Validate every incident field.
- Associate the report with the authenticated volunteer.
- Calculate preliminary severity.
- Assign the `Reported` status and report timestamp.
- Generate the incident identifier data.
- Store and retrieve central system records.
- Return consistent JSON responses and appropriate HTTP status codes.

### MySQL responsibilities

- Persist users, roles, and volunteer profiles.
- Persist API token records managed by Laravel Sanctum.
- Persist incidents and reference data.
- Persist dispatcher and admin workflow records.

MySQL credentials must exist only in the Laravel environment. They must never
be included in the Flutter application.

## Mobile source layout

Create the Flutter application at the repository root, separate from `docs/`:

```text
ResQLink/
|-- backend/
|-- mobile/
|   |-- android/
|   |-- ios/
|   |-- lib/
|   |-- test/
|   |-- pubspec.yaml
|   `-- pubspec.lock
|-- docs/
|   `-- mobile/
`-- README.md
```

Use a feature-oriented structure inside `mobile/lib/`:

```text
lib/
|-- app/
|   |-- app.dart
|   |-- app_router.dart
|   `-- app_theme.dart
|-- core/
|   |-- config/
|   |   `-- environment.dart
|   |-- errors/
|   |   `-- api_exception.dart
|   |-- network/
|   |   `-- api_client.dart
|   `-- storage/
|       `-- token_storage.dart
|-- features/
|   |-- authentication/
|   |   |-- data/
|   |   |   `-- auth_service.dart
|   |   |-- models/
|   |   |   `-- authenticated_user.dart
|   |   `-- presentation/
|   |       |-- auth_provider.dart
|   |       `-- login_screen.dart
|   `-- incidents/
|       |-- data/
|       |   `-- incident_service.dart
|       |-- models/
|       |   |-- incident_submission.dart
|       |   `-- submitted_incident.dart
|       `-- presentation/
|           |-- incident_form_screen.dart
|           |-- incident_map.dart
|           `-- submission_confirmation_screen.dart
`-- main.dart
```

This structure separates reusable infrastructure from feature code without
adding a large number of architectural layers.

## Layer design

### Presentation layer

Presentation contains screens, reusable widgets, and Provider state classes. It
is responsible for user interaction and visible state.

Examples:

- `LoginScreen` gathers credentials and displays login errors.
- `AuthProvider` tracks whether the app is checking, authenticated, or signed out.
- `IncidentFormScreen` owns field controllers and form validation.
- `IncidentMap` exposes selected coordinates and impact radius through callbacks.

Presentation should not manually construct HTTP requests or read secure storage.

### Feature service layer

Feature services describe operations meaningful to the application:

- `AuthService.login(...)`
- `AuthService.currentUser()`
- `AuthService.logout()`
- `IncidentService.submit(...)`

Services convert JSON to typed models and translate low-level API failures into
errors that presentation code can understand.

### Core infrastructure layer

Core infrastructure provides shared technical behavior:

- `ApiClient` sends requests with common headers and timeouts.
- `TokenStorage` stores and removes the API token securely.
- `Environment` supplies service base URLs.
- `ApiException` represents status, message, and field errors.

Core code must not depend on a particular screen.

## Authentication flow

```text
App starts
    |
    v
Read secure token
    |
    +-- no token ----------> Login screen
    |
    `-- token found
            |
            v
       GET /api/auth/user
            |
            +-- 200 -------> Volunteer home/form
            |
            `-- 401/403 ---> Remove token -> Login screen
```

Login should follow this sequence:

1. Validate that login, password, and device name are available.
2. Send them to `POST /api/auth/login` as JSON.
3. Laravel checks credentials, activation, role, and volunteer verification.
4. Laravel creates and returns a Sanctum device token and user summary.
5. Flutter stores the token using secure storage.
6. Flutter updates authentication state and opens the volunteer area.

Logout should attempt `POST /api/auth/logout`, revoke the current token on the
server, remove the local token, clear volunteer state, and return to login. If
the network is unavailable, the UI should clearly explain whether server-side
revocation could not be confirmed.

## Incident submission flow

```text
Incident form
    |
    | local usability validation
    v
IncidentService
    |
    | POST /api/incidents + bearer token
    v
Laravel validation and authorization
    |
    +-- 201 --> typed confirmation --> confirmation screen
    |
    +-- 401 --> session expired ------> login flow
    |
    +-- 403 --> access denied --------> account guidance
    |
    +-- 422 --> field errors ---------> form fields
    |
    `-- other -> retryable/general error message
```

The submitted model should represent the API contract:

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

Do not include `volunteer_id`, `reported_by`, status, severity, or report time.
Laravel derives those values.

## API response conventions

Use a consistent JSON envelope for successful resource responses:

```json
{
  "data": {
    "id": 1,
    "identifier": "INC-0001"
  }
}
```

Recommended status behavior:

| Status | Meaning in the mobile client |
| --- | --- |
| `200` | Successful login/profile/logout operation where applicable |
| `201` | Incident created successfully |
| `401` | Credentials or token are invalid |
| `403` | Account is not permitted to perform the operation |
| `422` | One or more submitted fields are invalid |
| `429` | Too many requests; wait before retrying |
| `500` | Unexpected server failure |

Flutter should not depend on Laravel exception class names. It should depend on
the status code and stable JSON fields such as `message` and `errors`.

## Map architecture

Separate map presentation from incident submission:

```text
Approved map style and tiles
             |
             v
       IncidentMap widget
        /      |       \
   GPS     map gesture   local GeoJSON
        \      |       /
         selected location
               |
               v
       incident form model
```

The map widget should communicate selected coordinates, barangay, landmark, and
radius through typed values or callbacks. It should not submit incidents itself.

The project currently uses local Cebu City barangay and landmark datasets. Reuse
approved datasets where their formats and licenses support mobile packaging.
Avoid embedding large data parsing blocks in widgets; isolate dataset loading
and location lookup in focused helpers or services.

## Configuration architecture

Keep environment-specific addresses outside feature code. For example:

```dart
abstract final class Environment {
  static const apiBaseUrl = String.fromEnvironment('API_BASE_URL');
  static const mapStyleUrl = String.fromEnvironment('MAP_STYLE_URL');
}
```

A development launch can then supply values without editing Dart files:

```powershell
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000 --dart-define=MAP_STYLE_URL=http://10.0.2.2:8080/styles/resqlink/style.json
```

Service addresses are configuration, not secrets. Passwords, API tokens, and
Laravel secrets must not be supplied this way or committed to the repository.

## Error model

Use one small application error type that can represent:

- HTTP status code.
- User-safe message.
- Field validation errors keyed by API field name.
- Whether retry is appropriate.
- Whether the session should be cleared.

The application should distinguish:

- Validation errors the volunteer can correct.
- Authorization problems requiring account support.
- Authentication expiry requiring a new login.
- Connectivity failures that may succeed when retried.
- Unexpected failures that should not reveal server internals.

## Security architecture

- Use HTTPS outside local development.
- Store tokens only in secure platform storage.
- Never log authorization headers or passwords.
- Revoke the current token during logout.
- Treat the mobile application as an untrusted client.
- Enforce all authorization and validation again in Laravel.
- Apply reasonable rate limiting to login and submission endpoints.
- Do not expose database or Laravel secrets in Flutter assets.
- Request foreground location only when the reporting workflow needs it.

Because a compiled mobile application can be inspected, no value included in
the app binary should be treated as confidential.

## Test boundaries

When test execution is explicitly approved, testing should cover:

### Flutter unit tests

- JSON model parsing.
- API error conversion.
- Authentication state transitions.
- Incident form validation helpers.

### Flutter widget tests

- Login loading and error states.
- Incident field validation.
- Submit button disabled/loading behavior.
- Confirmation rendering.

### Laravel feature tests

- Token login for an active, verified volunteer.
- Rejection of invalid, inactive, unverified, or wrong-role accounts.
- Token-authenticated incident submission.
- Reporter association cannot be overridden by the client.
- Validation and severity responses.
- Logout revokes the current token.

### Manual device checks

- Location permission allowed and denied.
- GPS unavailable.
- Marker placement and dragging.
- Radius rendering.
- Emulator and physical-device networking.
- Server and tile-service unavailability.

## Growth path

Keep the initial architecture small. Add new layers only when an approved
feature creates a concrete need. Possible future additions include:

- Offline report drafts and synchronized submission.
- Push notification registration.
- Photo evidence upload.
- Incident history for the reporting volunteer.
- Packaged offline maps.

Each addition should preserve Laravel as the source of truth, document its
security and data behavior, and remain within the repository file-size rules.

## Architecture decision summary

- Use Flutter for the volunteer mobile client.
- Use a feature-oriented layout rather than a large monolithic screen file.
- Use Provider for limited shared presentation state.
- Use small services over HTTP for Laravel operations.
- Use Sanctum bearer tokens for mobile authentication.
- Use secure platform storage for tokens.
- Use MapLibre and approved local map data.
- Keep authorization, validation, severity, and persistence in Laravel.
- Keep every source file below 500 lines and prefer 300–400 lines or fewer.

