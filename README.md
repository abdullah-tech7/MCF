# MCF Framework

> ⚠️ **Experimental:** This package is under active development. Use it for testing and evaluation only until a stable release is available.

> **MCF (Modular Code Framework)** is a modular architecture built on top of Laravel 12 that helps developers build scalable, maintainable, and well-organized applications using a feature-based structure.

---

# Overview

MCF extends Laravel without replacing it.

Instead of generating application code across Laravel's default directories, MCF organizes framework components inside a dedicated structure located at:

```text
app/MCF
```

This keeps the application clean, modular, and easier to maintain as projects grow.

MCF follows Laravel conventions whenever possible while introducing a structured modular architecture and a dedicated set of Artisan generators.

Every generated workflow is completely self-contained inside its module. Each workflow owns its controller, service, request, policy, views, routes, and language files.

MCF also provides a small set of framework base classes located under:

```text
app/MCF/Base
```

All generated controllers, requests, services, and policies inherit from these base classes, providing a single extension point for framework-wide functionality.

MCF does **not** replace Laravel's built-in asset or storage systems. Static assets should be stored using Laravel's standard `public/` directory, while uploaded files should use Laravel's standard `storage/` filesystem.

---

# Features

- Modular architecture
- Feature-based project organization
- Self-contained workflows
- Framework base classes
- Dedicated Artisan generators
- Isolated framework structure
- Laravel-compatible implementation
- Clean separation of responsibilities
- Predictable project layout
- Extensible design

---

# Project Structure

```text
app/MCF
├── Base/
│   ├── MfcController.php
│   ├── MfcPolicy.php
│   ├── MfcRequest.php
│   └── MfcService.php
├── Database/
│   ├── Factories/
│   ├── Migrations/
│   ├── Models/
│   └── Seeders/
├── Mail/
├── Middleware/
├── Modules/
├── Notifications/
├── Rules/
├── mcf_routes.php
├── README.md
└── Quick Start.md
```

---

# Quick Start

Create a module:

```bash
php artisan mcf:make:module Users
```

Create a workflow:

```bash
php artisan mcf:make:workflow Users Profile
```

Create a layout workflow:

```bash
php artisan mcf:make:workflow:layout Shared Layout
```

Remove a workflow:

```bash
php artisan mcf:remove:workflow Users Profile
```

Create a model:

```bash
php artisan mcf:make:model User
```

Create a migration:

```bash
php artisan mcf:make:migration create_users_table
```

---

# Workflow Structure

Every workflow is generated as an independent unit inside its module.

Example:

```text
app/MCF/
└── Modules/
    └── Users/
        └── Profile/
            ├── ProfileController.php
            ├── ProfileService.php
            ├── ProfileRequest.php
            ├── ProfilePolicy.php
            ├── ProfileRoutes.php
            ├── Views/
            └── Lang/
```

This structure keeps every feature isolated and allows workflows to be created, modified, or removed without affecting other workflows.

---

# Base Classes

MCF provides framework base classes located under:

```text
app/MCF/Base
```

Generated classes inherit from them automatically.

```text
ProfileController → MfcController
ProfileRequest    → MfcRequest
ProfileService    → MfcService
ProfilePolicy     → MfcPolicy
```

This allows framework functionality to be shared across every generated workflow while keeping generated code clean.

---

# Routes

Each workflow contains its own route file.

MCF recommends assigning names to routes using both the module name and workflow name.

Example:

```php
Route::get('/profile', [ProfileController::class, 'index'])
    ->name('users.profile.index');
```

Using named routes makes redirects, links, and future URL changes easier to maintain.

This naming convention is recommended but not required. Developers are free to use any route naming strategy that fits their application.

The application's entry route is defined inside:

```text
app/MCF/mcf_routes.php
```

Developers can change the default landing page by updating the named route used there.

---

# Default Layout Workflow

During installation, MCF automatically generates a default layout workflow:

```text
Module   : Shared
Workflow : Layout
```

The layout is **not** a special framework component.

It is simply a normal workflow generated using:

```bash
php artisan mcf:make:workflow:layout Shared Layout
```

Developers are free to modify it, rename it, delete it, recreate it, or create additional layout workflows as needed.

---

# Assets & Storage

MCF does not introduce a custom asset or storage system.

Use Laravel's standard locations:

- `public/` for CSS, JavaScript, images, fonts, and other public assets.
- `storage/` for uploaded files and filesystem storage.

This keeps MCF fully compatible with Laravel's native filesystem and deployment workflow.

---

# Documentation

Complete documentation is available inside the `docs` directory.

## Documentation Index

- Quick Start
- Architecture
- Folder Reference
- CLI Specification
- Generator Rules
- Coding Standards
- Best Practices
- Permissions
- Contributing

---

# Philosophy

MCF follows a few simple principles.

- Keep framework code isolated.
- Keep workflows self-contained.
- Reuse Laravel whenever possible.
- Avoid unnecessary boilerplate.
- Build modular applications that scale naturally.

---

# Requirements

- PHP 8.4+
- Laravel 12+

---

# License

This project is distributed under its respective license.