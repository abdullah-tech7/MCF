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