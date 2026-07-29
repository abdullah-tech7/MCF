# Generator Rules

---

# Overview

This document defines the official rules that every MCF code generator must follow.

All generators must produce predictable, consistent, and maintainable output while preserving Laravel conventions and MCF's Workflow-driven architecture.

These rules apply to every current and future generator provided by MCF.

---

# Core Principles

Every generator must follow these principles:

- Predictable output.
- Single responsibility.
- Consistent architecture.
- Laravel compatibility.
- Idempotent behavior.
- Minimal boilerplate.

Generated code should be immediately usable without additional restructuring.

---

# One Generator, One Responsibility

Every generator is responsible for generating exactly one type of component.

Examples:

| Generator | Responsibility |
|-----------|----------------|
| `mcf:make:module` | Create a Module |
| `mcf:make:workflow` | Create a Workflow |
| `mcf:make:workflow:crud` | Create a CRUD Workflow |
| `mcf:make:workflow:layout` | Create a Layout Workflow |
| `mcf:make:model` | Create an Eloquent Model |
| `mcf:make:migration` | Create a Migration |
| `mcf:make:factory` | Create a Factory |
| `mcf:make:seeder` | Create a Seeder |
| `mcf:make:middleware` | Create Middleware |
| `mcf:make:rule` | Create a Validation Rule |
| `mcf:make:notification` | Create a Notification |
| `mcf:make:mail` | Create a Mailable |

Generators should never perform unrelated work.

---

# Predictable Output

Every execution of the same generator should produce the same directory structure and file naming convention.

Developers should always know where generated files will be located.

Example:

```bash
php artisan mcf:make:workflow Users Authentication
```

Always generates:

```text
app/
└── MCF/
    └── Modules/
        └── Users/
            └── Authentication/
```

---

# Workflow Structure

Every Workflow generator must create the complete Workflow structure.

```text
Authentication
├── AuthenticationController.php
├── AuthenticationRequest.php
├── AuthenticationService.php
├── AuthenticationPolicy.php
├── AuthenticationRoutes.php
├── Views
├── Lang
└── README.md
```

Every Workflow must remain self-contained.

---

# Shared Base Classes

Generated Workflow components must inherit from the shared MCF base classes.

Controller:

```php
class AuthenticationController extends MfcController
```

Request:

```php
class AuthenticationRequest extends MfcRequest
```

Service:

```php
class AuthenticationService extends MfcService
```

Policy:

```php
class AuthenticationPolicy extends MfcPolicy
```

Base classes are located in:

```text
app/MCF/Base
```

---

# Generated Controllers

Controllers should contain only HTTP coordination.

Generated Controllers should:

- Receive requests.
- Delegate work to the Service.
- Return responses.
- Return Workflow Views.

Generated example:

```php
return view('Users::Authentication.index');
```

Business logic must never be generated inside Controllers.

---

# Generated Services

Services are responsible for business logic.

Generators should never place business logic in:

- Controllers
- Requests
- Policies
- Views

Generated Services should be intentionally lightweight and ready for implementation.

---

# Generated Requests

Every Workflow owns exactly one Request.

Generated Requests should inherit from:

```text
MfcRequest
```

Validation belongs here.

---

# Generated Policies

Every Workflow owns exactly one Policy.

Generated Policies inherit from:

```text
MfcPolicy
```

Generated Policies should contain authorization placeholders only.

Business authorization implementation belongs to the developer.

---

# Generated Views

Workflow generators create a dedicated Views directory.

Controllers should reference Workflow Views using the MCF namespace.

Example:

```php
return view('Users::Authentication.index');
```

Views remain inside their Workflow.

---

# Layout Generator

The Layout generator follows exactly the same Workflow architecture.

Example:

```bash
php artisan mcf:make:workflow:layout Shared Layout
```

Generated structure:

