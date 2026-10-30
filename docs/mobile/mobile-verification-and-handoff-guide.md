# ResQLink Mobile Verification and Handoff Guide

**Companion to:** [Mobile application implementation guide](mobile-implementation-guide.md)  
**Related work:** `TASK1` and `TASK2`

## Purpose

Use this checklist after implementing the core authentication, incident form,
GPS, and MapLibre phases. It separates final reliability and handoff work from
the main implementation instructions so both documents remain focused and
within the repository file-size limit.

## Reliability and security

### Prevent duplicate reports

- Disable submit while a request is active.
- Do not automatically retry a timed-out POST without knowing whether Laravel
  stored it.
- Consider an idempotency design later if unstable networks cause duplicates.

### Handle authentication expiry

When Laravel returns `401` for a protected operation:

1. Stop the operation.
2. Clear the invalid local token.
3. Preserve a safe incident draft in memory where possible.
4. Return to login with an expired-session message.

Handle `403` separately because the volunteer may need admin support rather
than a fresh login.

### Protect sensitive information

- Use HTTPS outside local development.
- Never log tokens, passwords, or sensitive profile data.
- Never store passwords.
- Keep Laravel and database secrets out of Flutter assets.
- Return user-safe server messages without stack traces.
- Apply rate limiting to login and incident submission as appropriate.

## Verification permission

Do not run automated tests, lint, builds, or other testing commands through an
AI coding agent until the user explicitly answers yes. Once approval is given,
run checks in proportion to the affected work and record the results.

## Authentication checklist

- Active, verified volunteer login succeeds.
- Username and email login behave as documented.
- Incorrect credentials fail safely.
- Inactive and unverified volunteers are rejected.
- Admin and dispatcher accounts are rejected.
- Saved token restores a valid session.
- Revoked or invalid token returns to login.
- Logout revokes the current device token.

## Incident validation checklist

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
2. Add `TASK1` Sanctum mobile authentication API.
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

The next session can implement Sanctum and connect the login screen. This avoids
mixing Flutter setup, backend schema changes, authentication, and mapping into
one difficult debugging session.
