# Project Lifecycle

## Overview

This document describes the recommended lifecycle for building an application with MCF.

The lifecycle focuses on how an application grows using MCF's modular architecture while maintaining a clean and predictable project structure.

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
Generate Required Components
        │
        ▼
Implement Business Logic
        │
        ▼
Test & Deploy
```

---

# Stage 1

## Create a Laravel Project

Create a standard Laravel application.

```bash
composer create-project laravel/laravel MyProject
```

At this point, the application is a standard Laravel installation.

---

# Stage 2

## Install MCF

Install the framework using Composer.

```bash
composer require mcf/framework
```

This installs the framework into the Laravel application.

---

# Stage 3

## Initialize MCF

Run:

```bash
php artisan mcf:install
```

MCF creates its working structure inside:

```text
app/MCF
```

Example:

```text
app/MCF
├── Assets
├── Database
│   ├── Factories
│   ├── Migrations
│   ├── Models
│   └── Seeders
├── Layouts
├── Mail
├── Middleware
├── Modules
├── Notifications
├── Rules
└── mcf_routes.php
```

The framework is now ready for development.

---

# Stage 4

## Create Modules

Modules represent business capabilities.

Examples:

```text
Users
Products
Orders
Inventory
Reports
```

Each module should encapsulate a single business domain.

---

# Stage 5

## Create Workflows

Workflows organize business processes inside a module.

Examples:

```text
Authentication
Profile
Checkout
Invoices
Reporting
```

A workflow represents a specific application use case.

---

# Stage 6

## Generate Required Components

Generate only the components required by the application.

Examples include:

- Models
- Migrations
- Factories
- Seeders
- Middleware
- Notifications
- Mail
- Rules

MCF follows explicit generation.

Components are created only when requested.

---

# Stage 7

## Implement Business Logic

After the application structure is in place, implement the business logic.

Business logic should remain:

- Modular
- Maintainable
- Independent
- Easy to test

Modules should collaborate through well-defined interfaces rather than tightly coupling implementation details.

---

# Growth

As the application evolves:

```text
New Business Requirement

↓

New Module or Workflow

↓

Generate Required Components

↓

Implement Business Logic
```

No architectural restructuring should be required.

MCF is designed to scale by extending modules and workflows rather than reorganizing the entire project.

---

# Maintenance

As new features are introduced:

- Reuse existing modules whenever appropriate.
- Create new workflows for new business processes.
- Generate only the required framework components.
- Keep responsibilities isolated.

Avoid unnecessary duplication.

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

1. Install the framework.
2. Initialize the project structure.
3. Define business modules.
4. Organize workflows.
5. Generate required components.
6. Implement business logic.
7. Test and deploy.

Following this lifecycle helps maintain a modular, predictable, and scalable application architecture.