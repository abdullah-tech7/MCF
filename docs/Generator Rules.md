# Coding Standards

## Overview

This document defines the coding standards used throughout the MCF codebase.

Following consistent conventions improves readability, maintainability, and long-term project stability.

All framework code should comply with these standards.

---

# General Principles

MCF follows these principles.

- Readability over cleverness.
- Consistency over personal preference.
- Explicit behavior over hidden magic.
- Simplicity over unnecessary abstraction.
- Laravel conventions whenever practical.

---

# PHP Version

MCF targets:

```text
PHP 8.4+
```

Framework code may use language features available in the minimum supported PHP version.

---

# Laravel Version

MCF is designed for:

```text
Laravel 12+
```

Generated code should remain compatible with supported Laravel releases.

---

# PSR Standards

MCF follows the PHP-FIG standards whenever applicable.

Including:

- PSR-1
- PSR-4
- PSR-12

Namespaces, autoloading, formatting, and file organization should comply with these standards.

---

# File Structure

Each PHP file should contain a single class, interface, trait, or enum.

Example:

```text
User.php
UserFactory.php
StrongPassword.php
WelcomeMail.php
```

Avoid defining multiple classes in the same file.

---

# Namespace Organization

Namespaces should reflect the directory structure.

Example:

```php
namespace App\MCF\Database\Models;
```

```php
namespace App\MCF\Notifications;
```

```php
namespace App\MCF\Rules;
```

Namespace names should never diverge from their physical location.

---

# Class Naming

Classes should use PascalCase.

Examples:

```text
User

Product

OrderService

WelcomeMail

StrongPassword

AuthenticateAdmin
```

Avoid abbreviations unless they are widely accepted.

---

# Method Naming

Methods should use camelCase.

Examples:

```php
createUser()

sendNotification()

registerModule()

buildWorkflow()
```

Method names should describe actions clearly.

---

# Property Naming

Properties should also use camelCase.

Examples:

```php
$userRepository

$modulePath

$routeFile
```

Avoid unnecessary abbreviations.

---

# Constants

Constants should use uppercase with underscores.

Example:

```php
DEFAULT_NAMESPACE

DEFAULT_STUB

FRAMEWORK_VERSION
```

---

# Imports

Always import classes instead of using fully qualified names throughout the file.

Preferred:

```php
use Illuminate\Support\Str;
```

Avoid:

```php
Str::studly(...)
```

with fully qualified namespaces inline.

Unused imports should be removed.

---

# Type Declarations

Use strict typing whenever possible.

Prefer explicit parameter and return types.

Example:

```php
public function handle(): int
```

instead of:

```php
public function handle()
```

---

# Visibility

Always declare visibility explicitly.

Preferred:

```php
private

protected

public
```

Avoid relying on default visibility.

---

# Documentation

Public classes and methods should be self-explanatory.

Use PHPDoc only when it provides meaningful information beyond what type declarations already express.

Avoid redundant comments.

Poor example:

```php
// Gets the user.
```

Better code is preferred over unnecessary comments.

---

# Formatting

Use four spaces for indentation.

Do not use tabs.

Keep blank lines meaningful.

Avoid excessive vertical spacing.

---

# Line Length

Favor readability over strict line limits.

Break long expressions across multiple lines when they become difficult to read.

---

# Conditionals

Prefer early returns over deeply nested conditions.

Preferred:

```php
if (! $moduleExists) {
    return;
}
```

instead of multiple nested blocks.

---

# Variables

Use descriptive variable names.

Good:

```php
$moduleName

$workflowPath

$migrationFile
```

Avoid vague names such as:

```php
$temp

$data

$value
```

unless their purpose is immediately obvious.

---

# Generator Design

Generator implementations should remain lightweight.

Business logic should be extracted into dedicated classes whenever complexity grows.

Command classes should primarily coordinate generation rather than contain extensive implementation details.

---

# Dependencies

Avoid unnecessary third-party dependencies.

Reuse Laravel components whenever possible.

Prefer framework-native solutions over introducing external packages.

---

# Error Handling

Error messages should be:

- Clear
- Actionable
- Consistent

Avoid exposing implementation details in user-facing console output.

---

# Backward Compatibility

Changes should preserve backward compatibility whenever practical.

Breaking changes should be introduced only when justified and documented.

---

# Code Review Principles

Before merging changes, verify that the implementation:

- Follows the project structure.
- Uses the correct namespace.
- Respects generator responsibilities.
- Produces predictable output.
- Maintains Laravel compatibility.
- Adheres to these coding standards.

---

# Summary

The objective of these standards is to ensure that every part of MCF remains:

- Consistent
- Readable
- Maintainable
- Predictable
- Extensible

Consistency is considered more valuable than individual coding style preferences.