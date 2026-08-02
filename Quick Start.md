# Quick Start

This guide walks you through creating your first MCF application from scratch.

By the end of this guide, you will know how to:

- Install MCF
- Create Modules
- Create Workflows
- Generate CRUD Workflows
- Add Endpoints
- Generate Language Files
- Understand the generated project structure

Estimated time: **10–15 minutes**

---

# Before You Begin

Make sure your environment meets the following requirements.

- PHP 8.4+
- Laravel 12
- Composer

---

# Step 1 — Install MCF

Inside your Laravel project, run:

```bash
php artisan mcf:install
```

The installer prepares the MCF framework directory and creates the default project structure.

After installation your application contains:

```text
app/MCF
├── Base
├── Mail
├── Middleware
├── Modules
├── Notifications
├── Rules
└── mcf_routes.php
```

The installer also creates the default Layout Workflow.

```text
Shared
└── Layout
```

The Layout Workflow is completely editable and behaves like every other Workflow.

---

# Step 2 — Create Your First Module

Modules organize related business features.

Create one using:

```bash
php artisan mcf:make:module Users
```

Result:

```text
Modules
└── Users
```

A Module is simply a container.

Business logic belongs inside Workflows.

---

# Step 3 — Create Your First Workflow

Create a Workflow inside the Users module.

```bash
php artisan mcf:make:workflow Users Profile
```

MCF generates:

```text
Users
└── Profile
    ├── Backend
    ├── Lang
    └── Views
```

The Backend directory contains:

```text
Backend
├── ProfileController.php
├── ProfilePolicy.php
├── ProfileRequest.php
├── ProfileRoutes.php
└── ProfileService.php
```

Every generated class inherits from the MCF base classes.

---

# Step 4 — Create a CRUD Workflow

Many applications require Create, Read, Update and Delete operations.

Instead of generating everything manually, use:

```bash
php artisan mcf:make:workflow:crud Users Products
```

MCF prepares a workflow designed for resource management.

This is recommended for administrative sections such as:

- Products
- Categories
- Customers
- Orders
- Employees

---

# Step 5 — Add Endpoints

A Workflow usually contains multiple actions.

Instead of editing routes and controllers manually, launch the Endpoint Generator.

```bash
php artisan mcf:endpoint:create
```

The generator will guide you through the required options.

Depending on your selections, it can generate:

- Controller methods
- Routes
- Views
- Additional workflow components

This is the recommended way to expand existing Workflows.

---

# Step 6 — Create Language Files

Generate Arabic translations for a Workflow.

```bash
php artisan mcf:lang:make ar Users Profile
```

Generate language files for an entire Module.

```bash
php artisan mcf:lang:make ar Users
```

Generate application-wide language files.

```bash
php artisan mcf:lang:make ar
```

MCF keeps translations close to the feature they belong to whenever possible.


---

# Step 7 — Other Generators

MCF also provides generators for common Laravel components.

Middleware

```bash
php artisan mcf:make:middleware AdminMiddleware
```

Mail

```bash
php artisan mcf:make:mail WelcomeMail
```

Notification

```bash
php artisan mcf:make:notification OrderCreated
```

Validation Rule

```bash
php artisan mcf:make:rule StrongPassword
```

Factory

```bash
php artisan mcf:make:factory UserFactory
```

Seeder

```bash
php artisan mcf:make:seeder UserSeeder
```

---

# Generated Workflow

A typical Workflow looks like this.

```text
Users
└── Profile
    ├── Backend
    │   ├── ProfileController.php
    │   ├── ProfilePolicy.php
    │   ├── ProfileRequest.php
    │   ├── ProfileRoutes.php
    │   └── ProfileService.php
    ├── Lang
    └── Views
```

Everything required for one feature lives in one place.

---

# Development Flow

Most applications follow this workflow.

```text
Install MCF
        │
        ▼
Create Module
        │
        ▼
Create Workflow
        │
        ▼
Generate CRUD (optional)
        │
        ▼
Create Endpoints
        │
        ▼
Generate Language Files
        │
        │
        ▼
Build Your Feature
```

