# MCF Database

## Overview

The **Database** structure in MCF follows Laravel’s standard database
architecture.

The core principle is that database files remain in Laravel’s natural
locations:

``` text
database/
├── migrations/
└── models/
```

Migrations and Models are not placed inside the `MCF` directory.

MCF only provides the default database components required by its
modules, while the database itself remains part of Laravel’s application
structure.

------------------------------------------------------------------------

# Migration Location

Migrations are stored in:

``` text
database/migrations/
```

They use Laravel’s standard migration system and execution order.

Example of the current structure:

``` text
database/
└── migrations/
    ├── 0000_create_laravel_tables.php
    ├── 0001_create_mcf_auth_tables.php
    └── 0002_create_mcf_audit_logs_table.php
```

The file order follows Laravel’s migration execution mechanism.

------------------------------------------------------------------------

# MCF Does Not Store Migrations Inside MCF

MCF does not use:

``` text
MCF/
└── Migrations/
```

and MCF table migrations are not stored inside individual module
directories.

Instead, migrations are delivered with the framework installation and
placed in:

``` text
database/migrations/
```

This preserves Laravel’s native structure and keeps all database
migrations in one location.

------------------------------------------------------------------------

# MCF Models

Models also remain in the application’s normal location:

``` text
app/
└── Models/
```

For example:

``` text
app/Models/
├── User.php
├── AuditLog.php
├── VerificationRequest.php
└── ...
```

Application Models are not placed inside:

``` text
MCF/
```

A Model belongs to the application’s data layer rather than to a
Workflow or Service.

------------------------------------------------------------------------

# Models Provided with MCF

When MCF is installed, the framework provides Models associated with the
components it ships with.

The developer can:

- Use them as provided.
- Extend them.
- Add columns to their tables.
- Adapt them to project requirements.
- Remove components that are not needed, provided the developer confirms
  that the related module is not being used.

When an MCF module depends on a particular table, the Model and database
schema must remain compatible with that module’s expectations.

------------------------------------------------------------------------

# Migrations Are Module-Dependent

Not every migration is mandatory.

Some migrations belong to the Laravel foundation, while others belong to
optional MCF modules.

For example:

``` text
0000_create_laravel_tables.php
```

represents the Laravel application foundation.

While:

``` text
0001_create_mcf_auth_tables.php
0002_create_mcf_audit_logs_table.php
```

belong to MCF modules such as Authentication and Audit.

------------------------------------------------------------------------

# Laravel Database Migration

The migration responsible for the Laravel foundation is part of the
application’s core database structure.

It should not be removed when the application depends on the tables it
creates.

Migrations belonging to optional MCF modules such as:

``` text
Authentication
Audit
```

can be used or removed depending on which modules the project chooses to
use.

They are recommended because they provide the required schema ready to
use.

------------------------------------------------------------------------

# Authentication and Audit

If the project chooses to use:

``` text
Authentication
```

or:

``` text
Audit
```

the database schema must remain compatible with what those modules
expect.

For example, if Authentication depends on specific columns in `users`,
simply renaming or deleting those columns without updating the module’s
configuration and related code can break the module.

The same principle applies to Audit and the tables it uses.

------------------------------------------------------------------------

# Modifying MCF Tables

Developers are free to modify the database schema according to project
requirements.

### Adding Columns

Adding columns to MCF tables is generally safe by itself.

For example:

``` text
users
├── id
├── name
├── email
├── phone
├── is_active
├── role_id
└── custom_column
```

The project can extend the `users` table as needed.

------------------------------------------------------------------------

# Removing Columns

Removing a column requires caution.

If an MCF module depends on that column, removing it can break
functionality or cause runtime errors.

Before removing a column, check:

``` text
Does Authentication use it?
Does Audit use it?
Does AccessControl use it?
Does the Model use it?
Does a Workflow use it?
```

------------------------------------------------------------------------

# Removing a Table

Removing a table is more sensitive than adding a column.

Before removing a table, verify that:

- No MCF module depends on it.
- No Model uses it.
- No Workflow depends on it.
- No relationships or Foreign Keys depend on it.
- No configuration references it.

If the related MCF module is not being used, the developer can remove
its table and migration according to the project’s design.

------------------------------------------------------------------------

# Database Compatibility with MCF

When using an optional module, compatibility should be maintained
between:

``` text
Database Table
      ↓
Eloquent Model
      ↓
MCF Configuration
      ↓
MCF Module / Workflow
```

Example:

``` text
users
   ↓
User Model
   ↓
Authentication
```

If the `users` schema changes, Authentication must still know how to
work with the new structure.

------------------------------------------------------------------------

# Module Configuration

If the developer changes a table structure, the corresponding module
configuration may need to be updated.

For example:

``` text
MCF Authentication
        ↓
expects users table
        ↓
developer changes column names
        ↓
Authentication configuration must be updated
```

Changing the database schema alone is not sufficient when a module
depends on specific column names.

------------------------------------------------------------------------

# Extending the User Model

The `User` Model provided with MCF is a ready starting point.

The developer can extend it according to project requirements.

For example:

``` text
role_id
department_id
avatar
last_seen_at
...
```

Using MCF does not require the `User` Model to remain identical to the
default model.

The important requirement is to preserve the columns required by the
modules being used, or update the module configuration to match the new
schema.

------------------------------------------------------------------------

# Practical Example

A project may start with:

``` text
users
├── id
├── name
├── email
├── phone
├── password
└── ...
```

Then add:

``` text
role_id
```

Resulting in:

``` text
users
├── id
├── name
├── email
├── phone
├── password
├── role_id
└── ...
```

This is normal.

However, if:

``` text
email
```

is renamed to:

``` text
email_address
```

the project must review modules that depend on `email` and update their
configuration or code accordingly.

------------------------------------------------------------------------

# MCF Database Philosophy

MCF does not attempt to create a separate database system.

Instead:

``` text
Laravel Database
       ↓
Migrations
       ↓
Models
       ↓
MCF Modules
```

The framework provides the database components required by its modules
while keeping Laravel’s database conventions in their natural locations.

------------------------------------------------------------------------

# Design Rules

1.  Migrations remain in `database/migrations`.
2.  Models remain in `app/Models`.
3.  Migrations are not stored inside the MCF directory.
4.  Application Models are not stored inside the MCF directory.
5.  The Laravel foundation migration is core to the application.
6.  Migrations for optional MCF modules can be used or removed according
    to project needs.
7.  Adding columns to MCF tables is allowed.
8.  Columns should only be removed after checking module dependencies.
9.  Tables require even more caution before removal.
10. When changing a table used by an MCF module, update the module
    configuration or required code to maintain compatibility.
11. The `User` Model can be extended according to project requirements.
12. MCF follows Laravel’s standard database architecture instead of
    creating a separate database system.

------------------------------------------------------------------------

# Architectural Goal

``` text
Laravel Database
        │
        ├── database/migrations
        │
        └── app/Models
                 │
                 ↓
            MCF Modules
                 │
        ┌────────┼────────┐
        ↓        ↓        ↓
      Auth     Audit    Access
```

The goal is for the database to remain a **natural part of Laravel**,
while MCF modules use that database without imposing a separate database
structure inside the framework.
