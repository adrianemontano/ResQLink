# ResQLink Naming Conventions

Use this guide when creating or renaming files, classes, routes, database fields,
Blade views, assets, tests, and documentation. The goal is to make every layer of
ResQLink use the same words for the same concept.

## Core Rules

- Use clear, domain-specific names over abbreviations.
- Prefer Laravel defaults unless this guide says otherwise.
- Use one name for one concept across the codebase.
- Keep file names, class names, route names, view paths, and database names aligned.
- Name things by what they represent, not where they are currently displayed.
- Avoid vague names such as `data`, `info`, `item`, `record`, `manager`, or `handler`.
- Do not mix similar terms unless they mean different things.

## Project Terms

| Concept | Preferred term | Avoid |
| --- | --- | --- |
| Emergency event being tracked | `incident` | `alert`, `case`, `emergency`, `report` |
| Submitted incident details | `incident report` | `alert report`, `case report` |
| Person coordinating response | `dispatcher` | `operator`, `staff` |
| System administrator | `admin` | `administrator`, `superuser` |
| Community responder | `volunteer` | `responder`, `helper` |
| User permission group | `role` | `user_type`, `account_type` |
| Account enabled/disabled state | `active` / `inactive` | `enabled`, `disabled`, `status` |
| Map display | `map` | `geo_view`, `location_view` |

Use `status` only for workflow state, such as an incident status. Use `active`
only for account availability.

## PHP And Laravel

Use `PascalCase` for PHP classes and `camelCase` for methods and variables.

| Type | Pattern | Example |
| --- | --- | --- |
| Model | Singular noun | `Incident`, `Role`, `User` |
| Controller | Singular resource + `Controller` | `IncidentController` |
| Form request | Verb/action + resource + `Request` | `StoreIncidentRequest` |
| Middleware | Verb or rule phrase | `EnsureUserHasRole` |
| Service | Domain action + `Service` | `IncidentDispatchService` |
| Action | Verb + object | `AssignIncidentDispatcher` |
| Policy | Model + `Policy` | `IncidentPolicy` |
| Seeder | Singular domain + `Seeder` | `RoleSeeder` |
| Factory | Model + `Factory` | `IncidentFactory` |

For role-specific controllers, use namespaces instead of longer class names:

```php
App\Http\Controllers\Admin\IncidentController
App\Http\Controllers\Dispatcher\IncidentController
```

Resource controllers should use Laravel method names where possible: `index`,
`create`, `store`, `show`, `edit`, `update`, and `destroy`.

Use action names for custom methods: `resetPassword`, `toggleActivation`,
`assignDispatcher`. Use boolean-style names for checks: `isActive`, `hasRole`,
`canDispatch`.

Collections should be plural. Single model instances should be singular:

```php
$incident
$incidents
$activeDispatchers
$selectedRole
```

## Routes

Use lowercase kebab-case URLs and dot-separated route names.

```php
/admin/dashboard       -> admin.dashboard
/admin/users           -> admin.users.index
/admin/users/create    -> admin.users.create
/dispatcher/incidents  -> dispatcher.incidents.index
/dispatcher/map        -> dispatcher.map
```

Rules:

- Prefix routes by role when the page belongs to one role: `admin.*`,
  `dispatcher.*`.
- Use plural resource names in URLs: `/users`, `/incidents`, `/reports`.
- Use singular route parameters: `/users/{user}`, `/incidents/{incident}`.
- Use action nouns only when the route is not a normal resource action:
  `admin.users.password`, `admin.users.activation`.
- Keep route names aligned with Blade folders and controller namespaces.

## Blade Views

Use lowercase folder and file names. Use kebab-case for multi-word view files.

```text
resources/views/admin/dashboard.blade.php
resources/views/admin/users/index.blade.php
resources/views/admin/users/partials/form.blade.php
resources/views/dispatcher/incidents/index.blade.php
resources/views/layouts/dashboard.blade.php
```

Rules:

- Group role-specific views under the role folder: `admin/`, `dispatcher/`.
- Group resource views under plural folders: `users/`, `incidents/`, `reports/`.
- Put shared page frames in `layouts/`.
- Put reusable fragments in `partials/`.
- Name partials by purpose: `form.blade.php`, `filters.blade.php`,
  `status-badge.blade.php`.
