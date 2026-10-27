# ResQLink Android Studio Setup and Mobile Implementation Guide

**Platform:** Flutter for Android on Windows  
**Project location:** `mobile/`  
**Related work:** `TASK1` (Jassy) and `TASK2` (Adriane)  
**Guide status:** First configuration and implementation roadmap

## 1. Purpose and current state

Use this guide to configure Android Studio for the existing ResQLink Flutter
project and then implement the first volunteer mobile workflow. Do not create a
second Flutter project: the repository already contains the application in
`mobile/`.

At the time this guide was written, the scaffold has:

- Android application ID and namespace `com.resqlink.mobile`.
- Dart SDK constraint `^3.13.5` in `mobile/pubspec.yaml`.
- Java and Kotlin JVM target 17.
- `provider`, `http`, `flutter_secure_storage`, and `geolocator` dependencies.
- Only the generated `lib/main.dart`; feature implementation has not started.
- No `maplibre_gl` dependency yet.
- No Laravel Sanctum mobile authentication endpoints yet.

Laravel remains the source of truth. Flutter must never connect directly to
MySQL or decide final authorization, incident status, timestamps, or severity.

## 2. Read these project documents first

Read these before changing code:

1. [Naming conventions](../naming-conventions.md)
2. [Mobile scope](mobile-application-scope.md)
3. [Mobile architecture](mobile-application-architecture.md)
4. [Mobile technology stack](mobile-technology-stack.md)
5. [Existing implementation roadmap](mobile-implementation-guide.md)
6. [Development setup](../development-setup.md)

Mobile authentication belongs to `TASK1` and incident reporting belongs to
`TASK2`. Coordinate API contracts with the relevant owner. Use task identifiers
in branches, commits, checklists, and implementation notes, but not in Dart/PHP
class names, endpoints, database names, or user-facing labels.

## 3. Install the Windows development tools

### 3.1 Install Git and Flutter

1. Install Git for Windows.
2. Download the stable Flutter SDK from the official Flutter installation page.
3. Extract it to a short user-writable path without spaces, for example
   `C:\src\flutter`. Do not place it in `Program Files`.
4. Add the Flutter SDK's `bin` directory to the user `Path` environment variable.
5. Close and reopen PowerShell and Android Studio after changing `Path`.

Check the installation in PowerShell:

```powershell
flutter --version
dart --version
```

The Flutter Android Studio plugin does not install the Flutter SDK. Both are
required. Official references:

- <https://docs.flutter.dev/install/manual>
- <https://docs.flutter.dev/platform-integration/android/setup>

### 3.2 Install Android Studio and Android SDK components

1. Install the latest stable Android Studio.
2. Complete its first-run Setup Wizard using the standard installation.
3. Open **More Actions > SDK Manager** on the welcome screen, or **Tools > SDK
   Manager** when a project is open.
4. Under **SDK Platforms**, install the platform recommended by the current
   Flutter Android setup page and accepted by this project's Flutter SDK.
5. Under **SDK Tools**, install or update:
   - Android SDK Build-Tools
   - Android SDK Command-line Tools (latest)
   - Android SDK Platform-Tools
   - Android Emulator
   - Google USB Driver if a physical device will be used on Windows
6. Apply the changes and allow downloads to finish.

Avoid forcing a `compileSdk` number into Gradle just because it appears in a
tutorial. The current project deliberately uses `flutter.compileSdkVersion`,
`flutter.minSdkVersion`, and `flutter.targetSdkVersion`.

### 3.3 Install the Flutter and Dart plugins

In Android Studio:

1. Open **File > Settings > Plugins > Marketplace**.
2. Search for and install **Flutter**.
3. Accept installation of the Dart plugin when prompted.
4. Restart Android Studio.
5. Open **File > Settings > Languages & Frameworks > Flutter** and confirm that
   the Flutter SDK path points to the extracted SDK directory.

Reference: <https://docs.flutter.dev/tools/android-studio>

### 3.4 Accept Android licenses and inspect the toolchain

Run these commands yourself from PowerShell:

```powershell
flutter doctor --android-licenses
flutter doctor -v
```

Read each license before accepting it. Resolve errors under **Android
toolchain** and **Android Studio**. Warnings for platforms not being developed,
such as Visual Studio for Windows desktop, do not block Android development.

Repository rule: an AI coding agent must obtain your explicit `yes` before it
runs lint, test, build, or other testing commands. The commands in this guide
are instructions for the developer and are not records of completed checks.

## 4. Open the existing Flutter project correctly

