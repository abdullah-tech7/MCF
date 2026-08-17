# Folder Structure

MCF organizes applications around business capabilities rather than traditional framework-wide technical directories.

The main principle is to keep a feature's related resources close to the Module and Workflow that own them, while keeping framework-level shared components centralized inside `app/MCF`.

---

# Root Structure

A typical installed MCF application contains the MCF framework structure below:

```text
app
└── MCF
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

The exact contents of individual directories may grow as MCF evolves, but the important distinction is:

- `app/MCF` contains the MCF architecture and shared framework components.
- `app/MCF/Modules` contains the application's business features.
- `app/MCF/z_Guide` contains the installed MCF documentation.
- `app/MCF/mcf_routes.php` is part of MCF's route integration.

Do not treat `Modules` as if it were the whole MCF architecture.

---

# Base

```text
Base
├── MfcController.php
├── MfcRequest.php
└── MfcService.php
```

The Base area contains shared base classes used by generated and framework-level backend code.

Base classes provide a consistent foundation for MCF components.

---

# AccessControl

```text
AccessControl
```

Contains MCF's access-control infrastructure.

Access control should be handled through the application's supported MCF mechanisms rather than by hard-coding role checks throughout feature code.

---

# Audit

```text
Audit
```

Contains MCF Audit infrastructure.

Audit behavior is framework-level functionality and can be enabled or disabled through its supported settings.

Feature models can participate in auditing when configured to do so.

---

# Authentication

```text
Authentication
```

Contains shared MCF authentication infrastructure.

Authentication-related application features should normally be implemented through Modules and Workflows while using the shared Authentication services where appropriate.

---

# Language

```text
Language
```

Contains shared language and translation infrastructure used by MCF.

Feature-specific language resources remain inside their owning Workflow.

---

# Mail

```text
Mail
```

Contains reusable MCF Mail components.

Mail classes can be shared by multiple Modules and Workflows.

Mail provider configuration remains an application environment concern and is configured through the application's environment and mail configuration.

---

# Middleware

```text
Middleware
```

Contains reusable application Middleware that belongs to shared MCF infrastructure.

Middleware that is intended for use by multiple features should remain centralized rather than duplicated inside individual Workflows.

---

# Notification

```text
Notification
```

Contains MCF notification infrastructure.

Notifications can be used by multiple Modules and Workflows and should not be duplicated when the same behavior is shared.

---

# Result

```text
Result
```

Contains MCF result/response infrastructure used by application features.

Workflow Controllers should use the appropriate Result mechanisms instead of duplicating response-handling conventions across the application.

---

# Sms

```text
Sms
```

Contains shared SMS infrastructure.

SMS functionality that is reused by multiple features belongs here rather than inside a specific Workflow.

---

# Modules

`Modules` is the primary application-feature area inside MCF.

```text
Modules
```

Every business feature begins inside a Module.

A Module groups related Workflows together. The Module itself is an organizational boundary; the business implementation belongs inside its Workflows.

---

# Module Structure

Example:

```text
Modules
└── User
```

A Module can contain several related Workflows:

```text
User
├── Auth
├── Profile
└── UserManagement
```

The Module should have a clear business purpose.

---

# Workflow Structure

Each Workflow represents a focused business capability.

Example:

```text
User
├── Auth
├── Profile
└── UserManagement
```

A Workflow should not become a container for unrelated features.

---

# Workflow Directory

A Workflow keeps its feature resources together.

A typical Workflow may look like:

```text
Auth
├── Backend
├── Lang
└── Views
```

The Backend contains the server-side implementation, while Views and Lang remain close to the feature that owns them.

---

# Backend

The Backend directory contains the server-side classes and route definition for the Workflow.

A current Workflow can contain resources such as:

```text
Backend
├── AuthController.php
├── AuthService.php
└── AuthRoutes.php
```

A Workflow should not be assumed to have one universal Workflow Request.

Requests are now independent resources and can be created separately.

---

# Endpoint Requests

When an Endpoint uses a Request, the Request belongs to that Endpoint.

For example:

```text
Auth
└── Backend
    ├── AuthController.php
    ├── AuthRoutes.php
    ├── AuthService.php
    └── Request
        └── LoginRequest.php
