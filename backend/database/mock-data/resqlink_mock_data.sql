-- ResQLink TASK3 mock/demo data (MySQL 8.0+)
-- Academic demonstration data only. Run after migrations and the normal seeders.
-- This script never drops, truncates, updates, or deletes existing application data.
-- In MySQL Workbench, set the development schema as the default before running.
-- CLI alternative: mysql -u USER -p DATABASE < resqlink_mock_data.sql

START TRANSACTION;

-- 1. Existing account and role verification.
SELECT id, email, username, role_id, is_active
FROM users
WHERE email IN ('admin@resqlink.local', 'a@gmail.com', 'volunteer@resqlink.local')
   OR username IN ('admin', 'Angela', 'volunteer');

-- The normal ReferenceDataSeeder creates these rows. INSERT IGNORE only fills in
-- missing barangays needed by this demo and does not alter existing rows.
INSERT IGNORE INTO barangays (name, created_at, updated_at) VALUES
    ('Apas', NOW(), NOW()), ('Banilad', NOW(), NOW()), ('Basak San Nicolas', NOW(), NOW()),
    ('Carreta', NOW(), NOW()), ('Guadalupe', NOW(), NOW()), ('Hipodromo', NOW(), NOW()),
    ('Inayawan', NOW(), NOW()), ('Lahug', NOW(), NOW()), ('Mabolo', NOW(), NOW()),
    ('Pasil', NOW(), NOW()), ('Sambag I', NOW(), NOW()), ('Talamban', NOW(), NOW()),
    ('T. Padilla', NOW(), NOW()), ('Zapatera', NOW(), NOW());

-- 2. Mock volunteer records, if required.
-- Existing volunteer accounts are intentionally reused. The first account is the
-- seeded verified volunteer; additional accounts are created only when absent.
INSERT INTO users (name, username, email, password, role_id, is_active, created_at, updated_at)
SELECT v.name, v.username, v.email,
       '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCkL7sF9lJ5yQm7y8y2',
       r.id, 1, NOW(), NOW()
FROM (
    SELECT 'Demo Volunteer North' AS name, 'demo_volunteer_north' AS username, 'demo.volunteer.north@resqlink.local' AS email
    UNION ALL SELECT 'Demo Volunteer South', 'demo_volunteer_south', 'demo.volunteer.south@resqlink.local'
    UNION ALL SELECT 'Demo Volunteer Central', 'demo_volunteer_central', 'demo.volunteer.central@resqlink.local'
) v
JOIN roles r ON r.name = 'volunteer'
WHERE NOT EXISTS (SELECT 1 FROM users u WHERE u.email = v.email);

INSERT INTO volunteer_profiles (user_id, barangay, verification_status, verified_at, created_at, updated_at)
SELECT u.id, CASE u.username
    WHEN 'demo_volunteer_north' THEN 'Banilad'
    WHEN 'demo_volunteer_south' THEN 'Inayawan'
    ELSE 'Lahug' END, 'verified', NOW(), NOW(), NOW()
FROM users u
WHERE u.username IN ('demo_volunteer_north', 'demo_volunteer_south', 'demo_volunteer_central')
  AND NOT EXISTS (SELECT 1 FROM volunteer_profiles p WHERE p.user_id = u.id);

DROP TEMPORARY TABLE IF EXISTS mock_incident_seed;
CREATE TEMPORARY TABLE mock_incident_seed (
    mock_key VARCHAR(40) PRIMARY KEY, category_name VARCHAR(20), barangay_name VARCHAR(100),
    landmark VARCHAR(255), persons_count INT UNSIGNED, latitude DECIMAL(10,7),
    longitude DECIMAL(10,7), impact_radius INT UNSIGNED, severity_name VARCHAR(20),
    status_name VARCHAR(20), minutes_ago INT UNSIGNED, notes TEXT
);

