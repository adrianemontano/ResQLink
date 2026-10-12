# TASK1 — Jassy: Remaining User Access Work

## Development accounts

`DatabaseSeeder` creates the `admin`, `dispatcher`, and `volunteer` roles and
development accounts. The default Admin is `admin` / `admin@resqlink.local` /
`Admin@12345`; Dispatcher is `Angela` / `a@gmail.com` / `admin123`; Volunteer is
`volunteer` / `volunteer@resqlink.local` / `Volunteer@12345`. These credentials
are development-only and may be overridden by the documented environment values.

## Implemented by code inspection

- Session login/logout and role-protected Admin, Dispatcher, and Volunteer routes.
- Admin user listing, creation, editing, password reset, activation, and
  volunteer verification.
- Volunteer profiles and document metadata relationships.
- Upload of the three required document types: endorsement letter, barangay
  clearance, and certificate of residency.
- Private default-disk storage for PDF/JPG/PNG uploads up to 5 MB, with duplicate
  document types rejected for a volunteer.
- Direct volunteer-route and submission authorization for active, verified
  volunteers.

## Confirmed remaining gaps

- Add search to the Admin user list as required by `TASK1`.
- Add a protected document view/download route. Files are stored privately, but
  this checkout does not provide an authorized retrieval action.
- Add individual document approve/reject actions and persist the reviewer and
  review timestamp already represented by the data model.
- Review self-deactivation and self-role-change protections against the final
  approved account-management rules.
- Add focused coverage for account edits, password reset, role changes,
  volunteer verification, upload validation, duplicate document types,
  protected downloads, and document review.

## Existing routes

| Route name | Method and URL | Purpose |
| --- | --- | --- |
| `admin.users.index/create/store/edit/update` | `/admin/users...` | List, create, and edit users |
| `admin.users.password` | `PATCH /admin/users/{user}/password` | Reset a password |
| `admin.users.activation` | `PATCH /admin/users/{user}/activation` | Toggle active/inactive |
| `admin.users.verification` | `PATCH /admin/users/{user}/verification` | Verify or revert a volunteer |
| `admin.users.documents.store` | `POST /admin/users/{user}/documents` | Upload a volunteer document |

## Verification status

`backend/tests/Feature/WebAuthenticationTest.php` covers core authentication,
role access, Admin creation of a Dispatcher, and inactive Dispatcher login.
This documentation review did not execute tests or builds. Do not mark TASK1
complete until the remaining gaps are implemented and current automated and
manual results are recorded.

## Definition of done

All TASK1 acceptance criteria are implemented, verified, and documented for
handoff to Tasks 2 and 4.
