# ResQLink Task Checklist

Each requirement is followed by the specific frontend and backend work needed.

## Task 1 — Jassy — User Access and Volunteer Management

- `TASK1-001` An admin can create, edit, activate, and deactivate volunteer accounts.
  - Frontend: Build the user table and create/edit form with volunteer fields, account-state controls, feedback, and errors.
  - Backend: Implement authorized create, update, activation, and deactivation actions with validation and persisted state.
- `TASK1-002` An admin can create dispatcher and admin accounts and modify their user information.
  - Frontend: Provide create/edit forms with role choices and safe password/reset feedback.
  - Backend: Enforce roles, hash passwords, validate unique details, and prevent privilege escalation.
- `TASK1-003` The system authenticates users before protected-page access.
  - Frontend: Build login/logout screens, validation messages, role redirects, and unauthenticated states.
  - Backend: Configure password verification, sessions, logout, middleware, and redirects.
- `TASK1-004` The system authorizes actions by role; inactive volunteers cannot access volunteer functions.
  - Frontend: Show role-appropriate navigation and unauthorized/inactive states.
  - Backend: Enforce policies on direct requests and reject inactive-volunteer operations.
- `TASK1-005` An admin can record each required volunteer document and its file reference or metadata.
  - Frontend: Provide fields for the three required documents and display filename/reference and review state.
  - Backend: Persist document relationships/metadata, prevent duplicate types, authorize admins, and protect private files.
- `TASK1-006` Account availability uses `active` or `inactive`, separate from incident `status`.
  - Frontend: Display only `active` or `inactive` for account availability.
  - Backend: Store and validate availability separately from incident status.
- `TASK1-007` Validation and duplicate-account errors are clear and do not expose sensitive information.
  - Frontend: Show field-level validation, duplicate, authorization, and upload errors safely.
  - Backend: Add validation/uniqueness protection and exclude passwords/private documents from responses and logs.

## Task 2 — Adriane — Incident Reporting and Severity Assessment

- `TASK2-001` A verified volunteer can submit a complete incident report.
  - Frontend: Provide the complete incident form and submit action.
  - Backend: Accept reports only from authenticated, verified volunteers.
- `TASK2-002` Categories are limited to Flood, Earthquake, Landslide, and Fire.
  - Frontend: Present only the four allowed choices.
  - Backend: Validate and reject unsupported categories.
- `TASK2-003` An incident stores a local location and impact radius.
  - Frontend: Provide latitude, longitude, and radius inputs with validation.
  - Backend: Validate ranges and persist coordinates, radius, and timestamp.
- `TASK2-004` Each report is submitted under the authenticated volunteer session.
  - Frontend: Submit through the authenticated volunteer flow.
  - Backend: Associate the incident with the authenticated volunteer.
- `TASK2-005` Invalid or incomplete incident data produces useful errors.
  - Frontend: Display field, location, radius, and inactive-account errors.
  - Backend: Reject invalid submissions with safe validation responses.
- `TASK2-006` The system calculates preliminary severity as Low, Moderate, High, or Critical.
  - Frontend: Display the returned preliminary severity.
  - Backend: Calculate and return only the four supported values.
- `TASK2-007` Severity is preliminary decision support, not an official assessment.
  - Frontend: Label severity as preliminary decision support.
  - Backend: Keep it separate from dispatcher or official assessments.
- `TASK2-008` An accepted incident receives an identifier, timestamp, and initial `Reported` status.
  - Frontend: Display the identifier, timestamp, and status.
  - Backend: Generate the identifier, persist the timestamp, and assign `Reported`.

## Task 3 — Angela — Dispatcher Incident Coordination

- `TASK3-001` Dispatchers can view incoming incidents in an incident queue.
  - Frontend: Build the dispatcher queue with required summary fields.
  - Backend: Provide dispatcher-authorized access to incoming incidents.
- `TASK3-002` The queue is ordered by preliminary severity and shows workflow status.
  - Frontend: Display severity ordering and current status.
  - Backend: Order incidents and return workflow status.
- `TASK3-003` Dispatchers can view complete incident summaries and details.
  - Frontend: Build the incident details view.
  - Backend: Return all required summary fields.
- `TASK3-004` Active and historical incidents are shown on the local Leaflet map.
  - Frontend: Render local markers and impact-radius circles.
  - Backend: Supply authorized coordinates and radius values.
- `TASK3-005` Workflow status is limited to Reported, Received, Dispatched, and Completed.
  - Frontend: Provide only the four allowed status controls.
  - Backend: Validate and reject other status values.
- `TASK3-006` The system records the dispatcher and timestamp for every status change.
  - Frontend: Display dispatcher, timestamp, and history entries.
  - Backend: Persist the acting dispatcher and change timestamp.
- `TASK3-007` Active, completed, and archived incidents are visibly distinguished.
  - Frontend: Use clear labels or visual states.
  - Backend: Identify and filter each incident group consistently.
- `TASK3-008` Users without dispatcher access cannot perform dispatcher operations.
  - Frontend: Show unauthorized state and hide restricted controls.
  - Backend: Enforce authorization for all dispatcher operations.

## Task 4 — Monique — Admin Incident Records and Reports

- `TASK4-001` Admins can access persisted incident records.
  - Frontend: Build the admin incident-records view.
  - Backend: Provide admin-authorized record access.
- `TASK4-002` Admins can search records by relevant fields.
  - Frontend: Provide search controls and matching results.
  - Backend: Search persisted records by approved fields.
- `TASK4-003` Admins can filter records by category and workflow status.
  - Frontend: Provide category and status filters.
  - Backend: Apply validated filters to persisted records.
- `TASK4-004` Admins can retrieve archived and historical incidents.
  - Frontend: Provide archived and historical record views.
  - Backend: Retrieve those records under admin authorization.
- `TASK4-005` Admins can generate daily reports.
  - Frontend: Provide a daily period selector and results view.
  - Backend: Generate daily results from persisted data.
- `TASK4-006` Admins can generate weekly reports.
  - Frontend: Provide a weekly period selector and results view.
  - Backend: Generate weekly results from persisted data.
- `TASK4-007` Admins can generate monthly reports.
  - Frontend: Provide a monthly period selector and results view.
  - Backend: Generate monthly results from persisted data.
- `TASK4-008` Reports include category, barangay, status, and frequency statistics.
  - Frontend: Display the required statistics.
  - Backend: Aggregate the required statistics.
- `TASK4-009` Report results are scoped to the selected reporting period.
  - Frontend: Show the selected period and result scope.
  - Backend: Enforce the selected period in queries and aggregation.
- `TASK4-010` Users without admin access cannot access records or reports.
  - Frontend: Show an unauthorized state.
  - Backend: Enforce authorization for all admin record/report routes.

