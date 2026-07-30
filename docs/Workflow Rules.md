# Workflow Rules

This document defines the architectural rules for designing and implementing Workflows in MCF.

A Workflow is the fundamental building block of an MCF application.

Following these rules ensures that every Workflow remains consistent, maintainable and reusable.

---

# What Is a Workflow?

A Workflow represents one complete business capability.

It is not a database table.

It is not a Model.

It is not a Controller.

It is a feature that solves a business problem.

Examples include:

- Authentication
- User Management
- Product Catalog
- Checkout
- Reports
- Dashboard

A Workflow should describe **what the application does**, not **what the database stores**.

---

# One Responsibility

Every Workflow should have one clearly defined responsibility.

Good:

```text
Authentication
```

Good:

```text
Checkout
```

Good:

```text
Product Catalog
```

Avoid:

```text
System
```

Avoid:

```text
Everything
```

Avoid:

```text
Admin
```

Large Workflows become difficult to understand and maintain.

---

# Organize by Business Capability

Always design Workflows around business functionality.

Good:

```text
Users

├── Authentication
├── Profile
├── User Management
└── Settings
```

Avoid organizing Workflows around database entities.

Poor examples:

```text
User
Product
Order
```

Business capabilities are more stable than database structures.

---

# Workflow Structure

Every Workflow follows the same structure.

```text
Workflow

├── Backend
├── Views
└── Lang
```

This structure should remain consistent across the application.

---

# Backend Structure

The Backend directory contains all server-side classes.

```text
Backend

├── WorkflowController.php
├── WorkflowPolicy.php
├── WorkflowRequest.php
├── WorkflowRoutes.php
└── WorkflowService.php
```

Every class has one responsibility.

Do not introduce additional backend classes unless they are genuinely required.

---

# One Policy Per Workflow

Each Workflow owns one Policy.

Example:

```text
ProfilePolicy
```

All authorization requests for the Workflow should pass through this Policy.

Avoid creating separate Policies for individual endpoints.

---

# One Request Per Workflow

Each Workflow owns one Request.

Example:

```text
ProfileRequest
```

Validation should remain centralized.

Avoid scattering validation across multiple Controller methods.

---

# One Service Per Workflow

Business logic belongs inside the Workflow Service.

Example:

```text
ProfileService
```

Avoid placing business rules inside Controllers.

---

# One Controller Per Workflow

Controllers coordinate HTTP communication.

Example:

```text
ProfileController
```

Controllers should:

- Receive requests.
- Delegate work.
- Return responses.

They should not implement business rules.

---

# One Routes File Per Workflow

Every Workflow owns its own route definitions.

Example:

```text
ProfileRoutes.php
```

Routes should remain close to the feature they belong to.

MCF automatically discovers Workflow routes during application startup.

---

# Own Your Views

Every Workflow owns its own Blade templates.

Example:

```text
Views
├── index.blade.php
├── create.blade.php
└── edit.blade.php
```

Presentation should remain inside the feature that owns it.

---

# Own Your Translations

Every Workflow owns its own language resources.

```text
Lang
```

MCF automatically discovers and registers Workflow language resources during startup.

Developers never manually register language paths.

---

# Keep Workflows Independent

A Workflow should be as independent as possible.

Avoid tightly coupling one Workflow to another.

Business features should communicate through Services or well-defined interfaces when collaboration is required.

Independent Workflows are easier to maintain and reuse.

---

# Reuse Models

Multiple Workflows may use the same Model.

Example:

```text
Authentication

↓

User Model

↑

Profile
```

Avoid creating duplicate Models simply because multiple Workflows use the same data.

Models represent data.

Workflows represent business capabilities.

---

# Keep Business Logic in Services

Business rules belong inside the Workflow Service.

Typical examples include:

- Processing orders.
- Updating profiles.
- Calculating prices.
- Sending notifications.
- Coordinating domain operations.

Services should not become Controllers.

---

# Keep Controllers Thin

Controllers should remain small.

Good:

```php
public function update(ProfileRequest $request)
{
    return $this->service->update($request->validated());
}
```

Avoid:

- Database queries.
- Complex calculations.
- Validation.
- Authorization logic.
- Business rules.

---

# Centralize Validation

Validation belongs inside the Workflow Request.

Every validation rule should have one authoritative location.

This improves consistency and reduces duplication.

---

# Centralize Authorization

Authorization belongs inside the Workflow Policy.

Policies should delegate authorization to the application's authorization layer.

Avoid checking roles or permissions directly inside Controllers or Services.

---

# Keep Endpoints Together

Related actions should remain inside the same Workflow.

Example:

```text
User Management

├── index
├── create
├── store
├── edit
├── update
├── delete
└── export
```

Avoid creating a new Workflow for every endpoint.

---

# Naming Rules

Workflow names should describe business functionality.

Good:

- Authentication
- Product Catalog
- Sales Reports
- User Management

Avoid:

- Main
- Temp
- Default
- Test
- Misc

Names should immediately communicate the Workflow's purpose.

---

# Avoid Duplicate Workflows

Before creating a new Workflow, determine whether the functionality belongs to an existing business capability.

Creating unnecessary Workflows increases complexity and fragments related functionality.

---

# Workflow Lifecycle

Every Workflow follows the same execution pipeline.

```text
HTTP Request

↓

Workflow Route

↓

Workflow Controller

↓

Workflow Request

↓

Workflow Policy

↓

Workflow Service

↓

Response
```

Each layer performs one responsibility before passing execution to the next.

---

# Workflow Portability

A properly designed Workflow should be portable.

Moving a Workflow to another project should require little or no modification.

This is achieved by avoiding dependencies on:

- Specific database schemas.
- Role names.
- Permission identifiers.
- Application-specific configuration.
- Unrelated Workflows.

Portable Workflows are easier to share and maintain.

---

# Best Practices

When designing a Workflow:

- Give it one responsibility.
- Organize around business capability.
- Keep Controllers thin.
- Place business logic in Services.
- Centralize validation.
- Centralize authorization.
- Keep Views local.
- Keep translations local.
- Reuse shared Models.
- Prefer independence over coupling.

---

# Summary

A Workflow is the core architectural unit of MCF.

Each Workflow encapsulates a single business capability and owns its backend classes, views and language resources.

By following these rules, Workflows remain focused, predictable and portable, allowing applications to grow without sacrificing clarity, consistency or maintainability.