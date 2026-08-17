# MCF Framework

MCF is a structured application architecture built on Laravel for
production applications. It keeps Laravel as the underlying platform and
adds a consistent organization for Modules, Workflows, Requests,
Endpoints, Authentication, Access Control, Audit, Notifications,
Results, Storage, and framework generators.

## Quick Start

### 1. Install MCF

``` bash
composer require mcf/framework
```

### 2. Run the installer

``` bash
php artisan mcf:install
```

Install MCF on a new Laravel project whenever possible.

The installer creates the MCF application structure, installs the
framework-managed files, places the documentation in:

``` text
app/MCF/z_Guide
```

and creates `z_backup` for structures affected by installation. The
backup contains affected structures only; it is not a complete backup of
the previous project.

MCF prevents installing the framework again into an already-installed
project.

### 3. Configure the environment

Configure `.env`, especially the database connection.

### 4. Run migrations and seeders

``` bash
php artisan migrate --seed
```

The installed `DatabaseSeeder` is the central entry point for
application seeders.

### 5. Continue with the documentation

Start with this README, then read the individual guides in
`app/MCF/z_Guide`.

------------------------------------------------------------------------

# MCF Architecture

The main MCF area is:

``` text
app/
└── MCF/
```

A typical installation contains areas such as:

``` text
app/MCF/
├── Modules/
├── Authentication/
├── Access/
├── Audit/
├── Notification/
├── Result/
├── Storage/
├── Base/
└── z_Guide/
```

The installed version is the authoritative source for its exact
structure.

## Modules and Workflows

Modules represent application domains.

``` text
app/MCF/Modules/User
app/MCF/Modules/Book
app/MCF/Modules/School
```

A Module can contain multiple Workflows:

``` text
User/
├── Auth/
├── Profile/
└── Settings/
```

A Workflow owns a specific business capability and normally contains the
routes, controllers, services, requests, views, and other files
belonging to that capability.

The principle is:

> Business logic stays inside the Workflow that owns it.

## Models and Database

MCF continues to use Laravel Eloquent Models in:

``` text
app/Models/
```

Laravel remains responsible for the database system. MCF provides the
database structures required by its own features, while application
developers remain free to design and modify business tables.

Do not remove MCF-managed directories simply because they are not
currently being edited. Framework components can depend on each other.

## Routes and Access Control

A Workflow owns the HTTP routes for its operations.

For example:

``` php
Route::get(
    '/books/{book}/download',
    [BookController::class, 'download'],
)->name('book.download');
```

Access Control belongs to the business operation that owns the route.

If students, teachers, and administrators have different permissions for
downloading a book, those rules belong to the Book Workflow. Storage
does not decide business roles.

## Requests

Requests are workflow-level input validation objects.

Create one with:

``` bash
php artisan mcf:make:request User Auth Login
```

The command receives:

``` text
Module
Workflow
Request
```

If the Workflow has no `Request` directory, MCF creates it.

A Request may contain authorization, validation rules, messages, and an
optional Data class.

Requests are endpoint-specific when generated for an endpoint. The old
shared Workflow Request structure is not used.

## Endpoints

MCF provides endpoint creation and removal commands.

An endpoint is a complete operation rather than a partial fragment.
Depending on the selected options it can include:

``` text
Controller method
Route
Request
View
```

When an endpoint requests a Request, MCF creates a Request dedicated to
that endpoint.

## Results

MCF has a common Result base:

``` text
app/MCF/Result/McfResult.php
```

Example:

``` php
final class AuthenticationResult extends McfResult
{
    public const SUCCESS = 'success';

    public const INVALID_CREDENTIALS = 'invalid_credentials';
}
```

Results provide explicit operation states without exposing
implementation details.

## Authentication

Authentication is responsible for identifying the current user and
returning explicit authentication states.

Typical states include:

``` text
success
invalid_credentials
need_email_verification
need_phone_verification
not_allowed
throttled
failed
```

The exact states are defined by the installed MCF version.

## Audit

MCF Audit records configured Eloquent model events.

Auditable models define their rules through `auditDefinitions()`.

