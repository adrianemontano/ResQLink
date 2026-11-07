# TASK1-TASK2 Sanctum Migration Guide

**Owners:** Jassy (TASK1 authentication) and Adriane (TASK2 incident integration)

Use this guide after the Sanctum changes are merged into the shared branch. It
updates an existing ResQLink development environment from the retired custom
mobile-token table to Laravel Sanctum. Do not share `.env` files, passwords, or
bearer tokens between group members.

## What changes

- Mobile tokens move from `mobile_access_tokens` to Sanctum's
  `personal_access_tokens` table.
- Existing custom mobile tokens stop working; volunteers must log in again.
- Mobile authentication moves under `/api/mobile`.
- Web login and web sessions are unchanged.

The implemented mobile operations are:

| Method | Endpoint | Purpose |
| --- | --- | --- |
| POST | `/api/mobile/login` | Issue a Sanctum token |
| GET | `/api/mobile/user` | Restore the authenticated volunteer |
| POST | `/api/mobile/logout` | Revoke the current token |
| PUT | `/api/mobile/password` | Replace a temporary password |
| POST | `/api/incidents` | Submit a volunteer incident |

## Before pulling

Commit or stash personal work first. Do not discard another contributor's
changes.

```powershell
git status
git branch --show-current
```

Create a database backup using the method appropriate for your local setup.
Replace every placeholder before running the command:

```powershell
mysqldump -u <DB_USERNAME> -p <DB_DATABASE> > <BACKUP_FILE.sql>
```

The password prompt expects `<DB_PASSWORD>` and should not be copied into the
command or committed to Git.

## Update the repository

```powershell
git pull origin volunteer-mobile-app
Set-Location backend
composer install
```

`composer install` installs the locked Sanctum version. Do not use
`composer update`, because it may change unrelated package versions.

## Apply database migrations

Review the configured database before migrating:

```powershell
php artisan about
php artisan migrate:status
```

Then apply pending migrations:

```powershell
php artisan migrate
```

The migration creates `personal_access_tokens` and safely drops the retired
`mobile_access_tokens` table when it exists. Dropping that table invalidates old
custom tokens but does not delete users, incidents, or volunteer profiles.

Do not use `php artisan migrate:fresh`; it deletes all application tables.

## Refresh local configuration

```powershell
php artisan optimize:clear
```

No Sanctum secret belongs in `.env`. Keep existing database values private:

```env
DB_DATABASE=<LOCAL_DATABASE_NAME>
DB_USERNAME=<LOCAL_DATABASE_USERNAME>
DB_PASSWORD=<LOCAL_DATABASE_PASSWORD>
```

## Confirm the route contract

```powershell
php artisan route:list --path=api/mobile
php artisan route:list --path=api/incidents
```

Expected mobile routes use `auth:sanctum` except for login. Incident submission
also requires the `incidents:create` token ability and a changed password.

## Optional authorized verification

Repository rules require explicit approval before running tests or formatting.
The PHPUnit configuration uses an in-memory SQLite database, so enable the
`pdo_sqlite` and `sqlite3` PHP extensions before running it. Verify with:

```powershell
php -m
```

After approval, run:

```powershell
php artisan test
vendor\bin\pint --dirty
```

Record failures rather than changing unrelated code merely to hide them.

## Client update and re-login

Any local mobile client or API collection must replace old paths:

```text
/api/login             -> /api/mobile/login
/api/user              -> /api/mobile/user
/api/logout            -> /api/mobile/logout
/api/password/change   -> /api/mobile/password
```

Delete any previously stored custom bearer token and log in again. Store the
new token in the device's secure storage. Never commit or send the token in a
group chat.

## Troubleshooting

**401 after pulling:** remove the old client token and log in again.

**403 when reporting:** check whether `must_change_password` is true and call
`PUT /api/mobile/password` first. Also confirm the account is active, has the
volunteer role, and has a verified volunteer profile.

**Missing `personal_access_tokens`:** confirm `composer install` succeeded and
run `php artisan migrate:status`, followed by `php artisan migrate`.

**Local changes block pull:** stop and coordinate the overlapping files. Do not
use `git reset --hard` or delete the working tree.

## Handoff checklist

- [ ] Personal work committed or safely stashed.
- [ ] Database backup created and kept private.
- [ ] Shared branch pulled.
- [ ] Locked Composer dependencies installed.
- [ ] Pending migrations reviewed and applied.
- [ ] Old client token removed.
- [ ] Volunteer can log in through `/api/mobile/login`.
- [ ] Current-user restoration and logout work.
- [ ] Temporary-password restriction works.
- [ ] Authorized test results are recorded.

## Verification record — November 5, 2026

- Sanctum `v4.3.3` installed from the committed Composer lock file.
- The `personal_access_tokens` migration was applied successfully.
- The retired `mobile_access_tokens` table was dropped successfully.
- Pint completed and a second `--test` pass reported clean formatting.
- Laravel listed all four `/api/mobile/*` routes and `POST /api/incidents`.
- The first PHPUnit attempt was blocked because `pdo_sqlite` was disabled.
  Running PHPUnit directly with the available Windows SQLite DLLs executed all
  44 tests: 39 passed. The four focused mobile authentication tests passed with
  19 assertions. Four failures and one error remain in pre-existing admin
  report and dispatcher tests outside this Sanctum change; review them
  separately.