1. Start Android Studio.
2. Choose **Open**.
3. Select `ResQLink\mobile`, not `ResQLink\mobile\android`.
4. Wait for Dart indexing and Gradle synchronization to finish.
5. Confirm `mobile/pubspec.yaml` appears at the project root.
6. In the Project panel, select **Project** or **Project Files** if `lib/` is not
   visible.
7. If dependencies are not restored automatically, open Android Studio's
   terminal in `mobile/` and run:

```powershell
flutter pub get
```

Open only `mobile/android` when troubleshooting native Gradle code. Opening the
whole Flutter project is required for normal Dart editing, hot reload, Flutter
run configurations, and the widget inspector.

## 5. Configure an Android test device

### Option A: Android Emulator

1. Open **Tools > Device Manager**.
2. Select **Create Virtual Device**.
3. Choose a normal phone profile, such as a current Pixel profile.
4. Select a stable x86_64 or arm64 system image compatible with the host.
5. Download the image if needed and finish the wizard.
6. Start the emulator and wait for the Android home screen.
7. Confirm it appears in Android Studio's device selector.

The emulator reaches services on the Windows host through `10.0.2.2`; its own
`localhost` refers to the emulator. Android's Device Manager reference is
<https://developer.android.com/studio/run/managing-avds>.

### Option B: Physical Android device

1. On the phone, enable Developer options by tapping **Build number** seven
   times in **Settings > About phone**.
2. Enable **USB debugging** in Developer options.
3. Connect the phone by USB and approve the computer's RSA prompt.
4. Install the manufacturer's USB driver if Windows does not detect the phone.
5. Confirm the phone appears in Android Studio's device selector.

For wireless debugging, both devices must be on a trusted network. A physical
phone cannot use `10.0.2.2`; use the development computer's LAN IPv4 address.

## 6. First Android project configuration

### 6.1 Confirm identity and Java settings

Review `mobile/android/app/build.gradle.kts`:

```kotlin
namespace = "com.resqlink.mobile"
applicationId = "com.resqlink.mobile"
sourceCompatibility = JavaVersion.VERSION_17
targetCompatibility = JavaVersion.VERSION_17
```

Keep this application ID unless the team approves a permanent organization
domain. Changing it later affects installed app identity, signing, and store
configuration. In Android Studio's Gradle settings, prefer the JDK bundled with
Android Studio unless the Flutter toolchain reports a different requirement.

Change the generated app label from `mobile` to `ResQLink` in
`mobile/android/app/src/main/AndroidManifest.xml` when implementation begins:

```xml
<application
    android:label="ResQLink"
    ...>
```

### 6.2 Add required Android permissions

Before API, map, and GPS integration, place these immediately inside the root
`<manifest>` element and before `<application>`:

```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
```

Do not request background location. The volunteer must be able to select a
location manually if foreground permission is denied.

### 6.3 Permit local HTTP only in debug builds

The local Laravel and tile servers use HTTP. Keep the release manifest strict
and add a debug-only override at
`mobile/android/app/src/debug/AndroidManifest.xml`:

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">
    <application
        android:networkSecurityConfig="@xml/network_security_config"
        android:usesCleartextTraffic="true" />
</manifest>
```

Create `mobile/android/app/src/debug/res/xml/network_security_config.xml`:

```xml
<?xml version="1.0" encoding="utf-8"?>
<network-security-config>
    <base-config cleartextTrafficPermitted="true" />
