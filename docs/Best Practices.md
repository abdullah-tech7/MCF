
# Best Practices

This document describes the recommended development practices for building applications with MCF.

These practices are not framework requirements.

However, following them results in applications that are easier to understand, maintain and scale.

---

# Design Around Business Capabilities

The most important principle in MCF is:

> **Build applications around what users do, not around what the database stores.**

A Workflow should represent a business capability.

Good examples:

- Authentication
- User Management
- Dashboard
- Product Catalog
- Checkout
- Reports

Avoid creating Workflows that simply mirror database tables.

Poor examples:

- User
- Product
- Order
- Role

Business capabilities remain stable even when the database evolves.

---

# Keep Workflows Focused

Each Workflow should have one responsibility.

Good:

```text
Authentication
├── Login
├── Logout
├── Forgot Password
└── Reset Password
```

Avoid:

```text
Authentication
Users
Roles
Settings
Notifications
Reports
Dashboard
```

inside a single Workflow.

Smaller Workflows are easier to understand and maintain.

---

# Prefer Multiple Small Workflows

It is usually better to have several focused Workflows than one large Workflow.

Good:

```text
Users
├── Authentication
├── Profile
├── User Management
└── Settings
```

Instead of:

```text
Users
└── Everything
```

Smaller Workflows improve readability and reduce maintenance costs.

---

# Keep Related Actions Together

Actions belonging to the same business capability should remain inside one Workflow.

Example:

```text
User Management

├── List Users
├── Create User
├── Edit User
├── Delete User
└── Export Users
```

Creating a separate Workflow for every action introduces unnecessary fragmentation.

---

# Controllers Should Stay Thin

Controllers should coordinate requests.

They should not contain business logic.

Good responsibilities:

- Receive HTTP requests.
- Call Services.
- Return responses.

Avoid:

- Complex calculations.
- Database queries.
- Authorization logic.
- Validation rules.
- Business rules.

---

# Place Business Logic in Services

Business logic belongs inside Workflow Services.

Examples include:

- Price calculations.
- Order processing.
- Workflow coordination.
- Business validation.
- Domain rules.

Keeping Services focused makes them easier to test and reuse.

---

# Keep Validation in Requests

Validation should always be centralized.

Each Workflow owns one Request.

Avoid validating data directly inside Controllers.

Good:

```text
ProfileRequest
```

Avoid:

```php
$request->validate(...)
```

inside every Controller method.

---

# Keep Authorization in Policies

Every Workflow owns one Policy.

Authorization should never be mixed with business logic.

Policies should:

- Ask permission questions.
- Delegate authorization.
- Return decisions.

Services should never check roles directly.

---

# Never Hard-Code Roles

Avoid writing:

```php
$user->role == 'admin'
```

Avoid:

```php
$user->isAdmin()
```

Roles belong to the application, not to the framework.

Authorization should always remain dynamic.

---

# Keep Views Inside Their Workflow

Views belong to the feature that owns them.

Good:

```text
Users
└── Profile
    └── Views
```

Avoid creating one large global Views directory for unrelated features.

Keeping Views together improves discoverability.

---

# Keep Language Files Close to Features

Every Workflow should own its translations.

Example:

```text
Profile
└── Lang
```

Avoid placing feature-specific translations in unrelated locations.

Localized resources should move together with the Workflow.

---

# Prefer Endpoint Generation

Whenever possible, use the Endpoint Generator instead of manually editing Controllers and Routes.

Benefits include:

- Consistent code generation.
- Predictable structure.
- Reduced boilerplate.
- Fewer manual errors.

Generated code remains easier to maintain across large projects.

---

# Use CRUD Workflows Appropriately

CRUD Workflows are intended for resource management.

Good examples:

- Products
- Categories
- Customers
- Employees
- Roles

Avoid using CRUD generators for business processes such as:

- Checkout
- Authentication
- Payment Processing

Business-oriented Workflows usually require custom logic beyond standard CRUD operations.

---

# Reuse Shared Components

Components used by multiple Workflows should live in shared framework directories.

Examples include:

- Middleware
- Mail
- Notifications
- Validation Rules

Avoid duplicating reusable code across multiple Workflows.

---

# Keep Modules Organized

Modules should group related business capabilities.

Example:

```text
Users
├── Authentication
├── Profile
├── User Management
└── Settings
```

Avoid placing unrelated Workflows inside the same Module.

Modules should have a clear business purpose.

---

# Follow Consistent Naming

Workflow names should immediately describe their purpose.

Good:

- Authentication
- Product Catalog
- User Management
- Sales Reports

Avoid generic names such as:

- Main
- Default
- Test
- Temp
- NewWorkflow

Meaningful names improve project readability.

---

# Keep Workflows Independent

A Workflow should avoid depending on another Workflow whenever possible.

Business features should communicate through Services or well-defined interfaces rather than tightly coupling implementations.

Independent Workflows are easier to move, reuse and maintain.

---

# Extend Base Classes

Generated backend classes inherit from MCF base classes.

If multiple Workflows require shared behavior, extend the framework base classes instead of duplicating code across individual Workflows.

Centralizing shared behavior simplifies maintenance.

---

# Keep Routes Local

Every Workflow owns its own routes.

Avoid collecting unrelated routes in one global file.

Keeping routes close to their feature makes navigation significantly easier.

---

# Favor Convention

MCF follows predictable conventions.

Avoid unnecessary customization unless there is a clear benefit.

Following conventions helps every developer understand the project quickly.

---

# Design for Scalability

When creating a new Workflow, ask yourself:

- Can this feature grow independently?
- Does it have one responsibility?
- Can another team maintain it without understanding unrelated features?

If the answer is yes, the Workflow is probably designed well.

---

# Think About Maintenance

Code is usually read more often than it is written.

Optimize for:

- Readability.
- Predictability.
- Simplicity.
- Consistency.

Future maintainability is more valuable than short-term convenience.

---

# Summary

Successful MCF applications share several characteristics.

- Workflows represent business capabilities.
- Controllers remain thin.
- Services contain business logic.
- Requests handle validation.
- Policies handle authorization.
- Views and translations stay inside their Workflows.
- Shared components are reused.
- Modules remain organized.
- Naming stays consistent.
- Features remain independent.

Following these practices keeps applications scalable, predictable and easy to maintain as they grow.
````

