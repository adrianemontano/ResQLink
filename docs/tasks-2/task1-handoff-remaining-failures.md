# Note from Jassy — Remaining Test Failures for Tasks 2 & 3

Hey guys! 👋

Human na ang **Task 1 nako** (login, roles, user management, volunteer documents) —
all tests passing na, na-push na nako sa `jassy` branch.

Naa pa koy **3 failing tests** sa whole suite nga **dili nako** — para ninyo ni.
Gi-list nako para dali ninyo mahibal-an. Here's the simple breakdown:

---

## 🔧 Para nimo, Adriane (Task 2 — Incident Reporting)

**File:** `backend/tests/Feature/VolunteerIncidentSubmissionTest.php`
**Test:** `test_verified_volunteer_sees_confirmation_after_submitting_incident`

**Simple explanation:**
After ma-submit ang volunteer ug incident, dapat mo-appear sa confirmation page ang
incident number parehas ani: **`INC-0001`**.

Karon wari, wpa na mo-show. Dapat i-render sa volunteer confirmation page
(`volunteer/incidents/create.blade.php`) ang `INC-####` number sa bag-o nga incident.

**Tip:** Naa na ni nga format sa dispatcher map —
`'INC-'.str_pad((string) $incident->id, 4, '0', STR_PAD_LEFT)`. Pwede nimo gamiton
pareho para consistent.

---

## 🔧 Para nimo, Angela (Task 3 — Dispatcher Coordination)

**File:** `backend/tests/Feature/DispatcherIncidentCoordinationTest.php`

### 1. `test_dispatcher_can_view_and_filter_the_incident_queue`
**Simple explanation:**
Pag-filter sa dispatcher queue (by category o barangay), dapat **matching ra** nga
incidents ang mo-appear. Check lang nga pag filter by `category=Fire`, ang ubang
incidents (like ang Flood) dapat wga mo-show sa list — apil naa sa summary/sidebar.

### 2. `test_dispatcher_map_feed_returns_database_incidents_as_geojson`
**Simple explanation:**
Pariho ra sila ug numero pero lain type. Ang test mo-expect og **`300.0`** (decimal),
pero ang mo-return kay **`300`** (whole number). Pili lang nato:
- himoon ang test mo-expect og `300`, **o**
- siguruha nga mo-return gyud og decimal (`300.0`) sa map feed.

Any of the two, basta **consistent ra** — then mag-agree na mo sila. 😄

---

## 📌 Note for both

Before mo-run sa tests, **i-build muna ang frontend** kay naa'y stale build:
```bash
cd backend
npm run build
```

Then i-run ang tests:
```bash
DB_CONNECTION=mysql DB_DATABASE=resqlink_test php artisan test
```

Kung naa moy naay pangutana or naa koy naka-miss, chat lang ninyo ko. Good luck! 🙏

— Jassy