-- 3. Incident records: 32 records, eight in each workflow status and category.
-- Severity is paired with population/radius to provide plausible decision support.
INSERT INTO mock_incident_seed VALUES
('TASK3-M01','Flood','Lahug','Near JY Square Mall',180,10.3330000,123.8930000,250,'Critical','Reported',18,'TASK3 mock/demo: heavy rain flooding residential streets near JY Square Mall.'),
('TASK3-M02','Earthquake','Apas','Near Cebu IT Park entrance',35,10.3308000,123.9065000,80,'Moderate','Reported',32,'TASK3 mock/demo: cracked walls reported after simulated ground shaking.'),
('TASK3-M03','Landslide','Guadalupe','Near Guadalupe Church access road',95,10.3035000,123.8855000,180,'High','Reported',46,'TASK3 mock/demo: soil and rocks blocking a barangay access road.'),
('TASK3-M04','Fire','Mabolo','Near Mabolo Sports Complex',14,10.3263000,123.9089000,45,'Low','Reported',61,'TASK3 mock/demo: small residential fire with nearby houses notified.'),
('TASK3-M05','Flood','Banilad','Near Gaisano Country Mall',120,10.3548000,123.9123000,220,'High','Reported',75,'TASK3 mock/demo: floodwater entering homes after sustained rainfall.'),
('TASK3-M06','Earthquake','Talamban','Near University of San Carlos Talamban',240,10.3790000,123.9147000,300,'Critical','Reported',88,'TASK3 mock/demo: simulated earthquake damage around a campus perimeter.'),
('TASK3-M07','Landslide','Inayawan','Near South Road Properties access',22,10.2701000,123.8577000,60,'Moderate','Reported',101,'TASK3 mock/demo: minor slope failure beside an access lane.'),
('TASK3-M08','Fire','Carreta','Near Carreta Cemetery road',70,10.3187000,123.9021000,130,'High','Reported',116,'TASK3 mock/demo: fire affecting several closely spaced homes.'),
('TASK3-M09','Flood','Pasil','Near Pasil Fish Market',65,10.2929000,123.8774000,140,'High','Received',132,'TASK3 mock/demo: acknowledged flooding around market-side residences.'),
('TASK3-M10','Earthquake','Zapatera','Near Zapatera Elementary School',12,10.3003000,123.8885000,35,'Low','Received',148,'TASK3 mock/demo: inspection requested for light classroom damage.'),
('TASK3-M11','Landslide','T. Padilla','Near T. Padilla barangay hall',160,10.3216000,123.8954000,240,'Critical','Received',165,'TASK3 mock/demo: large debris field reported near the barangay hall.'),
('TASK3-M12','Fire','Basak San Nicolas','Near Basak San Nicolas public market',42,10.2921000,123.8668000,95,'Moderate','Received',179,'TASK3 mock/demo: shop fire contained while neighboring stalls are checked.'),
('TASK3-M13','Flood','Sambag I','Near USC Main Campus',28,10.3093000,123.8919000,75,'Moderate','Received',196,'TASK3 mock/demo: drainage overflow affecting a campus-side street.'),
('TASK3-M14','Earthquake','Hipodromo','Near Cebu Business Park perimeter',85,10.3206000,123.9057000,160,'High','Received',214,'TASK3 mock/demo: structural assessment requested for several buildings.'),
('TASK3-M15','Landslide','Apas','Near Lahug-Talamban road slope',6,10.3438000,123.8996000,30,'Low','Received',229,'TASK3 mock/demo: small rockfall cleared from one lane.'),
('TASK3-M16','Fire','Banilad','Near Banilad Town Center',210,10.3487000,123.9098000,280,'Critical','Received',244,'TASK3 mock/demo: warehouse fire with an estimated wide smoke impact.'),
('TASK3-M17','Flood','Mabolo','Near Mabolo Church',145,10.3268000,123.8993000,210,'High','Dispatched',261,'TASK3 mock/demo: response team dispatched to flooded residential blocks.'),
('TASK3-M18','Earthquake','Guadalupe','Near Guadalupe Elementary School',190,10.3039000,123.8879000,260,'Critical','Dispatched',278,'TASK3 mock/demo: response team dispatched for simulated structural damage.'),
('TASK3-M19','Landslide','Talamban','Near Talamban barangay hall',48,10.3751000,123.9161000,110,'Moderate','Dispatched',294,'TASK3 mock/demo: debris removal crew dispatched to an access road.'),
('TASK3-M20','Fire','Lahug','Near Lahug public market',9,10.3312000,123.8957000,40,'Low','Dispatched',311,'TASK3 mock/demo: fire response dispatched to a single residence.'),
('TASK3-M21','Flood','Inayawan','Near Inayawan bridge',260,10.2781000,123.8618000,320,'Critical','Dispatched',327,'TASK3 mock/demo: high-water response dispatched near a bridge crossing.'),
('TASK3-M22','Earthquake','Carreta','Near Carreta barangay hall',58,10.3179000,123.8998000,125,'High','Dispatched',344,'TASK3 mock/demo: search-and-assessment team dispatched.'),
('TASK3-M23','Landslide','Pasil','Near Pasil hillside homes',18,10.2944000,123.8751000,55,'Low','Dispatched',361,'TASK3 mock/demo: safety team dispatched for a contained slope failure.'),
('TASK3-M24','Fire','Zapatera','Near Zapatera covered court',105,10.2991000,123.8869000,190,'High','Dispatched',379,'TASK3 mock/demo: fire response dispatched near dense housing.'),
('TASK3-M25','Flood','Basak San Nicolas','Near Basak San Nicolas bridge',75,10.2878000,123.8690000,150,'Moderate','Completed',396,'TASK3 mock/demo: completed flood response after water receded.'),
('TASK3-M26','Earthquake','Sambag I','Near Sambag I barangay hall',4,10.3074000,123.8901000,25,'Low','Completed',412,'TASK3 mock/demo: completed inspection with no further assistance requested.'),
('TASK3-M27','Landslide','T. Padilla','Near T. Padilla access road',130,10.3248000,123.8971000,230,'High','Completed',429,'TASK3 mock/demo: completed debris-clearing operation.'),
('TASK3-M28','Fire','Hipodromo','Near Hipodromo residential lane',230,10.3237000,123.9039000,300,'Critical','Completed',447,'TASK3 mock/demo: completed fire response and resident relocation exercise.'),
('TASK3-M29','Flood','Apas','Near Cebu IT Park terminal',32,10.3321000,123.9027000,70,'Moderate','Completed',465,'TASK3 mock/demo: completed drainage response around a transport stop.'),
('TASK3-M30','Earthquake','Banilad','Near Banilad elementary school',68,10.3516000,123.9149000,145,'High','Completed',482,'TASK3 mock/demo: completed building inspection after simulated shaking.'),
('TASK3-M31','Landslide','Guadalupe','Near Guadalupe hillside footpath',11,10.2996000,123.8829000,45,'Low','Completed',499,'TASK3 mock/demo: completed removal of a small footpath obstruction.'),
('TASK3-M32','Fire','Inayawan','Near Inayawan public market',155,10.2764000,123.8642000,215,'Critical','Completed',516,'TASK3 mock/demo: completed market-area fire response exercise.');

