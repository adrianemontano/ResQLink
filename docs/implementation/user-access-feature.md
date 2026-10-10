# TASK1 — User Access and Volunteer Management Feature

**Assigned owner:** Jassy

## Scope

Task 1 establishes the secure account, role, volunteer-verification, and
volunteer-document foundation used by the other tasks. It covers session
authentication, role-protected routes, admin user management, account
activation, volunteer verification, and private volunteer-document records.
Incident status and report logic are intentionally out of scope.

## Seeder data

`DatabaseSeeder` creates the `admin`, `dispatcher`, and `volunteer` roles and the
development accounts below. These credentials are development-only and can be
overridden with the matching `DEFAULT_ADMIN_*`, `DEFAULT_DISPATCHER_*`, and
`DEFAULT_VOLUNTEER_*` environment variables.

| Role | Username | Email | Password |
| --- | --- | --- | --- |
| Admin | `admin` | `admin@resqlink.local` | `Admin@12345` |
| Dispatcher | `Angela` | `a@gmail.com` | `admin123` |
| Volunteer | `volunteer` | `volunteer@resqlink.local` | `Volunteer@12345` |

The seeded volunteer is active and `verified` for development web login and
incident-reporting demonstrations. When an admin creates a volunteer, the related
`volunteer_profiles` row starts as `pending` and must be verified by an admin
before incident submission is allowed.

## Models and relationships

- `roles` 1—N `users` (`users.role_id` → `roles.id`).
- `users` 1—1 `volunteer_profiles` (`volunteer_profiles` primary key is `user_id`).
- `volunteer_profiles` 1—N `volunteer_documents`
  (`volunteer_documents.volunteer_profile_id` → `volunteer_profiles.user_id`,
  unique per `document_type`).
- `volunteer_documents.reviewed_by` → `users.id`.

Because `volunteer_profiles` uses `user_id` as its primary key,
`VolunteerProfile::documents()` and `VolunteerDocument::volunteerProfile()`
explicitly bind `volunteer_profile_id` ↔ `user_id`.

## Volunteer documents and private storage

Required types (`VolunteerDocument::TYPES`): `endorsement_letter`,
`barangay_clearance`, `certificate_of_residency`. Files are stored on the private
`local` disk (`storage/app/private/volunteer-documents`, constant
`VolunteerDocument::DISK`) and are only served through the admin download route;
they are never exposed on the public disk. Metadata saved: `file_path`,
`original_filename`, server-detected `mime_type`, `review_status` (`pending`,
`approved`, `rejected`), `reviewed_by`, and `reviewed_at`.

## Routes and permissions

Authentication uses Laravel session login/logout. `GET /` redirects to `/login`.
`GET /dashboard` (`DashboardRedirectController`) sends each user to their role
dashboard. The `role` middleware alias (`EnsureUserHasRole`) protects every
role-specific group, and each mutating form request re-checks the admin role in
its `authorize()` method.

| Route name | Method and URL | Purpose |
| --- | --- | --- |
| `login` / `login.store` | `GET`/`POST /login` | Sign in |
| `logout` | `POST /logout` | Sign out |
| `dashboard` | `GET /dashboard` | Redirect to the role dashboard |
| `admin.dashboard` | `GET /admin/dashboard` | Admin landing page |
| `admin.users.index/create/store/edit/update` | `/admin/users…` | List, create, edit users |
| `admin.users.password` | `PATCH /admin/users/{user}/password` | Reset password |
| `admin.users.activation` | `PATCH /admin/users/{user}/activation` | Toggle active/inactive |
| `admin.users.verification` | `PATCH /admin/users/{user}/verification` | Verify or revert a volunteer |
| `admin.users.documents.store` | `POST /admin/users/{user}/documents` | Upload a volunteer document |
| `admin.users.documents.show` | `GET /admin/users/{user}/documents/{document}` | Download a document (404 if it belongs to another volunteer) |
| `admin.users.documents.review` | `PATCH /admin/users/{user}/documents/{document}/review` | Mark `approved` or `rejected` |

Admin user and document routes sit in the `auth` + `role:admin` group. Volunteer
routes sit in the `auth` + `role:volunteer` group.

## Validation rules

- **User:** name required; username required, unique, `alpha_dash`; email
  required and unique; `role_id` must be an existing `admin`/`dispatcher`/
  `volunteer` role; `barangay` required when the role is volunteer; password
  required, minimum 8 characters, and confirmed (on create).
- **Document:** `document_type` must be one of the three types and unique per
  volunteer (field error on `document_type`); `document` must be a PDF/JPG/PNG up
  to 5 MB (field error on `document`).
- **Review:** `review_status` must be `approved` or `rejected`.
- **Self-protection:** an admin cannot deactivate their own account or remove
  their own admin role.

## Authorization and inactive/unverified volunteers

`EnsureUserHasRole` rejects inactive users and inactive or unverified volunteers
with `403` on every `volunteer.*` URL. `StoreIncidentRequest::authorize()` rejects
the same users on the web form and on `POST /api/incidents`. This prevents direct
URL access, not just hidden navigation links.

## Account availability

Account availability is stored as the boolean `is_active` and rendered as
`active` or `inactive`. It is kept separate from incident workflow `status` in
models, requests, and queries.

## Requirement mapping

- `TASK1-001` (volunteer create/update/activate/deactivate):
  `Admin\UserController` (`store`, `update`, `toggleActivation`) with
  `StoreUserRequest`/`UpdateUserRequest` and `admin/users` views.
- `TASK1-002` (dispatcher/admin account management): same controller and requests,
  allowed roles enforced by `Rule::exists('roles', …)`, passwords hashed via the
  `User` model `hashed` cast.
- `TASK1-003` (authentication, sessions, redirects): `Auth\LoginController`,
  `DashboardRedirectController`, `auth`/`guest` middleware, and the `role`
  middleware alias in `bootstrap/app.php`.
- `TASK1-004` (role authorization, reject inactive volunteers):
  `EnsureUserHasRole` and `StoreIncidentRequest::authorize()`.
- `TASK1-005` (volunteer document persistence and review):
  `VolunteerDocument` model, `volunteer_documents` migration, and
  `Admin\VolunteerDocumentController` with its requests.
- `TASK1-006` (active/inactive availability): `users.is_active`, the toggle
  action, and the `Active`/`Inactive` display in the users table.
- `TASK1-007` (validation and safe errors): the admin form requests return
  field-level errors; passwords are never displayed or logged.

## Verification status

Automated coverage lives in `backend/tests/Feature/AdminUserAccessTest.php` and
`backend/tests/Feature/WebAuthenticationTest.php`; all 14 tests pass. See
`docs/tasks-2/jassy/task1-remaining-user-access.md` for the verification notes and
the test-environment command, and
`docs/tasks-2/task1-handoff-remaining-failures.md` for failures owned by other
tasks.
