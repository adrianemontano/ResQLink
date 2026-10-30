# ResQLink Mobile Technology Stack

**Status:** Recommended initial stack  
**Platform:** Android first, with an iOS-compatible Flutter codebase  
**Related work:** `TASK1` and `TASK2`

## Recommendation

Use a deliberately small Flutter stack:

> Flutter and Material 3, Provider, the Dart `http` package, Laravel Sanctum,
> secure token storage, Geolocator, and MapLibre, backed by the existing Laravel
> and MySQL application.

This choice follows the approved project technology while avoiding unnecessary
infrastructure and complex application frameworks.

## Stack overview

| Concern | Technology | Responsibility |
| --- | --- | --- |
| Mobile framework | Flutter and Dart | Android and future iOS application |
| User interface | Material 3 | Forms, navigation, dialogs, and visual theme |
| State management | Provider | Authentication and form workflow state |
| HTTP client | Dart `http` package | JSON communication with Laravel |
| Authentication | Laravel Sanctum | Device tokens and protected API routes |
| Token storage | `flutter_secure_storage` | Secure local storage of API tokens |
| Maps | `maplibre_gl` | Map display, pin placement, and radius rendering |
| Device location | `geolocator` | Permission-aware GPS coordinates |
| Permissions | `permission_handler`, if needed | Central handling of device permissions |
| Configuration | Compile-time environment values | API and map service base URLs |
| Backend | Existing Laravel application | Business rules, API, validation, and authorization |
| Database | Existing MySQL database | Central system records accessed only by Laravel |
| Mobile testing | Flutter Test | Widget, unit, and integration behavior |
| Backend testing | PHPUnit | API authentication and incident behavior |

## Why Flutter

Flutter is already the approved mobile technology in ResQLink. It provides:

- One Dart codebase for Android and potential future iOS support.
- A complete widget toolkit without requiring a separate UI library.
- Strong form and validation support.
- Mature packages for HTTP, secure storage, location, and maps.
- Fast development feedback through hot reload.
- A project structure that can remain understandable for a capstone team.

Android should be the first supported platform because it can be developed and
built on Windows. Producing an iOS build requires macOS and Xcode, even though
most Flutter source code can remain shared.

## User interface: Material 3

Use Flutter's built-in Material 3 components instead of installing a third-party
design system. Suitable components include:

- `Scaffold` for screen structure.
- `TextFormField` for incident inputs.
- `DropdownButtonFormField` for category selection.
- `FilledButton` for primary actions.
- `SnackBar` or inline panels for messages.
- `AlertDialog` for important confirmation.
- `CircularProgressIndicator` for loading states.

Define ResQLink colors, typography, field decoration, and button styles in one
application theme. Avoid repeating visual constants in individual screens.

## State management: Provider

Use Provider for the small amount of shared state expected in the first release:

- Current authentication state.
- Authenticated volunteer profile.
- Login progress and errors.
- Incident submission progress and errors.
- Selected incident location where it must be shared across widgets.

Keep temporary field values inside the form when they do not need to be shared.
Do not put every text controller or map detail into global state.

Provider is recommended because it has a smaller learning and boilerplate cost
than Bloc or Redux. If the application grows substantially, the team can record
and approve a later state-management decision.

## HTTP communication

Use the Dart `http` package behind a small application-owned API client. The API
client should handle:

- The Laravel API base URL.
- JSON encoding and decoding.
- `Accept: application/json`.
- `Content-Type: application/json` when a body is sent.
- `Authorization: Bearer <token>` for protected requests.
- Request timeouts.
- Conversion of HTTP errors into understandable application errors.

Screens should call feature services, not construct URLs and headers directly.
This keeps networking consistent and easier to test.

## Authentication: Laravel Sanctum

Use Laravel Sanctum personal access tokens for mobile authentication. The mobile
application exchanges valid credentials and a device name for a token. It then
sends the token as a bearer token on protected requests.

Sanctum is preferred here because:

- It is designed for simple Laravel API and mobile token authentication.
- It works alongside the existing browser session authentication.
- A token can be revoked during logout.
- Tokens can be separated by device.
- OAuth infrastructure is unnecessary for the current project.

The browser login and mobile login should remain separate entry points while
sharing consistent credential, activation, role, and verification rules.

