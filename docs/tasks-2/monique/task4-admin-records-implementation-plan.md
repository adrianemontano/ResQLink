# TASK4 — Monique: Admin Records and Reports Implementation Plan

## Seeder data

The current `DatabaseSeeder` creates `admin`, `dispatcher`, and `volunteer`
roles. The default development Admin account is `admin` /
`admin@resqlink.local` / `Admin@12345`. These credentials are development-only.

## Objective

Complete the admin-only incident-record workspace and reliable periodic reports
using persisted incident data.

## Implementation steps

1. Review the incident, reporter, status, and history relationships needed by
   the admin records and read-only detail pages.
2. Verify admin-authorized routes using the documented `admin.incidents.*` and
   `admin.reports.*` naming.
3. Implement or verify persisted record search across approved incident fields,
   category/status filters, pagination, and empty states.
4. Add read-only incident details, including status history and archived or
   completed records, without dispatcher status controls.
5. Define daily, weekly, and monthly period inputs with clear timezone and date
   boundary behavior.
6. Aggregate total incidents, category distribution, barangay distribution,
   status summaries, and frequency statistics from database queries.
7. Verify empty periods return zero totals and an explicit empty state.
8. Reconcile history behavior with Task 3 and field/severity definitions with
   Task 2, then document routes, report definitions, and permissions.

## Acceptance criteria

- Admins can search, filter, view, and access historical incident records.
- Records remain read-only in the admin workspace.
- Daily, weekly, and monthly reports use correctly scoped persisted data.
- Required category, barangay, status, and frequency statistics are accurate.
- Non-admin users are rejected from every records and reports route.
