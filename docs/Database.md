# Database

MCF provides generators for Laravel database components while keeping the framework's modular architecture intact.

The framework does not replace Laravel's database system.

Instead, it organizes database-related resources in a predictable location and automates their creation through Artisan commands.

---

# Database Philosophy

MCF follows one simple principle:

> **Business features live inside Workflows. Database components remain centralized.**

Database resources are shared across the application and are not owned by individual Workflows.

This keeps the domain model independent while allowing multiple Workflows to use the same database entities.

---

# Supported Components

MCF supports generating the following Laravel database components:

- Models
- Migrations
- Factories
- Seeders

All generated files remain fully compatible with Laravel.

---

# Database Structure

Database-related files are organized under the MCF database directory.

```text
app
└── MCF
    └── Database
        ├── Models
        ├── Migrations
        ├── Factories
        └── Seeders
```

Each directory has a dedicated responsibility.

---

# Models

Models represent application data.

Generate a Model:

```bash
php artisan mcf:make:model
```

Generated location:

```text
app/MCF/Database/Models
```

Models may be shared across multiple Modules and Workflows.

A Model should represent data.

It should not represent business workflows.

---

# Migrations

Migrations define the database schema.

Generate a Migration:

```bash
php artisan mcf:make:migration
```

Generated location:

```text
app/MCF/Database/Migrations
```

Migrations allow database schemas to evolve through version-controlled changes.

---

# Factories

Factories generate model instances for testing and seeding.

Generate a Factory:

```bash
php artisan mcf:make:factory
```

Generated location:

```text
app/MCF/Database/Factories
```

Factories help create consistent test data while reducing repetitive setup code.

---

# Seeders

Seeders populate the database with initial or sample data.

Generate a Seeder:

```bash
php artisan mcf:make:seeder
```

Generated location:

```text
app/MCF/Database/Seeders
```

Seeders are useful for:

- Initial application setup.
- Development environments.
- Automated testing.
- Demo data.

---

# Relationship Between Workflows and Models

A common misconception is that one Workflow should own one Model.

MCF intentionally avoids this relationship.

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

Business capabilities and database entities should remain independent concepts.

---

# Shared Models

Models are application-wide resources.

Examples:

```text
User
Product
Category
Order
Role
Permission
Invoice
```

These Models may be used by many different Workflows.

Keeping Models centralized avoids duplication and encourages reuse.

---

# Business Logic

Business logic should never be placed inside Migrations or Factories.

Business logic belongs inside Workflow Services.

Database components should focus on persistence and data representation.

This separation improves maintainability and keeps responsibilities clear.

---

# Migration Workflow

A typical database change follows this process.

```text
Generate Migration

↓

Modify Schema

↓

Run Migration

↓

Update Model

↓

Use Model Inside Workflows
```

Each step has a clear responsibility.

---

# Database Independence

Workflows should not depend on a particular database engine.

MCF remains compatible with Laravel's supported database drivers, including:

- MySQL
- PostgreSQL
- SQLite
- SQL Server

Changing the database engine should not require changes to Workflow architecture.

---

# Model Reuse

Avoid creating duplicate Models for similar concepts.

Good:

```text
User
```

Used by:

- Authentication
- Profile
- User Management
- Notifications

Avoid:

```text
AuthenticationUser
ProfileUser
ManagementUser
```

A single Model should represent a single data entity.

---

# Keep Models Focused

Models should describe application data.

Avoid placing Workflow-specific business processes inside Models.

Workflow coordination belongs to Services.

Models should remain reusable across different business capabilities.

---

# Factories and Testing

Factories should generate realistic data that reflects application requirements.

Using Factories consistently improves:

- Automated testing.
- Database seeding.
- Development efficiency.

Factories should remain independent of business workflows.

---

# Seeding Strategy

Use Seeders for predictable application data.

Examples include:

- Default roles.
- Default permissions.
- Initial configuration.
- Reference data.
- Sample records.

Avoid embedding large amounts of setup logic directly inside application code.

---

# Best Practices

When working with database components:

- Keep Models reusable.
- Keep Migrations focused on schema changes.
- Use Factories for generating test data.
- Use Seeders for initial and development data.
- Place business logic inside Workflow Services.
- Avoid coupling Models to specific Workflows.
- Reuse existing Models whenever possible.

---

# Laravel Compatibility

MCF database components are fully compatible with Laravel.

Developers can continue using familiar Laravel features, including:

- Eloquent ORM
- Relationships
- Query Builder
- Model Scopes
- Factories
- Seeders
- Migrations
- Database Transactions

MCF extends the project organization without changing Laravel's database capabilities.

---

# Summary

MCF centralizes database resources while organizing business features into independent Workflows.

Models, Migrations, Factories and Seeders remain shared application resources, allowing multiple Workflows to reuse the same data model without duplication.

This separation between business capabilities and database entities results in a cleaner, more maintainable architecture that scales naturally as applications grow.