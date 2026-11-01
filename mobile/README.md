# ResQLink Volunteer Mobile App

Initial Flutter implementation for verified volunteer login, mandatory
password change, incident submission, and optional compressed JPG/PNG photo
upload. Laravel remains the source of truth for authentication, validation,
severity, timestamps, and persistence.

## Local setup

From this directory, install Flutter packages and run the app:

```powershell
flutter pub get
flutter run --dart-define=RESQLINK_API_URL=http://10.0.2.2:8000/api
```

`10.0.2.2` is the Android emulator bridge to the host machine. For another
emulator or physical device, provide a reachable API base URL with
`--dart-define=RESQLINK_API_URL=...`. Use HTTPS outside local development.
Android network security allows cleartext only to local emulator hosts.

The app stores bearer tokens and the forced-password flag in
`flutter_secure_storage`. Photos are resized and JPEG-compressed before upload;
Laravel validates the image again and stores it on the private disk. The
advanced local map picker, offline synchronization, and mobile push
notifications are not implemented yet.
