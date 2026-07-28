# Best Practices

## Overview

This document describes the recommended practices for developing applications with MCF.

These guidelines are not strict requirements, but following them will result in cleaner, more maintainable, and more scalable applications.

---

# Organize by Business Features

Modules should represent business capabilities rather than technical layers.

Good examples:

```text
Users
Orders
Inventory
Products
Reports
```

Avoid creating modules based on implementation details.

Poor examples:

```text
Controllers
Services
Repositories
Helpers
```

A module should answer the question:

> "What business capability does this provide?"

---

# Keep Modules Independent

Each module should own its own business logic.

Avoid unnecessary dependencies between modules.

When communication is required, prefer well-defined interfaces or services instead of directly accessing another module's internals.

Low coupling makes applications easier to maintain.

---

# Keep Business Logic Out of Controllers

Controllers should coordinate requests.

Business rules belong inside services, workflows, or dedicated business classes.

Controllers should remain lightweight.

---

# Keep Models Focused

Models should represent application data.

Avoid placing unrelated business logic inside Eloquent models.

Business processes should live outside the model whenever possible.

---

# Use Validation Rules

When validation logic becomes reusable, extract it into dedicated Rule classes.

Instead of repeating validation logic across multiple requests, create a reusable rule.

Example:

```text
StrongPassword

ValidPhoneNumber

NationalId
```

---

# Use Middleware for Cross-Cutting Concerns

Middleware should handle concerns that apply to multiple requests.

Examples:

- Authentication
- Authorization
- Request logging
- Localization
- Maintenance mode

Avoid placing these concerns inside controllers.

---

# Keep Notifications Independent

Notifications should be responsible only for delivering notifications.

Avoid embedding unrelated business logic inside notification classes.

---

# Keep Mail Classes Focused

Mail classes should prepare email messages.

Business decisions should occur before the mail is created.

A Mailable should not determine whether an email should be sent.

---

# Generate Only What You Need

MCF intentionally provides focused generators.

Avoid generating components that are not required by the application.

Keeping the project small improves maintainability.

---

# Prefer Composition Over Duplication

When functionality is shared across multiple modules, extract it into reusable services or utilities.

Avoid copying the same implementation between modules.

---

# Use Consistent Naming

Use meaningful names that describe business intent.

Good:

```text
Customer

PurchaseOrder

InventoryReport

InvoiceNotification
```

Avoid vague or abbreviated names.

---

# Follow Laravel Conventions

MCF extends Laravel rather than replacing it.

Whenever possible, follow Laravel's established conventions for:

- Naming
- Routing
- Validation
- Dependency Injection
- Service Container
- Events
- Queues

This reduces the learning curve for developers.

---

# Keep Generators Predictable

Do not manually move generated files into unrelated directories.

Generated components should remain in their intended locations.

This keeps the project structure consistent across the application.

---

# Minimize Global State

Avoid relying on static state or globally shared mutable data.

Prefer dependency injection and explicit dependencies.

This improves testing and maintainability.

---

# Write Maintainable Code

Before adding new functionality, consider whether the implementation is:

- Simple
- Readable
- Reusable
- Testable

Favor clarity over unnecessary abstraction.

---

# Think Long-Term

Applications evolve over time.

Design modules and workflows with future growth in mind.

A solution that is slightly more structured today often prevents major refactoring later.

---

# Summary

Applications built with MCF should strive to be:

- Modular
- Predictable
- Maintainable
- Loosely Coupled
- Easy to Navigate
- Consistent with Laravel

Following these practices helps ensure that projects remain manageable as they grow in size and complexity.