# ResQLink - Volunteer-Based Emergency Alert and Dispatch Coordination Support System

ResQLink is a capstone project for supporting emergency incident reporting and dispatch coordination. The current implementation focuses on the Laravel backend and web application used by administrators and dispatchers.

## Project Overview

ResQLink is designed to help emergency operations teams receive, organize, and act on structured incident information from verified volunteers. The system addresses the problem of scattered or incomplete emergency reports by moving toward a centralized workflow for account management, incident coordination, and role-based access.

Current users:

* Administrators
* Dispatchers

Planned users:

* Barangay volunteers through a future mobile application

Current development phase:

* Laravel backend
* Web application for Admin and Dispatcher roles
* Volunteer mobile application is planned but is not currently implemented in this repository

## Technology Stack

Detected project technologies:

| Area | Technology | Version / Notes |
| --- | --- | --- |
| Backend | PHP | Required `^8.3`; local environment detected `8.4.12` |
| Framework | Laravel | `v13.17.0` from `composer.lock` |
| Dependency Manager | Composer | Local environment detected `2.9.5` |
| Database | MySQL | Project database; local environment detected MySQL `8.0.43` |
| Web Views | Blade | Laravel server-rendered templates |
| Frontend Tooling | Node.js | Local environment detected `v22.17.0` |
| Frontend Package Manager | npm | Local environment detected `10.9.2` |
| Asset Bundler | Vite | `^8.0.0` |
| CSS Tooling | Tailwind CSS package | `^4.0.0` installed; current auth views use custom Blade CSS |
| JavaScript | JavaScript / ES modules | `resources/js/app.js`, Vite config |
| Testing | PHPUnit | `12.5.30` from `composer.lock` |
| Code Style | Laravel Pint | `v1.29.3` from `composer.lock` |

Not currently installed:

* Laravel Breeze
* Laravel Sanctum
* Spatie Laravel Permission
* Pest
* Bootstrap package
* Leaflet package
* Flutter/mobile application code

## Prerequisites

Install these before setting up the project.

### Git

Git is required to clone the repository and manage source code changes.

Minimum recommended version: Git 2.x

Download: https://git-scm.com/downloads

Verify:

```bash
git --version
```

### PHP

PHP runs the Laravel backend.

Minimum required version: PHP 8.3

Recommended version: PHP 8.3 or newer

Download: https://www.php.net/downloads

Verify:

```bash
php -v
```

Required PHP extensions for the installed Laravel/PHPUnit stack include:

* `ctype`
* `dom`
* `fileinfo`
* `filter`
* `hash`
* `json`
* `libxml`
* `mbstring`
* `openssl`
* `pdo`
* `pdo_mysql`
* `session`
* `tokenizer`
* `xml`
* `xmlwriter`

You can check installed PHP extensions with:

```bash
php -m
```

### Composer

Composer installs PHP and Laravel dependencies.

Minimum recommended version: Composer 2.x

Download: https://getcomposer.org/download/

Verify:

```bash
composer -V
```

### Node.js and npm

Node.js and npm install and run the frontend asset tooling used by Vite.

Minimum recommended version: Node.js 20.x or newer

Download: https://nodejs.org/

Verify:

```bash
node -v
npm -v
```

On Windows PowerShell, if `npm -v` is blocked by script execution policy, use:

```bash
npm.cmd -v
```

### MySQL or MariaDB

MySQL stores the application data, users, sessions, cache tables, jobs, and roles.

Minimum recommended version: MySQL 8.0 or MariaDB 10.6+

Downloads:

* MySQL: https://dev.mysql.com/downloads/mysql/
* MariaDB: https://mariadb.org/download/

Verify:

```bash
mysql --version
```

### Code Editor

VS Code is recommended for Laravel, Blade, PHP, and JavaScript development.

Download: https://code.visualstudio.com/

## Installation Guide

These steps set up the project from a fresh clone.

### 1. Clone the Repository

Clone the repository to your machine.

```bash
git clone <repository-url>
cd ResQLink
```

### 2. Go to the Laravel Backend

The current application code lives in the `backend/` directory.

```bash
cd backend
```

### 3. Install Composer Dependencies

This installs Laravel and PHP packages listed in `composer.json`.

```bash
composer install
```

### 4. Install Node Dependencies

This installs Vite and frontend tooling listed in `package.json`.

```bash
npm install
```

If PowerShell blocks npm scripts, use:

```bash
npm.cmd install
```

### 5. Create the Environment File

Laravel reads local configuration from `.env`. Copy the example file:

```bash
copy .env.example .env
```

On macOS/Linux:

```bash
cp .env.example .env
```

### 6. Generate the Application Key

Laravel needs an application key for encryption and signed data.

```bash
php artisan key:generate
```

### 7. Configure MySQL

Create a MySQL database, then update `.env`.

Example:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=resqlink
DB_USERNAME=root
DB_PASSWORD=your_password
```

If you changed `.env` after running Laravel commands, clear cached config:

```bash
php artisan config:clear
```

### 8. Run Migrations and Seeders

Migrations create database tables. Seeders create the default roles and administrator account.

```bash
php artisan migrate:fresh --seed
```

Warning: `migrate:fresh` drops all existing tables in the configured database. Use it only during development or when you are comfortable resetting the database.

For a non-destructive migration run:

```bash
php artisan migrate
php artisan db:seed
```

### 9. Build Frontend Assets

Build production frontend assets:

```bash
npm run build
```

Or on Windows if PowerShell blocks npm:

```bash
npm.cmd run build
```

### 10. Start the Development Servers

Use separate terminals for the backend server and Vite development server.

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

Or:

```bash
npm.cmd run dev
```

Open the Laravel URL shown by `php artisan serve`, usually:

```text
http://127.0.0.1:8000
```

## Running the Application

From the `backend/` directory:

Backend server:

```bash
php artisan serve
```

Frontend development server:

```bash
npm run dev
```

The web application redirects `/` to `/login`.

Available web areas:

* `/login`
* `/admin/dashboard`
* `/admin/users`
* `/dispatcher/dashboard`

## Default Development Credentials

The seeders create one default administrator account.

Admin:

```text
Username: admin
Email: admin@resqlink.local
Password: Admin@12345
Role: Admin
```

These credentials are for development and testing only. Change them before using the system outside a local development environment.

The seeded administrator values can be overridden with these `.env` variables:

```env
DEFAULT_ADMIN_NAME="System Administrator"
DEFAULT_ADMIN_USERNAME=admin
DEFAULT_ADMIN_EMAIL=admin@resqlink.local
DEFAULT_ADMIN_PASSWORD=Admin@12345
```

## Project Structure

High-level repository layout:

```text
ResQLink/
├── backend/
├── docs/
└── README.md
```

Important backend directories:

| Path | Purpose |
| --- | --- |
| `backend/app/` | Laravel application code, including models, controllers, middleware, providers, and form requests |
| `backend/app/Http/Controllers/` | Web request controllers for login, dashboards, and admin user management |
| `backend/app/Http/Middleware/` | Custom role middleware |
| `backend/app/Http/Requests/` | Form request validation for login and admin user actions |
| `backend/app/Models/` | Eloquent models such as `User` and `Role` |
| `backend/bootstrap/` | Laravel bootstrap files and middleware registration |
| `backend/config/` | Laravel configuration files |
| `backend/database/` | Migrations, seeders, and factories |
| `backend/public/` | Web server entry point and public assets |
| `backend/resources/` | Blade views, CSS, and JavaScript source files |
| `backend/routes/` | Web and console route definitions |
| `backend/storage/` | Runtime storage for logs, sessions, cache, and generated files |
| `backend/tests/` | PHPUnit feature and unit tests |
| `docs/` | Supporting project documentation, planning notes, and implementation notes |

## Development Workflow

Current development order:

1. Backend foundation
2. Web application for administrators and dispatchers
3. Mobile application for volunteers

The volunteer mobile application depends on backend APIs that have not been implemented yet. Keep web authentication and administrator-managed account creation separate from future mobile authentication.

Recommended contributor workflow:

1. Pull the latest changes.
2. Create a feature branch.
3. Make focused changes.
4. Run tests and formatters.
5. Update documentation when behavior changes.
6. Commit with a clear message.

## Current Features

### Implemented

* Laravel web application setup
* Session-based web login
* Logout
* Password hashing through Laravel casts
* Role records for Admin, Dispatcher, and Volunteer
* Admin and Dispatcher web access
* Volunteer web login denial
* Role middleware for protected routes
* Default administrator seeder
* Admin dashboard placeholder metrics
* Dispatcher dashboard placeholder metrics
* Admin-managed dispatcher and volunteer account creation
* Admin-managed account editing
* Admin-managed password reset
* Admin-managed activation and deactivation
* PHPUnit feature tests for web authentication behavior
* Implementation documentation for the login feature

### Planned

* Volunteer mobile application
* Mobile/API authentication
* Incident reporting
* Incident queue management
* Incident maps
* Incident status workflow
* Notification system
* Preliminary severity assessment
* Reports and analytics

## Useful Commands

Run these from the `backend/` directory unless noted otherwise.

| Command | Purpose |
| --- | --- |
| `composer install` | Install PHP dependencies |
| `composer update` | Update PHP dependencies according to `composer.json` |
| `npm install` | Install frontend dependencies |
| `php artisan key:generate` | Generate the Laravel application key |
| `php artisan config:clear` | Clear cached Laravel configuration |
| `php artisan serve` | Start the Laravel development server |
| `npm run dev` | Start the Vite development server |
| `npm run build` | Build production frontend assets |
| `php artisan migrate` | Run pending migrations |
| `php artisan db:seed` | Run database seeders |
| `php artisan migrate:fresh --seed` | Reset database and seed default data |
| `php artisan test` | Run the PHPUnit test suite |
| `vendor/bin/pint` | Format PHP code with Laravel Pint |
| `vendor/bin/pint --dirty` | Format only changed PHP files |

On Windows PowerShell, use `npm.cmd` if `npm` is blocked:

```bash
npm.cmd install
npm.cmd run dev
npm.cmd run build
```

## Troubleshooting

### Composer Not Found

Cause: Composer is not installed or not available in your terminal path.

Fix:

* Install Composer from https://getcomposer.org/download/
* Restart your terminal
* Run `composer -V`

### PHP Version Mismatch

Cause: Laravel requires PHP 8.3 or newer.

Fix:

* Install PHP 8.3+
* Make sure your terminal uses the correct PHP executable
* Run `php -v`

### Missing PHP Extensions

Cause: Required extensions such as `openssl`, `pdo_mysql`, `mbstring`, `xml`, or `fileinfo` are disabled.

Fix:

* Enable extensions in `php.ini`
* Restart your terminal or local server
* Run `php -m`

### Node.js or npm Missing

Cause: Node.js is not installed or npm is not available in your path.

Fix:

* Install Node.js from https://nodejs.org/
* Restart your terminal
* Run `node -v` and `npm -v`

### PowerShell Blocks npm

Cause: Windows execution policy blocks `npm.ps1`.

Fix:

Use `npm.cmd` instead:

```bash
npm.cmd -v
npm.cmd install
npm.cmd run dev
```

### Missing `.env`

Cause: Laravel cannot find local environment configuration.

Fix:

```bash
copy .env.example .env
php artisan key:generate
```

### Application Key Missing

Cause: `APP_KEY` is empty in `.env`.

Fix:

```bash
php artisan key:generate
```

### Database Connection Errors

Cause: MySQL credentials are wrong, the database does not exist, or MySQL is not running.

Fix:

* Start MySQL
* Create the database listed in `.env`
* Verify `DB_CONNECTION=mysql`
* Verify `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`
* Run `php artisan config:clear`

### Tables Are Missing

Cause: Migrations have not been run.

Fix:

```bash
php artisan migrate
```

For a fresh development reset:

```bash
php artisan migrate:fresh --seed
```

### Default Admin Cannot Login

Cause: Seeders have not run, the database was changed, or the password was overridden in `.env`.

Fix:

```bash
php artisan db:seed
```

If the database is disposable:

```bash
php artisan migrate:fresh --seed
```

### Vite Development Server Not Running

Cause: Frontend assets are being loaded through Vite, but the Vite server is not running.

Fix:

```bash
npm run dev
```

Or build static assets:

```bash
npm run build
```

### Dependency Installation Failures

Cause: Outdated PHP, missing PHP extensions, network issues, or incompatible Node/npm versions.

Fix:

* Check `php -v`
* Check `php -m`
* Check `composer -V`
* Check `node -v`
* Delete `vendor/` or `node_modules/` only if you understand the impact, then reinstall dependencies

## Contributing

Before starting work:

* Pull the latest changes.
* Create a focused feature branch.
* Keep changes scoped to the task.
* Follow existing Laravel conventions.
* Write or update tests for behavior changes.
* Run `php artisan test` before committing.
* Run `vendor/bin/pint --dirty` before committing PHP changes.
* Update documentation when setup steps, routes, credentials, or behavior changes.
* Use meaningful commit messages.

## Documentation

Additional project documentation is available in the `docs/` directory.

Helpful starting points:

* `docs/project-overview.md`
* `docs/system-requirements.md`
* `docs/project-lifecycle.md`
* `docs/implementation/login-feature.md`

Some documentation describes planned system scope. The root README reflects the current implementation state.
