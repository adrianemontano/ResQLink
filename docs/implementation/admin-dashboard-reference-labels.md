# TASK4 Admin Dashboard Reference Labels

Task owner: Monique

The admin dashboard's recent-incidents query uses the normalized incident
reference tables for category, barangay, severity, and status labels. It must
not reference the removed legacy text columns on `incidents`.

The dashboard joins `incident_categories`, `barangays`, `severity_levels`, and
`incident_statuses` through the corresponding foreign keys and selects their
names for display.
