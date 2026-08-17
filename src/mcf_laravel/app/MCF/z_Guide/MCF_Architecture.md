# MCF Architecture

MCF (Modular Code Framework) is a feature-oriented application
architecture built on top of Laravel.

MCF keeps Laravel as the underlying application framework while
providing a structured application root, shared framework components,
Modules and Workflows, generated application resources, and conventions
for how those parts work together.

The MCF architecture is broader than the `Modules` directory. `app/MCF`
is the installed MCF root and contains the framework components,
application Modules, documentation, and the framework files that connect
the MCF structure to Laravel.

> MCF does not replace Laravel. It provides an organized application
> architecture on top of Laravel.

------------------------------------------------------------------------

## Architecture Overview

After MCF installation, the main MCF structure is located under:

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
    ├── z_Guide/
    ├── .mcf-installed
    └── mcf_routes.php
```

Each part has a different architectural purpose.

The most important distinction is:

``` text
app/MCF
├── Framework components
├── Application features
├── Documentation
└── MCF integration files
```

`Modules` are therefore a major part of MCF, but they are not the entire
MCF architecture.

------------------------------------------------------------------------

# Architecture Goals

MCF is designed to:

-   Organize application code around business capabilities.
-   Keep feature-specific resources together.
-   Provide reusable framework-level components.
-   Reduce unnecessary coupling.
-   Separate responsibilities clearly.
-   Reduce repetitive work through generators.
-   Keep Laravel compatibility.
-   Provide predictable conventions.
-   Support applications as they grow.
-   Make framework behavior configurable instead of requiring developers
    to delete unused framework directories.

------------------------------------------------------------------------

# The MCF Root

The `app/MCF` directory is the central root of the installed MCF
architecture.

It contains four broad categories:

``` text
app/MCF
│
├── Framework Components
│
├── Modules
│
├── Documentation
│
└── MCF Integration Files
```

### Framework Components

These provide reusable infrastructure for the application:

``` text
AccessControl
Audit
Authentication
Base
Language
Mail
Middleware
Notification
Result
Sms
```

### Modules

`Modules` contains the application's feature-oriented structure.

### Documentation

`z_Guide` contains the MCF documentation installed with the framework.

### Integration Files

Files such as:

``` text
.mcf-installed
mcf_routes.php
```

support the installed MCF structure and its integration with the Laravel
application.

------------------------------------------------------------------------

# Framework Components

The framework-level directories under `app/MCF` provide functionality
that can be shared by multiple features.

``` text
app/MCF
├── AccessControl
├── Audit
├── Authentication
├── Base
├── Language
├── Mail
├── Middleware
├── Notification
├── Result
└── Sms
```

These components should not be treated as individual application
features.

For example:

-   Authentication provides authentication infrastructure.
-   Audit provides auditing infrastructure.
-   AccessControl provides access-related infrastructure.
-   Base provides common base classes.
-   Mail provides MCF mail functionality.
-   Notification provides notification infrastructure.
-   Result provides standardized result handling.
-   Sms provides SMS-related functionality.

Each component has its own detailed documentation.

------------------------------------------------------------------------

# Modules

`Modules` is the feature-oriented part of the MCF architecture.

``` text
app/MCF/Modules
```

A typical installed application may contain:

``` text
Modules
├── Shared
│   └── Layout
└── User
    ├── Auth
    ├── Profile
    └── UserManagement
```

A Module groups related business capabilities.

A Workflow inside a Module represents a specific feature or business
capability.

Therefore:

``` text
MCF
└── Modules
    └── Module
        └── Workflow
```

Modules and Workflows are only one part of the larger `app/MCF`
architecture.

------------------------------------------------------------------------

# Workflows

A Workflow represents a business capability or feature.

Examples:

-   Auth
-   Profile
-   User Management
-   Checkout
-   Product Catalog
-   Reports

A Workflow should represent a meaningful application capability, not
simply a database table.

Example:

``` text
User
├── Auth
├── Profile
└── UserManagement
```

A Workflow contains the resources that belong specifically to that
feature.

------------------------------------------------------------------------

# Workflow Structure

A Workflow may contain:

``` text
User
└── Auth
    ├── Backend
    ├── Lang
    └── Views
```

The exact contents depend on the feature.

For example, the Backend may contain:

``` text
Backend
├── AuthController.php
├── AuthRoutes.php
├── AuthService.php
└── Request
    └── LoginRequest.php