-- Resolve all reference IDs and insert only notes not already present.
INSERT INTO incidents (
    reported_by, volunteer_id, category_id, barangay_id, severity_id, status_id,
    category, barangay, nearest_landmark, landmark, affected_population, persons_count,
    latitude, longitude, impact_radius, notes, reported_at, dispatched_at, completed_at,
    status, severity, created_at, updated_at
)
SELECT reporter.id, volunteer.id, category.id, barangay.id, severity.id, incident_status.id,
       seed.category_name, seed.barangay_name, seed.landmark, seed.landmark,
       seed.persons_count, seed.persons_count, seed.latitude, seed.longitude,
       seed.impact_radius, seed.notes,
       NOW() - INTERVAL seed.minutes_ago MINUTE,
       CASE WHEN status_order.sort_order >= 3 THEN NOW() - INTERVAL (seed.minutes_ago - 12) MINUTE END,
       CASE WHEN status_order.sort_order = 4 THEN NOW() - INTERVAL (seed.minutes_ago - 24) MINUTE END,
       seed.status_name, seed.severity_name, NOW(), NOW()
FROM mock_incident_seed seed
JOIN users reporter ON reporter.id = (
    SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id
    WHERE r.name = 'volunteer' AND u.is_active = 1 ORDER BY u.id LIMIT 1
)
JOIN users volunteer ON volunteer.id = reporter.id
JOIN incident_categories category ON category.name = seed.category_name
JOIN barangays barangay ON barangay.name = seed.barangay_name
JOIN severity_levels severity ON severity.name = seed.severity_name
JOIN incident_statuses incident_status ON incident_status.name = seed.status_name
JOIN incident_statuses status_order ON status_order.id = incident_status.id
WHERE NOT EXISTS (SELECT 1 FROM incidents i WHERE i.notes = seed.notes);

