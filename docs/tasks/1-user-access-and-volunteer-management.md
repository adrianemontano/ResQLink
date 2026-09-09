# Task 1 — User Access and Volunteer Management

## Seeder Credentials — Read First

The current `DatabaseSeeder` creates the roles `admin`, `dispatcher`, and
`volunteer`, but `AdminSeeder` creates only the default admin user:

| Role | Username | Email | Password |
| --- | --- | --- | --- |
| Admin | `admin` | `admin@resqlink.local` | `Admin@12345` |

| Dispatcher | `Angela` | `a@gmail.com` | `admin123` |
| Volunteer | `volunteer` | `volunteer@resqlink.local` | `Volunteer@12345` |

These are development credentials only. The seeded values can be overridden
with the corresponding `DEFAULT_ADMIN_*`, `DEFAULT_DISPATCHER_*`, and
`DEFAULT_VOLUNTEER_*` environment variables. The volunteer profile is seeded
with `pending` verification and requires admin verification before reporting.

When an admin creates a volunteer, the form records the volunteer's barangay
and creates the related `volunteer_profiles` row with `verification_status` set
to `pending`. An admin must verify that profile before incident submission is
allowed.

Dispatcher accounts do not require volunteer profile fields. If an account's
role is changed to volunteer, a pending volunteer profile is created; changing
it away from volunteer removes the volunteer profile.

**Assigned owner:** Jassy
**Primary system users:** Admin, Dispatcher, Volunteer  
**Platform:** Laravel backend and Blade/Bootstrap web dashboard  
**Priority:** Must

## Local map requirement

This task must not introduce a dependency on internet-hosted map tiles or map
assets. If shared layouts load map resources, they must use the project's local
Leaflet assets and local configuration only. The system must remain usable when
the development environment has no internet connection.

## Required pages and visibility

These locations follow the `resqgroup.html` Admin Control Panel layout:

| Page or UI location | Required content |
| --- | --- |
| Login page (`/login`) | Username/email field, password field, validation messages, and login action for all roles |
| Admin sidebar | `User Management` navigation item visible only to Admin |
| Admin — User Management page | User table with name, email, role, account state, search field, and `+ Add User` action |
| Admin — Add User page/modal | Name, username, email, role, temporary password, confirmation, and active state |
| Admin — Edit User page | Account details, role, active/inactive toggle, and password reset action |
| Admin — Volunteer details area | Required volunteer document types and document status/file references |
| Shared dashboard footer | Logged-in user identity and Logout action |

The HTML reference shows `User Management` as the first Admin sidebar page and
uses a user table plus an Add User modal. The Laravel implementation may use
separate create/edit pages while preserving the same visible fields and flow.

## System requirements covered

- System actors: Barangay Volunteer, Dispatcher, and Admin / Data Management
  Personnel.
- Dispatcher and administrative user management.
- Volunteer account management.
- Volunteer identification documents:
  - Endorsement Letter from Barangay Captain
  - Barangay Clearance
  - Certificate of Residency
- Authentication and authorization from the project lifecycle and technology
  stack requirements.

## Scope

The owner will implement the account and access foundation used by the other
tasks. This includes role assignment, login/logout, protected role-specific
routes, user creation and editing, volunteer activation, and volunteer document
records.

## Functional requirements

- `TASK1-001` An admin can create, edit, activate, and deactivate volunteer
  accounts.
- `TASK1-002` An admin can create dispatcher and admin accounts and modify their
  user information.
- `TASK1-003` The system authenticates users before they access protected pages.
- `TASK1-004` The system authorizes actions by role; inactive volunteer accounts
  cannot submit or access volunteer functions.
- `TASK1-005` An admin can record each required volunteer document type and its
  file reference or uploaded file metadata.
- `TASK1-006` The system distinguishes account availability using `active` or
  `inactive`; workflow `status` is reserved for incidents.
- `TASK1-007` Validation errors and duplicate account details are displayed
  clearly without exposing sensitive information.

## Expected implementation outputs

- Laravel authentication and role-protection logic.
- User, role, volunteer, and volunteer-document models/migrations as needed.
- Admin user-management routes, controllers, requests, policies, and views.
- Blade views under `resources/views/admin/users/` and
  `resources/views/admin/volunteers/`.
