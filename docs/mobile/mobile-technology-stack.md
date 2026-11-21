# ResQLink Mobile Technology Stack

**Status:** Approved architecture; implementation not started
**Target:** Android-first volunteer application

## Approved stack

<<<<<<< HEAD
| Concern | Technology | Purpose |
| --- | --- | --- |
| Mobile framework | Expo + React Native | Native volunteer application |
| Language | TypeScript | Typed application code |
| Navigation | Expo Router | File-based navigation |
| HTTP client | Axios | JSON requests to Laravel |
| Authentication | Laravel Sanctum personal access tokens | Volunteer API sessions |
| Token storage | `expo-secure-store` | Device-protected token storage |
| Location | `expo-location` | Foreground permission and GPS |
| Native maps | `@maplibre/maplibre-react-native` | Self-hosted map, marker, and layers |
| Development runtime | Expo Development Build | Includes project native modules |
| Android builds | Local Android build or EAS Build | Installable development client |
| Backend | Existing Laravel REST API | Validation, authorization, workflow |
| Database | Existing MySQL | Server-side persistence only |
| Web dashboard | Existing Laravel Blade + Bootstrap | Dispatcher and admin workflows |
| Map service | Existing TileServer GL Light | Approved local/self-hosted map data |
=======
Use a deliberately small Flutter stack:

> Flutter and Material 3, Provider, the Dart `http` package, a hashed Laravel
> bearer token,
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
| Authentication | Laravel API login and hashed bearer-token table | Device tokens and protected API routes |
| Token storage | `flutter_secure_storage` | Secure local storage of API tokens |
| Maps | `maplibre_gl` | Map display, pin placement, and radius rendering |
| Device location | `geolocator` | Permission-aware GPS coordinates |
| Permissions | `permission_handler`, if needed | Central handling of device permissions |
| Configuration | Compile-time environment values | API and map service base URLs |
| Backend | Existing Laravel application | Business rules, API, validation, and authorization |
| Database | Existing MySQL database | Central system records accessed only by Laravel |
| Mobile testing | Flutter Test | Widget, unit, and integration behavior |
| Backend testing | PHPUnit | API authentication and incident behavior |
>>>>>>> origin/jassy

Flutter and Dart are retired from the intended stack. Firebase, Supabase,
Google Maps, Mapbox-hosted APIs, and direct MySQL access are not approved.

## Expo Development Builds are mandatory

MapLibre React Native contains native code and is not included in Expo Go.
Expo Go therefore cannot execute the ResQLink map. A development build is a
project-specific debug application containing `expo-dev-client`, MapLibre
Native, and other configured native modules.

The initial implementation must install the package through Expo and configure
its official config plugin:

<<<<<<< HEAD
```json
{
  "expo": {
    "plugins": ["@maplibre/maplibre-react-native"]
  }
}
=======
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

## Authentication: Laravel bearer tokens

The current implementation uses opaque random bearer tokens. The mobile
application exchanges valid credentials and a device name for a token. It then
sends the token as a bearer token on protected requests; Laravel stores only
the token's SHA-256 hash in `mobile_access_tokens`.

This lightweight first-party token approach is used because:

- Random tokens are unguessable and never stored in plaintext by Laravel.
- It works alongside browser session authentication.
- A token can be revoked during logout or an Admin password reset.
- Tokens have a device label and 30-day expiry.
- OAuth infrastructure and downloaded Composer packages are unnecessary.

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
>>>>>>> origin/jassy
```

This is a planning example, not a generated project file. Validate plugin
options against the selected package release before creating the app.

Use `npx expo run:android` for a local Android development build, or an EAS
development profile for a cloud build. After installing the client, use
`npx expo start --dev-client` for normal TypeScript/JavaScript iteration.
Rebuild the native application after adding, removing, or upgrading a native
package; changing a config plugin or native configuration; changing the Expo
SDK or React Native runtime; or regenerating native projects. Ordinary TS/JS
and style changes need only Metro reload.

## Compatibility gate

Do not pin versions until implementation begins. At that time:

1. Select a supported Expo SDK and its bundled React Native version.
2. Confirm Android SDK, Gradle, JDK, and Node requirements in Expo's current
   documentation.
3. Select a compatible stable `@maplibre/maplibre-react-native` release.
4. Confirm New Architecture support. Current MapLibre v11 documentation says
   v11 requires React Native's New Architecture.
5. Confirm the selected release's config plugin and Android requirements.
6. Run the MapLibre proof of concept before building the complete form.

Current v11 documentation uses `Map`, `Camera`, `GeoJSONSource`, and layer
components. Older examples using `MapView` or `ShapeSource` must not be copied
without checking the selected release.

Official references:

- <https://maplibre.org/maplibre-react-native/docs/setup/expo/>
- <https://maplibre.org/maplibre-react-native/docs/setup/migrations/v11/>
- <https://docs.expo.dev/develop/development-builds/introduction/>
- <https://docs.expo.dev/guides/local-app-development/>

## Data and security boundaries

The app may validate input for usability, hold transient UI state, and render
server responses. Laravel remains authoritative for reporter identity, role and
verification checks, validation, severity, timestamps, initial workflow
status, and authorization. The app sends incident inputs only and never
connects directly to MySQL.

Only the bearer token belongs in SecureStore. Passwords, database credentials,
Laravel secrets, signing credentials, and private configuration must never be
stored in source code or public Expo environment variables.

## Map and radius rules

The map consumes the approved TileServer style and data. It supports GPS,
manual coordinate selection, a movable marker, barangay and landmark context,
and an impact-radius preview. The radius is a real-world distance: generate a
geographic GeoJSON polygon around the selected coordinate and render it with a
`GeoJSONSource` and compatible fill/line layers. Do not use a fixed-pixel
screen circle and do not infer authoritative severity on-device.

This design is online/self-hosted, not a claim of fully offline map support.
