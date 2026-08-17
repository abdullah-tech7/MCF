# Best Practices

This document describes recommended development practices for building applications with MCF.

These practices are not framework requirements. They are conventions intended to keep applications understandable, maintainable, and scalable.

---

# Design Around Business Capabilities

The central principle of MCF is:

> **Build applications around what users do, not around what the database stores.**

A Workflow should represent a business capability or a focused part of one.

Good examples:

- Authentication
- User Management
- Dashboard
- Product Catalog
- Checkout
- Reports

Avoid creating Workflows simply because a database table exists.

Poor examples:

- User
- Product
- Order
- Role

Business capabilities can remain stable even when the database structure changes.

---

# Keep Workflows Focused

Each Workflow should have a clear responsibility.

Good:

```text
Authentication
├── Login
├── Logout
├── Forgot Password
└── Reset Password
```

Avoid putting unrelated capabilities into one Workflow.

Smaller, focused Workflows are easier to understand, test, and maintain.

---

# Prefer Multiple Small Workflows

It is usually better to have several focused Workflows than one large Workflow.

Good:

```text
User
├── Auth
├── Profile
├── UserManagement
└── Settings
```

Instead of:

```text
User
└── Everything
```

The goal is not to create a Workflow for every single action. Related actions should remain together when they belong to the same business capability.

---

# Keep Related Actions Together

Actions belonging to the same business capability should remain inside one Workflow.

Example:

```text
UserManagement
├── List Users
├── Create User
├── Edit User
├── Delete User
└── Export Users
```

Do not create a separate Workflow for every endpoint or action when they clearly belong to the same capability.

---

# Controllers Should Stay Thin

Controllers should coordinate HTTP requests.

Good responsibilities:

- Receive the Request.
- Call the appropriate Service.
- Return the appropriate Result or response.

Avoid placing the following directly inside Controllers:

- Complex calculations.
- Business logic.
- Large database operations.
- Reusable business rules.
- Validation rules that belong to Requests.

A Controller should coordinate the Workflow, not become the Workflow itself.

---

# Place Business Logic in Services

Business logic belongs in the appropriate Workflow Service.

Examples include:

- Price calculations.
- Order processing.
- Workflow coordination.
- Business rules.
- Domain-specific operations.

Services should remain focused and reusable.

---

# Keep Validation in Requests

Validation should be centralized in Request classes.

MCF Requests are independent resources. A Request does not require an Endpoint to exist first.

For example:

```text
User
└── Auth
    └── Backend
        └── Request
            └── LoginRequest.php
```

A Request can be created independently:

```bash
php artisan mcf:make:request User Auth Login
```

When an Endpoint uses a Request, the Endpoint Generator connects that Request to the generated Controller method.

Do not duplicate the same validation rules directly across Controller methods.

---

# Request Data Classes

When a Request requires structured application data, use its Data class rather than passing loosely structured values throughout the Workflow.

The Request can define the Data class it produces, keeping validated input organized and explicit.

Keep Data classes focused on carrying validated request data rather than implementing business logic.

---

# Keep Authorization Separate

Authorization should remain separate from business logic.

Use the MCF authorization/access mechanisms provided by the application rather than hard-coding role checks throughout Controllers and Services.

Avoid patterns such as:

```php
if ($user->role === 'admin') {
    // ...
}
```

Business code should not become tightly coupled to a specific role name when access can be represented by a proper permission or policy decision.

---

# Do Not Hard-Code Roles

Avoid hard-coding application roles into business logic.

Roles belong to the application and may change between projects.

Prefer the application's access-control mechanisms so authorization remains configurable and maintainable.

---

# Keep Views Inside Their Workflow

Views belong to the feature that owns them.

Good:

```text
User
└── Profile
    └── Views
```

Avoid creating one large global Views directory for unrelated features.

The shared Layout Workflow is different: it is intended to provide reusable presentation structure for the application.

---

# Keep Language Files Close to Features

Feature-specific translations should remain close to their owning Workflow.

Example:

```text
Profile
└── Lang
```

Keeping localized resources with the Workflow makes the feature easier to move and maintain.

---

# Prefer Endpoint Generation

Whenever possible, use the Endpoint Generator instead of manually creating and connecting Controllers, Routes, Views, and Requests.

The Endpoint Generator keeps the complete Endpoint structure consistent.

An Endpoint should be treated as a complete feature path. Avoid manually creating only part of an Endpoint and leaving the rest inconsistent.

---

# Requests and Endpoints

Requests and Endpoints are related but independent concepts.

A Request may exist without an Endpoint.

