# Best Practices

---

# Overview

This document describes the recommended practices for developing applications with MCF.

These recommendations are not strict rules.

They represent the architectural philosophy of MCF and help applications remain predictable, maintainable, and scalable as they grow.

---

# Build Around Workflows

MCF is a Workflow-driven framework.

Every business capability should be implemented as a Workflow.

Think about what the application does rather than what the database stores.

Good examples:

```text
Authentication
User Management
Checkout
Inventory
Reports
```

Avoid creating Workflows that simply mirror database tables.

Poor examples:

```text
User
Product
Order
Role
```

---

# Group Workflows into Modules

A Module is an organizational boundary.

Modules group related Workflows that belong to the same business domain.

Example:

```text
Users
├── Authentication
├── Profile
└── User Management

Shop
├── Products
├── Cart
└── Checkout
```

Modules organize Workflows.

They should not contain business logic themselves.

---

# Keep Workflows Independent

Every Workflow should own its own implementation.

A Workflow should contain everything required to implement its business capability.

- Controller
- Service
- Request
- Policy
- Views
- Routes
- Lang
- README

Avoid unnecessary dependencies between Workflows.

When communication is required, expose clear interfaces rather than accessing another Workflow's internal implementation.

---

# Keep Business Logic Out of Controllers

Controllers should coordinate HTTP requests.

Every generated Controller should inherit from:

```text
MfcController
```

Their responsibilities are limited to:

- Receive requests.
- Validate input.
- Call the Service.
- Return responses.

Business rules should always live inside the Workflow's Service.

Controllers should remain small and predictable.

---

# Keep Services Focused

Each Workflow owns one Service.

Every generated Service should inherit from:

```text
MfcService
```

That Service contains the business logic for the Workflow.

Avoid splitting one Workflow across multiple unrelated Services unless there is a clear architectural reason.

Keeping Workflow logic together improves discoverability.

---

# Keep Requests Centralized

Each Workflow owns a single Request class.

Every generated Request should inherit from:

```text
MfcRequest
```

Instead of generating multiple Request classes for every action, centralize Workflow validation in one predictable location.

This makes validation easier to maintain.

---

# Keep Policies Close to the Workflow

Authorization belongs to the Workflow.

Each Workflow should own its own Policy.

Every generated Policy should inherit from:

```text
MfcPolicy
```

Keeping authorization together with the business capability makes security rules easier to understand and maintain.

---

# Keep Views Inside the Workflow

Every Workflow owns its own Views directory.

Avoid creating one large global collection of Blade files.

Generated Controllers should return:

```php
return view('Users::Authentication.index');
```

Shared Layout components should be referenced using:

```blade
@include('Shared::Layout.Components.head')
```

Keeping Views inside the Workflow keeps every feature self-contained.

---

# Treat Layout as a Workflow

Layout is implemented as a normal Workflow.

The default installation creates:

```text
Shared
└── Layout
```

Developers are free to:

- Rename it.
- Replace it.
- Delete it.
- Recreate it.
- Create multiple Layout Workflows.

MCF does not reserve Layout as a special framework component.

---

# Use Laravel's Native Assets and Storage

MCF does not provide a custom asset management system.

Use Laravel's standard locations.

Public assets:

```text
public/
```

Uploaded files:

```text
storage/
```

Following Laravel's conventions simplifies deployment and maintenance.

---

# Keep Models Focused

Models represent application data.

Avoid placing business processes inside Eloquent Models whenever possible.

Business logic belongs inside Workflow Services.

---

# Use Validation Rules

When validation becomes reusable across multiple Workflows, extract it into dedicated Rule classes.

Examples:

```text
StrongPassword

ValidPhoneNumber

NationalId
```

Reusable Rules reduce duplication and improve consistency.

---

# Use Middleware for Cross-Cutting Concerns

Middleware should handle concerns shared across multiple Workflows.

Examples:

- Authentication
- Authorization
- Localization
- Request Logging
- Maintenance Mode

Avoid implementing these concerns inside Controllers.

---

# Keep Notifications Focused

Notifications should only deliver notifications.

Business decisions should already be completed before a Notification is created.

---

# Keep Mail Classes Focused

Mail classes prepare email messages.

They should not contain business rules.

A Mailable should never decide whether an email should be sent.

---

# Generate Only What You Need

MCF generators intentionally generate focused components.

Avoid creating components that are never used.

Smaller projects are easier to understand and maintain.

---

# Prefer Composition Over Duplication

If functionality is shared across multiple Workflows, extract it into reusable components or shared services.

Avoid copying implementations between Workflows.

---

# Use Consistent Naming

Names should describe business intent.

Good examples:

```text
Authentication

User Management

Product Catalog

Inventory Report

Invoice Notification
```

Avoid abbreviations and vague names.

---

# Follow Laravel Conventions

MCF extends Laravel.

Whenever possible, follow Laravel's conventions for:

- Naming
- Routing
- Validation
- Dependency Injection
- Service Container
- Events
- Queues
- Blade

This reduces the learning curve for Laravel developers.

---

# Keep Generated Structure Intact

Avoid moving generated files into unrelated directories.

Every generated component has a predictable location.

Keeping the generated structure intact improves consistency across the application.

---

# Minimize Global State

Avoid static mutable state whenever possible.

Prefer dependency injection.

Explicit dependencies improve testing, maintainability and readability.

---

# Write Maintainable Code

Before adding new functionality, ask whether the implementation is:

- Simple
- Readable
- Reusable
- Predictable
- Testable

Favor clarity over unnecessary abstraction.

---

# Design for Growth

Applications evolve over time.

Design Modules and Workflows so they can grow without major restructuring.

A well-designed Workflow today often prevents significant refactoring later.

---

# Summary

Applications built with MCF should strive to be:

- Workflow-Driven
- Modular
- Predictable
- Maintainable
- Loosely Coupled
- Highly Cohesive
- Easy to Navigate
- Consistent with Laravel

Follow the generated MCF structure, inherit from the provided base classes, and keep each Workflow self-contained.

Following these practices helps keep applications understandable, scalable, and maintainable throughout their lifecycle.
