# MCF CLI

MCF provides a collection of Artisan commands for creating and managing
the application's MCF architecture.

The commands automate repetitive development tasks such as creating
Modules, Workflows, Requests, Endpoints, Middleware, and Mail classes
while keeping generated code consistent with the MCF structure.

All MCF commands are registered under the `mcf:` prefix so they are
clearly distinguishable from Laravel's native Artisan commands.

------------------------------------------------------------------------

# Command List

The current MCF CLI includes:

  Command                      Purpose
  ---------------------------- ----------------------------------------
  `mcf:install`                Install MCF into a Laravel application
  `mcf:make:module`            Create a Module
  `mcf:make:workflow`          Create a standard Workflow
  `mcf:make:workflow:crud`     Create a CRUD Workflow
  `mcf:make:workflow:layout`   Create a Layout Workflow
  `mcf:remove:workflow`        Remove a Workflow
  `mcf:make:request`           Create a Request
  `mcf:endpoint:create`        Create a complete Endpoint
  `mcf:endpoint:remove`        Remove an Endpoint
  `mcf:make:middleware`        Create MCF Middleware
  `mcf:make:mail`              Create an MCF Mail class

The commands above are the current MCF command set.

------------------------------------------------------------------------

# Installation

Install MCF into a Laravel application:

``` bash
php artisan mcf:install
```

The installer prepares the MCF application structure and installs the
framework resources required by the architecture.

The installer is intended to run once per Laravel project.

A project that already contains the MCF installation marker must not be
installed again.

After installation, follow the instructions displayed by the installer
for database and optional mail configuration.

------------------------------------------------------------------------

# Modules

Create a new Module:

``` bash
php artisan mcf:make:module
```

The command interactively asks for the Module name.

A Module is the top-level container for related application Workflows.

Example:

``` text
User
```

Generated location:

``` text
app/MCF/Modules/User
```

Create the Module before creating its Workflows.

------------------------------------------------------------------------

# Standard Workflow

Create a standard Workflow:

``` bash
php artisan mcf:make:workflow
```

The generator asks for:

-   Module
-   Workflow name

Example:

``` text
Module: User
Workflow: Profile
```

Typical structure:

``` text
app/MCF/Modules/User/Profile
├── Backend
├── Lang
└── Views
```

Use a standard Workflow for a business feature or process.

------------------------------------------------------------------------

# CRUD Workflow

Create a CRUD Workflow:

``` bash
php artisan mcf:make:workflow:crud
```

A CRUD Workflow provides the structure required for resource-oriented
features.

Typical examples:

-   Products
-   Customers
-   Employees
-   Categories

Use a standard Workflow when the feature represents a broader business
process rather than basic resource management.

------------------------------------------------------------------------

# Layout Workflow

Create a Layout Workflow:

``` bash
php artisan mcf:make:workflow:layout
```

The Layout Workflow provides a reusable presentation/layout structure
for the application.

The initial MCF installation includes a shared Layout Workflow.

The Layout Workflow is intended to remain available as a shared
application resource and should not normally be removed unless the
application has a deliberate replacement.

------------------------------------------------------------------------

# Remove Workflow

Remove an existing Workflow:

``` bash
php artisan mcf:remove:workflow
```

The command targets a specific Workflow and removes its associated
files.

Only the selected Workflow is affected.

Do not remove framework-level MCF components simply because they are not
currently used. MCF components may be interconnected and can usually be
disabled through their configuration where supported.

------------------------------------------------------------------------

# Request

Create a Request for an existing Module and Workflow:

``` bash
php artisan mcf:make:request <module> <workflow> <request>
```

Example:

``` bash
php artisan mcf:make:request User Auth Login
```

This creates:

``` text
app/MCF/Modules/User/Auth/Backend/Request/LoginRequest.php
```

If the Workflow does not already contain a `Request` directory, MCF
creates it.

Requests are independent from Workflows. A developer can create a
Request before creating an Endpoint and use it later.

The generated Request follows the MCF Request structure and can define
its own Data class.

The Request name is normalized so that the first character is uppercase
and the remaining characters are lowercase.

------------------------------------------------------------------------

# Endpoint

Create a complete Endpoint:

``` bash
php artisan mcf:endpoint:create
```

