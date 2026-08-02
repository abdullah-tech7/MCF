# Folder Structure

MCF organizes applications around business capabilities instead of framework directories.

Unlike a traditional Laravel project where Controllers, Requests, Policies and Views are separated into global directories, MCF keeps everything related to a feature together.

This structure improves discoverability, maintainability and scalability.

---

# Root Structure

A typical MCF application contains the following structure.

```text
app
└── MCF
    ├── Base
    ├── Mail
    ├── Middleware
    ├── Modules
    ├── Notifications
    └── Rules
```

Each directory has a specific responsibility.

---

# Base

```text
Base
├── MfcController.php
├── MfcPolicy.php
├── MfcRequest.php
└── MfcService.php
```

The Base directory contains the framework's shared base classes.

Every generated backend class inherits from one of these classes.

This provides a consistent foundation for all Workflows.

---

# Mail

```text
Mail
```

Contains reusable Mail classes.

Mail components are shared across multiple Workflows whenever appropriate.

---

# Middleware

```text
Middleware
```

Contains application Middleware shared throughout the project.

Middleware should not belong to individual Workflows.

---

# Notifications

```text
Notifications
```

Contains reusable Notification classes.

Notifications may be used by multiple Modules and Workflows.

---

# Rules

```text
Rules
```

Contains reusable validation Rules.

Rules shared by multiple Workflows should be placed here rather than duplicated.

---

# Modules

The Modules directory is the heart of the framework.

```text
Modules
```

Every business feature begins inside a Module.

Modules organize related Workflows together.

---

# Module Structure

Example:

```text
Modules
└── Users
```

A Module does not contain business logic directly.

Its purpose is to organize related Workflows.

---

# Workflow Structure

Each Module contains one or more Workflows.

Example:

```text
Users
├── Authentication
├── Profile
├── User Management
└── Settings
```

Each Workflow represents one business capability.

---

# Workflow Directory

A Workflow is completely self-contained.

```text
Profile
├── Backend
├── Views
└── Lang
```

Everything required to implement the feature lives here.

Developers never need to search across multiple framework directories.

---

# Backend Directory

The Backend directory contains all server-side classes.

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

```text
ProfileController.php
```

Responsible for:

- Receiving HTTP requests.
- Delegating business logic.
- Returning responses.

Controllers should remain lightweight.

---

# Service

```text
ProfileService.php
```

Responsible for:

- Business rules.
- Workflow coordination.
- Domain operations.

Business logic belongs here.

---

# Request

```text
ProfileRequest.php
```

Responsible for:

- Validation rules.
- Request authorization (when applicable).
- Input preparation.

Validation remains centralized.

---

# Policy

```text
ProfilePolicy.php
```

Responsible for authorization.

Every Workflow owns one Policy.

Authorization remains independent from Controllers and Services.

---

# Routes

```text
ProfileRoutes.php
```

Contains the Workflow's route definitions.

MCF automatically discovers and registers Workflow routes during application startup.

Developers never manually register Workflow routes.

---

# Views

The Views directory contains Blade templates for the Workflow.

```text
Views
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── components
```

Presentation remains close to the business feature it belongs to.

---

# Language Resources

Every Workflow owns its own language resources.

```text
Lang
```

MCF automatically discovers every Workflow Lang directory during application startup.

Translation resources are registered automatically without additional configuration.

Keeping translations inside the Workflow improves portability and organization.

---

# Example Project

```text
app
└── MCF
    ├── Base
    │   ├── MfcController.php
    │   ├── MfcPolicy.php
    │   ├── MfcRequest.php
    │   └── MfcService.php
    │
    │
    ├── Mail
    ├── Middleware
    ├── Notifications
    ├── Rules
    │
    └── Modules
        ├── Users
        │   ├── Authentication
        │   ├── Profile
        │   └── User Management
        │
        └── Shop
            ├── Product Catalog
            └── Checkout
```

This organization remains consistent regardless of application size.

---

# Why This Structure?

Traditional Laravel applications organize files by technical type.

Example:

```text
Controllers
Requests
Policies
Views
```

Finding everything related to one feature often requires navigating multiple directories.

MCF instead groups everything by business capability.

This allows developers to locate an entire feature from a single location.

---

# Benefits

The MCF folder structure provides several advantages.

- Feature isolation.
- Better organization.
- Easier navigation.
- Reduced coupling.
- Consistent architecture.
- Improved scalability.
- Better team collaboration.
- Simpler maintenance.

Each feature becomes easier to understand because all related resources live together.

---

# Summary

The MCF folder structure organizes applications around Modules and Workflows rather than framework directories.

Each Workflow contains its backend classes, views and language resources, while shared infrastructure such as middleware and notifications remains centralized.

This architecture keeps features self-contained, promotes consistency and allows applications to scale without sacrificing maintainability.