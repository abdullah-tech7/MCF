# CLI Specification

---

# Overview

MCF provides a dedicated collection of Artisan commands for generating framework components.

Every command is prefixed with:

```text
mcf:
```

This clearly distinguishes MCF commands from Laravel's native Artisan commands while preventing naming conflicts.

All commands follow Laravel's command style and behavior whenever possible.

---

# Command Naming Convention

MCF generators follow a consistent naming pattern.

```text
mcf:make:<component>
```

Examples:

```text
mcf:make:module
mcf:make:workflow
mcf:make:workflow:crud
mcf:make:workflow:layout
mcf:make:model
mcf:make:mail
mcf:make:notification
```

Commands are intentionally descriptive and predictable.

---

# Available Commands

| Command | Description |
|----------|-------------|
| `mcf:make:module` | Create a new Module. |
| `mcf:make:workflow` | Create a standard Workflow. |
| `mcf:make:workflow:crud` | Create a CRUD Workflow. |
| `mcf:make:workflow:layout` | Create a Layout Workflow. |
| `mcf:remove:workflow` | Remove an existing Workflow. |
| `mcf:make:model` | Create an Eloquent Model. |
| `mcf:make:migration` | Create a Migration. |
| `mcf:make:factory` | Create a Model Factory. |
| `mcf:make:seeder` | Create a Database Seeder. |
| `mcf:make:middleware` | Create Middleware. |
| `mcf:make:rule` | Create a Validation Rule. |
| `mcf:make:notification` | Create a Notification. |
| `mcf:make:mail` | Create a Mailable. |

---

# Module Generator

## Command

```bash
php artisan mcf:make:module {module}
```

Example:

```bash
php artisan mcf:make:module Users
```

Creates a new Module.

Generated location:

```text
app/MCF/Modules/Users
```

Modules act as containers for related Workflows.

---

# Workflow Generator

## Command

```bash
php artisan mcf:make:workflow {module} {workflow}
```

Example:

```bash
php artisan mcf:make:workflow Users Authentication
```

Creates a standard Workflow inside the specified Module.

---

# CRUD Workflow Generator

## Command

```bash
php artisan mcf:make:workflow:crud {module} {workflow}
```

Example:

```bash
php artisan mcf:make:workflow:crud Users UserManagement
```

Creates a Workflow preconfigured for CRUD operations.

---

# Layout Workflow Generator

## Command

```bash
php artisan mcf:make:workflow:layout {module} {workflow}
```

Example:

```bash
php artisan mcf:make:workflow:layout Shared Layout
```

Creates a Layout Workflow.

Generated structure includes:

```text
Views
└── Layout
    ├── app.blade.php
    └── Components
        ├── head.blade.php
        ├── header.blade.php
        ├── navbar.blade.php
        ├── sidebar.blade.php
        ├── footer.blade.php
        ├── guest.blade.php
        └── auth.blade.php
```

This command is also used internally by the MCF installer.

Layout is treated like any other Workflow and is not reserved.

---

# Remove Workflow

## Command

```bash
php artisan mcf:remove:workflow {module} {workflow}
```

Example:

```bash
php artisan mcf:remove:workflow Users Authentication
```

Removes the specified Workflow.

The command:

- Deletes the Workflow directory.
- Removes its route registration from `app/MCF/mcf_routes.php`.

Force mode:

```bash
php artisan mcf:remove:workflow Users Authentication --force
```

Skips the confirmation prompt.

---

# Model Generator

## Command

```bash
php artisan mcf:make:model {model}
```

Example:

```bash
php artisan mcf:make:model User
```

Creates an Eloquent Model inside:

```text
app/MCF/Database/Models
```

Supported options:

```bash
-m
-f
-s
```

These generate:

- Migration
- Factory
- Seeder

respectively.

---

# Migration Generator

## Command

```bash
php artisan mcf:make:migration {name}
```

Example:

```bash
php artisan mcf:make:migration create_users_table
```

Creates a Migration inside:

```text
app/MCF/Database/Migrations
```

Supports Laravel's native migration options.

---

# Factory Generator

## Command

```bash
php artisan mcf:make:factory {name}
```

Example:

```bash
php artisan mcf:make:factory UserFactory
```

Creates a Factory inside:

```text
app/MCF/Database/Factories
```

Supports:

```bash
--model=User
```

---

# Seeder Generator

## Command

```bash
php artisan mcf:make:seeder {name}
```

Example:

```bash
php artisan mcf:make:seeder UserSeeder
```

Creates a Seeder inside:

```text
app/MCF/Database/Seeders
```

---

# Middleware Generator

## Command

```bash
php artisan mcf:make:middleware {name}
```

Example:

```bash
php artisan mcf:make:middleware AuthenticateAdmin
```

Creates Middleware inside:

```text
app/MCF/Middleware
```

---

# Rule Generator

## Command

```bash
php artisan mcf:make:rule {name}
```

Example:

```bash
php artisan mcf:make:rule StrongPassword
```

Creates a Validation Rule inside:

```text
app/MCF/Rules
```

---

# Notification Generator

## Command

```bash
php artisan mcf:make:notification {name}
```

Example:

```bash
php artisan mcf:make:notification WelcomeNotification
```

Creates a Notification inside:

```text
app/MCF/Notifications
```

Only the Notification class is generated.

---

# Mail Generator

## Command

```bash
php artisan mcf:make:mail {name}
```

Example:

```bash
php artisan mcf:make:mail WelcomeMail
```

Creates a Mailable inside:

```text
app/MCF/Mail
```

Only the Mailable class is generated.

---

# Design Principles

Every MCF command follows the same design philosophy.

- Consistent naming.
- Single responsibility.
- Predictable output locations.
- Laravel-compatible behavior.
- Minimal boilerplate.
- No hidden side effects.

Commands generate only the component they are responsible for unless explicitly documented otherwise.

---

# Summary

MCF's CLI is intentionally small, predictable, and focused.

Every command has one clear responsibility, follows Laravel conventions, and generates components within the MCF architecture without introducing unnecessary complexity.
