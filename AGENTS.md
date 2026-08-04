# AGENTS.md

Instructions for AI coding agents working in this repository.

## Project

ResQLink is a Laravel-based emergency alert and dispatch coordination system. The
current implementation is the `backend/` Laravel application (Blade views, Vite,
MySQL) serving Admin and Dispatcher web users. A volunteer mobile app is planned
but not yet implemented. See `README.md` and `docs/` for full context.

## File Size & Reusability Rule

This is a hard constraint on all code written or modified in this repository:

- **Hard limit: under 500 lines per code file.** A file at or above 500 lines
  must be split before the change is considered done.
- **Target: 400 lines or fewer.** Prefer this over the hard limit.
- **Better: 300 lines or fewer.** Aim here whenever the task allows it.

The rule applies to source/code files (PHP classes, controllers, Blade
templates, JS/TS modules, etc.), not to generated files, vendor code, lock
files, or data files.

How to stay within the limit without cutting functionality:

- Extract reusable logic into small, focused, well-named units (methods,
  classes, traits, form requests, services, Blade partials/components,
  JS modules/helpers) instead of writing long procedural blocks.
- Favor composition over large monolithic files: a controller should
  delegate to services/actions/requests rather than holding all logic inline.
- Split by responsibility (single-responsibility principle), not by
  arbitrary line count — extractions should be things that make sense to
  reuse or test independently.
- Never truncate, stub, or drop required behavior to satisfy the line
  count. If a task genuinely cannot fit, split it into multiple cooperating
  files rather than leaving it incomplete or oversized.
- Before finishing a change, check the line count of every file you
  touched or created (e.g. `wc -l <file>` or the editor's line count) and
  refactor if it exceeds the limits above.

This rule applies equally to new files and to existing files you edit —
if an edit pushes a file over the limit, extract code out of it as part
of that change.

## Conventions

- Follow existing Laravel conventions already used in `backend/`.
- Update `docs/` when setup steps, routes, credentials, or behavior change.

## Agent & Command Restrictions

- **Do not spawn sub-agents** for tasks in this repository. Do the work
  directly yourself rather than delegating to another agent.
- **Do not run lint, test, or build commands** (e.g. `vendor/bin/pint`,
  `php artisan test`, `npm run build`, `npm run dev`, or any other
  lint/test/build/testing task) **without getting an explicit "yes" from
  the user first.** Ask before running any of these, even if a change would
  normally warrant it.
- **Exception (suggest, don't auto-run):** when a change involves a big
  database or migration change (new/altered/dropped tables or columns,
  data-affecting migrations, schema changes touching multiple models),
  proactively suggest running `php artisan migrate` and `php artisan test`
  and wait for the user's "yes" before running them. Still do not run them
  automatically.
