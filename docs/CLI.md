# CLI

MCF includes a collection of Artisan commands that automate common development tasks.

Rather than manually creating directories, classes and supporting files, developers use CLI generators to produce a consistent project structure.

All commands are fully integrated with Laravel's Artisan console.

---

# Command Categories

MCF commands are organized into several categories.

- Installation
- Modules
- Workflows
- Endpoints
- Languages
- Database
- Shared Components

Each category targets a specific part of the framework.

---

# Installation

Install MCF into a Laravel application.

```bash
php artisan mcf:install
```

The installer prepares the application structure required by the framework.

Typical tasks include:

- Creating framework directories.
- Publishing configuration.
- Registering framework components.
- Preparing the initial project structure.

Run this command only once per project.

---

# Modules

Create a new Module.

```bash
php artisan mcf:make:module
```

The command interactively asks for the Module name before generating the required directory structure.

Example:

```text
Users
```

Generated:

```text
Modules
└── Users
```

Modules organize related Workflows.

---

# Workflows

Create a standard Workflow.

```bash
php artisan mcf:make:workflow
```

The generator prompts for:

- Module
- Workflow name

Generated structure:

```text
Profile
├── Backend
├── Views
└── Lang
```

This command is appropriate for business-oriented features.

---

# CRUD Workflow

Create a resource-oriented Workflow.

```bash
php artisan mcf:make:workflow:crud
```

CRUD Workflows generate the structure required for common resource management features.

Typical examples include:

- Products
- Categories
- Customers
- Employees

Use standard Workflows for business processes that extend beyond basic CRUD operations.

---

# Layout Workflow

Generate a Layout Workflow.

```bash
php artisan mcf:make:workflow:layout
```

A Layout Workflow contains the shared presentation layer for the application.

Unlike traditional frameworks, Layout is implemented as a normal Workflow and follows the same architectural conventions.

---

# Remove Workflow

Delete an existing Workflow.

```bash
php artisan mcf:remove:workflow
```

The command removes the selected Workflow and its associated files.

Only the targeted Workflow is affected.

---

# Endpoint Generator

Create a new endpoint inside an existing Workflow.

```bash
php artisan mcf:endpoint:create
```

The generator interactively asks for:

- Module
- Workflow
- Endpoint name

It then updates the required classes automatically.

Typical generated changes include:

- Controller method
- Route definition
- Service method (when applicable)

This keeps endpoint creation consistent across the application.

---

# Remove Endpoint

Remove an existing endpoint.

```bash
php artisan mcf:endpoint:remove <module> <workflow> <endpoint>
```

Example:

```bash
php artisan mcf:endpoint:remove Users Profile export
```

Only the selected endpoint is removed.

The remaining Workflow structure is preserved.

---

# Language Generator

Generate translation resources.

```bash
php artisan mcf:lang:make <locale>
```

Example:

```bash
php artisan mcf:lang:make ar
```

Translations may also be generated for a specific Module or Workflow.

```bash
php artisan mcf:lang:make ar Users
```

```bash
php artisan mcf:lang:make ar Users Profile
```

This command creates the required language resources while preserving the Workflow-oriented localization structure.

---

# Remove Language

Remove generated language resources.

```bash
php artisan mcf:lang:remove <locale>
```

Examples:

```bash
php artisan mcf:lang:remove ar
```

```bash
php artisan mcf:lang:remove ar Users
```

```bash
php artisan mcf:lang:remove ar Users Profile
```

Only the selected localization resources are removed.

---

# Database Commands

MCF provides generators for common database components.

Create a Model.

```bash
php artisan mcf:make:model
```

Create a Migration.

```bash
php artisan mcf:make:migration
```

Create a Factory.

```bash
php artisan mcf:make:factory
```

Create a Seeder.

```bash
php artisan mcf:make:seeder
```

These commands follow the MCF project organization while remaining fully compatible with Laravel's database system.

---

# Shared Components

MCF also generates reusable application components.

Create Mail classes.

```bash
php artisan mcf:make:mail
```

Create Middleware.

```bash
php artisan mcf:make:middleware
```

Create Notifications.

```bash
php artisan mcf:make:notification
```

Create Validation Rules.

```bash
php artisan mcf:make:rule
```

These generators create shared components intended for reuse across multiple Workflows.

---

# Interactive Commands

Most MCF generators are interactive.

Instead of requiring numerous command-line arguments, the generator guides the developer through the required input.

Typical prompts include:

- Module
- Workflow
- Endpoint
- Locale
- Component name

This approach reduces typing errors and improves the developer experience.

---

# Generated Code

All generated files follow the same architectural conventions.

Generated code is:

- Consistent.
- Predictable.
- Organized.
- Ready for customization.

Developers are encouraged to extend generated classes rather than replacing the generated structure.

---

# Best Practices

When working with the CLI:

- Create Modules before creating Workflows.
- Prefer generators over manual file creation.
- Use CRUD generators for resource management features.
- Use standard Workflows for business processes.
- Generate endpoints instead of editing Controllers manually.
- Keep generated files organized inside their owning Workflow.
- Remove unused Workflows and endpoints to keep projects clean.

---

# Summary

MCF's Artisan commands automate the repetitive tasks involved in building modular applications.

By generating Modules, Workflows, Endpoints, Language resources, Database components and shared classes, the CLI ensures that every feature follows the same architecture and conventions, reducing boilerplate while improving consistency across the entire project.