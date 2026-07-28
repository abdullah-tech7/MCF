# MCF Framework

> ⚠️ **Experimental:** This package is under active development. Use it for testing and evaluation only until a stable release is available.
> 

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

---

# Features

- Modular architecture
- Feature-based project organization
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
├── Assets/
├── Database/
│   ├── Factories/
│   ├── Migrations/
│   ├── Models/
│   └── Seeders/
├── Layouts/
├── Mail/
├── Middleware/
├── Modules/
├── Notifications/
├── Rules/
├── mcf_routes.php
└── README.md
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

Create a model:

```bash
php artisan mcf:make:model User
```

Create a migration:

```bash
php artisan mcf:make:migration create_users_table
```

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
- Keep generators focused on a single responsibility.
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