An Endpoint may use a Request.

The current architecture does not use the old shared Workflow Request model.

When an Endpoint has a Request, that Request is specific to the Endpoint and is connected to its Controller method.

This keeps request validation close to the HTTP operation that owns it.

---

# Use CRUD Workflows Appropriately

CRUD Workflows are intended for resource-management features.

Good examples:

- Products
- Categories
- Customers
- Employees
- Roles

Avoid using CRUD generation for business processes such as:

- Checkout
- Authentication
- Payment Processing

Business-oriented Workflows often require custom logic beyond standard CRUD operations.

---

# Reuse Shared Components

Components used by multiple Workflows should remain in their appropriate shared MCF directories.

Examples include:

- Middleware
- Mail
- Notifications
- Result handling
- SMS
- Other framework-level shared services

Avoid duplicating reusable infrastructure inside individual Workflows.

---

# Keep Modules Organized

Modules group related business capabilities.

Example:

```text
User
├── Auth
├── Profile
├── UserManagement
└── Settings
```

Avoid placing unrelated capabilities inside the same Module.

A Module should have a clear business purpose.

---

# Keep the MCF Structure Intact

The installed `app/MCF` directory contains the framework architecture, not only Modules.

Typical areas include:

```text
app/MCF
├── AccessControl
├── Audit
├── Authentication
├── Base
├── Language
├── Mail
├── Middleware
├── Modules
├── Notification
├── Result
├── Sms
├── z_Guide
└── mcf_routes.php
```

These areas are interconnected.

Do not delete an MCF directory merely because the project does not currently use a feature. Where MCF provides a supported setting to disable a component, prefer configuration over deletion.

The application-specific `Modules` directory is the primary area where developers create, modify, and remove their own features.

---

# Keep Shared Layout Available

The installed shared Layout Workflow is intended to provide a common presentation layer.

It should normally remain available even when individual features do not currently use every part of it.

Remove or replace it only when the application has an intentional alternative architecture.

---

# Keep Routes Local

Each Workflow should own its feature routes.

Keep routes close to the Workflow instead of collecting unrelated feature routes into one large global file.

MCF's route architecture also supports framework-level route registration through `mcf_routes.php`.

This keeps feature routes and MCF route integration distinguishable.

---

# Follow Consistent Naming

Names should immediately describe their purpose.

Good:

- Authentication
- ProductCatalog
- UserManagement
- SalesReports

Avoid vague names such as:

- Main
- Default
- Test
- Temp
- NewWorkflow

Consistent naming makes a project easier to navigate.

---

# Keep Workflows Loosely Coupled

A Workflow should avoid unnecessary direct dependencies on another Workflow's internal implementation.

When one business capability needs another, communicate through clear Services, interfaces, or other defined application boundaries.

Avoid reaching deeply into another Workflow's internal files.

Loosely coupled Workflows are easier to maintain and evolve.

---

# Extend Base Classes

MCF provides base classes for common framework behavior.

When generated or framework classes already provide a suitable base abstraction, extend the appropriate MCF base class instead of duplicating common behavior.

Centralizing shared behavior reduces maintenance.

---

# Favor Convention

MCF follows predictable conventions for naming, locations, generated classes, and Workflow structure.

Prefer the established conventions unless there is a clear architectural reason to deviate.

Convention makes the project easier for another developer to understand.

---

# Design for Scalability

When creating a new Workflow, ask:

- Does this feature represent a clear business capability?
- Can it grow independently?
- Does it have a focused responsibility?
- Can another developer maintain it without understanding unrelated features?
- Are its dependencies clearly defined?

If the answers are yes, the Workflow is likely well structured.

---

# Think About Maintenance

Code is read more often than it is written.

Optimize for:

- Readability.
- Predictability.
- Simplicity.
- Consistency.
- Clear ownership.

A structure that is convenient today but difficult to understand later creates unnecessary maintenance costs.

---

# Summary

Successful MCF applications generally share these characteristics:

- Workflows represent business capabilities.
- Modules group related capabilities.
- Controllers remain thin.
- Services contain business logic.
- Requests handle validation.
- Request Data classes carry structured validated input.
- Authorization remains separate from business logic.
- Views and translations stay close to their Workflows.
- Endpoints are generated and maintained as complete feature paths.
- Shared components are reused.
- The installed MCF framework structure remains intact.
- Layout remains available as a shared application resource.
- Routes remain close to their owning features.
- Naming remains consistent.
- Workflows remain focused and loosely coupled.

Following these practices keeps MCF applications predictable, maintainable, and scalable as they grow.
