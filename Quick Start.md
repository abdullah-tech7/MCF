# Quick Start

## Installation

Install MCF into a fresh Laravel project:

```bash
php artisan mcf:install
```

During installation, MCF automatically creates the default application structure, including:

```text
Shared
└── Layout
```

The generated **Layout** is a normal Workflow created using:

```bash
php artisan mcf:make:workflow:layout Shared Layout
```

It is not reserved by the framework. You may modify it, rename it, delete it, recreate it, or create additional layout workflows whenever needed.

---

## Modules

Create a new module:

```bash
php artisan mcf:make:module Users
```

---

## Workflows

Create a workflow inside a module:

```bash
php artisan mcf:make:workflow Users Profile
```

Create a CRUD workflow:

```bash
php artisan mcf:make:workflow:crud Users UserManagement
```

Create a layout workflow:

```bash
php artisan mcf:make:workflow:layout Shared Layout
```

Remove a workflow:

```bash
php artisan mcf:remove:workflow Users Profile
```

> **Before creating your first Workflow, read the Workflow Rules at the bottom of this page.**

---

# Database

## Models

Create a model:

```bash
php artisan mcf:make:model User
```

Create a model with a migration:

```bash
php artisan mcf:make:model User -m
```

Create a model with a factory:

```bash
php artisan mcf:make:model User -f
```

Create a model with a seeder:

```bash
php artisan mcf:make:model User -s
```

Create a model with migration, factory, and seeder:

```bash
php artisan mcf:make:model User -mfs
```

---

## Migrations

Create a migration:

```bash
php artisan mcf:make:migration create_users_table
```

Create a migration for a new table:

```bash
php artisan mcf:make:migration create_users_table --create=users
```

Create a migration for an existing table:

```bash
php artisan mcf:make:migration add_email_to_users_table --table=users
```

---

## Factories

Create a factory:

```bash
php artisan mcf:make:factory UserFactory
```

Create a factory for a specific model:

```bash
php artisan mcf:make:factory UserFactory --model=User
```

---

## Seeders

Create a seeder:

```bash
php artisan mcf:make:seeder UserSeeder
```

---

# HTTP

## Middleware

Create a middleware:

```bash
php artisan mcf:make:middleware Auth
```

---

## Validation Rules

Create a validation rule:

```bash
php artisan mcf:make:rule StrongPassword
```

---

## Notifications

Create a notification:

```bash
php artisan mcf:make:notification OrderCreated
```

---

## Mail

Create a mailable:

```bash
php artisan mcf:make:mail WelcomeMail
```

---

# Routes

Register all application routes inside:

```text
app/MCF/mcf_routes.php
```

---

# Assets & Storage

MCF does not provide its own asset or storage system.

Use Laravel's native locations:

- `public/` for CSS, JavaScript, images, fonts, and other public assets.
- `storage/` for uploaded files and filesystem storage.

This keeps MCF fully compatible with Laravel's native filesystem and deployment workflow.

---

# Available Commands

| Command | Description |
|----------|-------------|
| `mcf:make:module` | Create a new module |
| `mcf:make:workflow` | Create a workflow |
| `mcf:make:workflow:crud` | Create a CRUD workflow |
| `mcf:make:workflow:layout` | Create a layout workflow |
| `mcf:remove:workflow` | Remove a workflow |
| `mcf:make:model` | Create a model |
| `mcf:make:migration` | Create a migration |
| `mcf:make:factory` | Create a factory |
| `mcf:make:seeder` | Create a seeder |
| `mcf:make:middleware` | Create a middleware |
| `mcf:make:rule` | Create a validation rule |
| `mcf:make:notification` | Create a notification |
| `mcf:make:mail` | Create a mailable |

---

# Workflow Design

## Introduction

MCF is a **Workflow-Driven Framework**.

Unlike traditional Laravel applications that are commonly organized around database models, MCF organizes applications around **business capabilities**.

Instead of starting with Models and building Controllers around them, MCF starts with a **Workflow** that represents a complete feature of your application.

The goal is simple:

> **Build your application around what the application does, not around what the database stores.**