- Feature documentation and seeded role/sample accounts where appropriate.

## Dependencies and handoff

This task should establish the roles and authenticated user relationship needed
by incident and dashboard work. Coordinate user and role schema names with the
database owner. Do not implement incident status or report logic here.

## Acceptance criteria

The feature is complete when:

- An admin can create and edit volunteer, dispatcher, and admin accounts.
- An admin can activate and deactivate volunteer accounts.
- A user can log in and log out successfully.
- Protected routes reject unauthenticated users.
- Role-inappropriate routes/actions are rejected.
- All three required volunteer document types can be recorded and reviewed.
- Inactive volunteers are prevented from volunteer operations.
- Validation and authorization behavior is documented and the implementation
  follows the naming conventions.

## Task folder label

This document belongs to **Task 1 — User Access and Volunteer Management**.

## Frontend/backend split

The same requirement may require both a user interface and server-side
behavior. The split below defines the specific work for each layer; no
requirement is added or removed.

### Frontend deliverables

- **TASK1-001:** Build the Admin user-management table and create/edit form
  with volunteer account fields, active/inactive control, confirmation feedback,
  and action errors.
- **TASK1-002:** Provide forms and screens for creating and editing
  dispatcher/admin accounts, including role selection and safe password/reset
  feedback.
- **TASK1-003:** Build login, logout, validation/error, redirect, and
  unauthenticated protected-page states for all supported roles.
- **TASK1-004:** Show only role-appropriate navigation/actions and display a
  clear inactive-volunteer access-denied state.
- **TASK1-005:** Add volunteer document fields/statuses and display the three
  required document types, file metadata, and review state.
- **TASK1-006:** Render account availability consistently as `active` or
  `inactive` and never use incident workflow `status` for it.
- **TASK1-007:** Display field-level validation, duplicate-account,
  authorization, and upload errors without passwords or other sensitive data.

### Backend deliverables

- **TASK1-001:** Implement volunteer create, update, activation, and
  deactivation actions; validate fields, persist the account state, and prevent
  deactivation from bypassing authorization.
- **TASK1-002:** Implement role assignment and account updates for
  dispatcher/admin users, enforce allowed roles, hash passwords, and prevent
  unauthorized privilege escalation.
- **TASK1-003:** Configure authentication, sessions, logout, middleware,
  password verification, and redirects for every protected route.
- **TASK1-004:** Enforce role authorization through middleware/policies and
  reject inactive volunteers on every volunteer operation, including direct
  requests.
- **TASK1-005:** Create document persistence and relationships; validate
  document type/file metadata, prevent duplicate required types per volunteer,
  and authorize admin access.
- **TASK1-006:** Store and validate the account availability field using
  `active`/`inactive`; keep it separate from incident status in models, requests,
  and queries.
- **TASK1-007:** Add request validation, unique constraints/checks, safe error
  responses, and logging that excludes passwords and sensitive document
  contents.

## Detailed implementation checklist

## Implementation note — TASK1

Implemented foundation includes role-protected authentication, admin account
management, inactive/verification checks for volunteer web access, volunteer
profiles, and private volunteer-document metadata storage. Documents support the
three required types and unique-per-volunteer type protection. Uploaded files
are stored through Laravel's non-public default disk. Automated coverage is
defined in backend/tests/Feature/WebAuthenticationTest.php and the admin
records/report feature test file.

Before marking TASK1 complete, Jassy should:

1. Confirm the `roles`, `users`, volunteer profile, and volunteer-document
   schema names and relationships, then document any migration or seeder
   assumptions.
2. Implement authentication first so all later screens and endpoints use the
   same authenticated user and role relationship.
3. Add admin-only routes for user listing, creation, editing, activation, and
   deactivation, with form requests and policies for each mutating action.
4. Add the three required volunteer document types and define whether each
   record stores an uploaded path, original filename, MIME type, and review
   state; do not expose private files publicly.
5. Build the Blade pages under the documented admin view paths and connect
   every form to a real backend action with success and failure feedback.
6. Verify inactive volunteers cannot reach volunteer actions by typing a URL
   directly, not only by hiding navigation links.
7. Add or update development seed data and environment-variable documentation
   without committing real credentials.
8. Record routes, permissions, validation rules, and the final requirement-
   to-code mapping in the relevant documentation before marking the task done.
