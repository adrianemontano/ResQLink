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
Code was reviewed and automated tests were written in
`backend/tests/Feature/AdminUserAccessTest.php`. **The tests have not been run yet**
(PHP is not installed on the author's machine). Do not mark TASK1 complete until
`php artisan test` passes.
