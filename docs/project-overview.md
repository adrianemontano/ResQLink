# ResQLink: A Volunteer-Based Emergency Alert and Dispatch Coordination Support System

## Problem Statement

Effective disaster response depends on timely reporting of incidents and rapid coordination of emergency resources.

Currently, incidents are reported through hotlines, social media, radio communications, and direct resident reports. These methods generate large volumes of reports that require verification before action can be taken.

For the Cebu City Disaster Risk Reduction and Management Office (CCDRRMO), incidents such as floods, earthquakes, landslides, and fires require immediate attention. However, public reports often vary in quality, completeness, and reliability, which can delay the Notify–Receive–Dispatch workflow.

A structured reporting and dispatch coordination system is needed to enable verified barangay volunteers to submit standardized incident reports directly to dispatch personnel, improving situational awareness and incident organization.

---

# Existing Solutions

Current emergency reporting mechanisms include:

* Radio communications
* Telephone hotlines
* Messaging platforms
* Manual incident logging systems
* Location-based incident reporting applications

Local agencies such as CCDRRMO typically rely on dispatcher assessment, radio communications, and manual record keeping during emergency operations.

## Limitation

Current approaches lack a centralized and standardized reporting platform specifically designed for verified barangay volunteers.

---

# Gaps

The following issues exist in current reporting practices:

* Public-facing reporting systems allow unrestricted submissions.
* Reports may be incomplete, inaccurate, duplicated, or low priority.
* Critical information is often missing, including:

  * Exact location
  * Barangay
  * Nearest landmark
  * Estimated affected population
  * Geographic extent of impact
* Incident information is not consistently standardized.
* Dispatchers spend additional time validating reports before initiating response activities.
* No dedicated system exists for verified barangay volunteer reporting and dispatch coordination support.

---

# Scope

The study focuses on the design and development of **ResQLink**, a Volunteer-Based Emergency Alert and Dispatch Coordination Support System that supports the Notify–Receive–Dispatch workflow.

## Volunteer Reporting Features

Verified barangay volunteers can submit structured incident reports containing:

* Incident category

  * Flood
  * Earthquake
  * Landslide
  * Fire
* Barangay
* Nearest landmark
* Estimated affected residents/persons
* Incident location through map pinning
* Impact radius
* Optional incident notes

## Dispatcher Features

* Incident map visualization
* Incident summary viewing
* Incident queue management
* Digital incident logging
* Incident status management
* Preliminary severity-based incident organization

## Administrative Features

* Volunteer account management
* Dispatcher account management
* Incident record access
* Report generation

## System Architecture Modules

1. Barangay Volunteer Mobile Module
2. Dispatcher Web Module
3. Admin / Data Management Module

---

# Delimitation

The study is limited to the following:

* Prototype implementation only
* No integration with live CCDRRMO systems
* No integration with BFP, PNP, EMS, or other external agencies
* No automated dispatching of resources
* No vehicle routing functionality
* No responder tracking functionality
* No predictive disaster analytics
* No resource allocation optimization
* Preliminary severity classification serves only as decision-support
* The system supports the Notify–Receive–Dispatch workflow only
* Data is intended for academic and research purposes

---

# Objectives

## General Objective

Develop a Volunteer-Based Emergency Alert and Dispatch Coordination Support System that improves incident reporting and supports the Notify–Receive–Dispatch workflow.

## Specific Objectives

1. Develop a mobile application for verified barangay volunteers.

2. Enable volunteers to submit structured incident reports containing:

   * Incident category
   * Barangay
   * Nearest landmark
   * Estimated affected residents
   * Incident location
   * Impact radius
   * Optional notes

3. Develop a dispatcher dashboard with map-based incident visualization.

4. Implement digital incident logging and incident status management.

5. Implement a preliminary incident severity assessment to assist dispatchers in organizing incoming incident reports.

6. Provide administrative tools for volunteer account management, dispatcher account management, and incident record management.

7. Improve situational awareness and dispatch coordination during emergencies.

---

# Proposed Solution

ResQLink enables verified barangay volunteers to submit structured emergency incident reports through a mobile application.

Submitted reports are transmitted to a centralized dispatcher dashboard where incidents can be visualized, reviewed, organized, and managed.

The system also provides administrative tools for managing volunteer accounts, dispatcher accounts, and incident records.

## User Roles

### Barangay Volunteers

* Submit verified incident reports
* Provide standardized incident information

### Dispatchers

* Review incident reports
* Monitor incidents through map visualization
* Update incident statuses
* Organize incidents according to preliminary severity classification

### Administrators / Data Management Personnel

* Manage volunteer accounts
* Manage dispatcher accounts
* Manage incident records
* Generate operational reports

## Expected Benefits

* Standardized incident reporting
* Improved situational awareness
* Faster report review and organization
* Better support for dispatch coordination
* Improved incident documentation

---

# System Modules

## 1. Barangay Volunteer Mobile Module

* Incident Alert Submission
* Incident Category Selection
* Barangay Entry
* Nearest Landmark Entry
* Affected Population Reporting
* Incident Location Pinning
* Impact Radius Definition
* Incident Notes Submission
* GPS Location Capture

---

## 2. Dispatcher Web Module

* Dispatcher Map View
* Incident Summary View
* Incident Queue Management
* Incident Status Management
* Digital Incident Logging
* Preliminary Severity-Based Incident Organization

---

## 3. Admin / Data Management Module

* Volunteer Account Management
* Dispatcher Account Management
* Incident Data Access
* Report Generation

---

# Core System Features

## Volunteer Side

* Submit incident alerts
* Select incident category
* Enter barangay
* Enter nearest landmark
* Enter estimated affected residents
* Pin incident location on an interactive map
* Define incident impact radius
* Add optional notes

---

## Dispatcher Side

* Map visualization of incidents
* Incident summary viewing
* Incident queue management
* Incident status updates:

  * Reported
  * Received
  * Dispatched
  * Completed
* Preliminary severity-based incident organization

---

## Admin Side

### Volunteer Account Management

* Create volunteer accounts
* Edit volunteer account information
* Activate or deactivate volunteer accounts
* Reset volunteer credentials
* View volunteer records

### Dispatcher Account Management

* Create dispatcher accounts
* Edit dispatcher account information
* Activate or deactivate dispatcher accounts
* Reset dispatcher credentials

### Incident Data Management

* Search incident records
* Filter incident records
* Access archived incidents
* Generate daily, weekly, and monthly reports

---

# System Logic

## Preliminary Incident Severity Assessment

The system shall generate a preliminary severity classification using information provided by the reporting barangay volunteer.

The assessment shall consider:

* Estimated affected residents/persons
* Declared incident impact radius

Severity levels:

* Low
* Moderate
* High
* Critical

The resulting classification shall be used to organize the dispatcher incident queue and assist dispatchers in reviewing incoming reports.

Final incident prioritization and dispatch decisions remain under dispatcher authority.

---