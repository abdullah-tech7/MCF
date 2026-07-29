
# Project Lifecycle

---

# Overview

This document describes the recommended lifecycle for developing applications with MCF.

MCF follows a Workflow-driven architecture that allows applications to grow incrementally while maintaining a clean, predictable, and modular structure.

Rather than organizing projects around technical layers, MCF encourages developers to build applications around business capabilities.

---

# Development Lifecycle

```text
Create Laravel Project
        │
        ▼
Install MCF
        │
        ▼
Initialize MCF
        │
        ▼
Create Modules
        │
        ▼
Create Workflows
        │
        ▼
Generate Additional Components
        │
        ▼
Implement Business Logic
        │
        ▼
Test
        │
        ▼
Deploy
```

Every stage builds upon the previous one.

---

# Stage 1 — Create a Laravel Project

Create a standard Laravel application.

```bash
composer create-project laravel/laravel MyProject
```

At this stage the project is a normal Laravel installation.

---

# Stage 2 — Install MCF

Install MCF using Composer.

```bash
composer require mcf/framework
```

MCF integrates with the existing Laravel application without replacing Laravel's architecture.

---

# Stage 3 — Initialize MCF

Initialize the framework.

```bash
php artisan mcf:install
```

The installer creates the MCF working structure.

Example:

```text
app/MCF
├── Database
│   ├── Factories
│   ├── Migrations
│   ├── Models
│   └── Seeders
├── Mail
├── Middleware
├── Modules
├── Notifications
├── Rules
├── mcf_routes.php
└── README.md
```

The installer also creates the default shared Layout Workflow.

```text
Modules
└── Shared
    └── Layout
```

Internally this is equivalent to:

```bash
php artisan mcf:make:module Shared

php artisan mcf:make:workflow:layout Shared Layout
```

The project is now ready for development.

---

# Stage 4 — Create Modules

Modules organize related business capabilities.

Examples:

```text
Users
Shop
Inventory
Reports
Accounting
```

A Module represents one business domain.

It does not contain business logic.

---

# Stage 5 — Create Workflows

Create Workflows inside Modules.

Example:

```text
Users
├── Authentication
├── Profile
└── UserManagement

Shop
├── Products
├── Cart
└── Checkout
```

Each Workflow represents one complete business capability.

A Workflow owns:

- Controller
- Service
- Request
- Policy
- Views
- Routes
- Lang
- README

---

# Stage 6 — Generate Additional Components

Generate only the components required by the application.

Examples include:

- Models
- Migrations
- Factories
- Seeders
- Middleware
- Notifications
- Mail
- Validation Rules

MCF intentionally generates components only when explicitly requested.

This keeps projects small and maintainable.

---

# Stage 7 — Implement Business Logic

After the project structure has been established, implement the application's business logic.

Business logic belongs inside Workflow Services.

Other components have focused responsibilities.

| Component | Responsibility |
|-----------|----------------|
| Controller | Coordinate HTTP requests |
| Request | Validation |
| Policy | Authorization |
| Service | Business logic |
| Views | Presentation |

Keeping responsibilities separated improves maintainability.

---

# Stage 8 — Test

Verify that:

- Workflows behave correctly.
- Validation rules work as expected.
- Authorization rules are correct.
- Routes are registered.
- Business logic is functioning correctly.

Testing should occur before deployment.

---

# Stage 9 — Deploy

Deploy the application using Laravel's normal deployment process.

MCF introduces no special deployment requirements.

Applications continue using Laravel's ecosystem for:

- Configuration
- Storage
- Assets
- Queues
- Caching
- Scheduling

---

# Growing the Application

As new requirements appear, extend the application by creating new Modules or Workflows.

```text
New Requirement

↓

Existing Module?

↓

Yes ─────► Create Workflow

No

↓

Create Module

↓

Create Workflow

↓

Generate Required Components

↓

Implement Business Logic
```

Existing architecture should rarely require restructuring.

---

# Maintaining the Application

As the project evolves:

- Reuse existing Modules whenever appropriate.
- Create new Workflows for new business capabilities.
- Generate only the components you need.
- Keep business logic inside Services.
- Keep Workflows independent.

Avoid duplicating functionality between Workflows.

---

# Removing Features

When a business capability is no longer required, remove its Workflow.

```bash
php artisan mcf:remove:workflow Users Authentication
```

This removes:

- Workflow files.
- Route registration.
- Generated structure.

The rest of the application remains unaffected.

---

# Lifecycle Summary

```text
Laravel

↓

Install MCF

↓

Initialize Framework

↓

Create Modules

↓

Create Workflows

↓

Generate Components

↓

Implement Business Logic

↓

Test

↓

Deploy
```

---

# Core Principles

The recommended development order is:

1. Install Laravel.
2. Install MCF.
3. Initialize the framework.
4. Define business Modules.
5. Create Workflows.
6. Generate required components.
7. Implement business logic.
8. Test.
9. Deploy.

Following this lifecycle keeps applications modular, predictable, maintainable, and easy to scale as they evolve.

