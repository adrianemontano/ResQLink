# Login Feature

## Overview

Implemented Phase 1 web authentication for ResQLink. The web application now supports session-based login and logout for administrator and dispatcher accounts, role-based route protection, seeded roles, a seeded default administrator, placeholder dashboards, and administrator-managed dispatcher/volunteer account creation.

Public registration was not added. Volunteer accounts exist for future mobile use, but they are blocked from the web login.

## Default Administrator

Username: `admin`

Email: `admin@resqlink.local`

Password: `Admin@12345`

Role: `Admin`

These credentials are for development and testing only. Change them before using the system in production. The seeded values can be overridden with `DEFAULT_ADMIN_NAME`, `DEFAULT_ADMIN_USERNAME`, `DEFAULT_ADMIN_EMAIL`, and `DEFAULT_ADMIN_PASSWORD`.

## Authentication Flow

Login: Users submit a username or email address and password at `/login`.

Session creation: Valid admin or dispatcher credentials create a Laravel web session and regenerate the session ID.

Authorization: Role middleware protects admin and dispatcher routes. Volunteers and users without web roles are denied web access.

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

* Mobile Only
* No Web Login
* Account can be created by an administrator for future mobile authentication

## Seeders Created

* `RoleSeeder`
* `AdminSeeder`

`DatabaseSeeder` calls both seeders, so `php artisan migrate:fresh --seed` creates the roles and the default administrator account.

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
* [ ] Volunteer cannot log in through the web.
* [ ] Unauthorized URLs return the correct response.

### User Management

* [ ] Admin creates a Dispatcher.
* [ ] Admin creates a Volunteer.
* [ ] Newly created Dispatcher can log in.
* [ ] Newly created Volunteer cannot log in through the web.
* [ ] Admin deactivates a Dispatcher.
* [ ] Deactivated Dispatcher cannot log in.
* [ ] Admin resets a user's password.
* [ ] User can log in with the new password.

### Security

* [ ] Passwords are hashed.
* [ ] CSRF protection is enabled.
* [ ] Authentication middleware protects restricted routes.
* [ ] Role middleware correctly restricts access.
