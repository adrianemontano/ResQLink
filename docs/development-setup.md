# Development setup and update runbook

This guide lists what to run after a fresh clone and after pulling or merging
changes from a remote branch. Run application commands from `backend/`.

## Prerequisites

For future mobile work, also install a Node version supported by the selected
Expo SDK, Android Studio and its compatible Android SDK/JDK tooling, and Android
platform tools. Exact versions must be selected and recorded during the
Expo/React Native/MapLibre compatibility proof of concept; the Expo application
has not yet been generated.

## Planned Expo Android workflow

MapLibre React Native cannot run in Expo Go. After the fresh `mobile/` project
is created and its native dependencies and config plugin are installed, create
a local development build with `npx expo run:android` or use an EAS development
build. Start Metro for the installed client with
`npx expo start --dev-client`. Rebuild after native dependency, config-plugin,
Expo/React Native runtime, or native configuration changes.

A physical phone must use the laptop LAN IP, for example
`http://<LAPTOP_LAN_IP>:8000/api` and
`http://<LAPTOP_LAN_IP>:8080/styles/<STYLE_ID>/style.json`; phone `localhost`
does not reach the laptop. Keep devices on the same network, bind services to
reachable interfaces, allow firewall ports, and use only debug-scoped cleartext
HTTP allowances. Production endpoints require HTTPS. An Expo tunnel does not
automatically expose Laravel or TileServer.

- PHP 8.3 or newer with the extensions listed in the root `README.md`
- Composer 2
- Node.js 20 or newer and npm
- MySQL 8.0 or MariaDB 10.6 or newer

The volunteer incident form and dispatcher incident map share MapLibre and a
local TileServer GL process. Their packages are declared in
`backend/package.json`.

## Map view quick start

Run the dependency installation once from the repository root:

```powershell
cd backend
npm.cmd install --ignore-scripts=false
```

Then keep three PowerShell terminals open. In each terminal, change to the
`backend/` directory and run one of these commands:

```powershell
# Terminal 1: Laravel application server
php artisan serve

# Terminal 2: Vite frontend asset server
npm.cmd run dev

# Terminal 3: local map tile server
npx.cmd tileserver-gl-light public/maps/osm-2020-02-10-v3.11_philippines_cebu.mbtiles --port 8080
```

All three processes must remain running for the map page and its local map
background to render during development. Open the application at
`http://127.0.0.1:8000`, then visit the volunteer incident form or dispatcher
map. If this is a fresh clone, complete the full setup below before starting
the servers.

## After a fresh clone

From the repository root:

```powershell
cd backend
composer install
npm.cmd install --ignore-scripts=false
Copy-Item .env.example .env
php artisan key:generate
```

Create a MySQL database named `resqlink`, then set the correct local credentials
in `backend/.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=resqlink
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

Clear cached configuration and initialize the database:

```powershell
php artisan config:clear
php artisan migrate:fresh --seed
```

`migrate:fresh` deletes every table in the configured database. Use it for a
new or disposable development database only.

## After pulling or merging remote changes

From `backend/`, update both dependency sets and apply pending migrations:

```powershell
composer install
npm.cmd install --ignore-scripts=false
php artisan config:clear
php artisan migrate
```

These dependency commands are safe to run when the lock files did not change.
Do not copy `.env.example` over an existing `.env`; that would replace local
database credentials and the application key. Review new environment variables
manually by comparing `.env.example` with the branch or commit you pulled.

Use `php artisan migrate:fresh --seed` after a pull only when the local database
is disposable and a complete reset is intentional. Otherwise use
`php artisan migrate`.

## Start the application

Keep three PowerShell terminals open in `backend/`.

Terminal 1 — Laravel:

```powershell
php artisan serve
```

Terminal 2 — Vite:

```powershell
npm.cmd run dev
```

Terminal 3 — local map server:

```powershell
npx.cmd tileserver-gl-light public/maps/osm-2020-02-10-v3.11_philippines_cebu.mbtiles --port 8080
```

Verify the services:

- Laravel: `http://127.0.0.1:8000`
- Tile server: `http://localhost:8080`
- Volunteer incident map: `http://127.0.0.1:8000/volunteer/incidents/create`
- Dispatcher incident map: `http://127.0.0.1:8000/dispatcher/map`

Both maps use the shared TileServer Positron style at
`http://localhost:8080/styles/positron/style.json`. Set the following value in
each developer's `backend/.env`:

```env
LOCAL_MAP_STYLE_URL=http://localhost:8080/styles/positron/style.json
```

After changing `.env`, refresh the app. If Laravel has cached configuration,
clear it from the `backend/` directory with `php artisan config:clear`.

## Development accounts

After successful seeding, use these accounts:

| Role | Email | Password |
| --- | --- | --- |
| Administrator | `admin@resqlink.local` | `Admin@12345` |
| Volunteer | `volunteer@resqlink.local` | `Volunteer@12345` |

The values can be overridden by the corresponding `DEFAULT_*` variables in
`.env`.

## Map-server troubleshooting on Windows

### Missing `node_sqlite3.node`

If TileServer cannot locate the SQLite bindings, npm lifecycle scripts were
probably disabled. Check the setting:

```powershell
npm.cmd config get ignore-scripts
```

If it prints `true`, rebuild SQLite with its install script enabled:

```powershell
npm.cmd rebuild sqlite3 --ignore-scripts=false
node -e "require('sqlite3'); console.log('SQLite binding loaded successfully')"
```

### Missing `tileserver-gl-styles/styles`

TileServer GL Light 5.6.0 may look for preview styles inside its own nested
`node_modules` directory even when npm hoists the package to the project root.
Install the style dependency at the expected location:

```powershell
npm.cmd install --prefix .\node_modules\tileserver-gl-light tileserver-gl-styles@2.0.0 --no-save --ignore-scripts=false
```

Confirm the directory exists, then restart the map server:

```powershell
Test-Path .\node_modules\tileserver-gl-light\node_modules\tileserver-gl-styles\styles
npx.cmd tileserver-gl-light public/maps/osm-2020-02-10-v3.11_philippines_cebu.mbtiles --port 8080
```

The `Test-Path` command should return `True`.

## Common cautions

- Do not commit `.env` or disclose its generated `APP_KEY` and passwords.
- Do not run `npm audit fix --force` without reviewing the proposed breaking
  dependency upgrades.
- Use `npm.cmd` on Windows when PowerShell blocks `npm.ps1`.
- Restart Laravel after changing `.env` or clearing configuration.