```text
Layout
├── LayoutController.php
├── LayoutRequest.php
├── LayoutService.php
├── LayoutPolicy.php
├── LayoutRoutes.php
├── Views
│   ├── index.blade.php
│   └── Components
│       ├── head.blade.php
│       ├── header.blade.php
│       ├── navbar.blade.php
│       ├── sidebar.blade.php
│       ├── footer.blade.php
│       ├── guest.blade.php
│       └── auth.blade.php
├── Lang
└── README.md
```

Generated Layout Controllers return:

```php
return view('Shared::Layout.index');
```

Shared Blade components are referenced using:

```blade
@include('Shared::Layout.Components.head')
```

Layout is treated like any other Workflow.

---

# Route Registration

Workflow generators must automatically register Workflow Routes through:

```text
app/MCF/mcf_routes.php
```

No manual registration should be required.

---

# Language Support

Every generated Workflow must include:

```text
Lang/
```

MCF automatically discovers Workflow translation files recursively.

No additional configuration should be generated.

---

# CRUD Generator

The CRUD generator follows the same Workflow architecture as the standard Workflow generator.

It additionally creates the files and scaffolding required for CRUD operations.

The generated structure must remain fully compatible with standard Workflows.

---

# Model Generator

The Model generator creates Models inside:

```text
app/MCF/Database/Models
```

Supported options:

```text
-m
-f
-s
```

Each option generates only the requested additional component.

---

# Migration Generator

Migration generators create files only inside:

```text
app/MCF/Database/Migrations
```

They should behave consistently with Laravel's migration generator.

---

# Factory Generator

Factories belong to:

```text
app/MCF/Database/Factories
```

The generator should support:

```bash
--model=User
```

---

# Seeder Generator

Seeders belong to:

```text
app/MCF/Database/Seeders
```

Generators should not modify `DatabaseSeeder`.

---

# Middleware Generator

Middleware belongs to:

```text
app/MCF/Middleware
```

Generators create Middleware only.

They should never modify routing or configuration automatically.

---

# Rule Generator

Validation Rules belong to:

```text
app/MCF/Rules
```

Generated Rules should contain only validation scaffolding.

---

# Notification Generator

Notifications belong to:

```text
app/MCF/Notifications
```

Only the Notification class should be generated.

---

# Mail Generator

Mailables belong to:

```text
app/MCF/Mail
```

Only the Mailable class should be generated.

---

# Naming Rules

Generators must preserve consistent naming.

Examples:

```text
AuthenticationController
AuthenticationRequest
AuthenticationService
AuthenticationPolicy
AuthenticationRoutes
```

Generated directories must use the Workflow name.

---

# Existing Files

Generators must never overwrite existing files without explicit user confirmation.

If a conflict is detected, generation should stop with a clear error message.

---

# Idempotency

Running the same generator multiple times should never silently corrupt an existing project.

Generators must fail safely when conflicts occur.

---

# Laravel Compatibility

Generators should follow Laravel conventions whenever possible.

Generated code should:

- Follow PSR-12.
- Follow Laravel Coding Style.
- Use dependency injection.
- Use Blade.
- Use Laravel routing.
- Use Laravel localization.

MCF extends Laravel rather than replacing it.

---

# Future Compatibility

Every generator should produce output compatible with future MCF versions whenever possible.

Generated projects should require minimal changes during framework upgrades.

---

# Design Principles

Every MCF generator follows these principles:

- Single Responsibility.
- Predictable Output.
- Consistent Naming.
- Workflow-Driven Architecture.
- Modular Structure.
- Laravel Compatibility.
- No Hidden Side Effects.
- Safe Generation.
- Minimal Boilerplate.

---

# Summary

Every MCF generator exists to automate project structure—not application behavior.

Generators create a predictable foundation by following the same architecture, naming conventions, and directory layout across every project.

The result is a consistent development experience where every generated component integrates naturally with the MCF architecture while remaining fully compatible with Laravel.
