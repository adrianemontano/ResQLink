# Dispatcher and volunteer enhancements — implementation notes

This change set extends the existing ResQLink Notify–Receive–Dispatch workflows without granting Dispatchers admin user-management permissions. The static visual review is [dispatcher-enhancements.html](../design-system/mockups/dispatcher-enhancements.html).

## Decisions and scope

- **Notifications:** database notifications with authenticated 11-second polling. It uses existing Laravel sessions and avoids introducing WebSocket infrastructure for the current prototype. High/Critical items receive stronger red treatment and a distinct sound pattern; sound is optional/mutable.
- **Status history:** reuse `incident_histories` (`status_id`, `changed_by`, `changed_at`, `notes`) as the source for stage and resolution timing. No duplicate `incident_status_logs` table is added.
- **My Account:** Admins, Dispatchers, and Volunteers can edit their own profile and password. Password changes require the new password and confirmation, but not the current password; reusing the existing password is rejected. Account creation, activation, and password resets for other people remain Admin-only.
- **Flutter tokens:** use random 256-bit bearer tokens stored only as SHA-256 hashes in `mobile_access_tokens`, with 30-day expiry and last-used time. The exact bearer is only returned at login and should be stored in platform secure storage. Admin password reset invalidates existing mobile tokens.
- **Reports:** the Dispatcher Reports module is removed. Admin reports and their PDF/XLSX exports remain available.
- **Time:** timestamps are displayed and grouped in `Asia/Manila`; configure `APP_TIMEZONE=Asia/Manila`.
- **Photo:** accepted types are JPEG/PNG up to 5 MB after client-side resize/compression and server validation. Files are stored on Laravel's private `local` disk and streamed only through authenticated Dispatcher/Admin routes.

## Database migration proposal / implementation

Existing pending migrations in this change set add `users.must_change_password`, nullable `users.password_changed_at`, Laravel's standard `notifications` table, `incident_photos` metadata, and `mobile_access_tokens`. Incidents already have `volunteer_id` and `reported_at`; `incident_histories` already provides the status/actor/time audit trail and is reused for timing analytics. Do not duplicate these fields/tables.

Apply the pending migrations only after reviewing the target database. `migrate:fresh` is destructive and must only be used on a disposable database.

## Manual acceptance checklist

1. **Received status action:** On a Reported incident, the Received action is light orange with Rosewood text. Check hover, pressed, keyboard focus, disabled, and contrast states; verify other statuses remain distinct.
2. **Reporter and timestamps:** Confirm queue, dashboard queue, and detail show the volunteer's full name, available barangay/contact, and `M d, Y, h:i A` Asia/Manila report time. Confirm each history row shows status, actor, and local timestamp.
3. **Notifications:** Submit a volunteer incident in one session while an authenticated Dispatcher page stays open. Within 11 seconds confirm unread badge, dropdown data, toast, sound/mute, and Critical/High emphasis. Open an incident, mark one notification read, then mark all read. Verify a non-dispatcher cannot call notification endpoints.
4. **Forced password:** Create an Admin-managed account and log in using its temporary password. Confirm all other web routes redirect to Change Password. Check too-short password, same password, mismatched confirmation, show/hide, strength meter, success, and return to role dashboard. Repeat mobile login; confirm the API rejects incident submission until password change, and the bearer is stored securely. Reset an account as Admin and repeat.
5. **My Account:** Confirm Admins, Dispatchers, and Volunteers can edit only their own name, username, email, contact number, and password. Verify a password change needs only a new password and confirmation, rejects reusing the current password, and that attempts to access Admin user management remain forbidden.
6. **Photos:** Submit with no photo and with camera/gallery JPEG/PNG near the 5 MB limit. Verify preview/remove before upload, compressed size, private file storage, detail thumbnail/lightbox/download, explicit no-photo state, and that unauthorized users cannot fetch the file.

## Setup notes

After pulling these changes, install Laravel dependencies with `composer install`, run `php artisan migrate`, clear config cache, and run `flutter pub get` from `mobile/`. For Android emulator local development, use API base `http://10.0.2.2:8000/api`; set `RESQLINK_API_URL` with `--dart-define` as needed. The Android manifest permits cleartext only to support local HTTP development; production must use HTTPS and disable cleartext traffic. Verify `ZipArchive` is enabled before XLSX export.

The `APP_TIMEZONE` setting changes application timestamps to Manila time. Existing timestamp values remain as stored; verify legacy records before rollout and migrate/backfill them only if their original timezone is known.