- View names should mirror route names where possible:
  `admin.users.index` maps to `admin/users/index.blade.php`.

## Database

Use Laravel conventions for database names.

| Item | Pattern | Example |
| --- | --- | --- |
| Tables | plural snake_case | `incidents`, `roles`, `users` |
| Columns | snake_case | `reported_at`, `incident_type`, `is_active` |
| Foreign keys | singular table + `_id` | `role_id`, `dispatcher_id` |
| Pivot tables | singular names alphabetically | `incident_user` |
| Indexes | table_columns_purpose | `incidents_status_index` |

Rules:

- Use `is_` or `has_` prefixes for booleans: `is_active`, `has_responded`.
- Use `_at` suffixes for timestamps: `reported_at`, `resolved_at`.
- Use `_date` only for date-only fields.
- Use `_count` for stored counts: `volunteer_count`.
- Avoid storing display labels in column names. Use stable values such as
  `pending`, `assigned`, `resolved`.

## Migrations

Use Laravel timestamped migration names with action phrases.

```text
2026_08_05_000000_create_incidents_table.php
2026_08_13_000000_add_status_to_incidents_table.php
2026_08_13_000001_create_incident_assignments_table.php
```

Migration file names should describe the schema action clearly.

## Frontend Assets

Use lowercase kebab-case for CSS and JavaScript files.

```text
public/css/resqlink-dashboard.css
public/css/resqlink-theme.css
public/js/dispatcher-map.js
public/js/sidebar-toggle.js
resources/js/app.js
resources/css/app.css
```

Rules:

- Prefix global custom public assets with `resqlink-`.
- Name feature-specific assets by feature: `dispatcher-map.js`.
- Name behavior files by action: `sidebar-toggle.js`.
- Use kebab-case for CSS classes: `.incident-card`, `.status-badge`,
  `.sidebar-nav`.
- Use `camelCase` for JavaScript variables and functions.
- Use `PascalCase` only for JavaScript classes or component constructors.

## Config And Environment

Use uppercase snake_case for environment variables and lowercase snake_case for
config keys.

```env
APP_NAME=ResQLink
MAP_DEFAULT_LATITUDE=10.3157
MAP_DEFAULT_LONGITUDE=123.8854
```

```php
'default_latitude' => env('MAP_DEFAULT_LATITUDE'),
'default_longitude' => env('MAP_DEFAULT_LONGITUDE'),
```

## Tests

Use `PascalCase` test class names ending in `Test`.

```text
tests/Feature/WebAuthenticationTest.php
tests/Feature/AdminUserManagementTest.php
tests/Feature/DispatcherIncidentMapTest.php
tests/Unit/IncidentStatusTest.php
```

Test method names should describe behavior:

```php
public function admin_can_create_dispatcher_account(): void
public function dispatcher_can_view_incident_map(): void
```

Prefer behavior names over implementation names.

## Documentation

Use lowercase kebab-case for Markdown files.

```text
docs/project-overview.md
docs/system-requirements.md
docs/naming-conventions.md
docs/implementation/login-feature.md
docs/decision-log/ADR-001-defer-mobile-development.md
```

Rules:

- Use `ADR-###-short-title.md` for architecture decision records.
- Use feature names for implementation notes: `login-feature.md`.
- Update docs when naming, routes, setup steps, credentials, or behavior change.

## Git Branches And Commits

Use lowercase kebab-case branches with a short type prefix.

```text
feature/dispatcher-incident-map
fix/admin-user-activation
docs/naming-conventions
refactor/incident-status-flow
```

Use concise imperative commit messages.

```text
Add dispatcher incident map
Fix admin user activation
Document naming conventions
Refactor incident status flow
```

## Quick Checklist

Before adding code, confirm:

- Does this name use the same domain term as the rest of the app?
- Does the model, table, controller, route, and view name line up?
- Is the URL kebab-case and the route name dot-separated?
- Is the database name snake_case?
- Is the PHP class `PascalCase` and the method or variable `camelCase`?
- Is the file under 500 lines, with a target of 400 lines or fewer?
