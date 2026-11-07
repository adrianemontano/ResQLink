# ResQLink Volunteer Mobile Application

Android-first Expo React Native client for verified ResQLink volunteers.

## Local setup

1. Install dependencies:

   ```powershell
   npm.cmd install
   ```

2. Copy `.env.example` to `.env.local` and replace `<LAPTOP_LAN_IP>` with the
   laptop address reachable from the Android device. Public Expo values must not
   contain passwords, bearer tokens, database credentials, or Laravel secrets.

3. After explicit repository-owner approval, create the development build:

   ```powershell
   npx.cmd expo run:android --device
   ```

4. For TypeScript-only development after installation, start Metro with:

   ```powershell
   npx.cmd expo start --dev-client
   ```

MapLibre contains native code, so Expo Go cannot run this application. Route
files live in `src/app/`; feature behavior belongs in `src/features/` and shared
API code belongs in `src/services/api/`.
