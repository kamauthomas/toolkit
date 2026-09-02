# Report System Enhancement and Module Expansion Proposal

## 1. Introduction

Following stakeholder consultations and review of the current reporting platform, several enhancements have been identified to improve usability, reporting efficiency, admissions monitoring, departmental oversight, and executive-level decision making.

The proposed changes aim to transform the system from a basic reporting repository into a comprehensive institutional management and monitoring platform.

---

## 2. Objectives

The proposed enhancements seek to:

* Improve report categorization and retrieval.
* Strengthen admissions monitoring and verification processes.
* Enhance management oversight through executive dashboards.
* Improve accountability through target tracking and performance monitoring.
* Introduce institutional meeting management through minutes recording.
* Implement automated notifications and reminders.
* Provide real-time visibility of departmental activities and institutional progress.

---

## 3. Proposed System Modules

## 3.1 Dashboard Enhancement

The dashboard shall be redesigned to provide role-based information visibility.

### Administrator Dashboard

The Administrator dashboard shall display:

* Total Reports Submitted
* Reports Pending Review
* Admissions Awaiting Verification
* Verified Admissions
* Monthly Intake Statistics
* Employee Performance Overview
* Recent Activities
* System Notifications

### Principal Dashboard

A dedicated executive dashboard shall be introduced for the Principal containing:

* Institution-wide performance overview
* Department performance summaries
* Admissions statistics
* Monthly and annual enrollment trends
* Marketing performance indicators
* Staff activity summaries
* Incentive tracking
* Meeting minutes overview
* Strategic performance charts and KPIs

This dashboard shall provide a complete institutional snapshot without requiring navigation into individual modules.

---

## 3.2 Reports Management Module

Reports shall be categorized into dedicated sections.

### General Reports

This section shall contain:

* Daily Reports
* Weekly Reports
* Monthly Reports
* Departmental Reports
* Administrative Reports

Features:

* Filtering by employee
* Filtering by department
* Date range search
* Export to PDF and Excel
* Approval workflow

### Marketing Reports

A dedicated marketing reporting section shall include:

* Campaign Reports
* Social Media Reports
* Outreach Activities
* Lead Generation Reports
* Conversion Tracking

Features:

* Monthly marketing performance analysis
* Lead tracking
* Marketing KPI monitoring
* Campaign effectiveness measurement

---

## 3.3 Admissions Management Module

A comprehensive Admissions Module shall be introduced.

### Admissions Records

Capture and manage:

* Student Information
* Program Applied For
* Enrollment Status
* Admission Date
* Assigned Officer
* Verification Status

The first working slice is now available in the reporting platform: authorised
staff can capture an admission record, see records within their reporting scope,
and open its detail/history page.

### Admission Verification

Administrators shall be able to:

* Review admissions records
* Verify admissions
* Reject invalid admissions
* Track verification history

The implemented workflow requires a decision note when a record needs more
information or is rejected, records the reviewer and time, and notifies the
record owner when another authorised reviewer changes the status.

Fee status is tracked separately and requires an approved payment reference;
only verified records marked fee-paid contribute to monthly intake actuals.

Status indicators:

* Pending Verification
* Verified
* Rejected

---

## 3.4 Monthly Intake Monitoring Module

A new intake performance tracking module shall be developed.

### Monthly Intake Dashboard

The module shall monitor:

* Enrollment targets
* Actual admissions achieved
* Percentage completion
* Monthly trends
* Department performance

### Employee Performance Tracking

For each admissions officer, the following shall be displayed:

* Employee Name
* Monthly Target
* Admissions Achieved
* Remaining Target
* Performance Percentage
* Last Activity Date

### Performance Indicators

Visual indicators shall include:

* Progress bars
* Achievement percentages
* Monthly comparison charts
* Target attainment rankings

The implemented monthly view lets authorised managers set or revise an
officer's target and compares it only with verified admissions attributed to
that officer in the selected month. Employees can see their own figures while
executive roles retain institution-wide visibility.

---

## 3.5 Incentives Management Module

An incentives tracking module shall be introduced.

