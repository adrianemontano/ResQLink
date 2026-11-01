# ResQLink Mobile Application Implementation Guide

**Status:** Plan only; do not generate Expo source as part of the migration
**Platform:** Expo + React Native + TypeScript, Android first

## Before implementation

Read the naming conventions, mobile scope, architecture, technology stack, and
verification guide. Preserve ownership: TASK1/Jassy owns access and volunteer
authentication; TASK2/Adriane owns incident reporting and severity; TASK3/Angela
owns dispatcher coordination and shared map integration; TASK4/Monique owns
admin records and reports.

Before selecting versions, verify the current Expo SDK, bundled React Native,
MapLibre release, New Architecture, Node, JDK, Gradle, and Android SDK
requirements using official documentation. Do not copy old component names or
pin a speculative combination.

## Stage 1 — Expo foundation

1. Create a task-scoped branch and generate a fresh Expo TypeScript application
   at repository-root `mobile/`.
2. Configure Expo Router and a feature-oriented structure.
3. Establish typed environment configuration for API and map-style URLs.
4. Install the approved dependencies with Expo-compatible versions: Axios,
   Expo Router, `expo-secure-store`, `expo-location`, `expo-dev-client`, and
   `@maplibre/maplibre-react-native`.
5. Add the MapLibre config plugin to app configuration.
6. Document the selected versions and why they are compatible.

Never put Laravel secrets, database credentials, signing material, or passwords
in Expo public environment values. Commit dependency lockfiles once created.

## Stage 2 — MapLibre compatibility proof of concept

This is a prerequisite for the complete mobile UI.

1. Confirm React Native New Architecture compatibility with the selected
   MapLibre release. Current v11 documentation requires it.
2. Configure Android package identity, location/internet permissions, and any
   debug-only cleartext network policy.
3. Build and install locally with `npx expo run:android`, or create an EAS
   development build.
4. Use a physical Android device with Developer Options and USB debugging, or a
   supported emulator. Accept the computer's debugging authorization prompt.
5. Start Metro with `npx expo start --dev-client` after the build is installed.
6. Load the actual approved TileServer style over the laptop LAN address.
7. Confirm pan/zoom, coordinate selection, and marker repositioning.
8. Generate a geographic GeoJSON impact-radius polygon and render it with the
   selected release's `GeoJSONSource` and layer API.
9. Record device, Expo SDK, React Native, MapLibre, Android SDK, JDK, and build
   results before continuing.

Expo Go is not a fallback for this checkpoint because it does not contain
MapLibre Native. Rebuild after native package, plugin, SDK, or native-setting
changes. Use Metro reload for TS/JS-only edits.

## Stage 3 — Laravel Sanctum authentication

### Backend contract

TASK1 must implement and document JSON token operations before mobile login is
called complete. Proposed paths are:

| Method | Endpoint | Purpose |
| --- | --- | --- |
| POST | `/api/mobile/login` | Validate volunteer and issue token |
| GET | `/api/mobile/user` | Return current volunteer |
| POST | `/api/mobile/logout` | Revoke current token |

These endpoints do not currently exist. Keep existing web session login and
logout unchanged. Require active status, volunteer role, and verified volunteer
profile before issuing a token.

### Client work

1. Create one Axios instance using the configured `/api` base URL.
2. Parse stable JSON validation and authentication errors.
3. Store only the token with `expo-secure-store`.
4. Attach `Authorization: Bearer <token>` through an interceptor.
5. Restore the session safely at launch.
6. On logout, revoke the server token and clear the local token.
7. On `401`, clear invalid state and return to login without exposing secrets.

## Stage 4 — Volunteer incident reporting

1. Confirm the actual Laravel validator and JSON field names before defining
   TypeScript request types.
2. Build inputs for incident category, barangay, nearest landmark, affected
   population, latitude, longitude, impact radius, and notes.
3. Add usability validation without weakening Laravel validation.
4. Request foreground location permission. If denied, explain the limitation
   and retain manual map selection.
5. Synchronize GPS/manual coordinates, marker, and geographic radius polygon.
6. Submit through Axios to `POST /api/incidents` with the bearer token.
7. Preserve form values on recoverable failures and prevent accidental duplicate
   submissions while a request is in progress.
8. Show confirmation based on Laravel's response.

Expo must not send trusted reporter identity, severity, timestamps, or initial
status. Laravel derives them. The current incident route exists but still needs
alignment to the mobile Sanctum/JSON contract.

## Stage 5 — Testing and verification

With explicit repository-owner approval before commands are run:

- verify token login, restoration, authorization, expiry, and logout;
- verify validation and duplicate-submit protection;
- verify GPS granted, denied, disabled, and unavailable states;
- verify manual coordinates and real-world radius geometry;
- verify API and TileServer LAN connectivity on a physical Android device;
- verify incident persistence through Laravel and expected MySQL data;
- verify backend reporter identity, severity, timestamp, and initial status;
- verify API, tile, timeout, and malformed-response error handling; and
- verify the selected Expo/React Native/MapLibre/Android combination.

## Stage 6 — Documentation and handoff

Record prerequisites, exact compatible versions, development-build creation,
start commands, LAN URLs, actual TileServer style endpoint, debug HTTP policy,
test evidence, known limitations, and remaining work. Update TASK1/TASK2 notes
and project-wide docs in the same change as implementation.

## Local device connectivity

Example development addresses:

```text
EXPO_PUBLIC_API_BASE_URL=http://<LAPTOP_LAN_IP>:8000/api
EXPO_PUBLIC_MAP_STYLE_URL=http://<LAPTOP_LAN_IP>:8080/styles/<STYLE_ID>/style.json
```

Do not use `localhost` or `127.0.0.1` from a physical phone. Bind services to a
reachable interface, keep phone and laptop on the same network, and allow the
ports through the firewall. A Metro tunnel exposes Metro only, not Laravel or
TileServer. Use HTTPS in production. Confirm `<STYLE_ID>` from the running
TileServer; it is intentionally not invented here.
