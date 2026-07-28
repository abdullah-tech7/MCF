# CLI Specification

## Overview

MCF provides a dedicated set of Artisan commands for generating framework components.

All commands are prefixed with:

```text
mcf:
```

This prevents conflicts with Laravel's native Artisan commands while clearly identifying MCF-generated components.

---

# Command Naming Convention

All generators follow a consistent naming convention.

```text
mcf:make:<component>
```

Examples:

```text
mcf:make:model
mcf:make:module
mcf:make:workflow
mcf:make:mail
mcf:make:notification
```

This convention mirrors Laravel's Artisan style while remaining isolated.

---

# Available Commands

| Command | Description |
|----------|-------------|
| `mcf:make:module` | Create a new module. |
| `mcf:make:workflow` | Create a workflow inside a module. |
| `mcf:make:workflow:crud` | Generate a CRUD workflow. |
| `mcf:make:model` | Create an Eloquent model. |
| `mcf:make:migration` | Create a migration. |
| `mcf:make:factory` | Create a model factory. |
| `mcf:make:seeder` | Create a database seeder. |
| `mcf:make:middleware` | Create middleware. |
| `mcf:make:rule` | Create a validation rule. |
| `mcf:make:notification` | Create a notification. |
| `mcf:make:mail` | Create a mailable. |

---

# Module Generator

## Command

```bash
php artisan mcf:make:module {name}
```

## Example

```bash
php artisan mcf:make:module Users
```

Creates a new business module inside:

```text
app/MCF/Modules
```

---

# Workflow Generator

## Command

```bash
php artisan mcf:make:workflow {module} {workflow}
```

## Example

```bash
php artisan mcf:make:workflow Users Profile
```

Creates a workflow inside an existing module.

---

# CRUD Workflow Generator

## Command

```bash
php artisan mcf:make:workflow:crud {module} {workflow}
```

## Example

```bash
php artisan mcf:make:workflow:crud Users Profile
```

Generates a CRUD-oriented workflow structure.

---

# Model Generator

## Command

```bash
php artisan mcf:make:model {name}
```

## Example

```bash
php artisan mcf:make:model User
```

Creates an Eloquent model inside:

```text
app/MCF/Database/Models
```

---

# Migration Generator

## Command

```bash
php artisan mcf:make:migration {name}
```

## Example

```bash
php artisan mcf:make:migration create_users_table
```

Creates a migration inside:

```text
app/MCF/Database/Migrations
```

---

# Factory Generator

## Command

```bash
php artisan mcf:make:factory {name}
```

## Example

```bash
php artisan mcf:make:factory UserFactory
```

Creates a factory inside:

```text
app/MCF/Database/Factories
```

---

# Seeder Generator

## Command

```bash
php artisan mcf:make:seeder {name}
```

## Example

```bash
php artisan mcf:make:seeder UserSeeder
```

Creates a seeder inside:

```text
app/MCF/Database/Seeders
```

---

# Middleware Generator

## Command

```bash
php artisan mcf:make:middleware {name}
```

## Example

```bash
php artisan mcf:make:middleware AuthenticateAdmin
```

Creates middleware inside:

```text
app/MCF/Middleware
```

---

# Rule Generator

## Command

```bash
php artisan mcf:make:rule {name}
```

## Example

```bash
php artisan mcf:make:rule StrongPassword
```

Creates a validation rule inside:

```text
app/MCF/Rules
```

---

# Notification Generator

## Command

```bash
php artisan mcf:make:notification {name}
```

## Example

```bash
php artisan mcf:make:notification WelcomeNotification
```

Creates a notification inside:

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

## Example

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

All MCF commands follow the same principles:

- Consistent naming.
- Single responsibility.
- Predictable output locations.
- Laravel-compatible behavior.
- Minimal boilerplate.
- No hidden side effects.

Each command generates only the component it is responsible for unless explicitly documented otherwise.