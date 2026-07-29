# Architecture

---

# Overview

MCF (Modular Code Framework) is a modular architecture built on top of Laravel.

It extends Laravel without replacing its core components, allowing developers to organize applications using a feature-oriented architecture while preserving Laravel's conventions and ecosystem.

The framework introduces a dedicated application structure located under:

```text
app/MCF
```

This isolates framework components from Laravel's default directories and provides a predictable, scalable project layout.

---

# Design Goals

MCF is designed around several core objectives.

- Organize application code by business capabilities.
- Keep framework code isolated.
- Encourage Workflow-driven development.
- Reduce project complexity.
- Promote clean architecture.
- Promote single responsibility.
- Minimize boilerplate.
- Remain fully compatible with Laravel.

---

# Architecture Philosophy

MCF follows several architectural principles.

## Modular Development

Applications are divided into independent Modules.

Each Module groups related business capabilities.

Example:

```text
Modules
├── Users
├── Orders
├── Products
├── Reports
└── Settings
```

Modules organize Workflows.

Business logic lives inside Workflows rather than inside the Module itself.

---

## Workflow-Driven Development

The primary building block of every application is the **Workflow**.

Instead of organizing applications around Models, MCF organizes applications around complete business capabilities.

Example:

```text
Users
├── Authentication
├── Profile
├── User Management
└── Roles
```

Every Workflow owns its own:

- Controller
- Request
- Service
- Policy
- Views
- Routes
- Lang
- README

This keeps every feature self-contained.

---

## Laravel Compatibility

MCF does not replace Laravel.

Laravel remains responsible for:

- Routing
- Service Container
- Eloquent ORM
- Events
- Queues
- Validation
- Middleware Pipeline
- Artisan
- Blade
- Configuration
- Filesystem

MCF builds on top of Laravel rather than replacing it.

---

## Isolated Framework Structure

Framework-specific code is generated inside:

```text
app/MCF
```

instead of Laravel's default directories.

Example:

Instead of:

```text
app/Models
```

MCF uses:

```text
app/MCF/Database/Models
```

This keeps Laravel's default structure clean while keeping all framework-generated code together.

---

# High-Level Architecture

```text
Laravel Application
│
├── Laravel Core
│
├── app/
│   │
│   └── MCF/
│       ├── Base
│       ├── Database
│       ├── Mail
│       ├── Middleware
│       ├── Modules
│       ├── Notifications
│       ├── Rules
│       └── mcf_routes.php
│
└── Framework Services
```

---

# Separation of Responsibilities

Every directory inside MCF has a dedicated responsibility.

Examples:

- Models represent database entities.
- Migrations modify database structure.
- Factories generate test data.
- Seeders populate databases.
- Notifications deliver notifications.
- Mail contains mailables.
- Rules contain reusable validation logic.
- Middleware processes HTTP requests.
- Modules organize Workflows.
- Base contains the shared foundation classes used by every generated Workflow.

Responsibilities are intentionally separated to reduce coupling.

---

# Single Responsibility Principle

MCF generators follow the Single Responsibility Principle.

Each generator creates only one type of component.

Examples:

## Model Generator

Creates:

- Model

Optionally:

- Migration
- Factory
- Seeder

It does not create:

- Controller
- Request
- Policy
- Resource

---

## Notification Generator

Creates:

- Notification

It does not create:

- Mail
- Blade Views
- Markdown Templates

---

## Mail Generator

Creates:

- Mailable

It does not create:

- Blade Views
- Markdown Templates

---

## Rule Generator

Creates:

- Validation Rule

Nothing else.

---

# Framework Layers

```text
Presentation

↓

Application

↓

Modules

↓

Workflows

↓

Database

↓

Infrastructure
```

Each layer has a clearly defined responsibility.

Business logic should remain independent from infrastructure whenever practical.

---

# Modules

Modules are organizational boundaries.

Each Module groups related Workflows.

Example:

- Users
- Inventory
- Sales
- Reports
- Customer Service

Modules themselves should remain lightweight.

---

# Workflows

Workflows are the primary business units inside MCF.

Every Workflow belongs to exactly one Module.

A Workflow represents one complete business capability.

Examples:

- Authentication
- User Management
- Product Catalog
- Checkout
- Reports

MCF applications are designed around Workflows rather than Models.

---

# Layout Workflow

MCF does not reserve a special Layout system.

Instead, Layout is implemented as a normal Workflow.

The installer automatically creates:

```text
Shared
└── Layout
```

Internally this is equivalent to:

```bash
php artisan mcf:make:workflow:layout Shared Layout
```

The generated Layout Workflow contains the application's default layout and optional Blade components.

Example:

```text
Shared
└── Layout
    ├── LayoutController.php
    ├── LayoutRequest.php
    ├── LayoutService.php
    ├── LayoutPolicy.php
    ├── LayoutRoutes.php
    ├── Lang
    └── Views
        ├── index.blade.php
        └── Components
            ├── head.blade.php
            ├── header.blade.php
            ├── navbar.blade.php
            ├── sidebar.blade.php
            ├── footer.blade.php
            ├── guest.blade.php
            └── auth.blade.php
```

