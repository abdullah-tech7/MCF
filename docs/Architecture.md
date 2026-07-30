# MCF Architecture

MCF (Modular Code Framework) is a feature-oriented architecture built on top of Laravel 12.

Instead of organizing an application around framework directories, MCF organizes it around **business capabilities** called **Workflows**.

Each Workflow contains everything required to implement one feature, making applications easier to understand, maintain and scale.

---

# Architecture Goals

MCF was designed around several core goals.

- Organize applications by business capability.
- Keep features completely self-contained.
- Minimize coupling between modules.
- Encourage clear separation of responsibilities.
- Reduce boilerplate through generators.
- Keep full compatibility with Laravel.
- Support long-term scalability.

---

# High-Level Architecture

An MCF application is organized into four primary layers.

```text
Application

│

├── Modules

│      └── Workflows

│              ├── Backend
│              ├── Views
│              └── Lang

│

├── Database

│

├── Framework Base Classes

│

└── Laravel
```

Laravel provides the runtime.

MCF provides the application architecture.

---

# Core Concepts

Understanding four concepts is enough to understand the entire framework.

- Module
- Workflow
- Backend
- Base Classes

Everything else builds upon these concepts.

---

# Modules

A Module is the highest organizational unit inside MCF.

Modules group related business capabilities together.

Example:

```text
Modules
├── Users
├── Shop
├── Reports
├── Shared
└── System
```

Modules do not contain business logic directly.

Their purpose is organization.

---

# Workflows

A Workflow represents one complete business capability.

Unlike traditional Laravel applications, MCF does not organize applications around Models.

Instead, every feature begins with a Workflow.

Example:

```text
Users
├── Authentication
├── Profile
├── User Management
└── Settings
```

Each Workflow represents something the user wants to accomplish.

Examples include:

- Authentication
- Checkout
- Dashboard
- Product Catalog
- Reports
- User Management

A Workflow should never represent a database table.

---

# Workflow Structure

Each Workflow is completely self-contained.

Example:

```text
Users
└── Profile
    ├── Backend
    ├── Lang
    └── Views
```

Every resource required by the feature remains inside this directory.

This eliminates the need to search across multiple Laravel folders.

---

# Backend Layer

The Backend directory contains all server-side logic for a Workflow.

```text
Backend
├── ProfileController.php
├── ProfilePolicy.php
├── ProfileRequest.php
├── ProfileRoutes.php
└── ProfileService.php
```

Each class has one clearly defined responsibility.

---

# Controller

Controllers receive HTTP requests.

Responsibilities include:

- Receive requests.
- Delegate business logic.
- Return responses.

Controllers should remain small.

Business logic belongs elsewhere.

---

# Service

Services contain business logic.

Typical responsibilities include:

- Business rules
- Data manipulation
- Workflow coordination
- Domain operations

Services should never become Controllers.

Controllers should never become Services.

Each class has a single responsibility.

---

# Request

Requests centralize validation.

Every validation rule belonging to a Workflow should remain inside its Request class.

Validation should never be duplicated across Controllers.

---

# Policy

Policies handle authorization.

Each Workflow owns exactly one Policy.

Policies delegate authorization to the application's authorization layer.

Policies should never know:

- Role names
- Permission identifiers
- Database schema

Authorization remains completely independent from business logic.

---

# Routes

Every Workflow owns its own route file.

```text
ProfileRoutes.php
```

Routes remain close to the feature they belong to.

MCF automatically discovers and registers Workflow routes during application startup.

---

# Views

Every Workflow owns its own Blade templates.

```text
Views
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── components
```

This keeps presentation logic together with the feature that owns it.

---

# Language Resources

Every Workflow owns its own language directory.

```text
Lang
```

Unlike traditional Laravel applications, translations are distributed across Workflows.

During application startup, MCF discovers every Workflow language directory and registers the available translation resources automatically.

Developers do not manually register Workflow language files.

This allows every Workflow to remain completely self-contained.

---

# Base Classes

MCF provides common framework base classes.

```text
app/MCF/Base
├── MfcController
├── MfcPolicy
├── MfcRequest
└── MfcService
```

Every generated Workflow inherits from these classes.

This provides a consistent foundation while allowing projects to extend shared framework behavior from a single location.

---

# Separation of Responsibilities

MCF encourages one responsibility per class.

| Component | Responsibility |
|-----------|----------------|
| Controller | HTTP communication |
| Service | Business logic |
| Request | Validation |
| Policy | Authorization |
| Routes | Endpoint definitions |
| Views | Presentation |
| Lang | Localization |

This separation improves readability, maintainability and testability.

---

# Feature Isolation

One of MCF's primary goals is feature isolation.

A Workflow contains everything related to one business capability.

Moving or deleting a Workflow should have minimal impact on unrelated features.

This organization allows applications to scale without scattering code across multiple directories.