```

Not every Workflow needs every type of resource.

------------------------------------------------------------------------

# Backend

The `Backend` directory contains the server-side implementation of a
Workflow.

Typical resources include:

-   Controller.
-   Routes.
-   Services.
-   Requests.
-   Other feature-specific backend classes.

The Backend is feature-specific. Shared framework functionality belongs
under the appropriate MCF framework component instead of being
duplicated inside every Workflow.

------------------------------------------------------------------------

# Controllers

Controllers handle HTTP communication.

Typical responsibilities are:

-   Receive HTTP requests.
-   Delegate application logic.
-   Return responses.

Controllers should remain focused on HTTP interaction.

Business logic should be placed in the appropriate Service or
application component.

------------------------------------------------------------------------

# Services

Services contain application and business logic.

Typical responsibilities include:

-   Business rules.
-   Data operations.
-   Workflow coordination.
-   Domain/application operations.

Services and Controllers have different responsibilities and should not
become substitutes for one another.

------------------------------------------------------------------------

# Requests

Requests contain validation and request-related authorization.

MCF provides:

``` bash
php artisan mcf:make:request {module} {workflow} {request}
```

Example:

``` bash
php artisan mcf:make:request User Auth Login
```

The Request is created inside the Workflow's `Request` directory.

If the directory does not exist, MCF creates it.

The current architecture uses endpoint-specific Requests rather than the
old shared Workflow Request concept.

A Request may also define a dedicated Data class for validated input.

Example:

``` php
final class LoginRequest extends MfcRequest
{
    protected function dataClass(): ?string
    {
        return LoginData::class;
    }
}
```

------------------------------------------------------------------------

# Endpoints

An Endpoint represents a complete HTTP feature path.

MCF provides:

``` bash
php artisan mcf:endpoint:create
```

Depending on the selected options, endpoint creation can generate and
connect:

-   A Controller method.
-   A Route.
-   A View.
-   A Request specific to the Endpoint.

When an Endpoint uses a Request, that Request is connected directly to
the Endpoint's Controller method.

Endpoint generation is treated as a complete operation rather than a
collection of unrelated partial operations.

------------------------------------------------------------------------

# Routes

Each Workflow owns its route file.

Example:

``` text
Auth
└── Backend
    └── AuthRoutes.php
```

MCF also provides:

``` text
app/MCF/mcf_routes.php
```

This file belongs to the MCF route integration layer.

It is separate from the individual Workflow route files.

The architecture therefore has two different route concepts:

``` text
Workflow
└── Backend
    └── WorkflowRoutes.php
```

for feature-specific routes, and:

``` text
app/MCF/mcf_routes.php
```

for the MCF-level route integration.

MCF discovers and registers the appropriate Workflow routes through its
framework integration.

------------------------------------------------------------------------

# Views

A Workflow may contain its own Blade views:

``` text
Views
├── index.blade.php
├── create.blade.php
└── edit.blade.php
```

Views stay close to the feature that owns them.

MCF registers Workflow view namespaces according to its conventions.

------------------------------------------------------------------------

# Language Resources

A Workflow may contain its own language resources:

``` text
Lang
```

This keeps translations close to the feature that uses them.

MCF handles the registration of Workflow language resources according to
its framework conventions.

------------------------------------------------------------------------

# Base Classes

MCF provides common application base classes under:

``` text
app/MCF/Base
```

Examples include:

``` text
MfcController
MfcPolicy
MfcRequest
MfcService
```

Generated application classes can inherit from these classes to use
common MCF behavior and conventions.

------------------------------------------------------------------------

# MCF Configuration and Optional Components

MCF provides settings for its framework components.

When a component supports enabling or disabling, the preferred approach
is to use its settings rather than deleting its directory.

The principle is:

``` text
Disable a component when it is not required.
Do not delete framework components simply because they are unused.
```

This is especially important because MCF components can be
interconnected.

The detailed documentation for each component explains its available
settings and behavior.

------------------------------------------------------------------------

# Installed Modules and Workflows

The installer provides an initial application structure.

For example:

``` text
app/MCF/Modules
├── Shared
│   └── Layout
└── User
    ├── Auth
    ├── Profile
    └── UserManagement
```

These provide a starting point for the application.

Developers can modify application-specific Modules and Workflows
according to project requirements.

The `Shared/Layout` Workflow is intended to provide a reusable
application layout and is recommended to remain installed unless the
developer has a clear replacement.

------------------------------------------------------------------------

# Database and MCF Architecture

The MCF architecture also includes database structures used by its
components.

MCF provides:

-   Database migrations.
-   Initial seed data.
-   Tables required by MCF components and the initial application
    structure.

The database is not physically located under `app/MCF`; it remains part
of the Laravel application's database structure.

The developer controls the application's database connection through
`.env`:

``` env
DB_CONNECTION=...
DB_DATABASE=...
```

After configuring the database:

``` bash
php artisan migrate --seed
```

MCF database tables can be extended according to application
requirements.

Because framework components may depend on these tables, developers
should understand the dependencies before removing or replacing MCF
database structures.

------------------------------------------------------------------------

# MCF Installation Marker

MCF creates:

``` text
app/MCF/.mcf-installed
```

This file acts as an installation marker for the MCF installation
process.

It allows the installer to recognize that MCF has already been installed
in the application and prevents accidental repeated installation.

It is an internal installation file and should not be treated as an
application feature.

------------------------------------------------------------------------

# MCF Documentation

The installed framework contains:

``` text
app/MCF/z_Guide
```

This directory contains the documentation available to the developer
after installation.

The guide covers:

-   Getting started.
-   Architecture.
-   Folder structure.
-   CLI commands.
-   Best practices.
-   Individual MCF components.
-   Generators.
-   Modules and Workflows.
-   Routes.
-   Database.
-   Authentication.
-   Audit.
-   Mail.
-   Notifications.
-   SMS.
-   Other MCF systems.

Start with:

``` text
README.md
```

Then use the detailed documentation for the component being used.

------------------------------------------------------------------------

# Shared vs Feature-Specific Code

The architecture distinguishes framework-level functionality from
application features.

``` text
app/MCF
│
├── Shared framework functionality
│   ├── Authentication
│   ├── Audit
│   ├── AccessControl
│   ├── Mail
│   ├── Notification
│   └── ...
│
└── Modules
    └── Application features
        └── Workflows
