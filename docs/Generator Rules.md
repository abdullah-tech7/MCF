# Coding Standards

---

# Overview

This document defines the official coding standards used throughout the MCF codebase.

These standards ensure that every MCF project remains consistent, predictable, maintainable, and fully compatible with Laravel.

All framework code and generated components should comply with these guidelines.

---

# General Principles

MCF follows these core principles.

- Readability over cleverness.
- Consistency over personal preference.
- Explicit behavior over hidden magic.
- Simplicity over unnecessary abstraction.
- Workflow-Driven Architecture.
- Laravel conventions whenever practical.

Framework code should be easy to understand before it is easy to optimize.

---

# PHP Version

MCF targets:

```text
PHP 8.4+
```

Framework code may freely use language features available in the minimum supported PHP version.

---

# Laravel Version

MCF targets:

```text
Laravel 12+
```

Generated code should remain compatible with supported Laravel releases.

MCF extends Laravel rather than replacing it.

---

# PSR Standards

MCF follows PHP-FIG standards whenever applicable.

Including:

- PSR-1
- PSR-4
- PSR-12

Namespaces, formatting, autoloading, and file organization should comply with these standards.

---

# File Organization

Each PHP file should contain exactly one:

- Class
- Interface
- Trait
- Enum

Examples:

```text
User.php

AuthenticationService.php

StrongPassword.php

WelcomeMail.php
```

Avoid defining multiple classes in a single file.

---

# Namespace Organization

Namespaces should always match the physical directory structure.

Examples:

```php
namespace App\MCF\Database\Models;
```

```php
namespace App\MCF\Notifications;
```

```php
namespace App\MCF\Rules;
```

Workflow components should follow the same convention.

Example:

```php
namespace App\MCF\Modules\Users\Authentication\Services;
```

Namespaces should never diverge from their directory location.

---

# Class Naming

Classes should use PascalCase.

Examples:

```text
User

AuthenticationController

AuthenticationService

StrongPassword

WelcomeMail

AuthenticateAdmin
```

Avoid abbreviations unless they are universally recognized.

---

# Module Naming

Modules should:

- Use PascalCase.
- Represent a business domain.
- Be concise.

Examples:

```text
Users

Reports

Inventory

Accounting
```

Avoid:

```text
UsersModule

MyUsers
```

---

# Workflow Naming

Workflow names describe business capabilities.

Examples:

```text
Authentication

UserManagement

Checkout

Profile

PasswordReset
```

Avoid names based on database tables.

Poor examples:

```text
User

Product

Order
```

---

# Method Naming

Methods should use camelCase.

Examples:

```php
createUser()

registerWorkflow()

removeWorkflow()

publishModule()
```

Method names should describe actions.

---

# Property Naming

Properties should also use camelCase.

Examples:

```php
$modulePath

$workflowName

$routeFile

$serviceClass
```

Avoid vague names whenever possible.

---

# Constants

Constants should use uppercase with underscores.

Examples:

```php
FRAMEWORK_VERSION

DEFAULT_NAMESPACE

DEFAULT_LAYOUT
```

---

# Imports

Always import classes instead of repeatedly using fully qualified class names.

Preferred:

```php
use Illuminate\Support\Str;
```

Avoid:

```php
\Illuminate\Support\Str::studly(...);
```

Remove unused imports.

---

# Type Declarations

Use explicit type declarations whenever possible.

Prefer:

```php
public function handle(): int
```

instead of:

```php
public function handle()
```

Use typed properties and typed parameters.

---

# Strict Typing

Every PHP file should begin with:

```php
declare(strict_types=1);
```

unless there is a documented reason not to.

MCF favors explicit typing.

---

# Visibility

Always declare visibility explicitly.

Examples:

```php
public

protected

private
```

Never rely on PHP defaults.

---

# Constructor Injection

Prefer constructor dependency injection.

Example:

```php
public function __construct(
    AuthenticationService $service
) {}
```

Avoid resolving dependencies manually.

```php
app(AuthenticationService::class);
```

Use manual resolution only when required.

---

# Static Methods

Avoid unnecessary static methods.

Prefer dependency injection and object instances.

Static methods should be limited to stateless utility scenarios.

---

# Documentation

Public APIs should be self-explanatory.

Use PHPDoc only when it adds information that type declarations cannot express.

Avoid comments like:

```php
// Gets the user.
```

Good names reduce the need for comments.

---

# Formatting

Follow Laravel's coding style.

Rules:

- Four spaces.
- No tabs.
- Meaningful blank lines.
- Consistent spacing.

Avoid excessive vertical whitespace.

---

# Line Length

Favor readability.

Long expressions should be split across multiple lines when necessary.

No strict maximum line length is enforced.

---

# Conditionals

Prefer early returns.

Good:

```php
if (! $moduleExists) {
    return;
}
```

Avoid deeply nested conditional structures.

---

# Variables

Use descriptive variable names.

Good:

```php
$workflowDirectory

$controllerClass

$moduleName

$routeFile
```

Avoid:

```php
$temp

$data

$value
```

unless the context is immediately obvious.

---

# Controllers

Controllers coordinate HTTP requests.

Controllers should:

- Receive Requests.
- Call Services.
- Return Responses.

Controllers should never contain:

- Business logic.
- Database queries.
- Complex calculations.

Controllers should remain thin.

---

# Services

Workflow Services contain business logic.

Responsibilities include:

- Business rules.
- Transactions.
- Database coordination.
- Event dispatching.

Services should never return Blade Views.

---

# Requests

Workflow Requests handle:

- Validation.
- Basic authorization.

Reusable validation belongs inside:

```text
app/MCF/Rules
```

---

# Policies

Policies contain authorization logic.

Responsibilities include:

- Permissions.
- Role checks.
- Access rules.

Policies should never contain business logic.

---

# Views

Views handle presentation only.

Views should never contain:

- Business logic.
- Database queries.
- Service calls.

Generated Workflow Views extend:

```blade
@extends('Shared.Layout.app')
```

---

# Layout Workflow

Layout is implemented as a Workflow.

Responsibilities include:

- Shared layouts.
- Navigation.
- Sidebar.
- Header.
- Footer.
- Shared Blade Components.

Business logic must never appear inside Layouts.

---

# Routes

Each Workflow owns one Route file.

Route files should contain only route definitions.

Workflow Routes are automatically registered through:

```text
app/MCF/mcf_routes.php
```

---

# Generator Design

Generator implementations should remain lightweight.

Command classes should coordinate generation.

Complex implementation should be extracted into dedicated classes whenever necessary.

Each generator should have one responsibility.

---

# Dependencies

Avoid unnecessary third-party packages.

Reuse Laravel components whenever possible.

Prefer framework-native solutions.

---

# Error Handling

Error messages should be:

- Clear.
- Actionable.
- Consistent.

Avoid exposing implementation details through console output.

---

# Backward Compatibility

Preserve backward compatibility whenever practical.

Breaking changes should be:

- Intentional.
- Documented.
- Justified.

---

# Code Review Checklist

Before merging, verify that the implementation:

- Follows the MCF architecture.
- Uses the correct namespace.
- Respects Workflow organization.
- Generates predictable output.
- Preserves Laravel compatibility.
- Follows the Coding Standards.
- Includes documentation when required.

---

# Summary

The objective of these standards is to ensure that every MCF project remains:

- Workflow-Driven
- Consistent
- Readable
- Predictable
- Maintainable
- Extensible
- Compatible with Laravel

Consistency is considered more valuable than individual coding style preferences.