Endpoint creation is an all-or-nothing operation.

An Endpoint represents a complete HTTP feature path rather than a
partial modification.

Depending on the selected options, the generator can create and connect:

-   Controller method
-   Route
-   View
-   Endpoint-specific Request

If a Request is selected, the Request belongs to the Endpoint and is
connected directly to the generated Controller method.

The current architecture no longer uses the old shared Workflow Request
concept.

------------------------------------------------------------------------

# Remove Endpoint

Remove an Endpoint:

``` bash
php artisan mcf:endpoint:remove <module> <workflow> <endpoint>
```

Example:

``` bash
php artisan mcf:endpoint:remove User Auth Login
```

The command removes the selected Endpoint from its Workflow.

The endpoint's associated resources are removed according to the current
MCF Endpoint structure.

The remaining Workflow is preserved.

Endpoint removal targets the complete Endpoint rather than removing an
unrelated partial piece.

------------------------------------------------------------------------

# Middleware

Create MCF Middleware:

``` bash
php artisan mcf:make:middleware
```

The command generates Middleware according to the MCF Middleware
conventions.

Use MCF Middleware for reusable request-pipeline behavior that belongs
to the application's shared infrastructure.

------------------------------------------------------------------------

# Mail

Create an MCF Mail class:

``` bash
php artisan mcf:make:mail
```

The generator creates a Mail class following the MCF Mail conventions.

Mail configuration itself is controlled through the application's `.env`
and Laravel mail configuration. The MCF installer does not automatically
configure a developer's mail provider credentials.

------------------------------------------------------------------------

# Interactive Commands

Several MCF commands are interactive.

For example:

``` bash
php artisan mcf:make:module
php artisan mcf:make:workflow
php artisan mcf:make:workflow:crud
php artisan mcf:make:workflow:layout
php artisan mcf:make:request
php artisan mcf:endpoint:create
```

The command guides the developer through the required information.

Commands that require a complete set of arguments expose those arguments
directly in the command signature, such as:

``` bash
php artisan mcf:make:request User Auth Login
```

and:

``` bash
php artisan mcf:endpoint:remove User Auth Login
```

------------------------------------------------------------------------

# Generated Code

MCF generators follow the installed MCF architecture and conventions.

Generated code is intended to be:

-   Consistent.
-   Predictable.
-   Organized.
-   Ready for customization.

Developers should customize the generated classes according to the
application's requirements rather than changing the underlying generator
conventions unnecessarily.

------------------------------------------------------------------------

# Recommended Development Order

For a new feature, the usual flow is:

``` text
Module
  ↓
Workflow
  ↓
Request / Endpoint
  ↓
Business implementation
```

For example:

``` bash
php artisan mcf:make:module
php artisan mcf:make:workflow
php artisan mcf:make:request User Auth Login
php artisan mcf:endpoint:create
```

A Request does not require an Endpoint. It can be generated
independently and connected later.

------------------------------------------------------------------------

# CLI Best Practices

-   Use the `mcf:` commands for MCF-managed resources.
-   Create the Module before creating its Workflows.
-   Prefer generators over manually creating framework-structured files.
-   Use CRUD Workflows for resource-oriented features.
-   Use standard Workflows for business processes.
-   Create Requests independently when they are useful before an
    Endpoint exists.
-   Use Endpoint generation to keep Controller, Route, View, and Request
    integration consistent.
-   Keep generated resources inside their owning Module and Workflow.
-   Remove only the intended Workflow or Endpoint.
-   Do not delete MCF framework components merely because they are
    unused; disable them through their supported settings when
    appropriate.
-   Keep the installed MCF structure intact because its components may
    depend on one another.

------------------------------------------------------------------------

# Summary

MCF's CLI provides commands for the current architecture:

``` text
Installation
    mcf:install

Modules
    mcf:make:module

Workflows
    mcf:make:workflow
    mcf:make:workflow:crud
    mcf:make:workflow:layout
    mcf:remove:workflow

Requests
    mcf:make:request

Endpoints
    mcf:endpoint:create
    mcf:endpoint:remove

Shared Components
    mcf:make:middleware
    mcf:make:mail
```

The CLI exists to keep the MCF structure consistent while reducing
repetitive manual work.
