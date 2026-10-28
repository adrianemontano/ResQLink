# ResQLink Mobile

Flutter client for active, verified ResQLink volunteers. Android is the first
supported platform. Laravel remains the source of truth for authentication,
volunteer access, and incident reports.

## Current status

The mobile foundation provides:

- A ResQLink Material 3 theme and application shell.
- Provider-based startup and unauthenticated state.
- Compile-time API and map configuration.
- API client, API exception, and secure token-storage foundations.
- A validated, UI-only volunteer login screen.

The login screen is not connected to Laravel. `TASK1` mobile authentication
must be agreed with Jassy and implemented before credentials are submitted.

## Development configuration

From this directory, launch the Android application with:

```powershell
flutter run -d emulator-5554 `
  --dart-define=API_BASE_URL=http://10.0.2.2:8000 `
  --dart-define=MAP_STYLE_URL=http://10.0.2.2:8080/styles/resqlink/style.json
```

`10.0.2.2` reaches the host computer from the standard Android emulator. A
physical Android device must use the development computer's reachable LAN IP.

See `docs/mobile/mobile-foundation-implementation.md` for the implementation
handoff and next milestone.
