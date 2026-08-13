# ResQLink Feature Requirements

**Project:** ResQLink: A Volunteer-Based Emergency Alert and Dispatch Coordination Support System  
**Version:** 1.0  
**Status:** Source of Truth for Feature Scope

## Purpose

This document translates `system-requirements.md` into implementable feature
requirements and assigns each requirement to the phases in
`project-lifecycle.md`. It is intended to guide analysis, design, database
work, backend development, dashboard development, mobile development,
integration, and verification.

The system supports the **Notify–Receive–Dispatch** workflow:

```text
Volunteer submits incident report
              ↓
Dispatcher receives and reviews incident
              ↓
Dispatcher updates status and coordinates response
```

## Actors and Platforms

| Actor | Platform | Primary responsibilities |
| --- | --- | --- |
| Volunteer | Flutter mobile application | Submit incident reports and view submission confirmation |
| Dispatcher | Laravel Blade and Bootstrap web dashboard | Review, prioritize, map, and update incidents |
| Admin | Laravel Blade and Bootstrap web dashboard | Manage users, volunteer documents, incident records, and reports |

## Requirement Format

Each requirement has a stable identifier, a priority, and acceptance criteria.
`Must` requirements are part of the initial system scope. `Should` requirements
support usability or maintainability but must not displace core workflow work.

## Phased Delivery Plan

### Phase 1 — Project Planning

**Objective:** Confirm scope, actors, constraints, and approved terminology.

Requirements and outputs:

- `PLAN-001` Confirm Volunteer, Dispatcher, and Admin as the system actors.
- `PLAN-002` Confirm the mobile application is for volunteers and the web
  dashboard is for dispatchers and admins.
- `PLAN-003` Confirm the initial incident categories: Flood, Earthquake,
  Landslide, and Fire.
- `PLAN-004` Confirm the incident status lifecycle: Reported, Received,
  Dispatched, and Completed.
- `PLAN-005` Record the preliminary severity assessment as decision support;
  it must not replace dispatcher judgment or official emergency assessment.
- `PLAN-006` Apply the terms `incident`, `incident report`, `volunteer`,
  `dispatcher`, `admin`, `role`, `active`, `inactive`, and `status` consistently.

**Completion evidence:** Approved scope, actor definitions, requirements,
technology stack, and development standards.

### Phase 2 — System Analysis

**Objective:** Define the behavior and data needed by every core feature.

- `ANALYSIS-001` Document the current and proposed Notify–Receive–Dispatch
  workflows.
- `ANALYSIS-002` Define authorization boundaries for each role.
- `ANALYSIS-003` Define incident data: category, barangay, nearest landmark,
  estimated affected persons, latitude, longitude, impact radius, notes,
  reporting volunteer, reported timestamp, severity, and status.
- `ANALYSIS-004` Define volunteer account data and the three required document
  types: endorsement letter, barangay clearance, and certificate of residency.
- `ANALYSIS-005` Define incident history, archived records, report periods, and
  category/barangay/status statistics.
- `ANALYSIS-006` Define validation, error, empty-state, and unauthorized-access
  behavior for each actor.

**Completion evidence:** Functional requirements, user stories, data
requirements, module boundaries, and workflow models are documented.

### Phase 3 — System Design

**Objective:** Produce an approved blueprint before implementation.

- `DESIGN-001` Design volunteer submission, dispatcher queue, incident details,
  map, admin management, records, and report screens.
- `DESIGN-002` Design use-case, activity, sequence, ER, class, architecture,
  and deployment diagrams for the approved scope.
- `DESIGN-003` Design relationships among users, roles, volunteers, incidents,
  incident history, and generated reports.
- `DESIGN-004` Design Leaflet marker and adjustable impact-radius behavior.
- `DESIGN-005` Define API request and response contracts between Flutter and
  Laravel, including validation errors and submission confirmation.
- `DESIGN-006` Map names to `naming-conventions.md`: Laravel resources use
  singular PascalCase classes, plural snake_case tables, role-prefixed routes,
  and role/resource-aligned Blade folders.

**Completion evidence:** Approved UI designs, diagrams, database schema,
architecture, and API contracts.

### Phase 4 — Environment Setup

**Objective:** Establish the approved implementation foundation.

- `SETUP-001` Configure the Laravel application and PHP 8.x environment.
- `SETUP-002` Configure MySQL and database connection settings.
- `SETUP-003` Configure Blade, Bootstrap 5, Leaflet.js, and Vite assets for the
  web dashboard.