</network-security-config>
```

Never add cleartext permission to the main or release manifest. Production API
and map endpoints must use HTTPS. Flutter's network policy reference is
<https://docs.flutter.dev/release/breaking-changes/network-policy-ios-android>.

## 7. Start the local ResQLink services

Use separate PowerShell terminals.

From `backend/`, start Laravel:

```powershell
php artisan serve
```

From `backend/`, start the existing local tile server:

```powershell
npx.cmd tileserver-gl-light public/maps/osm-2020-02-10-v3.11_philippines_cebu.mbtiles --port 8080
```

For an Android emulator, configure:

```text
API_BASE_URL=http://10.0.2.2:8000
MAP_STYLE_URL=http://10.0.2.2:8080/styles/positron/style.json
```

For a physical phone, start Laravel on an address reachable over the LAN:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

Then replace `10.0.2.2` in both URLs with the computer's LAN IPv4 address. Keep
the computer and phone on the same trusted network and allow only the necessary
private-network firewall access. Do not expose the development server to the
public internet.

The current `POST /api/incidents` route uses browser session authentication.
The mobile client cannot complete bearer-token login until the `TASK1` Sanctum
API work described below is implemented.

## 8. Create the Android Studio run configuration

The application should read URLs through Dart compile-time values:

```dart
abstract final class Environment {
  static const apiBaseUrl = String.fromEnvironment('API_BASE_URL');
  static const mapStyleUrl = String.fromEnvironment('MAP_STYLE_URL');
}
```

In Android Studio:

1. Open **Run > Edit Configurations**.
2. Add or select a **Flutter** configuration.
3. Set the Dart entrypoint to `lib/main.dart`.
4. In **Additional run args**, enter one line:

```text
--dart-define=API_BASE_URL=http://10.0.2.2:8000 --dart-define=MAP_STYLE_URL=http://10.0.2.2:8080/styles/positron/style.json
```

5. Name it `ResQLink Android (local)` and apply it.

URLs are configuration, not secrets. Never place passwords, bearer tokens,
database credentials, `APP_KEY`, or signing passwords in `--dart-define`.

## 9. Recommended source structure

Build the following structure gradually; do not create empty files merely to
match the diagram:

```text
mobile/lib/
|-- app/
|   |-- app.dart
|   |-- app_router.dart
|   `-- app_theme.dart
|-- core/
|   |-- config/environment.dart
|   |-- errors/api_exception.dart
|   |-- network/api_client.dart
|   `-- storage/token_storage.dart
|-- features/
|   |-- authentication/
|   |   |-- data/auth_service.dart
|   |   |-- models/authenticated_user.dart
|   |   `-- presentation/
|   |       |-- auth_provider.dart
|   |       `-- login_screen.dart
|   `-- incidents/
|       |-- data/incident_service.dart
|       |-- models/
|       |   |-- incident_submission.dart
|       |   `-- submitted_incident.dart
|       `-- presentation/
|           |-- incident_form_screen.dart
|           |-- incident_map.dart
|           `-- submission_confirmation_screen.dart
`-- main.dart
```

Every code file must remain below 500 lines; target 300 lines or fewer. Extract
widgets, services, models, and helpers by responsibility rather than splitting
code arbitrarily.

## 10. Implementation sequence

### Phase A: Application foundation

1. Replace the counter sample with `MaterialApp` using Material 3.
2. Add the shared ResQLink theme and environment validation.
3. Add `ApiException`, `TokenStorage`, and `ApiClient`.
4. Make `ApiClient` add JSON headers, bearer tokens, timeouts, safe JSON
   decoding, and Laravel `422` field errors.
5. Keep navigation and UI feedback outside `ApiClient`.

Checkpoint: the app opens to a branded placeholder/login screen and clearly
reports missing URL configuration.

### Phase B: `TASK1` Laravel mobile authentication

Coordinate with Jassy before finalizing the contract. Add Sanctum and implement:

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `POST` | `/api/auth/login` | Validate volunteer and issue device token |
| `GET` | `/api/auth/user` | Restore the authenticated volunteer |
| `POST` | `/api/auth/logout` | Revoke the current device token |

Login must check credentials, active account, `volunteer` role, an existing
volunteer profile, and `verified` verification status. Use `401` for invalid
credentials/token, `403` for prohibited accounts, `422` for invalid input, and
`429` for rate limiting. Never return hashes, documents, or internal errors.

Protect mobile incident submission with `auth:sanctum`. Laravel must derive the
reporter from the token; Flutter must not send a reporter ID. Installing
Sanctum may add a token migration, so migrations and tests require explicit user
approval before an AI agent runs them.

### Phase C: Flutter authentication

1. Create the typed user and login response models.
2. Implement `AuthService.login`, `currentUser`, and `logout`.
3. Store only the token with `flutter_secure_storage`; never store passwords.
4. Implement explicit checking, authenticated, unauthenticated, submitting,
   and failure states in `AuthProvider`.
5. Restore a saved session with `GET /api/auth/user` on startup.
6. Clear an invalid token after `401`; explain `403` without retrying login.
7. Disable duplicate login taps and revoke the server token during logout.

Checkpoint: verified volunteer login, restart restoration, expiry, and logout
all follow the approved contract. Admin, dispatcher, inactive, and unverified
accounts are rejected.

### Phase D: `TASK2` incident form without a map

Implement the API integration before adding map complexity. Send exactly:

```json
{
  "category": "Flood",
  "barangay": "Example Barangay",
  "nearest_landmark": "Example Hall",
  "affected_population": 10,
  "latitude": 10.3157,
  "longitude": 123.8854,
  "impact_radius": 250,
  "notes": "Optional details"
}
```

