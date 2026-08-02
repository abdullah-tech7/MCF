# Database

MCF uses Laravel's native database architecture without modification.

Database components remain in their default Laravel locations.

```text
database/
app/Models/
```

This includes:

- Models
- Migrations
- Factories
- Seeders

MCF does not replace Laravel's database layer.

Instead, it builds business features on top of Laravel while remaining fully compatible with its database ecosystem.

---

# Database Philosophy

MCF follows one simple principle:

> **Laravel manages persistence. MCF manages business workflows.**

Database resources belong to the application, not to individual Workflows.

This allows multiple Workflows to reuse the same Models without duplication.

---

# Supported Components

MCF works directly with Laravel's native database components:

- Eloquent Models
- Migrations
- Factories
- Seeders

No custom database structure is required.

Developers continue using Laravel's standard conventions.

---

# Database Structure

MCF uses Laravel's default structure.

```text
app
├── Models
└── ...

database
├── factories
├── migrations
└── seeders
```

This keeps the project fully compatible with Laravel tools and packages.

---

# Models

Models represent application data.

Generate a Model using Laravel's standard command.

```bash
php artisan make:model
```

Generated location:

```text
app/Models
```

Models are shared across the entire application.

A Model represents data.

It should not represent business workflows.

---

# Migrations

Migrations define the database schema.

Generate a Migration using Laravel.

```bash
php artisan make:migration
```

Generated location:

```text
database/migrations
```

MCF does not change Laravel's migration system.

---

# Factories

Factories generate model instances for testing and seeding.

Generate a Factory using Laravel.

```bash
php artisan make:factory
```

Generated location:

```text
database/factories
```

Factories remain fully compatible with Laravel testing tools.

---

# Seeders

Seeders populate the database with initial or sample data.

Generate a Seeder using Laravel.

```bash
php artisan make:seeder
```

Generated location:

```text
database/seeders
```

Seeders are commonly used for:

- Initial application setup
- Development environments
- Automated testing
- Reference data

---

# Relationship Between Workflows and Models

Workflows do not own Models.

Example:

```text
Users Module

├── Authentication
├── Profile
├── User Management
└── Settings
```

All of these Workflows may use the same:

```text
User Model
```

Likewise, one Workflow may coordinate multiple Models.

Business capabilities and database entities are independent concepts.

---

# Business Logic

Business logic should not be placed inside Models, Migrations, Factories or Seeders.

Business logic belongs inside Workflow Services.

Database components should focus only on persistence and data representation.

---

# Database Independence

Workflow Services should not depend on a specific database implementation.

They communicate with Models and repositories while Laravel handles persistence.

This allows applications to evolve without affecting Workflow architecture.

---

# Laravel Compatibility

Because MCF uses Laravel's native database structure, developers can use the complete Laravel ecosystem without additional configuration.

Examples include:

- Eloquent ORM
- Relationships
- Query Builder
- Model Scopes
- Observers
- Factories
- Seeders
- Migrations
- Database Transactions
- Third-party database packages
- Model generators
- Database utilities

---

# Summary

MCF does not introduce a custom database architecture.

Instead, it adopts Laravel's native database structure and conventions while focusing on business architecture.

Laravel is responsible for persistence.

MCF is responsible for organizing business workflows.

This separation keeps applications familiar to Laravel developers while allowing MCF to remain lightweight, extensible and fully compatible with the Laravel ecosystem.