# Application Startup

When an MCF application starts, the framework initializes every registered Workflow automatically.

The startup process includes:

1. Loading framework configuration.
2. Discovering Modules.
3. Discovering Workflows.
4. Registering Workflow routes.
5. Registering Workflow views.
6. Registering Workflow language resources.
7. Bootstrapping the application.

Developers are not required to manually register Workflows.

---

# Workflow Discovery

MCF automatically discovers every Workflow inside the Modules directory.

```text
app/MCF/Modules
```

Example:

```text
Modules
├── Users
│   ├── Authentication
│   └── Profile
│
├── Shop
│   ├── Product Catalog
│   └── Checkout
│
└── Reports
    └── Sales Reports
```

Every discovered Workflow becomes part of the application automatically.

Adding a new Workflow requires no additional registration.

---

# Route Registration

Every Workflow owns its own route definition.

Example:

```text
Profile
└── Backend
    └── ProfileRoutes.php
```

During startup MCF discovers every Workflow route file and registers it with Laravel.

Developers never maintain one large global route file.

Each feature remains responsible for its own endpoints.

---

# View Registration

Every Workflow owns its own Views directory.

Example:

```text
Users
└── Profile
    └── Views
```

MCF automatically registers Workflow views using a namespace derived from the Module and Workflow.

Example:

```php
return view('Users::Profile.index');
```

This keeps Blade templates isolated while remaining easy to reference throughout the application.

---

# Language Registration

Each Workflow contains its own language resources.

```text
Users
└── Profile
    └── Lang
```

During application startup, MCF recursively discovers every Workflow Lang directory.

All discovered translation resources are then registered automatically.

This provides several advantages.

- Workflows remain self-contained.
- Translations stay close to the feature they describe.
- No manual registration is required.
- Moving a Workflow automatically moves its translations.
- Removing a Workflow automatically removes its translations.

Localization scales naturally as applications grow.

---

# Database Layer

MCF keeps database-related classes together inside the framework.

```text
app/MCF/Database
├── Models
├── Migrations
├── Factories
└── Seeders
```

This organization keeps application code inside the MCF architecture while remaining fully compatible with Laravel's migration and Eloquent systems.

---

# Shared Components

Not every resource belongs to a single Workflow.

MCF provides shared framework directories for reusable components.

```text
app/MCF
├── Mail
├── Middleware
├── Notifications
└── Rules
```

These directories contain components that may be used by multiple Workflows.

Keeping them outside individual Workflows avoids unnecessary duplication.

---

# Layout Workflow

The installer creates a default Layout Workflow.

```text
Shared
└── Layout
```

Unlike many frameworks, the Layout is **not** a special framework component.

It is simply another Workflow.

Developers may:

- Rename it.
- Delete it.
- Recreate it.
- Duplicate it.
- Customize it.

MCF treats Layout exactly like every other Workflow.

---

# Request Lifecycle

A typical request travels through the following pipeline.

```text
HTTP Request

↓

Route

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

Each component performs one responsibility before passing control to the next.

This predictable execution flow makes applications easier to understand and debug.

---

# Extensibility

MCF was designed to be extended rather than modified.

Projects may:

- Extend base classes.
- Replace framework services.
- Add custom generators.
- Create reusable Workflow templates.
- Integrate external packages.
- Customize application structure where appropriate.

Because responsibilities remain well separated, extending the framework rarely requires changing existing code.

---

# Laravel Compatibility

MCF extends Laravel instead of replacing it.

Developers continue using familiar Laravel features such as:

- Eloquent
- Blade
- Queues
- Events
- Middleware
- Validation
- Policies
- Notifications
- Mail
- Caching
- Storage
- Broadcasting
- Scheduling

Existing Laravel packages continue to work normally inside MCF applications.

---

# Scalability

As applications grow, new features are added by creating additional Workflows.

Because every Workflow remains isolated:

- Features are easier to locate.
- Teams can work independently.
- Merge conflicts are reduced.
- Refactoring becomes simpler.
- Testing becomes more focused.

The architecture remains consistent regardless of project size.

---

# Design Principles

MCF architecture is built around the following principles.

- Business capability first.
- Feature isolation.
- One responsibility per class.
- Self-contained Workflows.
- Automatic discovery.
- Convention over configuration.
- Laravel compatibility.
- Extensibility.
- Predictable structure.
- Long-term maintainability.

Every architectural decision within MCF follows these principles.

---

# Summary

MCF organizes applications around Workflows rather than framework directories or database models.

Each Workflow contains everything required to implement a single business capability, including its backend classes, views and language resources.

The framework automatically discovers and registers Workflows, routes, views and translations, allowing developers to focus on building features instead of maintaining framework configuration.

By combining feature isolation, automatic discovery and Laravel compatibility, MCF provides a scalable architecture that remains easy to understand from small projects to large enterprise applications.