## Secure local storage

Use `flutter_secure_storage` for the API token. On supported platforms it uses
the operating system's protected credential facilities.

Store only the minimum session information needed. Do not store:

- The volunteer's password.
- Database credentials.
- Laravel application secrets.
- Full sensitive volunteer documents.

Removing the local token is not sufficient for logout. The application should
first request token revocation from Laravel and then clear local session data.

## Maps: MapLibre

Use `maplibre_gl` to remain aligned with the ResQLink technology decision and
existing web mapping approach. The mobile map is responsible for presentation
and location selection, including:

- Displaying the approved basemap.
- Navigating to Cebu City.
- Placing and dragging an incident marker.
- Displaying an impact-radius circle.
- Displaying barangay boundary GeoJSON.
- Displaying or selecting approved landmarks.

The map does not determine whether a report is authorized or valid. Laravel
still validates submitted coordinates and report fields.

The existing project prohibits remote tile URLs, CDNs, external geocoding, and
external map APIs. Development may use the project's locally hosted TileServer,
but deployment requires a documented decision between a reachable self-hosted
tile service and packaged offline map data.

## Location and permissions

Use `geolocator` to:

- Check whether location services are enabled.
- Check and request location permission.
- Retrieve the current position when permitted.
- Report meaningful failures to the user.

Use `permission_handler` only if it materially simplifies permission handling
across features. Avoid redundant permission libraries when Geolocator alone
covers the approved workflow.

The application should request foreground location only. Continuous background
tracking is outside the initial scope.

## Environment configuration

The mobile application needs configurable service addresses, such as:

```text
API_BASE_URL=http://10.0.2.2:8000
MAP_STYLE_URL=http://10.0.2.2:8080/styles/resqlink/style.json
```

Use compile-time values such as `--dart-define` for environment-specific URLs.
Do not commit production secrets or assume that `localhost` points to the
development computer from every device.

Common development addresses are:

| Runtime | Host computer address from the app |
| --- | --- |
| Android emulator | `10.0.2.2` |
| Physical Android device | Computer's reachable local network IP |
| Flutter web or desktop development | Commonly `127.0.0.1` |

Production endpoints must use HTTPS.

## Data ownership

Laravel and MySQL remain the source of truth for:

- Users, roles, activation, and volunteer verification.
- Incident reports.
- Reporter association.
- Incident timestamps and identifiers.
- Incident statuses.
- Preliminary severity classification.

Flutter may hold temporary presentation state and cached profile information,
but it must not become an independent authority for these records.

## Packages to start with

The initial Flutter application is expected to need packages in these groups:

```text
provider
http
flutter_secure_storage
geolocator
maplibre_gl
```

Add `permission_handler` only if the approved implementation needs it. Select
current versions compatible with the installed Flutter SDK and commit the
generated `pubspec.lock` for the application.

## Technologies intentionally not selected

| Technology | Reason not selected initially |
| --- | --- |
| Firebase Authentication | Volunteer identity already belongs to Laravel and MySQL |
| Supabase | Duplicates the existing backend and database responsibilities |
| Bloc or Redux | Adds avoidable boilerplate for the initial application size |
| GraphQL | Existing operations fit a small REST API |
| Laravel Passport | OAuth2 is more complex than the current first-party client needs |
| Direct MySQL access | Insecure and bypasses Laravel business rules |
| Microservices | Adds deployment and integration complexity without a current need |
| Remote geocoding API | Conflicts with the approved local map-data approach |

## Versioning guidance

- Use a stable Flutter SDK release agreed on by the team.
- Record the agreed Flutter and Dart versions in development setup documentation.
- Pin package constraints in `pubspec.yaml` and commit `pubspec.lock`.
- Review breaking changes before upgrading Flutter or MapLibre.
- Do not copy package versions from an old guide without checking compatibility.

## Simplicity rules

- Prefer Flutter SDK features before adding packages.
- Add a dependency only when it has a clear responsibility.
- Keep widgets and services focused and reusable.
- Keep every code file below 500 lines, targeting 300–400 lines or fewer.
- Avoid premature abstractions and generated layers.
- Keep server business rules in Laravel.
- Document every approved setup, API, or user-facing behavior change.

