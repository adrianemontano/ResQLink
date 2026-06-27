# System Requirements: ResQLink

---

## System Actors

- Barangay Volunteer
- Dispatcher
- Admin / Data Management Personnel

---

## Platform

- Mobile Application → For Barangay Volunteers
- Web-Based Dashboard → For Dispatcher and Admin / Data Management Personnel

---

# Barangay Volunteer Features

## 1. Incident Alert Summary

Volunteers can submit a verified emergency incident report.

The system shall allow volunteers to:

- Select incident category:
  - Flood
  - Earthquake
  - Landslide
  - Fire

- Enter barangay

- Enter nearest landmark

- Enter estimated affected residents/persons

- Pin incident location on an interactive map

- Define affected area through an adjustable radius

- Add optional incident notes

- Submit incident alert

---

## 2. GPS Location Capture

The system shall:

- Capture incident coordinates from the selected map location
- Store latitude and longitude
- Store report timestamp
- Associate report with the authenticated volunteer account

---

# Dispatcher Features

## 3. Dispatcher Map View

The system shall:

- Display reported incidents on a map
- Display incident location and affected area radius
- Allow selection of incidents for detailed viewing
- Display active and historical incidents

---

## 4. Incident Summary View

The system shall display:

- Incident category
- Reporting volunteer
- Date and time reported
- Estimated affected residents
- Incident location
- Barangay
- Nearest Landmark
- Impact radius
- Incident notes
- Current incident status
- Preliminary severity classification

---

## 5. Digital Incident Logging and Status Management

Dispatchers shall be able to update incident status:

- Reported
- Received
- Dispatched
- Completed

The system shall maintain a complete incident history for reference and reporting.

---

## 6. Incident Queue Management

The system shall:

- Display incoming incident reports
- Organize incidents according to severity classification
- Allow dispatchers to review and manage active incidents

---

# Admin / Data Management Features

## 7. Volunteer Account Management

The system shall allow administrators to:

- Create volunteer accounts
- Edit volunteer accounts
- Activate or deactivate volunteer accounts

- Store volunteer identification documents:
  - Endorsement Letter from Barangay Captain
  - Barangay Clearance
  - Certificate of Residency

---

## 8. Dispatcher and Administrative User Management

The system shall allow administrators to:

- Create dispatcher accounts
- Create administrator accounts
- Modify user information

---

## 9. Incident Data Access

The system shall allow administrators to:

- View incident records
- Search incident records

- Filter incident records by:
  - Category
  - Status

- Access archived incidents

---

## 10. Report Generation

The system shall generate:

- Daily incident reports
- Weekly incident reports
- Monthly incident reports

Reports shall include:

- Incident distribution by category
- Incident distribution by barangay
- Incident status summaries
- Incident frequency statistics

---

# Core System Logic

## 11. Preliminary Incident Severity Assessment

The system shall generate a preliminary severity classification based on:

- Estimated affected residents/persons
- Declared incident impact radius

Severity levels:

- Low
- Moderate
- High
- Critical

The preliminary severity classification is intended to assist dispatchers in organizing and reviewing incoming incident reports. It serves only as a decision-support mechanism and does not replace dispatcher judgment or official emergency assessment procedures.


---

# Nice To Have Features
