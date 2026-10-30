# ResQLink Mobile Technology Stack

**Status:** Approved architecture; implementation not started
**Target:** Android-first volunteer application

## Approved stack

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

Flutter and Dart are retired from the intended stack. Firebase, Supabase,
Google Maps, Mapbox-hosted APIs, and direct MySQL access are not approved.

## Expo Development Builds are mandatory

MapLibre React Native contains native code and is not included in Expo Go.
Expo Go therefore cannot execute the ResQLink map. A development build is a
project-specific debug application containing `expo-dev-client`, MapLibre
Native, and other configured native modules.

The initial implementation must install the package through Expo and configure
its official config plugin:

```json
{
  "expo": {
    "plugins": ["@maplibre/maplibre-react-native"]
  }
}
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
