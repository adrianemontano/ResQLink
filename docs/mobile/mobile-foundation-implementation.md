# TASK1-TASK2 Mobile Foundation Implementation

**Status:** Initial Flutter foundation implemented; Laravel authentication pending  
**Branch:** `volunteer-mobile-app`  
**Owners:** Jassy (`TASK1` authentication) and Adriane (`TASK2` incident reporting)

## Implemented foundation

The generated Flutter counter sample has been replaced with the initial
ResQLink application foundation described in the mobile implementation plan.
The current implementation includes:

- A Material 3 ResQLink application theme.
- A Provider-based startup and unauthenticated state gate.
- A startup loading indicator.
- A responsive volunteer login screen with local required-field validation.
- Password show and hide behavior.
- Compile-time `API_BASE_URL` and `MAP_STYLE_URL` configuration.
- A development notice when `API_BASE_URL` is missing.
- A focused JSON `ApiClient` with bearer-token support, timeouts, safe decoding,
  and Laravel field-error parsing.
- An `ApiException` model for network and server failures.
- A `TokenStorage` wrapper around `flutter_secure_storage`.

The login button intentionally does not send credentials yet. It displays a
message explaining that the `TASK1` mobile authentication contract must be
approved. This prevents temporary or fake authentication behavior from becoming
part of the application.

## Source locations

```text
mobile/lib/
|-- app/
|   |-- app.dart
|   `-- app_theme.dart
|-- core/
|   |-- config/environment.dart
|   |-- errors/api_exception.dart
|   |-- network/api_client.dart
|   `-- storage/token_storage.dart
|-- features/authentication/presentation/
|   |-- auth_provider.dart
|   `-- login_screen.dart
`-- main.dart
```

## Development launch

For the standard Android emulator, run from `mobile/`:

```powershell
flutter run -d emulator-5554 `
  --dart-define=API_BASE_URL=http://10.0.2.2:8000 `
  --dart-define=MAP_STYLE_URL=http://10.0.2.2:8080/styles/resqlink/style.json
```

The URLs are compile-time configuration, not secrets. Production services must
use approved HTTPS endpoints. Do not place Laravel or database credentials in
Flutter configuration.

## Next milestone: TASK1 mobile authentication

Before connecting the login button, Jassy and Adriane should approve the JSON
contract for:

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `POST` | `/api/auth/login` | Validate a volunteer and issue a device token |
| `GET` | `/api/auth/user` | Restore the authenticated volunteer session |
| `POST` | `/api/auth/logout` | Revoke the current device token |

Laravel must enforce valid credentials, an active account, the `volunteer`
role, an existing volunteer profile, and `verified` status. Flutter must never
decide those authorization rules.

Once the contract is approved:

1. Add Sanctum token support to Laravel under `TASK1` ownership.
2. Add typed `AuthenticatedUser` and `AuthService` classes in Flutter.
3. Extend `AuthProvider` with checking, submitting, authenticated, and failure
   states.
4. Connect login, saved-session restoration, and logout.
5. Verify rejection of inactive, unverified, admin, and dispatcher accounts.
6. Begin the `TASK2` incident form only after authentication passes its
   checkpoint.

Installing Sanctum may add a database migration. Under repository rules, an AI
coding agent must receive explicit approval before running migrations, lint,
tests, builds, or other verification commands.

## Verification status

The source and documentation have been reviewed for structure and file-size
limits. Direct Dart analysis completed with no issues on October 28, 2026.
`flutter test` was approved and attempted, but the Flutter CLI produced no
output and timed out while stale Dart/Flutter processes from the previous
development session were still present. The widget test and Android launch
remain pending after that local tool lock is cleared.
