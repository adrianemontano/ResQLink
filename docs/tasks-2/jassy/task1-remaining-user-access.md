# TASK1 — Jassy: Remaining User Access Work

## Seeder data
`DatabaseSeeder` creates `admin`, `dispatcher`, and `volunteer` roles. Development accounts: Admin `admin` / `admin@resqlink.local` / `Admin@12345`; Dispatcher `Angela` / `a@gmail.com` / `admin123`; Volunteer `volunteer` / `volunteer@resqlink.local` / `Volunteer@12345`. These are development-only.

## Assignment
- Verify role, user, volunteer-profile, and document relationships.
- Confirm admin-only user-management routes and authorization.
- Complete the three volunteer document types, upload metadata, and private file handling.
- Verify inactive volunteers cannot access protected volunteer URLs directly.
- Review duplicate-account, password, and field-level validation feedback.
- Confirm seed data and document routes, permissions, and validation rules.

## Definition of done
All Task 1 acceptance criteria are verified and documented for handoff to Tasks 2 and 4.

## Implementation notes — TASK1

### Relationships (verified by code review)
`roles` 1—N `users`; `users` 1—1 `volunteer_profiles` (primary key is `user_id`);
`volunteer_profiles` 1—N `volunteer_documents` (`volunteer_profile_id` references
`volunteer_profiles.user_id`, unique per `document_type`); `volunteer_documents.reviewed_by`
references `users.id`.

### Routes and permissions
All routes below sit in the `auth` + `role:admin` group (`EnsureUserHasRole`), and each
mutating request also re-checks the admin role in its form request.

| Route name | Method and URL | Purpose |
| --- | --- | --- |
| `admin.users.index/create/store/edit/update` | `/admin/users…` | List, create, edit users |
| `admin.users.password` | `PATCH /admin/users/{user}/password` | Reset password |
| `admin.users.activation` | `PATCH /admin/users/{user}/activation` | Toggle active/inactive |
| `admin.users.verification` | `PATCH /admin/users/{user}/verification` | Verify or revert a volunteer |
| `admin.users.documents.store` | `POST /admin/users/{user}/documents` | Upload a volunteer document |
| `admin.users.documents.show` | `GET /admin/users/{user}/documents/{document}` | Download a document (404 if it belongs to another volunteer) |
| `admin.users.documents.review` | `PATCH /admin/users/{user}/documents/{document}/review` | Mark `approved` or `rejected` |

### Documents and private storage
Required types: `endorsement_letter`, `barangay_clearance`, `certificate_of_residency`
(`VolunteerDocument::TYPES`). Files are stored on the private `local` disk
(`storage/app/private/volunteer-documents`, constant `VolunteerDocument::DISK`) and are
only served through the admin download route. Metadata saved: `file_path`,
`original_filename`, server-detected `mime_type`, `review_status` (`pending`,
`approved`, `rejected`), `reviewed_by`, `reviewed_at`.

### Validation rules
- User: name required; username unique, alpha-dash; email unique; role must be
  admin/dispatcher/volunteer; barangay required for volunteers; password min 8 and confirmed.
- Document: type must be one of the three and unique per volunteer (field error on
  `document_type`); file must be PDF/JPG/PNG up to 5 MB (field error on `document`).
- Admins cannot deactivate their own account or remove their own admin role.

### Inactive and unverified volunteers
`EnsureUserHasRole` rejects inactive or unverified volunteers with 403 on every
`volunteer.*` URL, and `StoreIncidentRequest::authorize()` rejects them on the web form
and on `POST /api/incidents`.

### Verification status
Task 1 was verified against `backend/tests/Feature/AdminUserAccessTest.php`. All
**7** admin user-access tests pass (38 assertions). Three blocking defects were
found and fixed during verification:

1. **`RoleSeeder` failed on every run.** The `roles` table has a NOT NULL, unique
   `slug` column, but `Role::$fillable` omitted `slug`, so the seeder value was
   discarded and the insert failed — which broke every test that seeds roles.
   Fix: added `slug` to `Role` `#[Fillable]`.
2. **Volunteer document relationships were broken.** Because
   `volunteer_profiles` uses `user_id` as its primary key, Laravel mis-inferred
   the `VolunteerDocument` foreign key as `volunteer_profile_user_id`. Fix:
   `VolunteerProfile::documents()` and `VolunteerDocument::volunteerProfile()`
   now explicitly bind `volunteer_profile_id` ↔ `user_id`.
3. **A flaky test from random factory data.** `UserFactory` used
   `fake()->userName()`, which can produce dots (e.g. `hegmann.valentin`). The
   app's `alpha_dash` username rule rejects dots, so
   `test_changing_role_creates_and_removes_volunteer_profile` failed whenever the
   generated username contained a dot. Fix: `UserFactory` now sanitizes the
   generated username to the `alpha_dash` character set and guarantees
   uniqueness.

#### Test-environment note
`phpunit.xml` runs tests on SQLite `:memory:`. The author's machine has only the
`pdo_mysql` driver (no `pdo_sqlite`, and the on-disk `pdo_sqlite.so` targets the
PHP 8.3 API and is incompatible with PHP 8.4), so every feature test errored with
`could not find driver (Connection: sqlite...)`. To verify without installing a
system extension, the suite was run against an isolated throwaway MySQL database:

```bash
mysql -e "CREATE DATABASE IF NOT EXISTS resqlink_test ..."
DB_CONNECTION=mysql DB_DATABASE=resqlink_test php artisan test
```

All 7 Task 1 tests pass this way. **TASK1 code and tests are verified complete.**
The 5 remaining full-suite failures are outside Task 1 (Tasks 2/3) and stem from a
stale Vite build manifest missing the `dispatcher-map.js` and `volunteer-report`
entrypoints (fixed by `npm run build`), plus Task 3 dispatcher data assertions.

After rebuilding the frontend assets, `test_verified_volunteer_can_log_in_through_web`
also failed because the test created a volunteer profile without the required
`barangay` column. This was a test-data bug (the controller and seeder always supply
`barangay` in production); the test now provides `barangay`. With that fix, all
Task 1 authentication and user-access tests pass (14/14 across
`AdminUserAccessTest` and `WebAuthenticationTest`).

The remaining full-suite failures belong to other tasks and are outside Task 1 scope:
`DispatcherIncidentCoordinationTest` barangay-filter and `impact_radius` `300` vs
`300.0` assertions (Task 3, Angela) and `VolunteerIncidentSubmissionTest` incident
reference display (Task 2, Adriane).
