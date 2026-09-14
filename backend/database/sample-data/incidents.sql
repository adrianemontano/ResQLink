-- ResQLink sample incidents for local development.
-- Run after migrations and reference/admin seeders have completed.

BEGIN TRANSACTION;

INSERT INTO barangays (name, created_at, updated_at)
SELECT 'Lahug', datetime('now'), datetime('now')
WHERE NOT EXISTS (
    SELECT 1 FROM barangays WHERE name = 'Lahug'
);

INSERT INTO barangays (name, created_at, updated_at)
SELECT 'Capitol Site', datetime('now'), datetime('now')
WHERE NOT EXISTS (
    SELECT 1 FROM barangays WHERE name = 'Capitol Site'
);

INSERT INTO incidents (
    reported_by,
    category_id,
    barangay_id,
    severity_id,
    status_id,
    category,
    barangay,
    nearest_landmark,
    landmark,
    affected_population,
    persons_count,
    latitude,
    longitude,
    impact_radius,
    notes,
    reported_at,
    dispatched_at,
    completed_at,
    status,
    severity,
    created_at,
    updated_at
)
SELECT
    reporter.id,
    category.id,
    barangay.id,
    severity.id,
    incident_status.id,
    'Flood',
    'Lahug',
    'Lahug area',
    'Lahug area',
    50,
    50,
    10.3330000,
    123.8930000,
    50,
    'TASK3 demo reported incident',
    datetime('now', '-10 minutes'),
    NULL,
    NULL,
    'Reported',
    'High',
    datetime('now'),
    datetime('now')
FROM users AS reporter
JOIN incident_categories AS category ON category.name = 'Flood'
JOIN barangays AS barangay ON barangay.name = 'Lahug'
JOIN severity_levels AS severity ON severity.name = 'High'
JOIN incident_statuses AS incident_status ON incident_status.name = 'Reported'
WHERE reporter.email = 'admin@resqlink.local'
  AND NOT EXISTS (
      SELECT 1 FROM incidents WHERE notes = 'TASK3 demo reported incident'
  );

INSERT INTO incidents (
    reported_by,
    category_id,
    barangay_id,
    severity_id,
    status_id,
    category,
    barangay,
    nearest_landmark,
    landmark,
    affected_population,
    persons_count,
    latitude,
    longitude,
    impact_radius,
    notes,
    reported_at,
    dispatched_at,
    completed_at,
    status,
    severity,
    created_at,
    updated_at
)
SELECT
    reporter.id,
    category.id,
    barangay.id,
    severity.id,
    incident_status.id,
    'Fire',
    'Capitol Site',
    'Capitol Site area',
    'Capitol Site area',
    100,
    100,
    10.3168000,
    123.8912000,
    80,
    'TASK3 demo dispatched incident',
    datetime('now', '-25 minutes'),
    datetime('now', '-20 minutes'),
    NULL,
    'Dispatched',
    'Critical',
    datetime('now'),
    datetime('now')
FROM users AS reporter
JOIN incident_categories AS category ON category.name = 'Fire'
JOIN barangays AS barangay ON barangay.name = 'Capitol Site'
JOIN severity_levels AS severity ON severity.name = 'Critical'
JOIN incident_statuses AS incident_status ON incident_status.name = 'Dispatched'
WHERE reporter.email = 'admin@resqlink.local'
  AND NOT EXISTS (
      SELECT 1 FROM incidents WHERE notes = 'TASK3 demo dispatched incident'
  );

COMMIT;
