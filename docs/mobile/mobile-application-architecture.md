# ResQLink Mobile Application Architecture

**Status:** Approved target architecture; Expo source not yet generated
**Style:** Feature-oriented Expo React Native client with Laravel REST API

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

MySQL stores users, volunteer records, incidents, statuses, and related domain
data. It is reachable by Laravel only; Expo never receives database credentials.

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

1. Volunteer enters credentials.
2. Expo sends them to the planned mobile login endpoint with Axios.
3. Laravel validates credentials, active account, volunteer role, and verified
   volunteer status.
4. Laravel issues a Sanctum personal access token.
5. Expo stores the token in SecureStore and restores it on a later launch.
6. An Axios interceptor attaches `Authorization: Bearer <token>`.
7. Laravel authenticates and authorizes each protected request.
8. Logout asks Laravel to revoke the current token, then clears the local token.

Current repository status: mobile token login/logout endpoints are not yet
implemented. `POST /api/incidents` exists, but its current `auth` middleware is
not yet the documented Sanctum mobile contract. Web session login/logout must
remain separate.

## Incident submission contract

The mobile request preserves these domain fields and must use the exact backend
field names established by the final API contract:

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

## Verification boundaries

- TypeScript unit tests cover pure validation, response mapping, and radius
  geometry.
- component tests cover form and permission/error states.
- Laravel feature tests cover token authorization, validation, severity,
  reporter association, timestamps, and status.
- a physical Android device validates MapLibre, GPS, LAN API and tiles, and the
  selected Expo/React Native/MapLibre combination.
