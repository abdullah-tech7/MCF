# Architecture

---

# Overview

MCF (Modular Code Framework) is a modular architecture built on top of Laravel.

It extends Laravel without replacing its core components, allowing developers to organize applications using a feature-based structure while preserving Laravel's conventions and ecosystem.

The framework introduces a dedicated application structure located under:

```text
app/MCF
```

This isolates framework components from Laravel's default directories and provides a predictable, scalable project layout.

---

# Design Goals

MCF is designed around several core objectives.

- Organize application code by feature.
- Keep framework code isolated.
- Reduce project complexity.
- Encourage clean architecture.
- Promote single responsibility.
- Minimize boilerplate.
- Remain fully compatible with Laravel.

---

# Architecture Philosophy

MCF follows several architectural principles.

## Modular Development

Applications are divided into independent modules.

Each module represents a business feature instead of a technical layer.

Example:

```text
Modules
├── Users
├── Orders
├── Products
├── Reports
└── Settings
```

Each module owns its own business logic.

---

## Feature-Based Organization

Instead of grouping code by type, MCF groups code by functionality.

Business features remain isolated and easier to maintain as applications grow.

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

MCF builds on top of these components rather than replacing them.

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

This keeps Laravel's default structure clean and prevents framework code from being scattered across the application.

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
│       ├── Assets
│       ├── Database
│       ├── Layouts
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
- Middleware handles HTTP requests.
- Modules contain business features.

Responsibilities are intentionally separated to reduce coupling.

---

# Single Responsibility Principle

MCF generators follow the Single Responsibility Principle.

Each generator creates only one type of component.

Examples:

Model Generator

Creates:

- Model

Optionally:

- Migration
- Factory
- Seeder

It does not create:

- Controller
- Policy
- Request
- Resource

---

Notification Generator

Creates:

- Notification

It does not create:

- Mail
- Markdown Templates
- Blade Views

---

Mail Generator

Creates:

- Mailable

It does not create:

- Blade Views
- Markdown Templates

---

Rule Generator

Creates:

- Validation Rule

Nothing else.

---

# Framework Layers

MCF separates responsibilities into several logical layers.

```text
Presentation

↓

Application

↓

Business Modules

↓

Database

↓

Infrastructure
```

Each layer has a clearly defined purpose.

Business logic should remain independent from infrastructure whenever possible.

---

# Modular Structure

Modules are the primary building blocks of the framework.

Every module should represent a complete business capability.

Examples:

- User Management
- Inventory
- Sales
- Reporting
- Customer Service

Modules should remain independent from each other whenever practical.

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

Keeping database components together simplifies maintenance and improves discoverability.

---

# Routing

MCF routes are separated from Laravel's default route files.

Framework routes are registered inside:

```text
app/MCF/mcf_routes.php
```

This avoids unnecessary growth of Laravel's default routing files.

---

# Extensibility

The architecture has been designed for future expansion.

New generators and framework components can be introduced without changing the existing project structure.

Reserved directories such as Assets and Layouts provide dedicated locations for future capabilities.

---

# Predictable Structure

Every generated component has a predefined destination.

Examples:

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
```

Developers always know where generated code will be located.

---

# Design Principles

MCF follows these architectural principles.

- Modular
- Predictable
- Maintainable
- Extensible
- Laravel Compatible
- Feature-Oriented
- Low Coupling
- High Cohesion
- Single Responsibility
- Clean Separation of Concerns

These principles guide both the framework implementation and application development using MCF.


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

```
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

```
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

```
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

```
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

Instead of searching through one large global Views directory, everything for Authentication stays inside the Authentication Workflow.

---

## Routes

Each Workflow owns its own Route file.

```
Routes
└── Authentication.php
```

This prevents route files from becoming large and difficult to maintain.

MCF automatically discovers every Workflow Route file and registers it inside the application's main routing system.

The developer only manages routes that belong to the current Workflow.

---

## Lang

Each Workflow owns its own language directory.

```
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

---

# Workflow Rules

## Rule 1 — A Workflow Represents a Goal

A Workflow should represent something the user wants to accomplish.

### ✔ Good

- Authentication
- User Management
- Profile
- Settings

### ✘ Avoid

- User
- Product
- Order

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

These are actions that belong inside another Workflow.

---

## Rule 4 — Follow the User Journey

Ask yourself:

> Where does the user naturally begin this action?

Example:

```
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

## Rule 7 — Never Name a Workflow After a Model

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

## Rule 8 — The Name Should Explain Itself

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

### ✘ Incorrect

> Export disappears.

Export is an action.

User Management is a business capability.