A Workflow is the primary building block of every MCF application.

---

# Modules

Every Workflow belongs to a **Module**.

A Module is simply a container that groups related business capabilities.

Example:

```text
Modules
├── System
├── Users
├── Shop
└── Reports
```

A Module does not contain business logic.

Its purpose is to organize related Workflows.

---

# Workflows

A Workflow represents one complete business responsibility inside a Module.

Example:

```text
Users
├── Authentication
├── Profile
├── User Management
└── Roles
```

Here:

- **Users** is the Module.
- **Authentication**, **Profile**, **User Management**, and **Roles** are Workflows.

Each Workflow is responsible for one business capability.

---

# Layout Workflow

Layout is implemented as a normal Workflow.

The installer creates the following by default:

```text
Shared
└── Layout
```

Internally this is equivalent to:

```bash
php artisan mcf:make:workflow:layout Shared Layout
```

The generated layout contains the standard application layout and optional Blade components.

Example:

```text
Shared
└── Layout
    ├── Controllers
    ├── Services
    ├── Requests
    ├── Policies
    ├── Routes
    ├── Lang
    └── Views
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

The Layout Workflow is **not reserved** by MCF.

You are free to:

- Rename it.
- Delete it.
- Recreate it.
- Create multiple layout workflows.
- Customize the generated Blade files.

MCF treats Layout exactly like any other Workflow.

---

# Creating Your First Workflow

A Workflow cannot exist without a Module.

First create the Module.

```bash
php artisan mcf:make:module Users
```

Then create a Workflow inside that Module.

```bash
php artisan mcf:make:workflow Users Authentication
```

MCF generates the following structure.

```text
Users
└── Authentication
    ├── AuthenticationController.php
    ├── Requests
    │   └── AuthenticationRequest.php
    ├── Services
    │   └── AuthenticationService.php
    ├── Views
    ├── Routes
    │   └── Authentication.php
    ├── Lang
    │   └── Authentication/
    └── README.md
```

Every generated component uses the same name as the Workflow.

This naming convention is intentional.

Once you know one Workflow, you immediately know every Workflow in the application.

---

# Generated Components

MCF generates much more than a Controller.

It generates a complete feature structure where every file has a single responsibility.

---

## AuthenticationController

The Controller is the entry point of the Workflow.

Its responsibilities are to:

- Receive HTTP requests.
- Coordinate the Workflow.
- Call the Service.
- Return the response.

Controllers should remain small.

Business logic should never be written inside Controllers.

---

## AuthenticationRequest

Each Workflow owns one Request class.

Unlike traditional Laravel applications, MCF does not generate one Request for every action.

Instead, every validation related to the Workflow is centralized inside one predictable file.

```text
AuthenticationRequest.php
```

Whether the Workflow contains Login, Logout, Reset Password or any other operation, all validation belongs to the same Request class.

This keeps validation easy to locate and avoids unnecessary file fragmentation.

---

## AuthenticationService

Each Workflow owns one Service class.

The Service contains all business logic for the Workflow.

For example, the Authentication Service may contain methods such as:

- login()
- logout()
- forgotPassword()
- resetPassword()

Instead of creating multiple Service classes for one feature, all business logic remains inside a single Service dedicated to that Workflow.

---

## Views

Every Workflow owns its own Views directory.

All Blade files related to that Workflow remain together.

Generated workflows extend the default layout:

```blade
@extends('Shared.Layout.app')

@section('content')

