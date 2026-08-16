# MCF Framework

> ⚠️ **Experimental:** MCF is currently under active development. The framework is suitable for evaluation, prototyping, and early adoption until the first stable release is published.

**MCF (Modular Code Framework)** is a modular architecture built on top of Laravel 12 that organizes applications into self-contained features instead of distributing application code across Laravel's default directories.

MCF extends Laravel without replacing it. It follows Laravel conventions whenever possible while providing a cleaner project structure, dedicated Artisan generators, and feature-oriented development.

---

# Overview

Laravel applications naturally become harder to maintain as they grow because controllers, requests, policies, services, views, and language files are spread across multiple framework directories.

MCF solves this by grouping everything related to a feature into a single workflow.

Every workflow owns its:

- Controller
- Service
- Request
- Policy
- Routes
- Views
- Language files

This allows features to be developed, modified, tested, and removed independently.

All framework components live inside:

```text
app/MCF
```

instead of Laravel's default application folders.

MCF also provides a small set of framework base classes under:

```text
app/MCF/Base
```

Generated backend classes inherit from these base classes automatically, giving the framework a centralized extension point while keeping generated code clean.

---

# Features

- Modular architecture
- Feature-based project organization
- Self-contained workflows
- Backend / Views / Lang separation
- Dedicated Artisan generators
- Endpoint generator
- Language generator
- CRUD workflow generator
- Layout workflow generator
- Framework base classes
- Laravel-compatible implementation
- Predictable project structure
- Clean separation of responsibilities
- Easily extensible architecture

---

# Requirements

- PHP 8.4+
- Laravel 12+

---

# Project Structure

```text
app/MCF
├── Base/
│   ├── MfcController.php
│   ├── MfcPolicy.php
│   ├── MfcRequest.php
│   └── MfcService.php
├── Database/
│   ├── Factories/
│   ├── Migrations/
│   ├── Models/
│   └── Seeders/
├── Mail/
├── Middleware/
├── Modules/
├── Notifications/
├── Rules/
├── mcf_routes.php
├── README.md
└── Quick Start.md
```

---

# Workflow Structure

Every workflow is completely isolated inside its module.

Example:

```text
app/MCF
└── Modules
    └── Users
        └── Profile
            ├── Backend
            │   ├── ProfileController.php
            │   ├── ProfilePolicy.php
            │   ├── ProfileRequest.php
            │   ├── ProfileRoutes.php
            │   └── ProfileService.php
            ├── Lang
            └── Views
```

Each directory has a dedicated responsibility.

| Folder | Description |
|----------|-------------|
| Backend | Controllers, Requests, Policies, Services and Routes |
| Views | Blade templates that belong only to this workflow |
| Lang | Workflow language JSON files |

Because every feature is isolated, removing or modifying one workflow never affects unrelated parts of the application.

---

# Base Classes

MCF provides shared framework base classes located under:

```text
app/MCF/Base
```

Generated backend classes automatically inherit from them.

```text
ProfileController → MfcController
ProfileRequest    → MfcRequest
ProfileService    → MfcService
ProfilePolicy     → MfcPolicy
```

This provides a centralized extension point for common framework functionality while keeping generated classes minimal.

---

# Quick Start

Install MCF:

```bash
php artisan mcf:install
```

Create your first module:

```bash
php artisan mcf:make:module Users
```

Create a workflow:

```bash
php artisan mcf:make:workflow Users Profile
```

Generate a CRUD workflow:

```bash
php artisan mcf:make:workflow:crud Users Products
```

Generate a layout workflow:

```bash
php artisan mcf:make:workflow:layout Shared Layout
```

Create a new endpoint:

```bash
php artisan mcf:endpoint:create
```

Create language files:

```bash
php artisan mcf:lang:make ar Shared Layout
```

---

# Artisan Commands

MCF ships with a collection of Artisan generators grouped by purpose.

## Installation

### Install the framework

```bash
php artisan mcf:install
```

Initializes the MCF directory structure inside the Laravel application.

---

# Modules

Modules represent the highest level of organization inside MCF.

Each module contains one or more workflows.

## Create a module

```bash
php artisan mcf:make:module Users
```

Example:

