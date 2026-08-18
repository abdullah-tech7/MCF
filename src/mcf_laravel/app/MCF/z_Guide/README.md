# MCF --- Modular Code Framework

**MCF (Modular Code Framework)** is a feature-oriented application
architecture built on top of Laravel.

> **MCF does not replace Laravel. It organizes and extends the
> application architecture on top of Laravel.**

------------------------------------------------------------------------

## Table of Contents

-   [What is MCF?](#what-is-mcf)

-   [Architecture at a Glance](#architecture-at-a-glance)

-   [What MCF Installs](#what-mcf-installs)

-   [Project Structure](#project-structure)

-   [Modules and Workflows](#modules-and-workflows)
    -   [Module](#module)
    -   [Workflow](#workflow)

-   [Framework Components](#framework-components)
    -   [Base](#1-base)
        -   [MfcController](#mfccontroller)
        -   [MfcRequest](#mfcrequest)
        -   [MfcService](#mfcservice)
    -   [Authentication](#2-authentication)
    -   [Access Control](#3-access-control)
        -   [Guards](#guards)
        -   [Access Modes](#access-modes)
    -   [Audit](#4-audit)
    -   [Language](#5-language)
    -   [Mail](#6-mail)
    -   [Notification](#7-notification)
    -   [SMS](#8-sms)
    -   [Middleware](#9-middleware)
        -   [McfAccessMiddleware](#mcfaccessmiddleware)
        -   [McfSessionSecurityMiddleware](#mcfsessionsecuritymiddleware)
        -   [SetLocaleMiddleware](#setlocalemiddleware)
    -   [Result](#10-result)
    -   [Storage](#11-storage)
        -   [StorageReference](#storagereference)
        -   [StorageRecord](#storagerecord)
        -   [StorageRegistry](#storageregistry)
        -   [StorageProvider](#storageprovider)
        -   [Multiple Providers](#multiple-providers)
        -   [Public and Protected
            Storage](#public-and-protected-storage)
        -   [Single Operations](#single-operations)
        -   [Multi Operations](#multi-operations)
        -   [Download Names](#download-names)
        -   [Storage Results](#storage-results)
        -   [Provider Independence](#provider-independence)

-   [Database](#database)
    -   [Database Principle](#database-principle)

-   [Routes](#routes)

-   [Resources and Public](#resources-and-public)

-   [Endpoint Generator](#endpoint-generator)
    -   [Generator First](#generator-first)
    -   [Request Integration](#request-integration)

-   [Requests and Data](#requests-and-data)

-   [MCF CLI](#mcf-cli)
    -   [Installation](#installation-1)
    -   [Module](#module-1)
    -   [Standard Workflow](#standard-workflow)
    -   [CRUD Workflow](#crud-workflow)
    -   [Layout Workflow](#layout-workflow)
    -   [Request](#request)
    -   [Endpoint](#endpoint)
    -   [Remove Endpoint](#remove-endpoint)
    -   [Remove Workflow](#remove-workflow)
    -   [Middleware](#middleware)
    -   [Mail](#mail-1)

-   [Installation](#installation)

-   [What Developers Should and Should Not
    Change](#what-developers-should-and-should-not-change)
    -   [Keep Laravel's standard
        locations](#keep-laravels-standard-locations)
    -   [Keep the MCF framework
        structure](#keep-the-mcf-framework-structure)
    -   [Database Components](#database-components)
    -   [Modules and Workflows](#modules-and-workflows-1)

-   [Recommended Development Flow](#recommended-development-flow)

-   [Design Principles](#design-principles)
    -   [Laravel First](#laravel-first)
    -   [Feature-Oriented Architecture](#feature-oriented-architecture)
    -   [Separation of
        Responsibilities](#separation-of-responsibilities)
    -   [Provider Abstraction](#provider-abstraction)
    -   [Generator First](#generator-first-1)
    -   [Predictability](#predictability)

-   [Final Architecture Summary](#final-architecture-summary)

-   [Documentation](#documentation)

-   ## [License](#license)

# What is MCF?

MCF provides a structured application root inside Laravel:

``` text
Laravel
   │
   └── MCF
       ├── Framework Components
       ├── Modules
       ├── Workflows
       ├── Generated Resources
       └── Integration Files
```

The purpose is to keep a large application predictable as it grows.

MCF focuses on:

-   organizing code around business capabilities;
-   keeping feature-specific code together;
-   separating reusable framework concerns from application features;
-   reducing repetitive structural work through generators;
-   keeping Laravel as the underlying framework;
-   providing predictable conventions;
-   reducing unnecessary coupling;
-   allowing framework behavior to be configured rather than requiring
    developers to remove framework directories.

------------------------------------------------------------------------

# Architecture at a Glance

The most important distinction in MCF is:

``` text
Module
   ↓
Workflow
   ↓
Business Operation
```

A **Model** represents data.

A **Module** organizes a domain or system area.

A **Workflow** represents a business capability or connected work path.

For example:

``` text
User
├── Auth
├── Profile
└── Management
```

Here:

-   `User` is the Module.
-   `Auth`, `Profile`, and `Management` are Workflows.
-   `User` is a Model/data concept, not a Workflow.

A Workflow is therefore **not** a database table, Model, Controller, or
merely an Endpoint.

------------------------------------------------------------------------

# What MCF Installs

After installation, the main MCF root is:

``` text
app/
└── MCF/
    ├── AccessControl/
    ├── Audit/
    ├── Authentication/
    ├── Base/
    ├── Language/
    ├── Mail/
    ├── Middleware/
    ├── Modules/
    ├── Notification/
    ├── Result/
    ├── Sms/
    ├── Storage/
    ├── z_Guide/
    ├── .mcf-installed
    └── mcf_routes.php
```

The MCF root is intentionally broader than `Modules`.

It contains:

1.  **Framework Components** --- reusable application infrastructure.
2.  **Modules** --- application feature organization.
3.  **Documentation** --- the installed MCF Guide.
4.  **Integration Files** --- files that connect MCF to Laravel.

------------------------------------------------------------------------

# Project Structure

A typical MCF application looks conceptually like:

``` text
app/
├── Models/
│   ├── User.php
│   └── ...
│
└── MCF/
    ├── AccessControl/
    ├── Audit/
    ├── Authentication/
    ├── Base/
    ├── Language/
    ├── Mail/
    ├── Middleware/
    │
    ├── Modules/
    │   ├── Shared/
    │   │   └── Layout/
    │   │
    │   └── User/
    │       ├── Auth/
    │       └── Profile/
    │
    ├── Notification/
    ├── Result/
    ├── Sms/
    ├── Storage/
    ├── z_Guide/
    └── mcf_routes.php
```

MCF deliberately leaves Laravel resources, Models, and database
migrations in their normal Laravel locations.

------------------------------------------------------------------------

# Modules and Workflows

## Module

A Module is the organizational boundary for a domain.

Example:

``` text
Modules/
├── User/
├── Shop/
├── Reports/
└── System/
```

A Module itself is not the place where business logic is implemented. It
groups related Workflows.

## Workflow

A Workflow is a complete business operation or capability.

Examples:

``` text
User
├── Auth
├── Profile
└── Management
```

``` text
Shop
├── Catalog
├── Cart
└── Checkout
```

Good Workflow names describe what the application does:

``` text
Authentication
Profile
Checkout
Reports
Dashboard
```

Avoid creating Workflows merely because a database table exists.

``` text
Model → Data
Module → Domain organization
Workflow → Business capability
```

A single Model may be used by multiple Workflows.

------------------------------------------------------------------------

# Framework Components

MCF includes several framework-level components. Each has a separate
responsibility.

------------------------------------------------------------------------

## 1. Base

`Base` is the mandatory foundation of MCF.

It provides the common base classes for:

``` text
Controllers
Requests
Services
Data → Model conversion
```

Structure:

``` text
Base/
├── MfcController.php
├── MfcRequest.php
└── MfcService.php
```

### MfcController

Provides a common MCF base for Controllers:

``` php
class UserController extends MfcController
{
    // ...
}
```

It currently extends Laravel's Controller and gives MCF a single base
point for future framework behavior.

### MfcRequest

Provides the common base for MCF Form Requests.

It extends Laravel's `FormRequest`.

MCF Requests can optionally define a Data class:

``` php
protected function dataClass(): ?string
{
    return CreateUserData::class;
}
```

Then:

``` php
$data = $request->getData();
```

The flow is:

``` text
Request
   ↓
Validation
   ↓
validated()
   ↓
dataClass()
   ↓
Data Object
```

If no Data class is defined, validated input is returned as an array.

### MfcService

Provides the common base for MCF Services and includes the framework
operation used to convert Data objects into Eloquent Models.

------------------------------------------------------------------------

## 2. Authentication

MCF Authentication is a structured layer on top of Laravel's native
authentication system.

It does not replace Laravel Auth.

It provides a unified authentication API covering:

-   login and logout;
-   authentication state;
-   current user and ID access;
-   login using an existing User;
-   credential-based login;
-   login throttling;
-   email and phone verification requirements;
-   password hashing, changing, and reset;
-   verified email and phone updates;
-   account enable/disable;
-   account deletion and restoration;
-   self-deletion and restoration;
-   restoration-period handling;
-   forced session logout;
-   Session Security;
-   Concurrent Session Control.

Main API:

``` php
McfAuth::check();
McfAuth::id();
McfAuth::user();
```

Credential login:

``` php
$result = McfAuth::loginByCredentials([
    'email' => 'user@example.com',
    'password' => 'password',
]);
```

Login using a User instance:

``` php
$result = McfAuth::loginByUser($user);
```

Authentication operations return `McfResult` states instead of relying
only on booleans.

Example:

``` php
if ($result->is(AuthenticationResult::SUCCESS)) {
    // Login successful.
}
```

------------------------------------------------------------------------

## 3. Access Control

MCF Access Control separates two concerns:

``` text
Guard
   ↓
Who can access the Route?

Access + Permissions
   ↓
What can the user do after Route access is allowed?
```

### Guards

Supported guards include:

``` text
any
guest
auth
role
```

Examples:

``` php
new AnyRouteAccess(
    routeNames: ['home'],
);
```

``` php
new GuestRouteAccess(
    routeNames: ['user.auth.login'],
);
```

``` php
new AuthRouteAccess(
    routeNames: ['user.profile.index'],
);
```

Role-based access:

``` php
new RoleRouteAccess(
    routeNames: ['admin.users.index'],
    roles: [
        new RoleData(role: 1),
    ],
)
```

### Access Modes

The permission list can be interpreted as:

``` text
all
none
only
except
```

For example:

``` php
new AuthRouteAccess(
    routeNames: ['users.index'],
    access: 'only',
    permissions: ['create', 'update'],
)
```

This means:

``` text
create → allowed
update → allowed
delete → denied
export → denied
```

The important rule is:

> **Guard controls Route access. Access and Permissions control Actions
> after Route access.**

------------------------------------------------------------------------

## 4. Audit

MCF Audit provides structured audit logging for important operations.

It can record:

-   user;
-   user role;
-   route;
-   action;
-   operation description;
-   IP address;
-   User Agent;
-   change details when applicable.

Audit integrates with MCF Authentication and can optionally integrate
with MCF Notification.

To enable auditing on a Model:

``` php
use App\MCF\Audit\McfAuditable;

class User extends Authenticatable
{
    use McfAuditable;
}
```

Auditing is explicitly defined through `AuditDefinition`.

Example:

``` php
new AuditDefinition(
    action: 'update',
    columns: ['email'],
    condition: null,
    message: 'The user updated the email.',
)
```

This means that an update affecting `email` can produce an audit record
according to the defined rule.

MCF Audit uses:

``` text
database/migrations/
    0002_create_mcf_audit_logs_table.php

app/Models/
    AuditLog.php
```

The main database table is:

``` text
audit_logs
```

------------------------------------------------------------------------

## 5. Language

MCF Language provides one centralized translation layer.

The main rules are:

-   one JSON file per language;
-   files live in the MCF Language directory;
-   MCF automatically discovers and loads them;
-   Laravel's translation system remains the underlying system;
-   the original text is used as the translation key;
-   optional Section Markers can organize large files.

Example:

``` text
MCF/
└── Language/
    ├── ar.json
    ├── en.json
    └── fr.json
```

Example:

``` json
{
    "Login successful.": "تم تسجيل الدخول بنجاح.",
    "Invalid credentials.": "بيانات الدخول غير صحيحة."
}
```

The key is the original text:

``` text
Key   = Original text
Value = Translation
```

Section markers may be used for organization:

``` json
{
    "--- User | Authentication ---": "----------------------------------------",
    "Login successful.": "تم تسجيل الدخول بنجاح."
}
```

Section markers are organizational only; they are not real translation
keys.

------------------------------------------------------------------------

## 6. Mail

MCF Mail is a lightweight wrapper around Laravel Mail.

Instead of repeatedly calling Laravel Mail directly:

``` php
Mail::to($to)->send($mail);
```

MCF provides:

``` php
McfMail::send($to, $mail);
```

It supports:

``` text
send
queue
later
```

Examples:

``` php
McfMail::send(
    $user->email,
    new VerifyEmailLinkMail($user),
);
```

``` php
McfMail::queue(
    $user->email,
    new VerifyEmailCodeMail($user),
);
```

``` php
McfMail::later(
    60,
    $user->email,
    new ResetPasswordCodeMail($user),
);
```

MCF does not recreate Laravel's Mailable system. Mail content remains
inside Laravel `Mailable` classes.

------------------------------------------------------------------------

## 7. Notification

MCF Notification wraps Laravel's native Notification system.

The goal is a unified MCF API without replacing Laravel Notifications.

The main components are:

``` text
NotificationData
NotificationRequest
McfNotification
McfNotificationCenter
NotificationSettings
```

`NotificationData` represents notification content:

``` text
message
title
url
```

Example:

``` php
$notification = NotificationData::passwordUpdated();
```

The User model should use Laravel's native:

``` php
use Illuminate\Notifications\Notifiable;
```

MCF uses Laravel's standard `notifications` table rather than creating a
separate notification table.

Stored notifications remain accessible through Laravel relationships:

``` php
$user->notifications;
$user->unreadNotifications;
$user->readNotifications;
```

This keeps MCF compatible with Laravel's notification infrastructure.

------------------------------------------------------------------------

## 8. SMS

MCF SMS separates application logic from the SMS provider.

A Workflow uses:

``` php
McfSms::send(
    $user->phone,
    $message,
);
```

The architecture is:

``` text
Workflow
   ↓
McfSms
   ↓
SmsProviderContract
   ↓
SMS Provider
```

Provider examples include:

``` text
TwilioSmsService
VonageSmsService
```

The Workflow does not need to know which provider is currently being
used.

Changing the provider changes the provider selection rather than every
Workflow call site.

This is the core benefit of the Provider abstraction.

------------------------------------------------------------------------

## 9. Storage

MCF Storage provides a provider-independent file storage abstraction for
application features.

It keeps application Modules independent from a concrete physical
storage backend while providing a consistent API for file operations.

The main concepts are:

``` text
McfStorage
StorageReference
StorageRecord
McfFileData
McfStorageResult
McfStorageMultiResult
StorageRegistry
StorageProvider
```

### 11. Storage Architecture

``` text
Application
    │
    ▼
McfStorage
    ├── StorageRegistry
    │       │
    │       ▼
    │   MCF Storage Records
    │
    └── StorageProvider
            │
            ▼
        Physical Storage
```

The `StorageRegistry` stores the MCF information about a file.

The `StorageProvider` performs the physical storage operations.

The physical file is not stored in the registry.

### 11. StorageReference

`StorageReference` is the internal identity of a stored file.

The original filename is not used as the physical storage identity.

Example:

``` text
Original name:
invoice.pdf

Storage reference:
20260818023227059301.pdf
```

The original filename remains part of the storage record and is used for
user-facing downloads.

### 11. StorageRecord

`StorageRecord` represents the MCF registry information for one stored
file.

It can contain:

``` text
reference
originalName
extension
type
mimeType
size
folder
provider
storageRoot
access
createdAt
updatedAt
```

A single record can be retrieved with:

``` php
$result = McfStorage::find($reference);
```

Bulk workflows should use the registry's bulk lookup capability where
appropriate instead of repeatedly querying one reference at a time.

### 11. StorageRegistry

`StorageRegistry` is responsible for storing and retrieving MCF storage
records.

It provides operations such as:

``` php
$registry->all();

$registry->find($reference);

$registry->findMany($references);

$registry->create($data);

$registry->update($reference, $data);

$registry->delete($reference);

$registry->exists($reference);
```

The registry represents metadata and storage identity. It does not
replace the physical storage provider.

### 11. StorageProvider

`StorageProvider` is the contract implemented by a physical storage
backend.

A provider is responsible for:

-   uploading files;
-   generating public URLs;
-   generating temporary URLs;
-   downloading files;
-   deleting files;
-   checking file existence;
-   returning provider metadata.

The provider contract includes operations equivalent to:

``` php
upload(...)
publicUrl(...)
temporaryUrl(...)
download(...)
delete(...)
exists(...)
metadata(...)
```

A provider may use Laravel Filesystem, S3, or another storage service.

The implementation detail remains hidden from application Modules.

#### Multiple Providers

Multiple storage providers can coexist in the same application:

``` text
                    McfStorage
                        │
                StorageProvider
                  /     │      \
                 ▼      ▼       ▼
             Laravel    S3    Custom
```

A storage record identifies its backend through information such as:

``` text
provider
storageRoot
folder
reference
```

This allows different files to use different storage backends while the
application continues to use the same MCF Storage API.

Adding another provider requires implementing the `StorageProvider`
contract and registering the provider with MCF's provider resolution
mechanism.

Application-level storage calls do not need to change.

### Upload

Single-file upload:

``` php
$data = new McfFileData(
    file: $file,
    folder: 'documents',
    access: 'protected',
);

$result = McfStorage::upload($data);
```

For multiple files:

``` php
$result = McfStorage::uploadMany($dataList);
```

Multi-upload is treated as one operation and is intended to provide
all-or-fail behavior at the workflow level, with cleanup of files
already uploaded when a later part of the operation fails where
possible.

### View and Access

MCF Storage supports:

``` text
public
protected
```

A public file can receive a permanent public source.

A protected file receives a temporary source with a limited lifetime.

``` php
$result = McfStorage::view($reference);

if ($result->isSuccess()) {
    $source = $result->data->source;
}
```

Protected storage is a storage access policy for the generated source.
It is not a replacement for application authorization.

Application permissions remain the responsibility of Access Control.

### Download

Single-file download:

``` php
$result = McfStorage::download($reference);

if ($result->isSuccess()) {
    return $result->data;
}
```

The physical storage identity is the reference, but the provider
receives the original filename for the user-facing download.

Therefore:

``` text
Physical storage:
20260818023227059301.pdf

User download:
invoice.pdf
```

### Multi Download

Multiple references can be downloaded as one archive:

``` php
$result = McfStorage::downloadMany([
    $reference1,
    $reference2,
    $reference3,
]);
```

The multi-download workflow:

1.  normalizes references;
2.  removes duplicates;
3.  retrieves records through bulk lookup;
4.  validates the records and providers;
5.  reads the physical files;
6.  creates a ZIP archive;
7.  uses original filenames inside the archive;
8.  resolves duplicate original filenames safely.

An archive name can include a timestamp, for example:

``` text
mcf-storage-20260818023227059301.zip
```

### Delete

Single deletion:

``` php
$result = McfStorage::delete($reference);
```

Multiple deletion:

``` php
$result = McfStorage::deleteMany([
    $reference1,
    $reference2,
    $reference3,
]);
```

Bulk deletion uses bulk registry lookup where appropriate, then removes
the physical files through their providers and the corresponding MCF
records.

### Metadata and Existence

Provider metadata:

``` php
$result = McfStorage::metadata($reference);
```

Existence:

``` php
$result = McfStorage::exists($reference);
```

`find()` retrieves the MCF storage record, while `metadata()` retrieves
provider-level file metadata.

### Single vs Multi Operations

The recommended application behavior is:

``` text
0 selected
    → no operation

1 selected
    → single operation

2+ selected
    → multi operation
```

For example:

``` text
1 selected
    → McfStorage::download()

2+ selected
    → McfStorage::downloadMany()
```

and:

``` text
1 selected
    → McfStorage::delete()

2+ selected
    → McfStorage::deleteMany()
```

#### Storage Results

Single operations return:

``` php
McfStorageResult
```

Bulk operations return:

``` php
McfStorageMultiResult
```

This keeps Storage consistent with MCF's Result architecture.

#### Provider Independence

Application Modules should depend on:

``` php
McfStorage::upload($data);
```

rather than directly calling a concrete storage implementation.

The intended dependency direction is:

``` text
Workflow / Service
        │
        ▼
   McfStorage
        │
        ├── StorageRegistry
        │
        └── StorageProvider
```

This allows the physical storage backend to change without rewriting
application features.

## 10. Middleware

MCF provides framework-level Middleware:

``` text
Middleware/
├── McfAccessMiddleware.php
├── McfSessionSecurityMiddleware.php
└── SetLocaleMiddleware.php
```

### McfAccessMiddleware

Connects request processing to MCF Access Control.

### McfSessionSecurityMiddleware

Handles Session Security integration.

### SetLocaleMiddleware

Sets the Locale used during the request and integrates with MCF
Language.

The Middleware are integrated through Laravel's Bootstrap configuration.

Their usage is configurable.

Recommended approach:

``` text
Keep the framework Middleware
        ↓
Enable when needed
        ↓
Configure according to the project
```

Do not delete framework Middleware simply because a project is not
currently using the related feature.

------------------------------------------------------------------------

## 11. Result

MCF Result provides an optional pattern for standardized operation
results.

Instead of:

``` php
return 'success';
```

or:

``` php
return 'invalid';
```

a Workflow can define a dedicated Result:

``` php
final class AuthenticationResult extends McfResult
{
    public const SUCCESS = 'success';

    public const INVALID_CREDENTIALS = 'invalid_credentials';
}
```

Then:

``` php
$result = new AuthenticationResult(
    AuthenticationResult::SUCCESS,
);
```

Check it with:

``` php
if ($result->is(AuthenticationResult::SUCCESS)) {
    // Success
}
```

Or read the raw value:

``` php
$value = $result->result();
```

Result classes can be organized by Workflow.

Result is optional, but recommended for larger applications where
standardized operation states improve clarity.

------------------------------------------------------------------------

# Database

MCF deliberately follows Laravel's standard database architecture.

Migrations remain in:

``` text
database/migrations/
```

Models remain in:

``` text
app/Models/
```

MCF does **not** create an MCF-specific migration directory.

Example:

``` text
database/
└── migrations/
    ├── 0000_create_laravel_tables.php
    ├── 0001_create_mcf_auth_tables.php
    └── 0002_create_mcf_audit_logs_table.php
    └── 0003_create_mcf_storage_table.php
```

The exact migrations depend on the MCF components installed and used.

MCF-provided Models can be:

-   used as provided;
-   extended;
-   adapted to project requirements;
-   given additional columns.

However, when a framework component depends on a specific schema, its
Model and database structure must remain compatible with that component.

### Database Principle

MCF does not attempt to replace Laravel's database layer.

``` text
Laravel Database
        ↑
      MCF
        ↑
Modules / Workflows
```

The database remains part of the normal Laravel application.

------------------------------------------------------------------------

# Routes

MCF reorganizes application Routes around Workflows.

Instead of keeping application Routes in:

``` text
routes/web.php
```

MCF uses:

``` text
mcf_routes.php
```

The routing flow is:

``` text
bootstrap/app.php
        ↓
mcf_routes.php
        ↓
Workflow Route Files
```

Each Workflow owns its own Route file:

``` text
Modules/
└── User/
    ├── Auth/
    │   └── Backend/
    │       └── AuthRoutes.php
    │
    └── Profile/
        └── Backend/
            └── ProfileRoutes.php
```

The central `mcf_routes.php` file loads the Workflow Route files.

Example:

``` php
require_once __DIR__
    . '/Modules/User/Auth/Backend/AuthRoutes.php';
```

The main Route file is therefore a collector, not a place to put every
application's Route definition.

This keeps Routes close to the Workflow that owns them.

------------------------------------------------------------------------

# Resources and Public

MCF does not create a separate Resource or Public file system.

Laravel's standard locations remain authoritative:

``` text
resources/
public/
```

Resources may include:

``` text
resources/views/
resources/css/
resources/js/
```

Blade Views remain under:

``` text
resources/views/
```

Public browser-accessible assets remain under:

``` text
public/
```

This is intentional.

MCF organizes application architecture without unnecessarily changing
Laravel's standard resource conventions.

------------------------------------------------------------------------

# Endpoint Generator

The Endpoint Generator is the preferred way to create a complete
Endpoint inside an existing Workflow.

Command:

``` bash
php artisan mcf:endpoint:create
```

The generator can structurally connect:

``` text
Route
Controller Method
View
Request
```

depending on the selected options.

An Endpoint represents one executable action inside a Workflow.

Example:

``` text
Authentication
├── login
├── loginPost
├── logout
├── forgotPassword
└── resetPassword
```

Each Endpoint belongs to exactly one Workflow.

### Generator First

Prefer the generator for structural changes instead of manually
maintaining:

-   Controller boilerplate;
-   Routes;
-   Endpoint Views;
-   Endpoint Requests.

The generator handles framework structure.

The developer remains responsible for business logic.

### Request Integration

Requests are independent MCF resources.

The current architecture does not use the old shared Workflow Request
concept.

If an Endpoint requires a Request:

``` text
Endpoint:
login

Request:
LoginRequest.php
```

The generated Controller can use:

``` php
public function login(LoginRequest $request)
```

The Endpoint Generator follows the same Request architecture as:

``` bash
php artisan mcf:make:request
```

An existing Request must not be silently overwritten.

If the Request has already been created independently, the Endpoint
creation process handles that state explicitly.

------------------------------------------------------------------------

# Requests and Data

Requests can be created independently from Endpoints.

Command:

``` bash
php artisan mcf:make:request User Auth Login
```

The Request belongs to:

``` text
Module
   ↓
Workflow
   ↓
Request
```

If the Workflow does not yet have a `Request` directory, the generator
creates it.

The generated Request can define a Data class:

``` php
protected function dataClass(): ?string
{
    return LoginData::class;
}
```

This provides a clean:

``` text
Request
   ↓
Validation
   ↓
Data
   ↓
Service
```

boundary.

The Endpoint can then depend on the Request without coupling itself to
the old shared Workflow Request structure.

------------------------------------------------------------------------

# MCF CLI

All MCF Artisan commands use the `mcf:` prefix so they remain clearly
distinguishable from Laravel's native commands.

Current commands:

``` text
mcf:install
mcf:make:module
mcf:make:workflow
mcf:make:workflow:crud
mcf:make:workflow:layout
mcf:remove:workflow
mcf:make:request
mcf:endpoint:create
mcf:endpoint:remove
mcf:make:middleware
mcf:make:mail
```

## Installation

``` bash
php artisan mcf:install
```

The installer prepares the MCF application structure.

Installation is intended to run once per Laravel project.

MCF uses an installation marker:

``` text
app/MCF/.mcf-installed
```

A project that already contains the MCF installation marker should not
be installed again.

## Module

``` bash
php artisan mcf:make:module
```

Creates a top-level application Module.

## Standard Workflow

``` bash
php artisan mcf:make:workflow
```

Creates a standard Workflow inside an existing Module.

Typical structure:

``` text
User/Profile/
├── Backend/
├── Lang/
└── Views/
```

## CRUD Workflow

``` bash
php artisan mcf:make:workflow:crud
```

Use this for resource-oriented features such as:

``` text
Products
Customers
Employees
Categories
```

## Layout Workflow

``` bash
php artisan mcf:make:workflow:layout
```

Provides a reusable presentation/layout structure.

The initial MCF installation includes a shared Layout Workflow.

## Request

``` bash
php artisan mcf:make:request User Auth Login
```

Creates an independent Request inside the selected Workflow.

## Endpoint

``` bash
php artisan mcf:endpoint:create
```

Creates a complete Endpoint structure interactively.

## Remove Endpoint

``` bash
php artisan mcf:endpoint:remove
```

Removes an Endpoint from its Workflow structure.

## Remove Workflow

``` bash
php artisan mcf:remove:workflow
```

Removes an existing Workflow.

## Middleware

``` bash
php artisan mcf:make:middleware
```

Creates MCF Middleware using the framework's conventions.

## Mail

``` bash
php artisan mcf:make:mail
```

Creates an MCF Mail class following the framework structure.

------------------------------------------------------------------------

# Installation

MCF is designed to be installed into a Laravel application.

After the package is available to the project, run:

``` bash
php artisan mcf:install
```

The installer warns that installation modifies the Laravel application's
structure.

MCF installation is intended for a new Laravel project or a project
where the developer understands the structural changes being made.

After installation, configure the project's database connection in
`.env`.

Then run:

``` bash
php artisan migrate --seed
```

The exact database requirements depend on the MCF components being used.

Mail configuration is optional unless the application uses email
features such as authentication emails, notifications, or other mail
delivery.

After installation, MCF documentation is available under:

``` text
app/MCF/z_Guide
```

------------------------------------------------------------------------

# What Developers Should and Should Not Change

MCF is intended to provide conventions, not to take ownership of the
entire Laravel application.

## Keep Laravel's standard locations

Keep these in their normal Laravel locations:

``` text
app/Models/
database/migrations/
resources/
public/
```

MCF does not move them into `app/MCF`.

## Keep the MCF framework structure

The framework directories provide stable locations for shared
components.

Prefer:

``` text
Keep
Configure
Disable when appropriate
```

over deleting framework directories simply because a feature is not
currently used.

## Database Components

MCF database components are tied to the framework features that use
them.

Before removing an optional MCF migration, Model, or component, confirm
that the related feature is not being used and that no other component
depends on it.

## Modules and Workflows

Application business logic belongs in Modules and Workflows.

Do not turn a Workflow into a database-table container.

Prefer:

``` text
User
├── Auth
├── Profile
└── Management
```

over:

``` text
User
└── UserTable
```

------------------------------------------------------------------------

# Recommended Development Flow

For a new feature, the recommended structural flow is:

``` text
1. Identify the domain
        ↓
2. Create or select the Module
        ↓
3. Create the Workflow
        ↓
4. Create independent Requests when needed
        ↓
5. Generate Endpoints
        ↓
6. Implement Service / business logic
        ↓
7. Define Routes and Access
        ↓
8. Add Results when useful
        ↓
9. Add Audit / Notification / Mail / SMS integration when required
```

Example:

``` bash
php artisan mcf:make:module
```

Then:

``` bash
php artisan mcf:make:workflow
```

Then, if validation is needed before Endpoint generation:

``` bash
php artisan mcf:make:request User Auth Login
```

Then:

``` bash
php artisan mcf:endpoint:create
```

The developer then implements the actual business behavior.

------------------------------------------------------------------------

# Design Principles

## Laravel First

MCF builds on Laravel instead of replacing it.

Laravel remains responsible for the underlying:

``` text
Authentication
Database
Mail
Notifications
Routing
HTTP
Eloquent
Storage
```

MCF adds organization, conventions, wrappers, and framework-level
abstractions where they provide value.

For file storage, MCF Storage adds a provider-independent abstraction
and registry/reference layer on top of Laravel's underlying storage
capabilities; it does not replace Laravel Filesystem.

## Feature-Oriented Architecture

Code is organized around what the application does rather than only
around database entities.

``` text
Module
   ↓
Workflow
   ↓
Business Capability
```

## Separation of Responsibilities

Each framework component should have one clear concern.

For example:

``` text
Authentication → authentication
AccessControl  → route/action authorization
Audit          → audit logging
Mail           → email delivery
Notification   → application notifications
Sms            → SMS delivery
Language       → translations
Result         → operation states
Middleware     → request-level framework behavior
Base           → common framework foundations
```

## Provider Abstraction

Where an external provider may change, MCF uses an abstraction so
Workflows do not need to know provider-specific implementation details.

The SMS architecture is an example:

``` text
Workflow
   ↓
McfSms
   ↓
Provider Contract
   ↓
Provider
```

This allows the provider to change without rewriting Workflow call
sites.

## Generator First

When MCF provides a generator for a structural operation, prefer the
generator.

This keeps:

``` text
Controllers
Routes
Requests
Views
```

consistent with the framework's conventions.

## Predictability

A developer should be able to look at an MCF project and quickly answer:

``` text
Where is this feature?
Where is its Workflow?
Where are its Routes?
Where is its Controller?
Where is its Request?
Where are its Views?
Which framework component provides this behavior?
```

That predictability is one of the primary goals of MCF.

------------------------------------------------------------------------

# Final Architecture Summary

MCF can be understood as four connected layers:

``` text
┌─────────────────────────────────────────────┐
│                 Laravel                     │
│                                             │
│  Database · Eloquent · Auth · Mail · HTTP   │
│  Notifications · Resources · Public · ...  │
└──────────────────────┬──────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────┐
│                    MCF                      │
│                                             │
│ Base · Authentication · Access · Audit      │
│ Language · Mail · Notification · SMS        │
│ Middleware · Result                         │
└──────────────────────┬──────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────┐
│            Application Architecture         │
│                                             │
│ Module                                       │
│    ↓                                        │
│ Workflow                                     │
│    ↓                                        │
│ Endpoint / Request / Service / View         │
└──────────────────────┬──────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────┐
│               Application Data              │
│                                             │
│ Models · Migrations · Resources · Public    │
└─────────────────────────────────────────────┘
```

**MCF's purpose is not to replace Laravel. Its purpose is to make a
Laravel application easier to structure, extend, and maintain as the
application grows.**

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