Mirror the current Laravel constraints for immediate feedback:

- Category is Flood, Earthquake, Landslide, or Fire.
- Barangay is required and at most 100 characters.
- Nearest landmark is required and at most 255 characters.
- Affected population is an integer from 0 to 1,000,000.
- Latitude is -90 through 90; longitude is -180 through 180.
- Impact radius is greater than 0 and at most 100,000 metres.
- Notes are optional and at most 5,000 characters.

Laravel remains authoritative. Preserve form values after validation or network
failure, disable submit while a request is active, and do not automatically
retry a timed-out POST because the first request may already have succeeded.

Checkpoint: a `201` response displays identifier, server timestamp, `Reported`
status, preliminary severity, category, barangay, and landmark.

### Phase E: GPS and MapLibre

1. Add a `maplibre_gl` version compatible with the installed Flutter SDK.
2. Implement a focused foreground-location service using `geolocator`.
3. Handle services disabled, permission denied, permanently denied, timeout,
   and success as distinct states.
4. Build a reusable map widget that loads `MAP_STYLE_URL`, starts over Cebu
   City, supports manual pin placement, and draws the radius in metres.
5. Keep map state synchronized with form coordinates.
6. Reuse approved Cebu City GeoJSON and landmark data only after checking file
   size, format, attribution, and packaging suitability.
7. Keep dataset parsing and spatial lookup out of the main screen widget.
8. Show a manual fallback when GPS or tiles are unavailable.

Do not introduce remote public maps, geocoding, or tile services without an
approved architecture change. Field deployment still needs a documented choice
between a reachable HTTPS tile service and approved offline map delivery.

## 11. Manual first-run checklist

After implementing a phase, and after obtaining any approval required for agent
run commands, check:

- Android Studio recognizes the Flutter SDK and an Android device.
- The app starts with the local run configuration.
- Emulator URLs use `10.0.2.2`; physical-device URLs use the host LAN address.
- Local HTTP works only in debug; release configuration remains HTTPS-only.
- Valid volunteer authentication succeeds and prohibited accounts fail safely.
- Tokens and passwords never appear in logs.
- A recoverable failure does not erase the incident form.
- Duplicate taps do not create concurrent submissions.
- GPS denial still leaves manual location selection usable.
- A created incident appears correctly in dispatcher and admin workflows.
- Every touched code file is below 500 lines.
- `TASK1`, `TASK2`, setup, API, and behavior documentation is updated with the
  approved implementation.

Use the [mobile verification and handoff guide](mobile-verification-and-handoff-guide.md)
for the full acceptance checklist.

## 12. Common setup problems

### Android Studio cannot find Flutter

Confirm the plugin is installed, configure the Flutter SDK root (not its `bin`
folder), restart Android Studio, and confirm `flutter --version` works in a new
PowerShell window.

### No Android device is listed

Start the emulator fully, enable CPU virtualization if the emulator reports a
hypervisor problem, or approve USB debugging on the phone. Then inspect devices
with `flutter devices`.

### The app cannot reach Laravel

Do not use `localhost` from Android. Use `10.0.2.2` on the standard emulator or
the computer's LAN IP on a physical phone. Confirm Laravel is running, the URL
includes port 8000, and private-network firewall access is allowed.

### The map is blank

Confirm TileServer is running, open its style URL from the host browser, use
port 8080, replace `localhost` with the correct Android host address, and inspect
whether the style's internal sprite, glyph, and tile URLs are also reachable
from the device.

### HTTP is blocked

Confirm the debug manifest and XML network security file are in
`src/debug`, not `src/main`. Keep this exception limited to local development.

### Gradle reports a JDK mismatch

Use Android Studio's bundled JDK first and retain the project's Java 17 source
and target compatibility. Avoid upgrading Gradle, Kotlin, Android Gradle Plugin,
or Java independently unless Flutter's compatibility guidance requires it.

## 13. Suggested first development session

Stop after this small, diagnosable milestone:

1. Configure Flutter, Android Studio, Android SDK, and one device.
2. Open the existing `mobile/` project and restore packages.
3. Configure the debug-only local network policy and Android permissions.
4. Add environment configuration and the local run configuration.
5. Replace the generated counter with a themed ResQLink login screen shell.
6. Agree on the authentication JSON contract with the `TASK1` owner.

Implement Sanctum and connect authentication in the next session. Add the
incident form only after authentication is stable, and add maps only after the
plain JSON incident workflow succeeds.
