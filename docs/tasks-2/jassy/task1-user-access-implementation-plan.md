# TASK1 — Jassy: User Access Implementation Plan

## Seeder data

The current `DatabaseSeeder` creates `admin`, `dispatcher`, and `volunteer`
roles. Development accounts are Admin `admin` / `admin@resqlink.local` /
`Admin@12345`, Dispatcher `Angela` / `a@gmail.com` / `admin123`, and Volunteer
`volunteer` / `volunteer@resqlink.local` / `Volunteer@12345`. These credentials
are development-only.

## Objective

Complete the secure account, role, volunteer verification, and volunteer
document foundation used by the other tasks.

## Implementation steps

1. Review the role, user, volunteer-profile, and volunteer-document migrations,
   models, relationships, and seeders.
2. Verify login, logout, sessions, protected routes, and role-based redirects.
3. Complete admin user-management routes, requests, policies, and Blade views
   for listing, creating, editing, activating, and deactivating users.
4. Ensure role changes create or remove volunteer profiles appropriately.
5. Add the three required volunteer document types and persist file metadata in
   private storage with review status.
6. Add clear validation for required fields, duplicate account details, and
   password changes without exposing sensitive information.
7. Verify inactive volunteers and unverified volunteers cannot access volunteer
   actions by direct URL or form/API request.
8. Document routes, permissions, validation rules, seed assumptions, and the
   final requirement-to-code mapping.

## Acceptance criteria

- Admins can manage users and account availability.
- Roles and volunteer verification are enforced server-side.
- Required volunteer documents are recorded securely.
- Unauthorized and inactive-user access is rejected consistently.
- Development seed data and setup assumptions are documented.