```text
app/MCF/Modules/Users
```

---

# Workflows

A workflow represents a complete feature.

Each workflow contains its backend classes, views, routes and language resources.

## Create a standard workflow

```bash
php artisan mcf:make:workflow Users Profile
```

Creates:

```text
Users
└── Profile
    ├── Backend
    ├── Views
    └── Lang
```

## Create a CRUD workflow

```bash
php artisan mcf:make:workflow:crud Users Products
```

Generates a workflow preconfigured for Create, Read, Update and Delete operations.

## Create a layout workflow

```bash
php artisan mcf:make:workflow:layout Shared Layout
```

Generates a reusable layout workflow that can be shared across the application.

The default installation creates this workflow automatically.

## Remove a workflow

```bash
php artisan mcf:remove:workflow Users Profile
```

Force removal without confirmation:

```bash
php artisan mcf:remove:workflow Users Profile --force
```

Removes the entire workflow including its backend classes, views and language resources.

---

# Endpoints

Endpoints allow new actions to be added to an existing workflow without manually editing multiple files.

## Create an endpoint

```bash
php artisan mcf:endpoint:create
```

The command launches an interactive wizard that generates the required controller method, route and optional view based on the selected options.

## Remove an endpoint

```bash
php artisan mcf:endpoint:remove Shared Layout downloadReport
```

Removes the endpoint from the controller, workflow routes and generated view when applicable.


# Language Files

MCF stores translations using JSON language files.

Language files can be generated globally, for an entire module, or for a single workflow.

---

## Create Global Language Files

```bash
php artisan mcf:lang:make ar
```

Creates language resources for the entire application.

---

## Create Language Files for a Module

```bash
php artisan mcf:lang:make ar Users
```

Creates language files for every workflow inside the **Users** module.

---

## Create Language Files for a Workflow

```bash
php artisan mcf:lang:make ar Users Profile
```

Creates language files only for the selected workflow.

---

## Remove Global Language Files

```bash
php artisan mcf:lang:remove ar
```

Removes the generated global language resources.

---

## Remove Module Language Files

```bash
php artisan mcf:lang:remove ar Users
```

Removes language files belonging to the selected module.

---

## Remove Workflow Language Files

```bash
php artisan mcf:lang:remove ar Users Profile
```

Removes language files only from the selected workflow.

---

## Force Language Operations

Language creation and removal support the `--force` option.

Example:

```bash
php artisan mcf:lang:make ar Users Profile --force
```

```bash
php artisan mcf:lang:remove ar Users Profile --force
```

This skips confirmation prompts.

---

# Database

MCF keeps database-related classes inside the framework directory while remaining fully compatible with Laravel's migration system.

```text
app/MCF/Database
├── Factories
├── Migrations
├── Models
└── Seeders
```

---

## Models

Create a model.

```bash
php artisan mcf:make:model User
```

Generate a model with migration.

```bash
php artisan mcf:make:model User --migration
```

Generate everything.

```bash
php artisan mcf:make:model User --all
```

Laravel model options such as controllers, factories, policies, requests, seeders and tests are fully supported.

---

## Migrations

Create a migration.

```bash
php artisan mcf:make:migration create_users_table
```

Create a table.

```bash
php artisan mcf:make:migration create_users_table --create=users
```

Modify an existing table.

```bash
php artisan mcf:make:migration add_status_to_users_table --table=users
```

---

## Factories

Create a factory.

```bash
php artisan mcf:make:factory UserFactory
```

Associate it with a model.

```bash
php artisan mcf:make:factory UserFactory --model=User
```

---

## Seeders

Create a seeder.

```bash
php artisan mcf:make:seeder UserSeeder
```

---

# Framework Components

MCF also provides generators for common Laravel components while keeping them inside the MCF framework structure.

---

## Middleware

```bash
php artisan mcf:make:middleware AdminMiddleware
```

Creates a middleware inside:

```text
app/MCF/Middleware
```

---

## Mail

```bash
php artisan mcf:make:mail WelcomeMail
```

Creates a mailable class.

Use `--force` to overwrite an existing class.

---

## Notifications

```bash
php artisan mcf:make:notification AccountActivated
```

Creates a notification class.

Use `--force` if required.

