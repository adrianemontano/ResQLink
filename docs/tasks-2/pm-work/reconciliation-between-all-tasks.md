# PM Work — Reconciliation Between All Tasks

> **Current state:** The primary web workflow is implemented. Final reconciliation
> waits on the remaining feature gaps and recorded automated/manual verification.

## Seeder data
`DatabaseSeeder` creates `admin`, `dispatcher`, and `volunteer` roles. Development accounts: Admin `admin` / `admin@resqlink.local` / `Admin@12345`; Dispatcher `Angela` / `a@gmail.com` / `admin123`; Volunteer `volunteer` / `volunteer@resqlink.local` / `Volunteer@12345`. These are development-only.

## Assignment
- Reconcile authentication, volunteer verification, incident submission, dispatcher coordination, and admin reporting.
- Check shared field names, statuses, severity values, routes, permissions, and handoff contracts.
- Verify end-to-end behavior and record defects or unresolved ownership.
- Prepare formal test/UAT evidence, final diagrams, documentation, and presentation materials.

## Known items before final reconciliation

- Add search to the Admin user list.
- Complete individual volunteer-document review and protected file-download
  actions, or formally reduce that requirement.
- Complete TASK2 and TASK3 negative-path and map-fallback verification.
- Reconcile task notes with the actual routes and tests in the final integration
  branch; a code review alone must not be described as an executed passing test.
- Decide whether notifications are required for the capstone release or remain
  a documented future enhancement.

## Definition of done
All four task owners agree that the integrated workflow is consistent, documented, and ready for final demonstration.