```

If the Request directory does not exist, the MCF Request Generator creates it.

A Request can also be created before an Endpoint exists:

```bash
php artisan mcf:make:request User Auth Login
```

This makes Requests reusable during feature development without forcing every Workflow to have a single shared Request class.

---

# Request Data

A Request may define its own Data class when structured validated input is required.

The Request and its Data representation belong to the Request's feature area.

For example:

```text
Request
└── LoginRequest.php
```

The Request can reference:

```php
LoginData::class
```

The Data class carries validated input and should not contain business logic.

---

# Controller

```text
AuthController.php
```

The Controller is responsible for coordinating HTTP requests.

It should:

- Receive the Request.
- Delegate work to the Service.
- Return the appropriate Result or response.

Controllers should remain lightweight.

---

# Service

```text
AuthService.php
```

The Service contains the Workflow's business logic and coordination.

Examples include:

- Business rules.
- Domain operations.
- Workflow coordination.
- Application-specific processing.

---

# Routes

```text
AuthRoutes.php
```

The Workflow route file contains the routes belonging to that Workflow.

Routes should remain close to the feature they serve.

MCF also provides:

```text
app/MCF/mcf_routes.php
```

for MCF-level route integration.

---

# Route Access

MCF routes can also define access requirements through the MCF access-control mechanism.

A Workflow route definition may register its required access information using the MCF route data registry.

This keeps route access metadata close to the route rather than scattering it across unrelated files.

---

# Views

The `Views` directory contains Blade templates belonging to the Workflow.

Example:

```text
Views
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── components
```

Views should remain close to the business feature that owns them.

The shared Layout Workflow is an exception in purpose: it provides reusable application presentation structure and should normally remain available.

---

# Language Resources

Each Workflow can own its feature-specific language resources.

```text
Lang
```

Keeping translations with the Workflow makes the feature easier to understand, maintain, and move.

Shared language infrastructure remains under:

```text
app/MCF/Language
```

---

# Layout

MCF may provide a shared Layout Workflow under the Modules structure.

The Layout Workflow is intended to be shared by application features.

It should normally remain available even if a particular feature does not currently use every part of it.

Replace or remove it only when the application has an intentional alternative presentation architecture.

---

# Example Project

A simplified current project can look like:

```text
app
└── MCF
    ├── AccessControl
    ├── Audit
    ├── Authentication
    ├── Base
    ├── Language
    ├── Mail
    ├── Middleware
    ├── Modules
    │   ├── Shared
    │   │   └── Layout
    │   └── User
    │       ├── Auth
    │       │   └── Backend
    │       │       └── Request
    │       │           └── LoginRequest.php
    │       ├── Profile
    │       └── UserManagement
    ├── Notification
    ├── Result
    ├── Sms
    ├── z_Guide
    └── mcf_routes.php
```

This illustrates the important architectural levels:

```text
MCF
└── Modules
    └── Module
        └── Workflow
            ├── Backend
            ├── Views
            └── Lang
```

The Workflow is the main business-feature boundary.

---

# Why This Structure?

Traditional Laravel applications often organize files by technical type:

```text
Controllers
Requests
Policies
Views
Services
```

Finding everything belonging to one feature can therefore require navigating multiple unrelated directories.

MCF instead organizes application features around Modules and Workflows.

The shared framework infrastructure remains centralized under `app/MCF`, while feature-specific resources remain close to their owning Workflow.

---

# What Developers Usually Modify

After installation, developers primarily work inside:

```text
app/MCF/Modules
```

This is where application Modules, Workflows, Endpoints, Requests, Views, and feature-specific language resources are created and modified.

Developers can customize the supplied application structure and database according to their project requirements.

However, the framework-level MCF directories should not be deleted simply because a feature is currently unused.

Where MCF provides a supported setting to disable a component, prefer disabling it through configuration.

---

# What Comes With MCF

An installed project receives more than an empty Modules directory.

MCF provides:

- Shared framework infrastructure.
- Base classes.
- Authentication infrastructure.
- Access Control.
- Audit.
- Mail.
- Middleware.
- Notifications.
- Result handling.
- SMS infrastructure.
- A prepared Modules structure.
- A shared Layout Workflow.
- Database structure and migrations.
- MCF documentation under `app/MCF/z_Guide`.

The supplied structure is a starting architecture that the application can extend.

---

# Summary

The MCF folder structure has two important levels.

The first is the MCF architecture itself:

```text
app/MCF
```

The second is the application's business-feature area:

```text
app/MCF/Modules
```

Inside Modules:

```text
Module
└── Workflow
    ├── Backend
    ├── Views
    └── Lang
```

Endpoint-specific Requests are created under the appropriate Workflow rather than using the old shared Workflow Request concept.

This separation keeps shared framework infrastructure centralized while allowing application features to remain self-contained, discoverable, and maintainable.