- `SETUP-004` Create the Flutter application and HTTP/HTTPS API configuration.
- `SETUP-005` Configure Git branches, GitHub collaboration, and documentation
  locations using the naming conventions.

**Completion evidence:** Team members can run the approved project structure
locally and access the shared repository.

### Phase 5 — Database Development

**Objective:** Implement the data layer required by all approved features.

- `DATA-001` Create tables for users, roles, volunteers, incidents, incident
  history, generated reports, and volunteer documents as approved by the ERD.
- `DATA-002` Store incident coordinates as latitude and longitude, impact radius,
  reported timestamp, severity, and workflow status.
- `DATA-003` Store volunteer document type, file reference, and audit metadata.
- `DATA-004` Enforce foreign keys, required fields, valid categories, valid
  statuses, and valid severity levels.
- `DATA-005` Create Eloquent models, relationships, factories, and seeders using
  names such as `Incident`, `Role`, `User`, `IncidentFactory`, and `RoleSeeder`.
- `DATA-006` Preserve incident history whenever a dispatcher changes status.

**Completion evidence:** MySQL schema, migrations, models, relationships,
seeders, and constraints support the approved entities and workflow.

### Phase 6 — Backend Development

**Objective:** Implement authentication, authorization, business rules, and APIs.

- `BACKEND-001` Authenticate volunteers, dispatchers, and admins.
- `BACKEND-002` Authorize role-specific actions and protect dashboard and API
  routes.
- `BACKEND-003` Validate and store volunteer incident reports, including all
  required fields, coordinates, timestamp, volunteer association, and notes.
- `BACKEND-004` Calculate preliminary severity from estimated affected persons
  and impact radius, returning Low, Moderate, High, or Critical.
- `BACKEND-005` Provide incident listing, detail, search, filtering, map data,
  and status-update operations for authorized users.
- `BACKEND-006` Record each status transition with actor and timestamp.
- `BACKEND-007` Provide admin operations for volunteer, dispatcher, and admin
  accounts, including volunteer document records and account activation.
- `BACKEND-008` Generate daily, weekly, and monthly incident summaries by
  category, barangay, status, and frequency.
- `BACKEND-009` Return consistent validation, authorization, not-found, and
  server-error responses from RESTful endpoints.

**Completion evidence:** Laravel business logic, validation, authorization,
REST API operations, and report generation are functional.

### Phase 7 — Web Dashboard Development

**Objective:** Deliver role-specific web workflows with Blade and Bootstrap.

#### Dispatcher features

- `WEB-DISPATCHER-001` Show a dashboard summary of incoming and active incidents.
- `WEB-DISPATCHER-002` Display an incident queue organized by preliminary
  severity and current status.
- `WEB-DISPATCHER-003` Show incident details including category, volunteer,
  date/time, affected persons, location, barangay, landmark, radius, notes,
  severity, and status.
- `WEB-DISPATCHER-004` Display active and historical incidents on a Leaflet map
  with markers and impact-radius visualization.
- `WEB-DISPATCHER-005` Allow authorized dispatchers to move an incident through
  Reported, Received, Dispatched, and Completed, with visible history.

#### Admin features

- `WEB-ADMIN-001` Create, edit, activate, and deactivate volunteer accounts.
- `WEB-ADMIN-002` Store and review the three required volunteer document types.
- `WEB-ADMIN-003` Create dispatcher and admin accounts and modify user details.
- `WEB-ADMIN-004` Search incident records, filter by category and status, and
  access archived incidents.
- `WEB-ADMIN-005` Select daily, weekly, or monthly report periods and view the
  required incident distributions and summaries.

**Completion evidence:** Dispatchers and admins can perform their approved
tasks through role-protected, responsive dashboard pages.

### Phase 8 — Mobile Application Development

**Objective:** Deliver the volunteer incident-reporting workflow in Flutter.

- `MOBILE-001` Allow an authenticated volunteer to log in and log out.
- `MOBILE-002` Provide a validated incident form for category, barangay,
  nearest landmark, estimated affected persons, and optional notes.
- `MOBILE-003` Allow the volunteer to select a location on a Leaflet-backed map,
  capture coordinates, and adjust the impact radius.
- `MOBILE-004` Submit the incident report to the Laravel API over HTTP/HTTPS.
- `MOBILE-005` Show clear loading, validation-error, failure, and successful
  submission-confirmation states.
- `MOBILE-006` Associate every submission with the authenticated volunteer and
  prevent inactive accounts from submitting reports.

**Completion evidence:** A verified volunteer can submit a complete incident
report and receive confirmation.

