# ResQLink Mobile Application Architecture

**Status:** Approved target architecture; Expo source not yet generated
**Style:** Feature-oriented Expo React Native client with Laravel REST API

**Current implementation:** `mobile/` now contains the first Flutter shell,
secure bearer login, forced-password gate, incident form, and optional photo
upload. The map/GPS picker remains planned. Mobile tokens are random opaque
values whose SHA-256 hashes are stored by Laravel; this is the current
first-party alternative to the earlier Sanctum proposal.

## System context

```text
Volunteer
   |
Expo React Native + TypeScript
   | Axios + bearer token
Laravel REST API
   | Eloquent
MySQL

Expo map -> MapLibre Native -> TileServer GL Light -> approved map dataset
Dispatcher/admin -> Laravel Blade + Bootstrap -> same Laravel domain data
```

This preserves the Notify–Receive–Dispatch workflow: a verified volunteer
notifies Laravel, Laravel receives and assesses the incident, and dispatchers
coordinate the response in the existing web application.

## Responsibility boundaries

### Expo application

- Present volunteer login and incident-reporting screens.
- Request foreground location permission and obtain GPS when allowed.
- Allow manual location selection and marker repositioning.
- Send the documented incident fields through Axios.
- Store only the Sanctum token in `expo-secure-store`.
- Render the self-hosted map and geographic impact-radius preview.
- Handle loading, validation, expired sessions, denied permissions, API errors,
  and unavailable tiles without discarding entered form data.

### Laravel

- Validate credentials, active status, volunteer role, and verification.
- Issue and revoke Sanctum personal access tokens.
- Authenticate and authorize every protected request.
- Validate incident inputs and associate reporter identity from the token.
- Calculate authoritative severity and assign timestamps and initial status.
- Return stable JSON responses and persist through Eloquent.

### MySQL

<<<<<<< HEAD
MySQL stores users, volunteer records, incidents, statuses, and related domain
data. It is reachable by Laravel only; Expo never receives database credentials.
=======
- Persist users, roles, and volunteer profiles.
- Persist hashed mobile access-token records managed by Laravel.
- Persist incidents and reference data.
- Persist dispatcher and admin workflow records.
>>>>>>> origin/jassy

## Recommended source layout

Create this only during Stage 1 of implementation:

```text
mobile/
|-- app/
|   |-- _layout.tsx
|   |-- index.tsx
|   `-- (auth)/
|-- src/
|   |-- components/
|   |-- features/
|   |   |-- auth/
|   |   `-- incidents/
|   |-- services/api/
|   |-- config/
|   |-- hooks/
|   |-- types/
|   `-- utils/
|-- assets/
|-- app.json
|-- package.json
`-- tsconfig.json
```

Expo Router owns route files under `app/`. Feature behavior belongs under
`src/features`; shared API configuration belongs under `src/services/api`.
This is a starting recommendation and may evolve without mixing domain logic
into route components.

## Authentication flow

<<<<<<< HEAD
1. Volunteer enters credentials.
2. Expo sends them to the planned mobile login endpoint with Axios.
3. Laravel validates credentials, active account, volunteer role, and verified
   volunteer status.
4. Laravel issues a Sanctum personal access token.
5. Expo stores the token in SecureStore and restores it on a later launch.
6. An Axios interceptor attaches `Authorization: Bearer <token>`.
7. Laravel authenticates and authorizes each protected request.
8. Logout asks Laravel to revoke the current token, then clears the local token.
=======
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
      GET /api/user
            |
            +-- 200 -------> Volunteer home/form
            |
            `-- 401/403 ---> Remove token -> Login screen
```
>>>>>>> origin/jassy

Current repository status: mobile token login/logout endpoints are not yet
implemented. `POST /api/incidents` exists, but its current `auth` middleware is
not yet the documented Sanctum mobile contract. Web session login/logout must
remain separate.

<<<<<<< HEAD
## Incident submission contract

The mobile request preserves these domain fields and must use the exact backend
field names established by the final API contract:
=======
1. Validate that login, password, and device name are available.
2. Send them to `POST /api/login` as form data.
3. Laravel checks credentials, activation, role, and volunteer verification.
4. Laravel creates a random expiring bearer token and returns it with the user summary.
5. Flutter stores the token using secure storage.
6. Flutter updates authentication state and opens the volunteer area.

Logout should attempt `POST /api/logout`, revoke the current token on the
server, remove the local token, clear volunteer state, and return to login. If
the network is unavailable, the UI should clearly explain whether server-side
revocation could not be confirmed.
>>>>>>> origin/jassy

- incident category;
- barangay;
- nearest landmark;
- affected population;
- latitude and longitude;
- impact radius; and
- notes.

The client must not send a trusted reporter ID, severity, timestamp, or initial
status. Laravel derives those values. Documentation and implementation must not
rename fields until the actual request validator and JSON contract are agreed.

## Map architecture

MapLibre loads the TileServer style URL from environment configuration. A
selected coordinate drives the marker and incident payload. The impact radius
is converted from the documented real-world unit into a geographic GeoJSON
polygon and rendered with a `GeoJSONSource` plus compatible layers. The same
coordinate and radius go to Laravel; the preview is not a severity engine.

GPS denial leaves manual map selection available. Tile failure shows a clear
retry/configuration state and must not claim offline support.

## Device networking

A physical phone's `localhost` is the phone. During LAN development use, for
example:

```text
API:        http://<LAPTOP_LAN_IP>:8000/api
Map style:  http://<LAPTOP_LAN_IP>:8080/styles/<STYLE_ID>/style.json
```

The laptop and phone must share a network; Laravel and TileServer must listen
on reachable interfaces; Windows Firewall must allow the selected ports. An
Expo/Metro tunnel does not automatically expose Laravel or TileServer. Local
cleartext HTTP may require debug-only Android network configuration. Production
services require HTTPS and appropriate certificates.

The exact TileServer style endpoint remains an implementation-time check; do
not assume `<STYLE_ID>`.

## Development-build boundary

Expo Go is insufficient because it does not contain MapLibre Native. The config
plugin must be present when Android native code is generated. Build and install
with `npx expo run:android`, or use an EAS development build. Use
`npx expo start --dev-client` after installation. Rebuild whenever native
dependencies, config plugins, Expo/React Native runtime, or native settings
change; TS/JS-only changes do not require a native rebuild.

<<<<<<< HEAD
## Verification boundaries
=======
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
- Use Laravel-managed, hashed bearer tokens for mobile authentication.
- Use secure platform storage for tokens.
- Use MapLibre and approved local map data.
- Keep authorization, validation, severity, and persistence in Laravel.
- Keep every source file below 500 lines and prefer 300–400 lines or fewer.
>>>>>>> origin/jassy

- TypeScript unit tests cover pure validation, response mapping, and radius
  geometry.
- component tests cover form and permission/error states.
- Laravel feature tests cover token authorization, validation, severity,
  reporter association, timestamps, and status.
- a physical Android device validates MapLibre, GPS, LAN API and tiles, and the
  selected Expo/React Native/MapLibre combination.