Features:

* Employee incentive allocation
* Performance-based rewards
* Admissions incentive calculations
* Approval workflow
* Incentive payment records

Reports:

* Monthly incentives report
* Employee incentive history
* Department incentive summary

The working slice calculates each proposal server-side, prevents the creator
from self-approving, requires an independent approval before payment, and keeps
proposal/approval/payment events in an audit history.

---

## 3.6 Minutes Management Module

A centralized meeting minutes repository shall be implemented.

### Features

* Create meeting records
* Upload minutes
* Assign action items
* Set deadlines
* Track completion status

The implemented repository records the meeting, attendees, summary and
department, then assigns actions to authorised users with due dates. Owners or
authorised managers can move an action through open, in-progress, blocked and
completed states; blocked actions require an explanation and completed actions
retain their completion time.

### Minutes Information

Each record shall include:

* Meeting Date
* Meeting Title
* Department
* Attendees
* Minutes Summary
* Action Items
* Responsible Officers
* Due Dates

---

## 3.7 Notifications and Reminder System

An automated notification system shall be implemented.

### Email Notifications

The system shall automatically send reminders for:

* Pending reports
* Unverified admissions
* Outstanding action items
* Monthly target reviews
* Meeting follow-ups

### Reminder Scheduling

Administrators shall be able to configure:

* Reminder frequency
* Reminder recipients
* Escalation timelines

Configurable in-app rules now cover pending admissions, due meeting actions,
reports awaiting review and intake follow-up. Each run is idempotent and keeps
a delivery log; email remains a separate provider-gated follow-up.

## 3.8 Objectives and Key Results

The operational OKR module provides:

* institution-wide or department-scoped objectives;
* assigned owners, periods and lifecycle states;
* numeric baselines, targets, current values, units and due dates;
* server-calculated progress, including decreasing targets;
* append-only progress notes and evidence references;
* employee access only to departmental or directly assigned work.

The finalized 26 May 2026–July 2027 Toolkit portfolio is the planning source.
Management must approve its owners and targets before those records are treated
as institutional commitments. Poster and campaign outputs can be cited in the
same evidence trail as software, meetings and reports.

An executive-only idempotent loader now creates all eight objectives and thirty
key results as assignment-gated records. It includes the brand/poster objective and can
be run repeatedly without duplicating records.

## 3.9 Wingu Dispatch Queue

The local workflow now queues only approved daily reports. It records whether
attendance came from manual entry or an approved Excel reference, prevents a
second queue row for the same report, deliberately leaves the project blank
until read from Wingu, and records reconciliation events.

External browser submission remains disabled until an authenticated Wingu field
review and approved attendance spreadsheet format are provided. No credentials,
cookies or assumed Wingu project codes are stored.

The spreadsheet side is operational locally: the system generates the canonical
five-column workbook, previews and validates each row without mutation, and can
queue valid rows only when they match exactly one approved report in the user's
authorised scope.

---

## 4. Reporting and Analytics

The system shall include advanced reporting capabilities.

### Available Analytics

* Admissions trends
* Enrollment performance
* Employee productivity
* Marketing effectiveness
* Department performance
* Incentive summaries

### Export Options

* PDF
* Excel
* CSV

---

## 5. Security and Access Control

Role-based permissions shall be implemented.

### Roles

### Principal

* Full system visibility
* Executive reporting access
* Strategic dashboards

### Administrator

* System management
* Admission verification
* User management

### Department Heads

* Department-specific reporting
* Staff monitoring

### Employees

* Report submission
* Activity tracking
* Personal performance monitoring

---

## 6. Expected Benefits

The proposed enhancements will provide:

* Improved operational transparency
* Better admissions monitoring
* Increased accountability
* Faster management decision-making
* Enhanced performance tracking
* Improved communication and follow-up
* Centralized institutional records management

---

## 7. Conclusion

The proposed enhancements will transform the current reporting platform into a comprehensive institutional management system capable of supporting operational, administrative, admissions, marketing, and executive decision-making processes while providing improved visibility, accountability, and performance monitoring across the institution.