### Phase 9 — System Integration

**Objective:** Connect the Flutter app, Laravel backend, MySQL database, and
web dashboards into one working prototype.

- `INTEGRATION-001` Verify volunteer login, report submission, persistence, and
  confirmation end to end.
- `INTEGRATION-002` Verify submitted incidents appear in the dispatcher queue,
  detail view, and Leaflet map.
- `INTEGRATION-003` Verify severity is calculated and used for queue ordering.
- `INTEGRATION-004` Verify dispatcher status changes update the incident and
  append incident history.
- `INTEGRATION-005` Verify admins can search, filter, archive-access, and report
  on the same incident data.
- `INTEGRATION-006` Verify role restrictions, inactive-account behavior, and
  API error handling across clients.

**Completion evidence:** The Notify–Receive–Dispatch workflow operates correctly
across all approved components.

### Phase 10 — Testing

**Objective:** Verify each requirement and resolve critical defects.

- `TEST-001` Test authentication and role authorization for all actors.
- `TEST-002` Test incident validation, coordinates, radius, severity, and status
  transitions.
- `TEST-003` Test dashboard queue, details, map, search, filters, and reports.
- `TEST-004` Test volunteer account activation and document handling.
- `TEST-005` Test Flutter-to-Laravel API communication and failure states.
- `TEST-006` Test database relationships, constraints, and incident history.
- `TEST-007` Conduct acceptance testing with representative volunteers and
  dispatchers using the Notify–Receive–Dispatch workflow.

**Completion evidence:** Test reports exist, critical issues are resolved, and
known remaining issues are recorded.

### Phase 11 — Documentation

**Objective:** Keep project documentation synchronized with the implementation.

- `DOCS-001` Update system requirements when approved behavior changes.
- `DOCS-002` Document setup, routes, credentials/configuration, API contracts,
  database entities, and report behavior.
- `DOCS-003` Update diagrams and implementation notes for completed features.
- `DOCS-004` Verify documentation filenames and technical terms follow
  `naming-conventions.md`.
- `DOCS-005` Record approved scope changes and decisions in the appropriate
  documentation or ADR.

**Completion evidence:** User, technical, database, API, and project documents
accurately reflect the implemented system.

### Phase 12 — Final Presentation

**Objective:** Demonstrate the complete approved prototype.

- `PRESENT-001` Prepare seeded users for volunteer, dispatcher, and admin roles.
- `PRESENT-002` Demonstrate volunteer submission with map location and radius.
- `PRESENT-003` Demonstrate dispatcher receipt, severity-based queue review,
  map inspection, and status updates.
- `PRESENT-004` Demonstrate admin account/document management, incident search,
  archive access, and report generation.
- `PRESENT-005` Prepare slides, a demonstration script, known limitations, and
  evidence that requirements map to implemented features.

**Completion evidence:** The final prototype and demonstration are ready for
panel evaluation.

## Cross-Cutting Requirements

- `CROSS-001` Use Laravel for backend logic, authentication, authorization,
  validation, APIs, database communication, and reports.
- `CROSS-002` Use Blade and Bootstrap 5 for dispatcher and admin web interfaces.
- `CROSS-003` Use Flutter for the volunteer mobile application.
- `CROSS-004` Use MySQL for centralized relational data storage.
- `CROSS-005` Use Leaflet.js for map display, markers, coordinates, and impact
  radius visualization.
- `CROSS-006` Use Git and GitHub for version control, review, collaboration, and
  documentation history.
- `CROSS-007` Apply role-based access control, server-side validation, protected
  credentials, and audit-friendly incident history.
- `CROSS-008` Keep source/code files under 500 lines, targeting 400 or fewer,
  by extracting reusable controllers, services, requests, policies, views, and
  frontend modules.
- `CROSS-009` Use the exact domain term `incident`; do not substitute `alert`,
  `case`, or `emergency` for the tracked event.

## Traceability and Change Control

Every implemented feature should reference its requirement ID in its issue,
pull request, or implementation note. A requirement is complete only when its
implementation, documentation, and applicable test evidence are available.

If a requirement changes, update this document first, then update affected
diagrams, schema/API designs, implementation notes, and
`project-lifecycle.md` when phase deliverables change. Approved changes must be
recorded through the project's normal GitHub review and documentation workflow.

## Phase Definition of Done

A phase is complete when its requirements and deliverables are finished,
reviewed, documented, committed to GitHub, and its outstanding issues are
recorded, in accordance with `project-lifecycle.md`.
