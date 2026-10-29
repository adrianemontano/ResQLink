# TASK1-TASK2 Mobile Foundation Migration Status

**Status:** Flutter foundation removed; Expo foundation not yet implemented
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
3. Implement TASK1 Sanctum token endpoints and mobile authentication.
4. Implement TASK2 incident reporting against the approved API contract.
5. Verify Laravel authority over identity, severity, timestamps, and status.

## Known backend gap

`POST /api/incidents` exists, but the current repository does not yet implement
the planned mobile token login, current-user, or logout endpoints. The incident
route also requires alignment with Sanctum bearer-token middleware and a stable
mobile JSON contract. Existing web sessions must remain unchanged.

## References

- [Mobile scope](mobile-application-scope.md)
- [Mobile architecture](mobile-application-architecture.md)
- [Mobile technology stack](mobile-technology-stack.md)
- [Implementation guide](mobile-implementation-guide.md)
- [Verification and handoff](mobile-verification-and-handoff-guide.md)
