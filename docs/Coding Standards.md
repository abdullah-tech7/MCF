# MCF Coding Standards

---

# Overview

This document defines the official coding standards for MCF (Modular Code Framework).

These standards ensure every MCF application follows the same architectural principles, naming conventions, and development practices.

They apply to every Module, Workflow, and shared framework component.

---

# Core Principles

MCF is built around five principles.

- Consistency
- Simplicity
- Predictability
- Separation of Concerns
- Workflow-Driven Architecture

Every class should have one clear responsibility.

---

# Naming Conventions

## Modules

Module names must:

- Use PascalCase.
- Represent a business domain.
- Be concise and descriptive.

Examples:

```text
Users
Shop
Inventory
Reports
Accounting
```

Avoid:

```text
UsersModule
MyUsers
ModuleUsers
```

---

## Workflows

Workflow names describe business capabilities.

Examples:

```text
Authentication
UserManagement
Profile
Checkout
PasswordReset
```

Avoid:

```text
CRUD
Controller1
Main
Test
```

Workflow names should describe what the application does, not what the database stores.

---

# Controllers

Each Workflow owns one Controller.

Controllers coordinate HTTP requests.

Allowed responsibilities:

- Receive requests.
- Call the Request.
- Call the Service.
- Return responses.
- Return Views.

Controllers should never contain:

- Business logic.
- Database queries.
- Complex calculations.
- Authorization rules.
- Long conditional logic.

Example:

```text
AuthenticationController
```

Controllers should remain small.

---

# Services

Every Workflow owns one Service.

The Service contains all business logic for that Workflow.

Responsibilities include:

- Business rules.
- Application behavior.
- Database coordination.
- Transactions.
- Event dispatching.

Services should never:

- Return Blade Views.
- Perform request validation.
- Handle presentation concerns.

---

# Requests

Every Workflow owns one Request.

Responsibilities:

- Validation.
- Basic request authorization.

Validation should remain inside the Request whenever possible.

Reusable validation belongs in:

```text
app/MCF/Rules
```

---

# Policies

Every Workflow owns one Policy.

Policies determine whether an action is permitted.

Responsibilities:

- Authorization.
- Permission checks.
- Role checks.
- Access rules.

Policies should never contain business logic.

---

# Models

Models represent application entities.

Models belong to:

```text
app/MCF/Database/Models
```

Models are shared across the application.

They never belong to a specific Module or Workflow.

Avoid placing business processes inside Models.

---

# Views

Views are responsible only for presentation.

Views may contain:

- Blade directives.
- Components.
- Loops.
- Simple rendering conditions.

Views must never contain:

- Business logic.
- Database queries.
- Authorization logic.
- Service calls.

Generated Workflow views extend:

```blade
@extends('Shared.Layout.app')
```

---

# Layout Workflow

Layouts are implemented as a normal Workflow.

Example:

```text
Shared
└── Layout
```

Responsibilities include:

- Header.
- Footer.
- Navigation.
- Sidebar.
- Shared layouts.
- Common Blade components.

Layouts must never contain business logic.

Developers may replace or customize the Layout Workflow as needed.

---

# Assets

MCF does not provide a custom asset system.

Use Laravel's standard locations.

Shared assets:

```text
public/
```

Uploaded files:

```text
storage/
```

Avoid placing assets inside Workflows unless there is a specific architectural reason.

---

# Language Files

Each Workflow owns its own language directory.

Example:

```text
Lang
└── Authentication
```

Language keys should be descriptive.

Good examples:

```text
login
logout
save
delete
password_invalid
```

Avoid unnecessary nesting.

---

# Routes

Each Workflow owns one Route file.

Route files should contain only route definitions.

Business logic must never appear inside Routes.

Workflow Routes are automatically registered through:

```text
app/MCF/mcf_routes.php
```

---

# Middleware

Middleware is infrastructure.

Use Middleware for:

- Authentication.
- Guest access.
- Localization.
- Logging.
- Rate limiting.
- Request filtering.

Authorization belongs inside Policies.

Business logic belongs inside Services.

---

# Notifications

Notifications should only describe how notifications are delivered.

Notifications should never determine:

- Whether a notification should be sent.
- Who receives notifications.
- Business decisions.

Those responsibilities belong to Workflow Services.

---

# Mail

Mail classes prepare email messages.

They should never contain business decisions.

Workflow Services decide when Mail should be created or sent.

---

# Jobs

Jobs execute background work.

Jobs should receive complete data from the Service.

Jobs should avoid making business decisions.

---

# Events

Events describe something that already happened.

Examples:

```text
UserRegistered
OrderCompleted
PasswordChanged
```

Event names should use the past tense.

---

# Class Size

Keep classes focused.

If a class grows excessively, consider splitting the business capability into multiple Workflows instead of creating large classes.

---

# File Organization

One class per file.

One responsibility per class.

Avoid utility classes that collect unrelated functionality.

---

# Dependency Injection

Prefer constructor injection.

Example:

```php
public function __construct(
    AuthenticationService $service
) {}
```

Avoid manually resolving dependencies.

```php
app(AuthenticationService::class);
```

Use manual resolution only when truly necessary.

---

# Static Methods

Avoid unnecessary static methods.

Prefer dependency injection and object instances.

Static methods should be limited to utility scenarios where state is unnecessary.

---

# Business Logic

Business logic belongs only inside Workflow Services.

Never place business logic inside:

- Controllers
- Requests
- Policies
- Views
- Layouts
- Middleware
- Notifications
- Mail
- Models
- Routes

---

# Shared Components

Reusable functionality should be extracted into shared framework components.

Avoid duplicating business logic across multiple Workflows.

Prefer reuse over duplication.

---

# Comments

Prefer self-explanatory code.

Use meaningful names instead of excessive comments.

Comments should explain **why**, not **what**.

---

# Formatting

Follow Laravel's official coding style.

MCF follows:

- PSR-12
- Laravel Coding Style

MCF extends Laravel conventions rather than replacing them.

---

# Golden Rules

1. One Module groups related Workflows.
2. One Workflow represents one business capability.
3. One Workflow owns one Controller.
4. One Workflow owns one Service.
5. Controllers remain thin.
6. Services contain business logic.
7. Requests contain validation.
8. Policies contain authorization.
9. Models represent entities.
10. Views handle presentation only.
11. Layout is implemented as a Workflow.
12. Assets use Laravel's standard `public/` directory.
13. Uploaded files use Laravel's `storage/` directory.
14. Shared functionality should never be duplicated.
15. Every class should have one clear responsibility.