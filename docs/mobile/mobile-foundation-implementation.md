# TASK1-TASK2 Mobile Foundation Migration Status

**Status:** Sanctum backend implemented; Expo foundation not yet implemented
**Decision date:** October 29, 2026

## Current repository state

The previous Flutter/Dart application and its generated Android, build, IDE,
test, and dependency files were intentionally removed. No source was migrated
because the approved Expo application will start fresh. The repository-root
`mobile/` path is reserved for the future Expo + React Native + TypeScript app.

## Approved next milestone

TASK1 (Jassy) and TASK2 (Adriane) should begin only after the MapLibre/Expo
compatibility gate is recorded. Work follows this order:

1. Create the Expo TypeScript foundation and configure Expo Router.
2. Prove MapLibre on a physical Android development build.
3. Integrate the implemented TASK1 Sanctum endpoints into the Expo client.
4. Implement TASK2 incident reporting against the approved API contract.
5. Verify Laravel authority over identity, severity, timestamps, and status.

## Implemented backend contract

The repository implements `POST /api/mobile/login`, `GET /api/mobile/user`,
`POST /api/mobile/logout`, `PUT /api/mobile/password`, and the protected
`POST /api/incidents` operation with Sanctum bearer tokens. Existing web
sessions remain separate. The Expo client and physical-device verification are
the remaining foundation work.

## References

- [Mobile scope](mobile-application-scope.md)
- [Mobile architecture](mobile-application-architecture.md)
- [Mobile technology stack](mobile-technology-stack.md)
- [Implementation guide](mobile-implementation-guide.md)
- [Verification and handoff](mobile-verification-and-handoff-guide.md)
- [Sanctum migration guide](sanctum-migration-guide.md)