```

Use a Workflow for code belonging to a specific business capability.

Use the framework components for functionality intentionally shared
across features.

------------------------------------------------------------------------

# Feature Isolation

Feature-specific code should remain close to the feature that owns it.

Example:

``` text
User
└── Auth
    ├── Backend
    ├── Lang
    └── Views
```

This makes features easier to locate, maintain, test, and extend.

Feature isolation does not mean that Workflows cannot use shared MCF
services.

------------------------------------------------------------------------

# Application Startup

When the application starts, MCF initializes its framework integration.

Depending on the component, this can include:

-   Loading MCF configuration.
-   Registering MCF services.
-   Discovering Modules and Workflows.
-   Registering Workflow routes.
-   Registering Workflow views.
-   Registering Workflow language resources.
-   Initializing other MCF infrastructure.

The purpose of automatic discovery is to avoid unnecessary manual
registration of every feature.

------------------------------------------------------------------------

# Automatic Discovery

MCF uses conventions and the installed structure to discover application
resources.

The primary feature root is:

``` text
app/MCF/Modules
```

while the complete MCF root remains:

``` text
app/MCF
```

This distinction is important:

``` text
app/MCF
    = complete MCF architecture

app/MCF/Modules
    = application feature architecture inside MCF
```

------------------------------------------------------------------------

# Request Lifecycle

A typical MCF HTTP request can follow:

``` text
HTTP Request
    ↓
Route
    ↓
Controller
    ↓
Request / Validation
    ↓
Business Logic / Service
    ↓
Response
```

Middleware, authorization, audit, notifications, and other MCF systems
may participate depending on the feature and configuration.

Not every request uses every component.

------------------------------------------------------------------------

# Extensibility

MCF is designed to be extended.

Projects can:

-   Add Modules.
-   Add Workflows.
-   Add Endpoints.
-   Add Requests.
-   Extend MCF Base Classes.
-   Add application-specific Services.
-   Integrate Laravel packages.
-   Add project-specific functionality.

The framework provides conventions without preventing the application
from implementing its own domain requirements.

------------------------------------------------------------------------

# Laravel Compatibility

MCF is built on Laravel and continues to use Laravel's ecosystem.

Applications can use Laravel functionality such as:

-   Eloquent.
-   Blade.
-   Validation.
-   Middleware.
-   Policies.
-   Events.
-   Queues.
-   Notifications.
-   Mail.
-   Cache.
-   Storage.
-   Broadcasting.
-   Scheduling.

Laravel packages can also be used when compatible with the application's
Laravel version.

------------------------------------------------------------------------

# Scalability

As an application grows, new business capabilities can be added as
Workflows inside Modules.

``` text
app/MCF/Modules
└── Module
    └── Workflow
        ├── Backend
        ├── Lang
        └── Views
```

At the same time, shared framework functionality remains under
`app/MCF`.

This keeps the architecture predictable as the project grows.

------------------------------------------------------------------------

# Design Principles

MCF follows these principles:

-   Business capability first.
-   Feature isolation.
-   Clear separation of responsibilities.
-   Shared infrastructure for shared concerns.
-   Self-contained feature resources.
-   Convention over unnecessary configuration.
-   Automatic discovery where appropriate.
-   Laravel compatibility.
-   Extensibility.
-   Predictable structure.
-   Long-term maintainability.
-   Prefer configuration and disabling over deletion of framework
    components.

------------------------------------------------------------------------

# Summary

MCF is not only a Module and Workflow structure.

The complete architecture is rooted at:

``` text
app/MCF
```

and contains:

``` text
Framework Components
        +
Modules / Workflows
        +
Documentation
        +
MCF Integration Files
```

Modules and Workflows organize application features, while the
framework-level directories provide shared infrastructure used across
those features.

MCF also integrates routes, views, language resources, database
structures, configuration, installation state, and framework services
into the Laravel application.

Laravel remains the underlying application framework. MCF provides a
consistent feature-oriented application architecture on top of it.

For the complete details of each part, use the individual documentation
files included with MCF.
