# Client Architecture Decision

## Overall Architecture

ResQLink will follow a **multi-client architecture** where different user roles interact with the system through different clients while sharing a common Laravel backend and REST API.

```
                    +----------------------+
                    |   Laravel Backend    |
                    |----------------------|
                    | Authentication       |
                    | Business Logic       |
                    | REST API             |
                    | Database             |
                    +----------+-----------+
                               |
               +---------------+---------------+
               |                               |
       Web Application                  Mobile Application
               |                               |
      Admin & Dispatcher               Volunteers
```

---

# Web Application

The web application is intended exclusively for **administrative and operational personnel**.

## Supported Roles

* Administrator
* Dispatcher

Access to modules and functionality will be controlled using **Role-Based Access Control (RBAC)**.

### Administrator Responsibilities

* User Management
* Dispatcher Management
* Volunteer Management
* System Configuration
* Reports and Analytics
* AI Configuration
* Incident Monitoring
* Audit Logs

### Dispatcher Responsibilities

* Incident Monitoring
* SOS Management
* Incident Verification
* Incident Prioritization
* Volunteer Coordination
* Group Management
* Notification Management
* Response Tracking

---

# Mobile Application

The mobile application is intended exclusively for **Volunteers**.

The mobile client will consume the backend REST APIs and provide field operations functionality.

## Volunteer Capabilities

* Secure Login
* Profile Management
* Receive Incident Assignments
* Receive SOS Alerts
* View Incident Details
* Navigation to Incident Location
* Accept or Decline Assignments
* Update Response Status
* Submit Incident Evidence
* Receive Notifications

The mobile application will **not** contain administrative or dispatcher functionality.

---

# API Organization

The backend REST API will be organized by client responsibility.

```
/api
│
├── auth/
│
├── admin/
│   ├── users
│   ├── dispatchers
│   ├── volunteers
│   ├── reports
│   ├── settings
│   └── dashboard
│
├── dispatcher/
│   ├── incidents
│   ├── sos
│   ├── groups
│   ├── volunteers
│   ├── notifications
│   └── dashboard
│
├── volunteer/
│   ├── profile
│   ├── incidents
│   ├── assignments
│   ├── status
│   ├── location
│   └── notifications
│
└── shared/
    ├── authentication
    ├── uploads
    └── common resources
```

Each API group will be protected by authentication and authorization middleware appropriate to the authenticated user's role.

---

# Development Order

The project will be developed in the following order:

## Phase 1

Laravel Backend

* Database
* Authentication
* Authorization
* Business Logic
* REST APIs
* AI Integration
* Notifications

---

## Phase 2

Web Application

* Administrator Interface
* Dispatcher Interface
* Dashboard
* Incident Management
* Reporting
* System Administration

---

## Phase 3

Mobile Application

* Volunteer Authentication
* Assignment Management
* Incident Response
* Navigation
* Notifications
* Field Reporting

The mobile application will only begin development after the backend APIs and web interfaces have reached feature completeness.