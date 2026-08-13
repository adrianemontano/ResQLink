# Task 1 — User Access and Volunteer Management

## Seeder Credentials — Read First

The current `DatabaseSeeder` creates the roles `admin`, `dispatcher`, and
`volunteer`, but `AdminSeeder` creates only the default admin user:

| Role | Username | Email | Password |
| --- | --- | --- | --- |
| Admin | `admin` | `admin@resqlink.local` | `Admin@12345` |

These are development credentials only. The admin values can be overridden with
`DEFAULT_ADMIN_USERNAME`, `DEFAULT_ADMIN_EMAIL`, and `DEFAULT_ADMIN_PASSWORD`.
Dispatcher and volunteer users must currently be created through the admin user
management feature.

**Suggested owner:** One groupmate responsible for authentication, roles, and
admin account management  
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