Layout is not reserved.

Developers may:

- Rename it.
- Delete it.
- Recreate it.
- Create multiple Layout Workflows.
- Customize it freely.

---

# Assets & Storage

MCF does not introduce its own asset management system.

Use Laravel's standard locations:

```text
public/
```

for public assets such as:

- CSS
- JavaScript
- Images
- Fonts

Use:

```text
storage/
```

for uploaded files and filesystem storage.

This keeps applications fully compatible with Laravel's deployment workflow.

---

# Database Layer

Database-related classes are grouped together.

```text
Database
├── Models
├── Migrations
├── Factories
└── Seeders
```

Keeping database components together improves discoverability and maintenance.

---

# Routing

Every Workflow owns its own route definition.

MCF automatically registers all generated Workflow routes through:

```text
app/MCF/mcf_routes.php
```

Each Workflow is responsible only for its own routes, keeping routing modular and maintainable.

---

# Extensibility

MCF is designed for future expansion.

New generators, Modules, and Workflows can be introduced without changing the overall project structure.

Because every feature follows the same architecture, extending the framework remains predictable.

---

# Predictable Structure

Every generated component has a predefined destination.

```text
Model
→ app/MCF/Database/Models

Migration
→ app/MCF/Database/Migrations

Factory
→ app/MCF/Database/Factories

Seeder
→ app/MCF/Database/Seeders

Notification
→ app/MCF/Notifications

Mail
→ app/MCF/Mail

Rule
→ app/MCF/Rules

Middleware
→ app/MCF/Middleware

Workflow
→ app/MCF/Modules/{Module}/{Workflow}
```

Developers always know where generated code will be located.

---

# Design Principles

MCF follows these architectural principles.

- Workflow-Driven
- Modular
- Predictable
- Maintainable
- Extensible
- Laravel Compatible
- Low Coupling
- High Cohesion
- Single Responsibility
- Separation of Concerns

These principles guide both the framework itself and applications built with MCF.

---

# Workflow Design

---

# Introduction

MCF is a **Workflow-Driven Framework**.

Unlike traditional Laravel applications that are commonly organized around database models, MCF organizes applications around **business capabilities**.

Instead of starting with Models and building Controllers around them, MCF starts with a **Workflow** that represents a complete business capability.

The goal is simple:

> **Build your application around what the application does, not around what the database stores.**

A Workflow is the primary building block of every MCF application.

---

# Modules

Every Workflow belongs to exactly one **Module**.

A Module groups related Workflows into a single business domain.

Example:

```text
Modules
├── Users
├── Shop
├── Reports
└── System
```

A Module does not contain business logic.

Its responsibility is to organize Workflows.

---

# Workflows

A Workflow represents one complete business capability.

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

Every Workflow owns everything required to implement that capability.

---

# Layout Workflow

Layout is implemented as a normal Workflow.

The installer automatically creates:

```text
Shared
└── Layout
```

using:

```bash
php artisan mcf:make:workflow:layout Shared Layout
```

The generated structure looks like:

```text
Shared
└── Layout
    ├── LayoutController.php
    ├── LayoutRequest.php
    ├── LayoutService.php
    ├── LayoutPolicy.php
    ├── LayoutRoutes.php
    ├── Lang
    └── Views
        ├── index.blade.php
        └── Components
            ├── head.blade.php
            ├── header.blade.php
            ├── navbar.blade.php
            ├── sidebar.blade.php
            ├── footer.blade.php
            ├── guest.blade.php
            └── auth.blade.php
```

Layout is not reserved by the framework.

Developers are free to:

- Rename it.
- Delete it.
- Recreate it.
- Create additional Layout Workflows.
- Customize it freely.

Generated Workflow controllers return:

```php
return view('Users::Authentication.index');
```

Layout components are referenced using:

```blade
@include('Shared::Layout.Components.head')
```

---

# Creating Your First Workflow

A Workflow cannot exist without a Module.

First create a Module.

```bash
php artisan mcf:make:module Users
```

Then create a Workflow.

```bash
php artisan mcf:make:workflow Users Authentication
```

MCF generates:

```text
Users
└── Authentication
    ├── AuthenticationController.php
    ├── AuthenticationRequest.php
    ├── AuthenticationService.php
    ├── AuthenticationPolicy.php
    ├── AuthenticationRoutes.php
    ├── Views
    ├── Lang
    └── README.md
```

Every generated component uses the Workflow name.

This predictable convention makes every Workflow immediately recognizable.

---

# Generated Components

MCF generates a complete feature structure rather than only a Controller.

Every generated file has one responsibility.

---

## Controller

The Controller is the HTTP entry point.

Responsibilities:

- Receive requests.
- Coordinate the Workflow.
- Call the Service.
- Return responses.

Business logic should never be written inside Controllers.

---

## Request

Each Workflow owns one Request class.

Validation for the Workflow is centralized inside this class.

Instead of generating many Request classes, MCF keeps validation predictable and easy to locate.

---

## Service

Each Workflow owns one Service.

The Service contains the business logic.

Example:

**AuthenticationService**

- login()
- logout()
- forgotPassword()
- resetPassword()

All Workflow logic remains inside one Service.

---

## Policy

Each Workflow owns its own Policy.

Authorization rules remain together with the Workflow instead of being scattered across the application.

---

## Views

Each Workflow owns its own Views directory.

Generated Workflow controllers return:

```php
return view('Users::Authentication.index');
```

Layout components are referenced using:

```blade
@include('Shared::Layout.Components.head')
```

All Blade files remain together with the Workflow.

---

## Routes

Each Workflow owns its own route definition.

```text
AuthenticationRoutes.php
```

MCF automatically registers all Workflow routes through:

```text
app/MCF/mcf_routes.php
```

---

## Lang

Each Workflow owns its own language directory.

```text
Lang
```

Translation files are optional.

MCF recursively discovers translation files inside every Workflow's `Lang` directory.

Keeping translations beside the Workflow improves discoverability.

---

# Why This Architecture?

As applications grow, code becomes scattered across multiple directories.

Finding the Controller, Request, Service, Policy, Routes, translations, and documentation for one feature becomes increasingly difficult.

MCF solves this by keeping every business capability completely self-contained.

Every Workflow contains:

- Controller
- Request
- Service
- Policy
- Views
- Routes
- Lang
- README

Developers always know where everything belongs.

There is no guessing.

There is no searching.

Every Workflow follows exactly the same architecture.

---

# Workflow Rules

## Rule 1 — A Workflow Represents a Goal

A Workflow represents something the user wants to accomplish.

### ✔ Good

- Authentication
- User Management
- Profile
- Dashboard
- Settings

### ✘ Avoid

- User
- Product
- Order
- Role

Think about business capabilities, not database tables.

---

## Rule 2 — One Workflow, One Responsibility

Each Workflow should own one business responsibility.

Example:

**Authentication**

- Login
- Logout
- Forgot Password
- Reset Password

These all belong to Authentication.

---

## Rule 3 — Actions Are Not Workflows

Avoid creating Workflows such as:

- Export
- Delete
- Upload
- Import
- Print

These are actions.

Actions belong inside another Workflow.

---

## Rule 4 — Follow the User Journey

Ask yourself:

> Where does the user naturally begin this task?

Example:

```text
User Management
└── Users List
    └── Export
```

Export belongs to User Management.

---

## Rule 5 — Keep Related Actions Together

If actions share:

- Business context
- Permissions
- Views
- Data

they belong inside the same Workflow.

---

## Rule 6 — A Workflow Must Be Independent

Ask:

> Can this Workflow logically exist on its own?

If not, it probably belongs inside another Workflow.

---

## Rule 7 — Every Workflow Belongs to a Module

Every Workflow belongs to exactly one Module.

Example:

```text
Users
└── Authentication
```

This relationship is mandatory.

---

## Rule 8 — Layout Is Just Another Workflow

Layout follows the same architecture as every other Workflow.

It may be:

- Renamed
- Deleted
- Recreated
- Replaced
- Duplicated

The default `Shared/Layout` exists only because the installer creates it.

---

## Rule 9 — Never Name a Workflow After a Model

Models describe data.

Workflows describe business capabilities.

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

A Workflow name should immediately describe its responsibility.

---

## Rule 11 — Every Workflow Uses the Same Foundation

Every generated Workflow inherits from the MCF base classes.

```text
app/MCF/Base

├── MfcController.php
├── MfcRequest.php
├── MfcService.php
└── MfcPolicy.php
```

Every generated Workflow uses these base classes to provide a consistent architecture across the framework.

---

# The Golden Rule

Before creating a Workflow ask yourself:

> **If I delete this Workflow, what business capability disappears?**

✔ Authentication disappears.

✔ User Management disappears.

✘ Export disappears.

✘ Delete disappears.

Export and Delete are actions.

Authentication and User Management are business capabilities.

---

# Summary

Every MCF application follows the same structure.

```text
Module
└── Workflow
    ├── WorkflowController.php
    ├── WorkflowRequest.php
    ├── WorkflowService.php
    ├── WorkflowPolicy.php
    ├── WorkflowRoutes.php
    ├── Views
    ├── Lang
    └── README.md
```

Every generated Workflow inherits from:

```text
MfcController
MfcRequest
MfcService
MfcPolicy
```

Keep Workflows focused.

Keep business logic inside Services.

Keep validation inside Requests.

Keep authorization inside Policies.

Keep related functionality together.

Build applications around business capabilities—not database tables.