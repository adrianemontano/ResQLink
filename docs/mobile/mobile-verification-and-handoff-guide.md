# ResQLink Mobile Verification and Handoff Guide

**Status:** Planned verification for the future Expo implementation

Do not run repository lint, test, or build commands without explicit user
approval. Record the command, environment, result, and unresolved failures when
verification is authorized.

## Development-build evidence

- [ ] Selected Expo SDK and React Native versions are recorded.
- [ ] Selected MapLibre version and New Architecture requirement are recorded.
- [ ] Node, JDK, Android SDK, Gradle, and device prerequisites are recorded.
- [ ] MapLibre config plugin is present in Expo app configuration.
- [ ] A local `npx expo run:android` or EAS development build succeeds.
- [ ] The installed client opens through `npx expo start --dev-client`.
- [ ] No map certification relies on Expo Go.
- [ ] Native rebuild triggers are documented.

## Authentication

- [ ] Implemented endpoint paths match Laravel routes and API documentation.
- [ ] Invalid credentials, inactive accounts, wrong roles, and unverified
  volunteers are rejected by Laravel.
- [ ] Sanctum token is stored only in SecureStore.
- [ ] Axios sends the bearer token to protected operations.
- [ ] Session restoration handles missing, expired, and revoked tokens.
- [ ] Logout revokes the server token and clears the device copy.
- [ ] Web session authentication remains unchanged.

## Incident reporting

- [ ] Category, barangay, landmark, affected population, coordinates, radius,
  and notes match the final Laravel contract.
- [ ] Laravel rejects invalid data independently of client validation.
- [ ] Reporter identity comes from the authenticated token.
- [ ] Laravel calculates severity and assigns timestamps and initial status.
- [ ] A successful request is visible through Laravel-backed records in MySQL.
- [ ] In-flight submit protection prevents accidental duplicates.
- [ ] Retryable failure retains entered values.

## Location and map

- [ ] Foreground permission granted, denied, disabled, and unavailable states
  are understandable.
- [ ] Manual selection works without GPS permission.
- [ ] Marker and displayed coordinates stay synchronized.
- [ ] Impact radius is a geographic GeoJSON polygon, not a pixel circle.
- [ ] Actual TileServer style, barangays, and required landmarks render.
- [ ] Unavailable tiles produce a graceful state and recovery path.
- [ ] Documentation does not claim fully offline maps.

## Physical Android networking

- [ ] Phone and laptop share a reachable network.
- [ ] API and TileServer use the laptop LAN address, not phone `localhost`.
- [ ] Laravel and TileServer listen on reachable interfaces.
- [ ] Firewall permits the configured development ports.
- [ ] Any cleartext HTTP allowance is debug-only.
- [ ] Production URLs use HTTPS.
- [ ] Handoff notes explain that a Metro tunnel does not expose other services.

## Handoff record

Include:

- exact tool and dependency versions;
- physical device/Android version and USB-debugging steps;
- local or EAS build procedure;
- API base URL and actual TileServer style URL format;
- implemented versus planned Laravel endpoints;
- environment-variable names without secret values;
- verification results and approved commands run;
- known limitations and remaining TASK1/TASK2/TASK3 work; and
- links to architecture, stack, setup, and implementation documents.

## Recommended delivery sequence

1. Stage 1: Expo foundation.
2. Stage 2: physical-device MapLibre proof of concept.
3. Stage 3: Sanctum authentication.
4. Stage 4: volunteer incident reporting.
5. Stage 5: approved tests and verification.
6. Stage 6: documentation and handoff.

Keep task identifiers and owners in handoff notes and commits. Do not place
owner names or task numbers in application domain identifiers.
