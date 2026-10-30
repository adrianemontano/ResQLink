# Login Feature

## Overview

Implemented session-based web authentication for ResQLink. The application
supports login and logout for administrators, dispatchers, and active verified
volunteers, role-based route protection, seeded development accounts, database-
backed dashboards, and administrator-managed accounts.

Public registration is not available. Volunteer accounts are created by an
administrator. Active, verified volunteers can use the web dashboard and
incident-reporting workflow; inactive or unverified volunteers are denied.

## Default Administrator

Username: `admin`

Email: `admin@resqlink.local`

Password: `Admin@12345`

Role: `Admin`

These credentials are for development and testing only. Change them before using the system in production. The seeded values can be overridden with `DEFAULT_ADMIN_NAME`, `DEFAULT_ADMIN_USERNAME`, `DEFAULT_ADMIN_EMAIL`, and `DEFAULT_ADMIN_PASSWORD`.

## Default Dispatcher

Username: `Angela`

Email: `a@gmail.com`

Password: `admin123`

Role: `Dispatcher`

## Default Volunteer

Username: `volunteer`

Email: `volunteer@resqlink.local`

Password: `Volunteer@12345`

Role: `Volunteer`

The dispatcher and volunteer credentials are for development and testing only.
The seeded values can be overridden with `DEFAULT_DISPATCHER_FIRST_NAME`,
`DEFAULT_DISPATCHER_LAST_NAME`,
`DEFAULT_DISPATCHER_USERNAME`, `DEFAULT_DISPATCHER_EMAIL`,
`DEFAULT_DISPATCHER_PASSWORD`, `DEFAULT_VOLUNTEER_FIRST_NAME`,
`DEFAULT_VOLUNTEER_LAST_NAME`,
`DEFAULT_VOLUNTEER_USERNAME`, `DEFAULT_VOLUNTEER_EMAIL`,
`DEFAULT_VOLUNTEER_PASSWORD`, and `DEFAULT_VOLUNTEER_BARANGAY`.

The seeded volunteer profile is active and verified for development and
demonstration. Volunteers created through Admin start with `pending`
verification and must be verified before they can access volunteer routes or
submit an incident report.

## Authentication Flow

Login: Users submit a username or email address and password at `/login`.

Session creation: Valid credentials create a Laravel web session and regenerate
the session ID.

Authorization: Role middleware protects Admin, Dispatcher, and Volunteer route
groups. Inactive accounts are denied. Volunteer routes additionally require a
verified volunteer profile.

Logout: `/logout` signs out the user, invalidates the session, regenerates the CSRF token, and redirects to `/login`.

## Roles

Admin

* Web Access
* Full Administration
* Can create dispatcher and volunteer accounts
* Can reset passwords and activate or deactivate managed accounts

Dispatcher

* Web Access
* Operational Features
* Can access the dispatcher dashboard
* Cannot create accounts

Volunteer

* Web access when active and verified
* Can access the volunteer dashboard and incident-reporting form
* Account and verification state are managed by an administrator

## Seeders Created

* `RoleSeeder`
* `AdminSeeder`
* `DispatcherSeeder`
* `VolunteerSeeder`

`DatabaseSeeder` creates the roles and the default Admin, Dispatcher, and
verified Volunteer development accounts.

## Routes Added

* `GET /`
* `GET /login`
* `POST /login`
* `POST /logout`
* `GET /dashboard`
* `GET /admin/dashboard`
* `GET /admin/users`
* `GET /admin/users/create`
* `POST /admin/users`
* `GET /admin/users/{user}/edit`
* `PUT /admin/users/{user}`
* `PATCH /admin/users/{user}/password`
* `PATCH /admin/users/{user}/activation`
* `GET /dispatcher/dashboard`
* `GET /volunteer/dashboard`
* `GET /volunteer/incidents/create`
* `POST /volunteer/incidents`

## Middleware

* Laravel `auth` middleware protects authenticated web routes.
* Laravel `guest` middleware prevents authenticated users from reopening the login page.
* `role` middleware was registered in `bootstrap/app.php` and implemented by `App\Http\Middleware\EnsureUserHasRole`.

## Database Changes

Tables created:

* `roles`

Columns added to `users`:

* `username`
* `role_id`
* `is_active`

Relationships:

* `roles` has many `users`
* `users` belongs to `roles`

## Test Credentials

Default administrator:

* Username: `admin`
* Email: `admin@resqlink.local`
* Password: `Admin@12345`
* Role: `Admin`

## Manual Test Cases

### Authentication

* [ ] Admin can log in.
* [ ] Dispatcher can log in.
* [ ] Invalid password is rejected.
* [ ] Non-existent username is rejected.
* [ ] Logged-out users cannot access protected pages.
* [ ] Session expires correctly.

### Authorization

* [ ] Admin can access Admin Dashboard.
* [ ] Dispatcher cannot access Admin Dashboard.
* [ ] Dispatcher can access Dispatcher Dashboard.
* [ ] Active, verified Volunteer can log in through the web.
* [ ] Inactive or unverified Volunteer cannot access volunteer routes.
* [ ] Unauthorized URLs return the correct response.

### User Management

* [ ] Admin creates a Dispatcher.
* [ ] Admin creates a Volunteer.
* [ ] Newly created Dispatcher can log in.
* [ ] Newly created Volunteer remains blocked until verified.
* [ ] Verified Volunteer can log in and open the incident-reporting form.
* [ ] Admin deactivates a Dispatcher.
* [ ] Deactivated Dispatcher cannot log in.
* [ ] Admin resets a user's password.
* [ ] User can log in with the new password.

### Security

* [ ] Passwords are hashed.
* [ ] CSRF protection is enabled.
* [ ] Authentication middleware protects restricted routes.
* [ ] Role middleware correctly restricts access.
