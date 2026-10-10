# Note from Jassy — Remaining Test Failures for Tasks 2 and 3

Hello Angela and Adriane,

Task 1 (login, roles, user management, and volunteer documents) is now complete. All
Task 1 tests are passing, and the work has been pushed to the `jassy` branch.

There are 3 remaining failing tests in the full suite that fall outside Task 1 scope.
They are listed below with a brief explanation so they are easy to pick up.

---

## For Adriane — Task 2 (Incident Reporting and Severity)

**File:** `backend/tests/Feature/VolunteerIncidentSubmissionTest.php`
**Test:** `test_verified_volunteer_sees_confirmation_after_submitting_incident`

After a volunteer submits an incident, the confirmation page should display the incident
reference number in the format `INC-0001`. Currently, this reference is not shown on the
page. The volunteer confirmation view
(`resources/views/volunteer/incidents/create.blade.php`) needs to render the `INC-####`
reference for the newly submitted incident.

Note that this format is already used in the dispatcher map feed:
`'INC-'.str_pad((string) $incident->id, 4, '0', STR_PAD_LEFT)`. Using the same format
will keep references consistent across the system.

---

## For Angela — Task 3 (Dispatcher Incident Coordination)

**File:** `backend/tests/Feature/DispatcherIncidentCoordinationTest.php`

### 1. `test_dispatcher_can_view_and_filter_the_incident_queue`

When the dispatcher queue is filtered by category or barangay, only matching incidents
should appear. Please confirm that filtering by `category=Fire` does not display the
unrelated flood incident anywhere on the page, including any summary or sidebar counts.

### 2. `test_dispatcher_map_feed_returns_database_incidents_as_geojson`

The test expects the `impact_radius` value as the decimal `300.0`, but the response
currently returns the integer `300`. Please make these consistent by either updating the
test to expect `300`, or ensuring the map feed returns a decimal value. Either approach
is fine as long as the test and the response agree.

---

## Note for both

Before running the tests, please rebuild the frontend assets, as the current build is
out of date:

```bash
cd backend
npm run build
```

Then run the tests with:

```bash
DB_CONNECTION=mysql DB_DATABASE=resqlink_test php artisan test
```

If you have any questions or need clarification, please let me know. Thank you.

— Jassy