---

## Validation Rules

```bash
php artisan mcf:make:rule StrongPassword
```

Create an implicit validation rule.

```bash
php artisan mcf:make:rule StrongPassword --implicit
```

---

# Routing

Every workflow owns its own route file.

Example:

```text
Users
└── Profile
    └── Backend
        └── ProfileRoutes.php
```

MCF recommends using route names based on both the module and workflow.

Example:

```php
Route::get('/profile', [ProfileController::class, 'index'])
    ->name('users.profile.index');
```

The application's default entry route is defined inside:

```text
app/MCF/mcf_routes.php
```

Developers may change the application's landing page by updating the named route used in this file.

---

# Default Layout Workflow

MCF installs a reusable layout workflow by default.

```text
Module   : Shared

Workflow : Layout
```

This is a normal workflow generated using:

```bash
php artisan mcf:make:workflow:layout Shared Layout
```

It is not a special framework component and may be modified, renamed, deleted, or recreated like any other workflow.

# Assets & Storage

MCF does not replace Laravel's asset or filesystem implementation.

Continue using Laravel's standard locations.

## Public Assets

Store public resources inside:

```text
public/
```

Typical assets include:

- CSS
- JavaScript
- Images
- Fonts
- Icons
- Downloads

---

## File Storage

Store uploaded files using Laravel's Storage system.

```text
storage/
```

MCF remains fully compatible with Laravel's filesystem drivers including:

- Local
- Public
- S3
- FTP
- Custom disks

No additional asset or storage configuration is required.

---

# Development Workflow

A typical development cycle in MCF looks like this.

## 1. Create a module

```bash
php artisan mcf:make:module Users
```

---

## 2. Create a workflow

```bash
php artisan mcf:make:workflow Users Profile
```

or

```bash
php artisan mcf:make:workflow:crud Users Products
```

---

## 3. Add endpoints

```bash
php artisan mcf:endpoint:create
```

The interactive wizard generates the required controller methods, routes and optional Blade views.

---

## 4. Create language resources

```bash
php artisan mcf:lang:make ar Users Profile
```

---

## 5. Create models and migrations

```bash
php artisan mcf:make:model Product --all
```

or

```bash
php artisan mcf:make:migration create_products_table
```

---

## 6. Continue development

Because every workflow is isolated, all future development happens inside the workflow itself without affecting unrelated features.

---

# Best Practices

MCF is designed around feature isolation.

For the best experience, follow these recommendations.

- Keep one responsibility per workflow.
- Create separate workflows instead of large controllers.
- Keep business logic inside services.
- Keep validation inside requests.
- Keep authorization inside policies.
- Keep Blade views inside their workflow.
- Keep translations inside the workflow whenever possible.
- Use endpoint generators instead of manually editing controllers and routes.
- Use CRUD workflows for resource-based features.
- Use Layout workflows for reusable application layouts.
- Prefer multiple small workflows over one large workflow.

---

# Documentation

The complete documentation is available inside the **docs** directory.

## Documentation Index

- Installation
- Quick Start
- Architecture
- Folder Structure
- Base Classes
- Modules
- Workflows
- CRUD Workflows
- Layout Workflows
- Endpoints
- Language Files
- Database
- Routing
- Framework Components
- CLI Reference
- Coding Standards
- Best Practices
- Contributing

---

# Philosophy

MCF follows a small number of core principles.

## Modular First

Every feature should exist as an independent workflow.

## Laravel Compatible

MCF extends Laravel rather than replacing it.

Developers can continue using Laravel packages, middleware, routing, storage, authentication, queues and ecosystem without modification.

## Predictable Structure

Every project follows the same organization.

Developers always know where controllers, requests, services, routes, views and language files belong.

## Low Boilerplate

Generators automate repetitive tasks while leaving generated code easy to understand and customize.

## Scalable Applications

As projects grow, workflows remain isolated, making large applications easier to maintain than traditional directory-based architectures.

---

# Contributing

MCF is currently under active development.

Bug reports, feature requests and pull requests are welcome.

Please ensure new contributions follow the existing framework architecture and coding standards.

---

# License

MCF is distributed under its respective license.

---

**Built with Laravel 12.**