# Enfrute Inscricao — Case Study

> **Custom WordPress Registration Theme** · Event & Conference Sign-Up Platform · PHP · ACF-Managed Content

![WordPress](https://img.shields.io/badge/WordPress-6.0+-21759B?logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)
![ACF Pro](https://img.shields.io/badge/ACF-Pro-00A0D2)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)

<!-- TODO: Add screenshot of the Enfrute registration form here -->

---

## 1. Project Overview

Enfrute Inscricao is a purpose-built WordPress theme powering the online registration platform for the Enfrute scientific and academic event series. The theme provides a structured, guided registration flow for event attendees, presenters, and reviewers — capturing all necessary participant data in a format directly compatible with the event's backend management systems, including the SciFlow WP peer review plugin.

<!-- TODO: Add screenshot of the registration confirmation page here -->

---

## 2. The Problem

Academic events like Enfrute require collecting highly structured registration data from multiple distinct participant profiles — general attendees, paper authors submitting work for review, and reviewers accepting assignment. Managing these flows through a generic contact form plugin or spreadsheet created data inconsistency, required manual processing, and offered no mechanism for participants to track their registration status after submission.

---

## 3. The Solution & Architecture

The theme implements a multi-profile registration system as a WordPress theme with Custom Post Types for each participant category, ACF Pro field groups defining the data schema for each profile type, and PHP template logic routing participants through the correct form flow based on their selected role.

### Registration Flow

```
Participant lands on registration page
    │
    ├── Selects role: Attendee / Author / Reviewer
    │
    ▼
Role-specific registration form rendered (ACF-driven field sets)
    │
    ├── Author → additional fields: paper title, abstract, co-authors
    ├── Reviewer → additional fields: expertise areas, availability
    └── Attendee → standard contact + dietary/accessibility requirements
    │
    ▼
Submission saved as WordPress post (per-role CPT)
    │
    ├── Admin notification dispatched
    └── Confirmation email sent to participant
```

### Architecture

- **Custom Post Types** per participant role, each with dedicated archive and single templates for admin review.
- **ACF field groups** registered programmatically — one group per role, with conditional field logic for optional sections.
- **`functions.php`** handling CPT registration, asset enqueuing, email dispatch hooks, and AJAX form processing.
- **Integration-ready** data schema compatible with SciFlow WP for author and reviewer onboarding into the peer review workflow.

---

## 4. Technologies Used

- **CMS & Backend:** WordPress 6.0+, PHP 8.0+, MySQL
- **Content Management:** ACF Pro — programmatic field groups per participant role
- **Custom Post Types:** Attendee, Author, Reviewer registrations
- **Email:** WordPress `wp_mail()` — confirmation and admin notifications
- **Integration:** Schema-compatible with SciFlow WP peer review plugin

---

## 5. Design Process & UI/UX

The registration form was designed to minimize abandonment through progressive disclosure: each step presents only the fields relevant to the participant's chosen role, preventing the visual overwhelm of a single long form. Inline field validation provides immediate feedback, and a progress indicator communicates how many steps remain — two design choices that consistently improve completion rates in multi-step academic registration contexts.

<!-- TODO: Add screenshot of the multi-step form with progress indicator here -->
<!-- TODO: Add screenshot of the admin view of registered participants here -->

---

## 6. Project Outcomes

- **Structured data capture:** All registrations arrive in a consistent, validated format — eliminating the manual data-cleaning step that preceded every event.
- **Role separation:** The three-CPT architecture ensures author, reviewer, and attendee data are independently queryable and manageable, with no data model overlap.
- **Automation:** Confirmation emails are dispatched automatically at submission, reducing the administrative team's communication workload significantly.
- **SciFlow integration:** Author registrations feed directly into the SciFlow WP peer review workflow, closing the loop between event registration and manuscript submission without manual data transfer.
