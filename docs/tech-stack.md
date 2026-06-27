# ResQLink Technology Stack
**Project:** ResQLink: A Volunteer-Based Emergency Alert and Dispatch Coordination Support System

Version: 1.0  
Status: Source of Truth (Technology Stack)

---

# Purpose

This document defines the official technology stack used throughout the development of the ResQLink system.

All developers, designers, and documentation writers should refer to this document whenever discussing implementation technologies.

If future technology changes are approved by the project team, this document must be updated accordingly.

---

# Technology Stack Overview

| Layer | Technology |
|---------|------------|
| Backend | PHP Laravel |
| Web UI | Laravel Blade |
| CSS Framework | Bootstrap 5 |
| Database | MySQL |
| Mobile Application | Flutter |
| Interactive Maps | Leaflet.js |
| Version Control | Git |
| Repository Hosting | GitHub |

---

# Architecture Overview

```
                    +----------------------+
                    |   Flutter Mobile App |
                    | Barangay Volunteers  |
                    +----------+-----------+
                               |
                               | HTTP/HTTPS
                               |
                +--------------v--------------+
                |     Laravel Backend API     |
                | Business Logic & Validation |
                +--------------+--------------+
                               |
                +--------------v--------------+
                |          MySQL Database     |
                +--------------+--------------+
                               |
         +---------------------+---------------------+
         |                                           |
+--------v---------+                     +-----------v----------+
| Dispatcher Portal|                     |   Admin Dashboard    |
| Blade + Bootstrap|                     | Blade + Bootstrap    |
+--------+---------+                     +-----------+----------+
         |                                           |
         +-------------------+-----------------------+
                             |
                       Leaflet Map
                  (Incident Visualization)
```

---

# Backend

## Technology

- PHP 8.x
- Laravel Framework

## Purpose

Laravel serves as the primary backend framework responsible for:

- Business logic
- Authentication
- Authorization
- RESTful APIs
- Database communication
- Validation
- Report generation

## Responsibilities

The backend manages:

- Volunteer accounts
- Dispatcher accounts
- Administrator accounts
- Incident reports
- Severity classification
- Incident status updates
- Report generation

---

# Web Frontend

## Technology

- Laravel Blade
- Bootstrap 5

## Purpose

The web application is intended for:

- Dispatchers
- Administrators

Blade provides server-side rendered pages while Bootstrap provides responsive and consistent user interface components.

## Responsibilities

Dispatcher Module

- Dashboard
- Incident queue
- Incident details
- Incident status management
- Interactive incident map

Admin Module

- Volunteer management
- Dispatcher management
- Incident records
- Reports
- User management

---

# Mobile Application

## Technology

- Flutter

## Purpose

Flutter is used to develop the Barangay Volunteer mobile application.

The mobile application enables verified volunteers to:

- Login
- Submit incident reports
- Select incident category
- Pin incident location
- View submission confirmation

---

# Database

## Technology

- MySQL

## Purpose

MySQL serves as the centralized relational database.

## Stores

- Users
- Volunteers
- Dispatchers
- Administrators
- Incident reports
- Severity levels
- Incident statuses
- Generated reports
- Audit records

---

# Mapping and Location Services

## Technology

- Leaflet.js

## Purpose

Leaflet provides interactive map visualization for both the mobile application and the dispatcher dashboard.

## Features

- Map display
- Incident pinning
- GPS coordinates
- Incident markers
- Impact radius visualization
- Multiple incident visualization

---

# Version Control

## Technology

- Git

## Purpose

Git manages source code history.

It allows developers to:

- Track changes
- Create branches
- Merge features
- Restore previous versions
- Collaborate safely

---

# Repository Hosting

## Technology

- GitHub

## Purpose

GitHub serves as the project's central repository.

The repository is used for:

- Source code hosting
- Team collaboration
- Pull requests
- Code reviews
- Issue tracking
- Project documentation

---

# Development Workflow

```
Developer
     │
     ▼
Create Feature Branch
     │
     ▼
Develop Feature
     │
     ▼
Commit Changes
     │
     ▼
Push to GitHub
     │
     ▼
Pull Request
     │
     ▼
Code Review
     │
     ▼
Merge into Main Branch
```

---

# Technology Responsibilities by Module

| Module | Technology |
|---------|------------|
| Volunteer Mobile App | Flutter |
| Dispatcher Dashboard | Blade + Bootstrap |
| Admin Dashboard | Blade + Bootstrap |
| Backend API | Laravel |
| Authentication | Laravel |
| Business Logic | Laravel |
| Database | MySQL |
| Incident Mapping | Leaflet |
| Team Collaboration | GitHub |
| Version Control | Git |

---

# Reasons for Technology Selection

## Laravel

Chosen because:

- Mature PHP framework
- MVC architecture
- Built-in authentication
- Excellent database integration
- Secure
- Suitable for academic capstone projects
- Large community support

---

## Blade

Chosen because:

- Native Laravel templating engine
- Fast rendering
- Easy integration with Laravel
- Minimal configuration

---

## Bootstrap

Chosen because:

- Responsive design
- Ready-made UI components
- Faster interface development
- Consistent styling

---

## Flutter

Chosen because:

- Single codebase
- Android-ready
- Modern UI
- Good performance
- Easy integration with REST APIs

---

## MySQL

Chosen because:

- Reliable relational database
- Widely used
- Easy Laravel integration
- Suitable for structured incident records

---

## Leaflet

Chosen because:

- Open-source
- Lightweight
- Easy integration
- Excellent support for GPS visualization
- Radius drawing support
- Marker clustering support

---

## Git

Chosen because:

- Industry-standard version control
- Enables collaborative development
- Maintains project history
- Supports branching strategies

---

## GitHub

Chosen because:

- Cloud-based Git repository hosting
- Supports pull requests
- Issue tracking
- Project management
- Team collaboration
- Documentation hosting

---

# Compatibility Matrix

| Component | Technology |
|------------|------------|
| Backend | Laravel |
| Web Frontend | Blade + Bootstrap |
| Mobile | Flutter |
| Database | MySQL |
| Maps | Leaflet |
| Version Control | Git |
| Repository | GitHub |

---

# Future Technology Changes

Any changes to the technology stack must be approved by the project team and reflected in this document before implementation.

This document serves as the official technology reference for the ResQLink project.
