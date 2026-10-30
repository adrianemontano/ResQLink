# ResQLink Technology Stack

**Status:** Approved architecture and current implementation summary

## Stack overview

| Component | Technology | State |
| --- | --- | --- |
| Backend and REST API | Laravel / PHP | Implemented; mobile token surface incomplete |
| Database | MySQL with Eloquent | Implemented |
| Dispatcher/admin dashboard | Laravel Blade + Bootstrap | Implemented |
| Web asset tooling | Vite | Implemented |
| Web maps | MapLibre GL JS | Implemented with local/self-hosted data |
| Local map service | TileServer GL Light | Existing approved service |
| Volunteer mobile app | Expo + React Native | Approved; not yet generated |
| Mobile language | TypeScript | Approved |
| Navigation | Expo Router | Approved |
| API communication | Axios | Approved |
| Mobile authentication | Laravel Sanctum personal access tokens | Planned |
| Secure token storage | `expo-secure-store` | Approved |
| GPS/location | `expo-location` | Approved |
| Native maps | `@maplibre/maplibre-react-native` | Approved; proof of concept required |
| Mobile runtime | Expo Development Build | Mandatory for native MapLibre |
| Android build | Local Android build or EAS Build | Approved alternatives |

Flutter and Dart are retired. The removed implementation will not be migrated.
Laravel, MySQL, Blade, Bootstrap, the web MapLibre integration, TileServer, and
approved local map datasets remain unchanged.

## Architecture

```text
Volunteer Android app
Expo React Native -> Axios -> Laravel REST API -> Eloquent -> MySQL
       |                              |
       |                              `-> authoritative identity, severity,
       |                                  timestamps, status, authorization
       `-> MapLibre Native -> TileServer GL Light -> approved map dataset

Dispatcher/Admin browser -> Blade + Bootstrap + MapLibre GL JS -> Laravel
```

This architecture supports the Notify–Receive–Dispatch workflow without giving
the client direct database access.

## Backend and database

Laravel owns authentication and authorization, input validation, reporter
association, preliminary severity assessment, timestamps, incident state, and
JSON responses. Eloquent is the only application path to MySQL. Existing web
sessions remain separate from planned mobile Sanctum bearer tokens.

Current API status:

- `POST /api/incidents` exists but still requires alignment to the documented
  Sanctum mobile middleware and stable JSON contract.
- Mobile token login, current-user, and logout endpoints are planned, not
  implemented.
- Expo never connects directly to MySQL.

## Web dashboard

The dispatcher and admin modules remain server-rendered Laravel Blade with
Bootstrap and existing application assets. Their MapLibre GL JS integration is
not replaced by the native mobile library.

## Mobile client

Expo and React Native provide the Android-first client; TypeScript supplies
typed routes, API contracts, and feature logic. Expo Router manages navigation.
Axios sends JSON and attaches the Sanctum bearer token. SecureStore holds only
that token. Expo Location handles foreground permission and GPS.

The approved client does not include Firebase, Supabase, Google Maps, hosted
Mapbox APIs, or direct MySQL access.

## MapLibre and development builds

`@maplibre/maplibre-react-native` contains native code not bundled into Expo Go.
Testing the map therefore requires a project-specific Expo Development Build.
The package config plugin must be included when native projects are generated.

Local Android build:

```text
npx expo run:android
npx expo start --dev-client
```

An EAS development build is the alternative. Rebuild when native dependencies,
config plugins, Expo/React Native runtime, or native settings change. Metro is
sufficient for ordinary TypeScript/JavaScript changes.

Do not pin a version matrix in planning documentation. At implementation time,
confirm the selected Expo SDK and bundled React Native version, MapLibre stable
release, New Architecture requirement, Android SDK, Gradle, JDK, and Node
compatibility. Current MapLibre v11 documentation requires New Architecture
and uses `Map`, `Camera`, `GeoJSONSource`, and layer APIs; older examples using
`MapView` or `ShapeSource` may not apply.

## Self-hosted map architecture

Both clients retain the approved local/self-hosted dataset and TileServer GL
Light. Mobile supports GPS, manual coordinate selection, a movable marker,
barangay and landmark context, and an impact-radius preview. The radius is a
real-world geographic distance represented as a GeoJSON polygon and rendered
through MapLibre layers—not a fixed pixel circle. Laravel, not the app, remains
authoritative for severity.

The design requires reachable tiles and does not claim full offline maps.

## Device networking

A physical phone reaches laptop services through the laptop LAN IP, for example:

```text
http://<LAPTOP_LAN_IP>:8000/api
http://<LAPTOP_LAN_IP>:8080/styles/<STYLE_ID>/style.json
```

Phone `localhost` is not the laptop. Same-network routing, service binding,
firewall access, and debug-only cleartext policy must be configured. Production
uses HTTPS. An Expo tunnel does not automatically expose Laravel or TileServer.
The actual style endpoint must be verified from the existing server.

## Official compatibility references

- <https://docs.expo.dev/>
- <https://docs.expo.dev/develop/development-builds/introduction/>
- <https://maplibre.org/maplibre-react-native/>
- <https://maplibre.org/maplibre-react-native/docs/setup/expo/>