Audit can be globally enabled or disabled through `AuditSettings`. This
allows operations such as seeders to disable auditing centrally without
changing every Seeder.

Audit listeners are registered globally so model booting is not affected
by per-model observer registration.

## Notifications

MCF provides reusable notification infrastructure. Notifications remain
separate from the business Workflow that triggers them.

------------------------------------------------------------------------

# Storage

MCF Storage is infrastructure, not a Storage Workflow.

The business Workflow owns the meaning and authorization of a file.

For example:

``` text
Book Workflow
    ↓
Access Control
    ↓
Book Service
    ↓
MCF Storage
    ↓
Storage Provider
```

The Book Workflow decides who can upload, download, or delete a book
file.

MCF Storage only manages the file.

## Storage Reference

The central Storage concept is the **Storage Reference**.

A Workflow stores a stable reference, not:

``` text
provider-specific URL
storage database ID
disk name
bucket name
provider details
```

For example, a business table can contain:

``` text
file_reference
```

The reference is the stable contract between the Workflow and MCF
Storage.

The provider may later change from Laravel local storage to Amazon S3,
Appwrite, or another provider without requiring changes to the Workflow
business code.

## Storage API

The intended API includes operations such as:

``` php
McfStorage::upload(...);
McfStorage::view($reference);
McfStorage::download($reference);
McfStorage::metadata($reference);
McfStorage::delete($reference);
```

Conceptually:

``` text
upload()
    → Storage Reference

view()
    → URL

download()
    → HTTP download response

metadata()
    → file metadata

delete()
    → removes the file
```

The Workflow does not directly use Laravel's filesystem API.

The Storage Provider is an implementation detail behind MCF Storage.

The reference remains stable while the actual provider and URL may
change.

------------------------------------------------------------------------

# Laravel Relationship

MCF is an architecture layer over Laravel.

Laravel remains the foundation for:

``` text
Eloquent
Database
Filesystem
HTTP
Routing
Console
Mail
Queues
Configuration
```

MCF wraps and organizes these capabilities where a stable
application-level abstraction is useful.

------------------------------------------------------------------------

# MCF Commands

MCF commands use the `mcf:` prefix so they are clearly separated from
native Laravel Artisan commands.

Common commands include:

``` bash
php artisan mcf:install
php artisan mcf:make:module
php artisan mcf:make:workflow
php artisan mcf:make:workflow:crud
php artisan mcf:make:workflow:layout
php artisan mcf:make:request
php artisan mcf:endpoint:create
php artisan mcf:endpoint:remove
php artisan mcf:remove:workflow
php artisan mcf:make:middleware
php artisan mcf:make:mail
```

Use:

``` bash
php artisan list
```

for the exact command list in the installed version.

Detailed command documentation is available in `z_Guide`.

------------------------------------------------------------------------

# Recommended Development Flow

For a new application:

``` text
1. Create a Laravel project
2. Install MCF
3. Configure .env
4. Run migrations and seeders
5. Read README and Quick Start
6. Review the installed MCF structure
7. Keep framework-managed structures intact
8. Build business functionality through Modules and Workflows
9. Use MCF generators for supported components
10. Keep business rules inside their owning Workflow
```

For large applications, keep the distinction clear:

``` text
Business
    Modules / Workflows

Infrastructure
    Authentication
    Access
    Audit
    Notification
    Storage
    Result
```

The goal is to keep business decisions independent from infrastructure
implementations.

## Core Principle

> Business Workflows own business decisions. MCF infrastructure owns
> infrastructure concerns.

For example:

``` text
Book Workflow
    ↓
Access Control
    ↓
Book Service
    ↓
MCF Storage
    ↓
Storage Provider
```

Book decides whether a user may download a book.

MCF Storage decides how the file is retrieved.

The provider decides where the file physically exists.

Each layer has one responsibility.

------------------------------------------------------------------------

# Documentation

The installed `app/MCF/z_Guide` directory contains the detailed
documentation for the framework.

Start with:

``` text
README
```

Then use the individual guides for Authentication, Access Control,
Audit, Notification, Storage, Modules, Workflows, Requests, Endpoints,
Commands, and other components.

# License

See the repository license for the licensing terms of MCF.
