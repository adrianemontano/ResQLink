# Project Lifecycle

**Project:** ResQLink: A Volunteer-Based Emergency Alert and Dispatch Coordination Support System

Version: 1.0
Status: Source of Truth (Project Development Roadmap)

---

# Purpose

This document defines the official development lifecycle of the ResQLink project.

It serves as the implementation roadmap for the development team by breaking the project into manageable phases. Each phase contains specific objectives, deliverables, and completion criteria.

The phases should generally be completed in order, although minor revisions may occur as project requirements evolve.

---

# Development Principles

The project follows these principles:

* Requirements before implementation
* UI before backend logic
* Backend before mobile integration
* Testing after each completed phase
* Documentation throughout development
* GitHub is the single source of truth for source code

---

# Phase 1 — Project Planning

## Goal

Establish the project scope and define system requirements.

## Activities

* Finalize project title
* Define project objectives
* Define scope and delimitation
* Identify stakeholders
* Define user roles
* Gather system requirements
* Create technology stack
* Create development standards

## Deliverables

* Project Proposal
* Project Documents
* System Requirements
* Technology Stack
* Development Standards

**Status:** Complete

---

# Phase 2 — System Analysis

## Goal

Model how the system will behave before implementation.

## Activities

* Analyze current workflow
* Design proposed workflow
* Identify actors
* Identify system modules
* Identify functional requirements
* Identify data requirements

## Deliverables

* Current Workflow
* Proposed Workflow
* Functional Requirements
* System Modules
* User Stories

**Completion Criteria**

The complete behavior of the system is documented.

---

# Phase 3 — System Design

## Goal

Create the blueprint of the system.

## Activities

### UI/UX Design

* Mobile wireframes
* Web wireframes
* Dashboard layouts
* Navigation flow

### System Design

* Use Case Diagrams
* Activity Diagrams
* Sequence Diagrams
* ER Diagram
* Class Diagram
* System Architecture Diagram
* Deployment Diagram

### Database Design

* Tables
* Relationships
* Constraints

## Deliverables

* Complete UI Designs
* Complete UML Diagrams
* Database Schema
* System Architecture

**Completion Criteria**

All major system components have approved designs.

---

# Phase 4 — Environment Setup

## Goal

Prepare the development environment.

## Activities

* Create GitHub repository
* Configure branching strategy
* Install Laravel
* Configure MySQL
* Create Flutter project
* Install Bootstrap
* Install Leaflet
* Configure development environments

## Deliverables

* Working development environment
* Initial project structure
* Shared repository

**Completion Criteria**

All team members can build and run the project locally.

---

# Phase 5 — Database Development

## Goal

Implement the project's data layer.

## Activities

* Create database schema
* Create Laravel migrations
* Create Eloquent models
* Define relationships
* Seed sample data

## Deliverables

* MySQL database
* Laravel migrations
* Models
* Seeders

**Completion Criteria**

The database supports all required entities and relationships.

---

# Phase 6 — Backend Development

## Goal

Develop the server-side functionality.

## Activities

* Authentication
* Authorization
* Incident management
* Volunteer management
* Dispatcher management
* Admin management
* Report generation
* Severity assessment
* API development

## Deliverables

* Laravel backend
* REST API
* Business logic
* Validation

**Completion Criteria**

All core backend features are functional.

---

# Phase 7 — Web Dashboard Development

## Goal

Develop the dispatcher and administrator web interfaces.

## Activities

### Dispatcher Module

* Dashboard
* Incident Queue
* Incident Details
* Map View
* Status Updates

### Admin Module

* Volunteer Management
* User Management
* Incident Records
* Reports

## Deliverables

* Dispatcher Dashboard
* Admin Dashboard

**Completion Criteria**

Dispatchers and administrators can perform all required tasks.

---

# Phase 8 — Mobile Application Development

## Goal

Develop the volunteer mobile application.

## Activities

* Login
* Incident submission
* Map pinning
* GPS capture
* Incident confirmation
* API integration

## Deliverables

* Flutter mobile application

**Completion Criteria**

Volunteers can submit incident reports successfully.

---

# Phase 9 — System Integration

## Goal

Connect all system components into a working application.

## Activities

* Connect Flutter to Laravel APIs
* Connect dashboard to database
* Verify authentication
* Verify map functionality
* Verify incident flow

## Deliverables

* Fully integrated prototype

**Completion Criteria**

The Notify–Receive–Dispatch workflow operates correctly.

---

# Phase 10 — Testing

## Goal

Verify system quality and functionality.

## Activities

### Functional Testing

* Authentication
* Incident reporting
* Dashboard functions
* Administration

### Integration Testing

* API communication
* Database operations
* Map visualization

### User Acceptance Testing

* Volunteer testing
* Dispatcher testing

## Deliverables

* Test Reports
* Bug List
* Bug Fixes

**Completion Criteria**

All critical issues have been resolved.

---

# Phase 11 — Documentation

## Goal

Produce complete project documentation.

## Activities

* Update project documentation
* Finalize diagrams
* Prepare user manual
* Prepare technical documentation
* Document database
* Document APIs

## Deliverables

* Complete Capstone Documentation
* User Manual
* Technical Documentation

**Completion Criteria**

Documentation reflects the implemented system.

---

# Phase 12 — Final Presentation

## Goal

Prepare the project for defense and demonstration.

## Activities

* Final system review
* Prepare presentation slides
* Prepare live demonstration
* Prepare sample data
* Conduct mock defense

## Deliverables

* Final Prototype
* Presentation Slides
* Demonstration Script

**Completion Criteria**

The project is ready for panel evaluation.

---

# Overall Development Flow

```
Project Planning
        │
        ▼
System Analysis
        │
        ▼
System Design
        │
        ▼
Environment Setup
        │
        ▼
Database Development
        │
        ▼
Backend Development
        │
        ▼
Web Dashboard Development
        │
        ▼
Mobile Development
        │
        ▼
System Integration
        │
        ▼
Testing
        │
        ▼
Documentation
        │
        ▼
Final Presentation
```

---

# Definition of Done

A phase is considered complete when:

* All planned deliverables are finished.
* Team members have reviewed the outputs.
* Documentation has been updated.
* Source code has been committed to GitHub.
* Outstanding issues are recorded before moving to the next phase.

---

# Change Management

Project requirements may evolve based on adviser or panel feedback.

When changes are approved:

1. Update the project requirements.
2. Update affected diagrams.
3. Update the implementation plan.
4. Update documentation.
5. Update this lifecycle document if phase deliverables change.

This document is the official development roadmap for the ResQLink project.
