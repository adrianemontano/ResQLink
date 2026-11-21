# ResQLink Mobile Verification and Handoff Guide

<<<<<<< HEAD
**Status:** Planned verification for the future Expo implementation
=======
> **Current implementation note:** Run `flutter pub get` after pulling. The
> initial volunteer app now supports secure bearer login, forced password
> change, and optional compressed incident photos. The interactive map picker,
> offline queue, and push notifications remain future work. The current mobile
> API uses Laravel-managed hashed tokens, not Sanctum.

**Companion to:** [Mobile application implementation guide](mobile-implementation-guide.md)  
**Related work:** `TASK1` and `TASK2`
>>>>>>> origin/jassy

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

<<<<<<< HEAD
Keep task identifiers and owners in handoff notes and commits. Do not place
owner names or task numbers in application domain identifiers.
=======
- All four approved categories are accepted.
- An unsupported category is rejected.
- Population and radius boundaries are enforced.
- Latitude and longitude boundaries are enforced.
- Required barangay and landmark values are enforced.
- Notes length is enforced.
- The client cannot choose another reporting volunteer.

## Submission checklist

- A valid request receives `201`.
- Identifier, time, status, and severity parse correctly.
- Repeated tapping does not issue concurrent submissions.
- Form values survive a recoverable failure.
- Dispatcher/admin workflows display the stored report.

## Location and map checklist

- GPS success centers or selects the correct position.
- Permission denial leaves manual map selection usable.
- Permanently denied permission gives useful settings guidance.
- Map pin and form coordinates remain synchronized.
- Impact radius renders and submits in metres.
- Barangay and landmark data work without prohibited external APIs.
- The application handles map or tile-service unavailability safely.

## Quality checklist

- Each modified or created code file is below 500 lines.
- Target files are preferably 300–400 lines or fewer.
- No secrets or machine-specific production values are committed.
- API errors do not reveal stack traces.
- Setup and behavior documentation matches the implementation.
- The application remains usable when GPS permission is denied.

## Documentation handoff

For each approved implementation milestone, update documentation in the same
change. At minimum, keep these areas current:

- Root `README.md` project structure and mobile status.
- `docs/development-setup.md` Flutter and Android prerequisites.
- `docs/implementation/incident-reporting-feature.md` mobile API behavior.
- Relevant `TASK1` documentation for mobile authentication.
- Relevant `TASK2` documentation for mobile incident reporting.
- Files under `docs/mobile/` for scope, stack, architecture, and implementation.

Document:

- Supported Flutter and Dart versions.
- Required Android SDK configuration.
- Commands for installing packages and launching the application.
- API and map URL configuration.
- Android permissions.
- Authentication request and response examples.
- Incident request and response examples.
- Emulator and physical-device networking.
- Map tile availability and offline limitations.
- Known issues and deferred features.

## Recommended commit sequence

Keep commits focused and include the applicable task identifier where it helps
traceability:

1. Create Flutter mobile application foundation.
2. Verify `TASK1` mobile bearer login, profile, and logout API.
3. Add Flutter volunteer authentication flow.
4. Add `TASK2` mobile incident form and API submission.
5. Add `TASK2` MapLibre location selection.
6. Document mobile setup and approved behavior.

Do not mark a task complete until both its approved implementation and related
documentation are complete.

## Recommended first-session checkpoint

For the first development session, stop after this achievable checkpoint:

1. Install and validate Flutter/Android tooling.
2. Create the root `mobile/` Flutter project.
3. Add the initial packages.
4. Create the application theme and environment configuration.
5. Display a basic ResQLink login screen without connecting it to Laravel.
6. Review and agree on the authentication contract with the `TASK1` owner.

The next session can implement the deferred map picker and connect it to the API. This avoids
mixing Flutter setup, backend schema changes, authentication, and mapping into
one difficult debugging session.
>>>>>>> origin/jassy
