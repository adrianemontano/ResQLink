# TASK4 — Monique: ResQLink Implementation Plan

## Ownership and objective

- **Task:** TASK4 — Admin Incident Records and Reports
- **Owner:** Monique
- **Primary user:** Admin / data management personnel
- **Objective:** Deliver a read-only administrative workspace for persisted
  incident records and period-based reports.

## Scope

The implementation covers admin-authorized incident records, search and
filtering, historical and archived access, and daily, weekly, and monthly
reporting. Records and reports must use the local MySQL data already managed by
the Laravel application and must work without remote map, tile, geocoding, or
CDN dependencies.

Task 2 owns incident fields and severity definitions. Task 3 owns dispatcher
coordination and status-history behavior. TASK4 should consume those contracts
without duplicating dispatcher queue logic or changing incident data.

## Implementation sequence

1. **Confirm domain contracts**
   - Review the `Incident` fields, reporter relationship, workflow statuses,
     severity values, and status-history relationship.
   - Record any cross-task assumptions before implementing queries or views.

2. **Define admin boundaries and routes**
   - Keep records under `admin.incidents.*` and reports under
     `admin.reports.*`.
   - Apply the existing admin authorization middleware or policy to every
     records and reports route, including detail and filtered requests.

3. **Build the incident-record query layer**
   - Add a focused query or service for persisted record retrieval.
   - Support incident ID, reporter identity, approved incident text fields,
     and report-date search, plus category, status, and archived/history scope.
   - Use pagination and stable ordering; preserve an explicit empty state.

4. **Implement read-only record pages**
   - Add views under `resources/views/admin/incidents/` for the index and
     detail pages, with reusable filter and status-history partials.
   - Display incident ID, reporter, category, affected persons, barangay,
     landmark, reported time, status, severity, and history.
   - Do not expose dispatcher status controls or mutation actions.

5. **Implement report aggregation**
   - Add a focused `AdminIncidentReportService` or equivalent service.
   - Support daily, Monday-based weekly, and monthly periods with validated
     date inputs, application-timezone boundaries, and start-inclusive /
     end-exclusive filtering.
   - Aggregate total incidents, status totals, category distribution,
     barangay distribution, and incident frequency statistics from persisted
     records.

6. **Build the reports workspace**
   - Add views under `resources/views/admin/reports/` for period selection,
     summary cards, charts or tables, and empty-period messaging.
   - Show the selected reporting period and make zero-result periods explicit.
   - Reuse shared dashboard navigation and identity/logout UI.

7. **Document and reconcile**
   - Document routes, permissions, report definitions, field meanings,
     dependencies, and date-boundary behavior in the relevant implementation
     documentation.
   - Use `TASK4` in checklist items and implementation notes, while keeping
     owner/task labels out of PHP classes, routes, tables, and domain names.

## Acceptance checklist

- [ ] `TASK4-001` Admins can view persisted incident records.
- [ ] `TASK4-002` Admins can search approved incident and reporter fields.
- [ ] `TASK4-003` Admins can filter by category and workflow status.
- [ ] `TASK4-004` Admins can access archived and historical records read-only.
- [ ] `TASK4-005` Daily reports use the selected persisted-data period.
- [ ] `TASK4-006` Weekly reports use the selected persisted-data period.
- [ ] `TASK4-007` Monthly reports use the selected persisted-data period.
- [ ] `TASK4-008` Reports include category, barangay, status, and frequency
  statistics.
- [ ] `TASK4-009` Report results are scoped correctly and empty periods show
  zero totals with an explicit empty state.
- [ ] `TASK4-010` Non-admin users are rejected from every records and reports
  route.
- [ ] Related documentation is updated with the delivered behavior.

## Expected implementation locations

- Routes: `backend/routes/`
- Controllers, queries, services, requests, and policies:
  `backend/app/`
- Admin incident views: `backend/resources/views/admin/incidents/`
- Admin report views: `backend/resources/views/admin/reports/`
- Feature coverage: `backend/tests/Feature/`
- Supporting documentation: `docs/implementation/` and this TASK4 folder

## Verification handoff

After implementation, Monique should verify the acceptance checklist against
the seeded development accounts documented in the Task 4 requirements. Any
database migration or data-affecting change must be called out separately
before migration and test commands are run.
