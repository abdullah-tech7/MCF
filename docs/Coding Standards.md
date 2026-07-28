# MCF Coding Standards

## Overview

This document defines the official coding standards of MCF (Modular Control Framework).

Its purpose is to ensure consistency across all Modules and shared components.

These standards apply to every project built with MCF.

---

# General Principles

MCF follows four core principles:

- Consistency
- Simplicity
- Predictability
- Separation of Concerns

Every file should have one clear responsibility.

---

# Naming Convention

## Modules

Module names must:

- Use PascalCase.
- Be singular or plural depending on the domain.
- Represent a business domain.

Examples:

```
Users
Orders
Accounting
Inventory
```

Avoid:

```
UserModule
MyUsers
ModuleUsers
```

---

## Workflows

Workflow names must describe a business process.

Examples:

```
Authentication
UserManagement
Profile
PasswordReset
OrderCheckout
```

Avoid:

```
Controller1
CRUD
Main
Test
```

---

# Controllers

Rules:

- One Controller = One Workflow.
- Controllers must remain thin.
- Business logic is not allowed inside Controllers.

Allowed:

- Request validation
- Calling Services
- Returning Views
- Returning Responses

Avoid:

- Database queries
- Business calculations
- Complex conditions

Example:

```
AuthenticationController
```

---

# Services

Every Controller owns one Service.

Responsibilities:

- Business logic
- Application rules
- Database coordination

Services should not return Views.

---

# Requests

Every Workflow owns one Request.

Responsibilities:

- Validation
- Authorization (basic request authorization)

Validation must remain inside the Request whenever possible.

Shared validation belongs to:

```
MCF/Rules/
```

---

# Policies

Every Workflow owns one Policy.

Responsibilities:

- Permissions
- Authorization
- Role checks
- Business access rules

Policies must never perform business logic.

---

# Models

Models represent entities only.

Models are shared across the application.

Models must never belong to a Module.

Avoid placing business workflows inside Models.

---

# Views

Views are presentation only.

Views must not contain:

- Database access
- Business logic
- Permission logic

Views may contain:

- Blade directives
- Components
- Loops
- Conditions required for rendering

---

# Layouts

Layouts are shared infrastructure.

Layouts are responsible for:

- Header
- Footer
- Navigation
- Sidebar
- Asset loading

Business logic must never exist inside Layouts.

---

# Assets

Shared assets belong to:

```
Assets/
```

Examples:

- CSS
- JavaScript
- Images
- Icons
- Fonts

Workflow-specific assets should be avoided whenever possible.

---

# Language Files

Each Workflow owns one language file.

Example:

```
Authentication.php

UserManagement.php
```

Language keys should be descriptive.

Example:

```
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

Keep route definitions simple.

Route logic must never contain business code.

---

# Middleware

Middleware is infrastructure.

Use Middleware only for:

- Authentication
- Guest access
- Localization
- Logging
- Rate limiting

Authorization belongs inside Policies.

---

# Notifications

Notifications are reusable.

They should only know how to send notifications.

They must never decide:

- Who receives notifications.
- When notifications are sent.

Those decisions belong to Services.

---

# Jobs

Jobs execute background work.

Jobs should receive complete data.

Jobs should not perform business decisions.

---

# Events

Events describe something that happened.

Examples:

```
UserRegistered

OrderCompleted

PasswordChanged
```

Event names should use past tense.

---

# Class Size

Classes should remain focused.

Large classes should be split into multiple Workflows rather than becoming complex.

---

# File Structure

One class per file.

One responsibility per class.

Avoid utility classes containing unrelated functionality.

---

# Dependency Injection

Always prefer constructor injection.

Avoid resolving dependencies manually.

Preferred:

```
public function __construct(
    UserService $service
)
```

Avoid:

```
app(UserService::class)
```

unless absolutely necessary.

---

# Static Methods

Avoid unnecessary static methods.

Prefer dependency injection and instance classes.

---

# Business Logic

Business logic belongs only inside Services.

Never place business rules inside:

- Controllers
- Views
- Layouts
- Middleware
- Notifications
- Models

---

# Shared Components

Shared functionality belongs inside:

```
MCF/
```

Never duplicate shared logic between Modules.

---

# Comments

Write self-explanatory code.

Prefer meaningful names over excessive comments.

Comments should explain "why", not "what".

---

# Formatting

Follow the official Laravel coding style (PSR-12) for formatting.

MCF extends Laravel conventions and does not replace them.

---

# Golden Rules

1. One Controller = One Workflow.

2. One Workflow owns exactly one Service.

3. Controllers remain thin.

4. Services contain business logic.

5. Models represent entities.

6. Layouts are shared.

7. Assets are shared.

8. Authorization belongs to Policies.

9. Validation belongs to Requests.

10. Shared infrastructure belongs outside Modules.

11. Modules contain business workflows only.

12. Every class should have one clear responsibility.