-- 4. Incident history records. Each incident receives every transition up to its
-- current status, with a dispatcher actor resolved from the existing accounts.
INSERT INTO incident_histories (incident_id, status_id, changed_by, notes, changed_at, created_at, updated_at)
SELECT i.id, s.id, dispatcher.id,
       CONCAT('TASK3 mock/demo history: ', s.name, ' transition.'),
       CASE s.sort_order
           WHEN 1 THEN i.reported_at
           WHEN 2 THEN i.reported_at + INTERVAL 6 MINUTE
           WHEN 3 THEN i.reported_at + INTERVAL 12 MINUTE
           WHEN 4 THEN i.reported_at + INTERVAL 24 MINUTE END,
       NOW(), NOW()
FROM incidents i
JOIN incident_statuses current_status ON current_status.name = i.status
JOIN incident_statuses s ON s.sort_order <= current_status.sort_order
JOIN users dispatcher ON dispatcher.id = (
    SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id
    WHERE r.name = 'dispatcher' AND u.is_active = 1 ORDER BY u.id LIMIT 1
)
WHERE i.notes LIKE 'TASK3 mock/demo:%'
  AND NOT EXISTS (
      SELECT 1 FROM incident_histories h
      WHERE h.incident_id = i.id AND h.status_id = s.id
  );

COMMIT;

-- 5. Verification queries.
SELECT COUNT(*) AS mock_incident_total
FROM incidents WHERE notes LIKE 'TASK3 mock/demo:%';

SELECT status, COUNT(*) AS incident_count
FROM incidents WHERE notes LIKE 'TASK3 mock/demo:%' GROUP BY status ORDER BY status;

SELECT category, COUNT(*) AS incident_count
FROM incidents WHERE notes LIKE 'TASK3 mock/demo:%' GROUP BY category ORDER BY category;

SELECT severity, COUNT(*) AS incident_count
FROM incidents WHERE notes LIKE 'TASK3 mock/demo:%' GROUP BY severity ORDER BY severity;

SELECT barangay, COUNT(*) AS incident_count
FROM incidents WHERE notes LIKE 'TASK3 mock/demo:%' GROUP BY barangay ORDER BY barangay;

SELECT i.id, i.notes, s.name AS history_status, h.changed_at, u.email AS changed_by
FROM incident_histories h
JOIN incidents i ON i.id = h.incident_id
JOIN incident_statuses s ON s.id = h.status_id
JOIN users u ON u.id = h.changed_by
WHERE i.notes LIKE 'TASK3 mock/demo:%'
ORDER BY i.id, h.changed_at;

SELECT id, notes, latitude, longitude
FROM incidents
WHERE notes LIKE 'TASK3 mock/demo:%'
  AND (latitude IS NULL OR longitude IS NULL
       OR latitude NOT BETWEEN 10.20 AND 10.45
       OR longitude NOT BETWEEN 123.80 AND 124.00);

DROP TEMPORARY TABLE IF EXISTS mock_incident_seed;