---

# Next Steps

After completing this guide, continue with the documentation:

- Architecture
- Workflow Design
- Endpoint Generator
- CLI Reference
- Language Generator
- Best Practices

You are now ready to start building applications with MCF.


# Workflow Design Essentials

MCF is built around **business capabilities**, not database tables.

Before creating a new Workflow, ask yourself:

> **What does the user want to accomplish?**

If the answer describes a business capability, it is probably a Workflow.

---

## Good Workflow Names

✔ Authentication

✔ User Management

✔ Profile

✔ Dashboard

✔ Settings

✔ Reports

✔ Product Catalog

---

## Avoid These Names

✘ User

✘ Product

✘ Order

✘ Role

These represent data models rather than business features.

---

## One Workflow, One Responsibility

Each Workflow should focus on one responsibility.

Example:

```text
Authentication
├── Login
├── Logout
├── Forgot Password
└── Reset Password
```

All of these belong to the same Workflow because they serve the same business capability.

Avoid splitting related functionality into multiple Workflows unnecessarily.

---

## Actions Are Not Workflows

Actions belong inside Workflows.

For example:

```text
User Management
├── List Users
├── Create User
├── Edit User
├── Delete User
└── Export Users
```

Here:

- **User Management** is the Workflow.
- **Export** is an action.
- **Delete** is an action.
- **Create** is an action.

Do not create separate Workflows named:

- Export
- Delete
- Upload
- Print
- Import

---

## Every Workflow Belongs to a Module

A Workflow cannot exist on its own.

Correct:

```text
Users
└── Authentication
```

```text
Shop
└── Product Catalog
```

```text
Reports
└── Sales Reports
```

Incorrect:

```text
Authentication
```

without a Module.

---

## Keep Features Together

If multiple pages use the same business rules, permissions and data, they usually belong to one Workflow.

For example:

```text
Product Catalog
├── List Products
├── Create Product
├── Edit Product
├── Delete Product
├── Product Details
└── Export Products
```

Keeping related functionality together makes the application easier to understand and maintain.

---

# Base Classes

All generated backend classes inherit from the MCF base classes.

```text
Controller → MfcController
Request    → MfcRequest
Service    → MfcService
Policy     → MfcPolicy
```

These classes provide a common foundation across every Workflow while allowing projects to extend shared behavior from a single location.

---

# Routes

Each Workflow owns its own route file.

Example:

```text
Backend
└── ProfileRoutes.php
```

MCF automatically loads Workflow routes through:

```text
app/MCF/mcf_routes.php
```

This keeps route definitions close to the feature they belong to.

---

# Views

Each Workflow stores its Blade templates inside its own Views directory.

```text
Profile
└── Views
    ├── index.blade.php
    ├── create.blade.php
    └── edit.blade.php
```

Instead of sharing one large global views directory, every feature keeps its templates together.

---

# Language Files

Workflow-specific translations live inside the Workflow.

```text
Profile
└── Lang
```

This keeps translations synchronized with the feature they describe and simplifies moving or removing Workflows.

---

# Tips

When starting a new feature:

1. Create a Module if one does not already exist.
2. Create a Workflow.
3. Generate endpoints as needed.
4. Keep business logic inside the Service.
5. Keep validation inside the Request.
6. Keep authorization inside the Policy.
7. Keep views inside the Workflow.
8. Keep translations inside the Workflow.

Following this structure ensures every feature remains isolated, predictable and easy to maintain.

---

# Where to Go Next

You have now created your first MCF project and understand the basic development workflow.

For more detailed information, continue with:

- **README.md** — Framework overview and command reference.
- **Architecture.md** — Internal framework architecture.
- **CLI.md** — Complete Artisan command reference.
- **Workflow Rules.md** — Workflow design principles.
- **Endpoint Generator.md** — Endpoint generation guide.
- **Language Generator.md** — Translation generation guide.
- **Best Practices.md** — Recommended project organization and development patterns.

The Quick Start guide intentionally focuses on getting you productive quickly. The remaining documentation explores each topic in greater depth.

---

**End of Quick Start**