@endsection
```

Instead of searching through one large global Views directory, everything for Authentication stays inside the Authentication Workflow.

---

## Routes

Each Workflow owns its own Route file.

```text
Routes
└── Authentication.php
```

This prevents route files from becoming large and difficult to maintain.

MCF automatically registers every generated Workflow route inside:

```text
app/MCF/mcf_routes.php
```

The developer only manages routes that belong to the current Workflow.

---

## Lang

Each Workflow owns its own language directory.

```text
Lang
└── Authentication
```

Translation files are optional.

If your application uses JSON translation files, MCF automatically discovers and merges them into the application's translation system.

Keeping translations inside the Workflow allows every feature to remain completely self-contained.

---

## README

Every Workflow contains its own README file.

The README documents the feature itself.

It may include:

- Business rules.
- Development notes.
- Technical decisions.
- Feature documentation.

Documentation stays next to the code instead of becoming outdated elsewhere.

---

# Why This Architecture?

As applications grow, code often becomes scattered across many unrelated directories.

Finding the Controller, Request, Service, Routes, translations, and documentation for a single feature may require searching the entire project.

MCF eliminates this problem.

Every Workflow is completely self-contained.

Everything related to one business capability lives inside one directory.

Developers always know where to find:

- Controller
- Request
- Service
- Views
- Routes
- Language files
- Documentation

There is no guessing.

There is no searching.

Every Workflow follows exactly the same architecture.


# Workflow Rules

## Rule 1 — A Workflow Represents a Goal

A Workflow should represent something the user wants to accomplish.

### ✔ Good

- Authentication
- User Management
- Profile
- Settings
- Dashboard

### ✘ Avoid

- User
- Product
- Order
- Role

Always think about what the user wants to achieve, not what tables exist in the database.

---

## Rule 2 — One Workflow, One Responsibility

Each Workflow should have one business responsibility.

Example:

**Authentication**

- Login
- Logout
- Forgot Password
- Reset Password

All of these belong to the Authentication Workflow because they describe the same business capability.

---

## Rule 3 — Actions Are Not Workflows

An action is not a Workflow.

Avoid creating Workflows such as:

- Export
- Delete
- Upload
- Print
- Import

These are actions that belong inside another Workflow.

---

## Rule 4 — Follow the User Journey

Ask yourself:

> Where does the user naturally begin this action?

Example:

```text
User Management
    └── Users List
            └── Export
```

Since the user starts from **User Management**, Export belongs to that Workflow.

---

## Rule 5 — Keep Related Actions Together

If multiple actions share the same business context, permissions, pages or data, they belong to the same Workflow.

Example:

**User Management**

- List Users
- Create User
- Edit User
- Delete User
- Export Users

These all describe one business capability.

---

## Rule 6 — A Workflow Must Be Independent

Ask yourself:

> Can this Workflow logically exist on its own?

If the answer is **No**, it probably belongs inside another Workflow.

---

## Rule 7 — Every Workflow Belongs to a Module

A Workflow cannot exist by itself.

It always belongs to exactly one Module.

Example:

```text
Users
└── Authentication
```

Here:

- **Users** is the Module.
- **Authentication** is the Workflow.

This relationship is mandatory throughout MCF.

---

## Rule 8 — Layout Is Just Another Workflow

The Layout Workflow follows the same architecture as every other Workflow.

It can be:

- Renamed.
- Deleted.
- Recreated.
- Replaced.
- Duplicated.

MCF does not reserve any special location for layouts.

The default Shared/Layout Workflow exists only because the installer creates it for convenience.

---

## Rule 9 — Never Name a Workflow After a Model

Models represent data.

Workflows represent business capabilities.

### ✘ Avoid

- User
- Product
- Role

### ✔ Prefer

- User Management
- Product Catalog
- Role Management

---

## Rule 10 — The Name Should Explain Itself

A Workflow name should immediately describe its purpose.

Example:

**Authentication**

A developer immediately understands what business capability this Workflow implements.

---

# The Golden Rule

Before creating a new Workflow, ask yourself:

> **If I delete this Workflow, what business capability disappears?**

### ✔ Correct

> User Management disappears.

### ✔ Correct

> Authentication disappears.

### ✘ Incorrect

> Export disappears.

### ✘ Incorrect

> Delete disappears.

Export and Delete are actions.

Authentication and User Management are business capabilities.

---

# Summary

Every MCF application follows the same architecture:

```text
Module
└── Workflow
    ├── Controller
    ├── Request
    ├── Service
    ├── Policy
    ├── Routes
    ├── Views
    ├── Lang
    └── README
```

Keep Workflows focused.

Keep business logic inside Services.

Keep related functionality together.

Design your application around business capabilities—not database tables.