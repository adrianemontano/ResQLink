# ResQLink Android Studio Setup and Mobile Implementation Guide

**Platform:** Expo React Native for Android on Windows
**Status:** Setup plan; the Expo application has not been generated

## Required tools

- Current Node.js release supported by the selected Expo SDK.
- Git and a package manager used consistently by the team.
- Android Studio with the Android SDK, platform tools, build tools, and an SDK
  platform supported by the selected Expo/React Native combination.
- A compatible JDK as required by that combination.
- A physical Android device for the MapLibre proof of concept.
- Optional EAS CLI/account when cloud development builds are used.

Do not pin versions until the implementation compatibility gate is completed.
Record exact versions in this guide when the project is generated.

## Physical device setup

1. Enable Developer Options and USB debugging.
2. Connect the device by a data-capable USB cable.
3. Accept the computer authorization prompt.
4. Confirm the device is listed by Android platform tools.
5. Keep the phone and laptop on the same reachable network for API and tiles.

An emulator may assist general UI development, but a real device is required
for the initial GPS, MapLibre, and LAN-connectivity checkpoint.

## Development build workflow

MapLibre React Native is not part of Expo Go. The future project must install
`expo-dev-client`, install `@maplibre/maplibre-react-native`, and add its config
plugin to `app.json` or `app.config.*`.

Local workflow:

```powershell
npx expo run:android
npx expo start --dev-client
```

The first command generates/compiles native Android code, installs the debug
application, and can start Metro. The second starts Metro for the already
installed development client. EAS Build may create the development APK instead
when local native tooling is unavailable.

Rebuild after native library changes, config-plugin changes, Expo/React Native
runtime upgrades, or native Android configuration changes. Do not rebuild for
ordinary TypeScript-only edits.

## Service connectivity

Run the existing Laravel API and TileServer GL Light on laptop interfaces the
phone can reach. Configure URLs similar to:

```text
http://<LAPTOP_LAN_IP>:8000/api
http://<LAPTOP_LAN_IP>:8080/styles/<STYLE_ID>/style.json
```

The exact style ID must be discovered from the current TileServer setup. Phone
`localhost` and `127.0.0.1` do not refer to the laptop. Allow ports through the
firewall and use debug-only Android cleartext configuration if local HTTP is
required. Use HTTPS for production. Expo tunnels do not expose Laravel or the
tile server automatically.

## Compatibility checklist

- [ ] Expo SDK supports the chosen Node and Android toolchain.
- [ ] Its React Native version supports the selected MapLibre release.
- [ ] New Architecture is enabled when required (v11 currently requires it).
- [ ] MapLibre config plugin instructions match the selected release.
- [ ] Current APIs (`Map`, `Camera`, `GeoJSONSource`, compatible layers) are
  confirmed rather than copied from older examples.
- [ ] Local style loads and coordinate/radius interaction works on-device.

## Common failures

**Device not listed:** confirm USB debugging, cable capability, authorization,
platform tools, and Windows device drivers.

**App cannot reach Laravel or tiles:** replace `localhost` with the laptop LAN
IP, bind the services correctly, confirm same-network routing, inspect firewall
rules, and test the URL from the phone browser where appropriate.

**Map module missing:** Expo Go or an outdated development client is running.
Rebuild and reinstall a client containing MapLibre Native.

**Blank map:** verify the exact style URL, TileServer logs, style source URLs,
LAN reachability, HTTP policy, and required attribution/configuration.

**Gradle/JDK failure:** compare the installed JDK, Android SDK, Gradle, Expo SDK,
React Native, and MapLibre requirements. Do not upgrade one independently
without rechecking the compatibility matrix.

See the implementation and verification guides for the staged delivery plan.
