# MCF Modules & Workflows

## Overview

MCF uses two different organizational levels for structuring an
application:

``` text
Module
    ↓
Workflow
```

These two levels have different responsibilities.

A **Module** is the organizational container that separates parts of the
system and groups related functionality within a domain.

A **Workflow** is a connected work path representing a specific business
operation.

The Workflow is the unit that performs a function, while the Module
organizes a group of related Workflows.

------------------------------------------------------------------------

# Why Modules?

Modules exist for separation and organization.

In large applications, all functionality should not be mixed together in
one place.

Example:

``` text
Modules/
├── User/
├── Shop/
├── Reports/
├── System/
└── Shared/
```

Each Module represents a domain or a group of related functionality.

A Module itself is not the place for direct Business Logic.

Its primary responsibility is to **organize and separate system areas**.

------------------------------------------------------------------------

# Why Workflow?

A Workflow represents a complete and connected path for solving a
Business Problem.

It is:

- Not a database table.
- Not a Model.
- Not a Controller.
- Not merely an endpoint.

It is a complete Feature or Business Operation.

Examples:

``` text
Authentication
Checkout
User Management
Product Catalog
Reports
Dashboard
```

A Workflow describes:

``` text
What does the application do?
```

rather than:

``` text
What does the database store?
```

------------------------------------------------------------------------

# Model vs Module vs Workflow

The three levels must be clearly distinguished.

### Model

Represents data:

``` text
User
Product
Order
```

### Module

Separates and organizes system domains:

``` text
User
Shop
Reports
```

### Workflow

Represents a business capability or work path:

``` text
Authentication
Profile
Checkout
Product Catalog
```

Therefore:

``` text
Module
   ↓
Related Workflows
   ↓
Each Workflow implements a Business Operation
   ↓
Workflows use Models when needed
```

------------------------------------------------------------------------

# Do Not Make a Workflow a Database Table

Workflows should not be designed around database tables.

An unsuitable structure would be:

``` text
Modules/
└── User/
    └── User/
```

when the only purpose is to represent the `users` table.

The rule is:

``` text
Model → Data
Workflow → Business Capability
```

Multiple Workflows may use the same Model.

Example:

``` text
User Model
    ↑
    ├── Authentication
    ├── Profile
    └── User Management
```

Do not create duplicate Models simply because different Workflows use
the same data.

------------------------------------------------------------------------

# Organize Workflows by Business Capability

Workflow names should describe what the system does.

Good:

``` text
Modules/
└── User/
    ├── Auth/
    └── Profile/
```

Not:

``` text
Modules/
└── User/
    ├── UserTable/
    └── UserModel/
```

Good examples:

``` text
Authentication
User Management
Profile
Settings
Checkout
Product Catalog
Sales Reports
```

Avoid:

``` text
Main
Temp
Default
Test
Misc
```

The name should immediately communicate the Workflow’s purpose.

------------------------------------------------------------------------

# Module Structure

A Module can contain multiple Workflows.

Example:

``` text
Modules/
└── User/
    ├── Auth/
    └── Profile/
```

A shared Module can also exist:

``` text
Modules/
└── Shared/
    └── Layout/
```

A Module is not limited to a single Workflow.

It can grow according to the domain it represents.

------------------------------------------------------------------------

# Workflow Structure

The current Workflow structure is:

``` text
Workflow/
└── Backend/
    ├── WorkflowController.php
    ├── WorkflowRoutes.php
    └── WorkflowService.php
```

It can also contain, when needed:

``` text
Workflow/
├── Backend/
└── Views/
```

`Request` is **optional** and is added when the operation needs a Form
Request.

Example matching the current structure:

``` text
Modules/
└── User/
    └── Auth/
        ├── Backend/
        │   ├── Request/
        │   ├── AuthController.php
        │   ├── AuthRoutes.php
        │   └── AuthService.php
        │
        └── Views/
```

------------------------------------------------------------------------

# Core Workflow Components

The Workflow is fundamentally based on:

``` text
Routes
Controller
Service
```

These are the core parts of its Backend/HTTP path.

The following are added when needed:

``` text
Request
Views
```

------------------------------------------------------------------------

# Routes

Each Workflow owns its own Routes file.

Example:

``` text
AuthRoutes.php
```

It contains the entry points for the Workflow.

Example:

``` php
Route::post(
    '/login',
    [AuthController::class, 'login'],
);
```

Routes define:

- HTTP Method
- URL
- Controller Action
- Middleware

Business Logic should not be placed inside Routes.

MCF automatically discovers and registers Workflow Routes during
application startup.

------------------------------------------------------------------------

# Controller

Each Workflow owns its own Controller.

Example:

``` text
AuthController.php
```

It extends:

``` php
MfcController
```

The Controller is the HTTP layer.

Its responsibility is to:

- Receive the request.
- Perform basic checks required to direct the operation.
- Delegate execution to the Service.
- Handle the result and return the appropriate Response.

The Controller is not the place for Business Logic.

------------------------------------------------------------------------

# Keep Controllers Thin

The preferred structure is:

``` text
HTTP Request
      ↓
Route
      ↓
Controller
      ↓
Service
      ↓
Response
```

Avoid placing the following inside Controllers:

- Database Queries.
- Complex Calculations.
- Business Rules.
- Core system operations.

The Controller should be a **lightweight HTTP orchestrator**, not the
execution center of the operation.

------------------------------------------------------------------------

# Service

Each Workflow owns its own Service.

Example:

``` text
AuthService.php
```

It extends:

``` php
MfcService
```

The Service is the primary location for the Workflow’s Business Logic.

Typical responsibilities include:

- Business Rules.
- Data Manipulation.
- Workflow Coordination.
- Domain Operations.
- Database operations related to the operation.
- Updating Models.

------------------------------------------------------------------------

# Why Separate the Service?

So the Controller does not become responsible for everything.

Instead of:

``` text
Controller
    ↓
Query
    ↓
Business Logic
    ↓
Model Update
    ↓
Response
```

the structure becomes:

``` text
Controller
    ↓
Service
    ↓
Business Logic
    ↓
Model
```

This makes the Workflow clearer and easier to test and reuse.

------------------------------------------------------------------------

# Service and Data

Services should preferably receive a **Data object** rather than depend
directly on an HTTP Request.

Example:

``` php
public function execute(
    UpdateProfileData $data,
): UpdateResult {
    // ...
}
```

instead of:

``` php
public function execute(
    Request $request,
): UpdateResult {
    // ...
}
```

The reason is that Data is not tied to HTTP.

The same Service can therefore be used from:

``` text
Web
API
CLI
Job
Command
Other Workflow
```

For example:

``` text
Web
 ↓
Request
 ↓
Data
 ↓
Service
```

or:

``` text
API
 ↓
Data
 ↓
Service
```

or:

``` text
Job
 ↓
Data
 ↓
Service
```

This separates Business Logic from the mechanism used to access it.

------------------------------------------------------------------------

# Request

The Request is **optional** and is not required for every Workflow.

It is used when the operation needs a Laravel Form Request for:

- Validation.
- HTTP-layer authorization.
- Preparing HTTP input.

Requests belonging to the Workflow are placed inside:

``` text
Backend/Request/
```

Example:

``` text
Auth/
└── Backend/
    └── Request/
        └── LoginRequest.php
```

It extends:

``` php
MfcRequest
```

------------------------------------------------------------------------

# How MfcRequest Works

The flow is:

``` text
HTTP Request
      ↓
MfcRequest
      ↓
Validation
      ↓
validated()
      ↓
Data
      ↓
Service
```

The Request can define its Data class:

``` php
protected function dataClass(): ?string
{
    return LoginData::class;
}
```

Then:

``` php
$data = $request->getData();
```

returns a Data object ready for the Service.

If no Data class is defined, the Request can return the validated data
as an array.

------------------------------------------------------------------------

# Data Class

Data represents the operation’s **Data Contract**.

Example:

``` php
final class UpdateProfileData
{
    public function __construct(
        public string $name,
        public string $phone,
    ) {
    }
}
```

Then:

``` text
Request
    ↓
Validated Input
    ↓
UpdateProfileData
    ↓
Service
```

Data does not depend on:

``` text
HTTP
Request
Controller
```

Therefore it can be reused by multiple entry points.

------------------------------------------------------------------------

# Views

Views are optional.

If a Workflow is Web-oriented, it can own:

``` text
Views/
```

Example:

``` text
Views/
├── index.blade.php
├── create.blade.php
└── edit.blade.php
```

An API-oriented Workflow may not need Views at all.

The principle is that a Workflow owns its presentation resources when it
needs them.

------------------------------------------------------------------------

# Keep Workflows Independent

Each Workflow should be as independent as reasonably possible.

Avoid unnecessary direct coupling between Workflows.

When one Workflow needs to collaborate with another, communication
should preferably use:

``` text
Services
Interfaces
Well-defined contracts
```

This makes Workflows easier to maintain and reuse.

------------------------------------------------------------------------

# Reuse Models Across Workflows

Multiple Workflows can use the same Model.

Example:

``` text
Authentication
       ↓
   User Model
       ↑
Profile
```

This is normal and desirable.

Do not create duplicate Models simply because different Workflows use
the same data.

The rule is:

``` text
Model = Data
Workflow = Business Capability
```

------------------------------------------------------------------------

# One Responsibility

Every Workflow should have one clearly defined responsibility.

Good:

``` text
Authentication
```

Good:

``` text
Checkout
```

Good:

``` text
Product Catalog
```

Avoid overly broad Workflows such as:

``` text
System
```

or:

``` text
Everything
```

or:

``` text
Admin
```

If a Workflow becomes too large, split it according to actual Business
Capabilities.

------------------------------------------------------------------------

# Keep Related Operations Together

Separation does not mean creating a new Workflow for every endpoint.

If operations belong to the same Business Capability, keep them
together.

Example:

``` text
User Management
├── index
├── create
├── store
├── edit
├── update
├── delete
└── export
```

Do not create a separate Workflow for every HTTP operation when they
belong to the same business capability.

------------------------------------------------------------------------

# Module vs Workflow

Complete example:

``` text
Modules/
└── User/
    │
    ├── Auth/
    │   └── Backend/
    │       ├── Request/
    │       ├── AuthController.php
    │       ├── AuthRoutes.php
    │       └── AuthService.php
    │
    └── Profile/
        └── Backend/
            ├── Request/
            ├── ProfileController.php
            ├── ProfileRoutes.php
            └── ProfileService.php
```

Here:

``` text
User
```

is the Module.

While:

``` text
Auth
Profile
```

are Workflows.

Each Workflow contains its own connected work path.

------------------------------------------------------------------------

# Relationship with Models

Models remain separate:

``` text
app/
└── Models/
    └── User.php
```

while:

``` text
Modules/
└── User/
    ├── Auth/
    └── Profile/
```

Multiple Workflows can use:

``` text
User.php
```

instead of duplicating the Model for every Workflow.

------------------------------------------------------------------------

# Workflow Pipeline

The primary path is:

``` text
HTTP Request
      ↓
Workflow Route
      ↓
Workflow Controller
      ↓
Workflow Request (if used)
      ↓
Workflow Service
      ↓
Data
      ↓
Model
      ↓
Database
      ↓
Result / Response
```

Not every Workflow must use every stage.

For example:

``` text
Request
```

is optional.

And:

``` text
Data
```

is used when a Data Contract is beneficial for the operation.

------------------------------------------------------------------------

# Portability

A well-designed Workflow should be reusable and portable whenever
practical.

This is achieved by avoiding unnecessary dependencies on:

- HTTP.
- Specific role names.
- Application-specific permission identifiers.
- Unnecessary application-specific configuration.
- Unrelated Workflows.

The less unnecessary coupling exists, the easier the Workflow is to
maintain and reuse.

------------------------------------------------------------------------

# Design Rules

1.  Module is the highest organizational level inside MCF.
2.  A Module exists for separation and organization, not direct Business
    Logic execution.
3.  A Workflow is a connected work path representing a complete Business
    Capability.
4.  A Workflow is not a Model, Database Table, or Controller.
5.  Every Workflow should have a clearly defined responsibility.
6.  Workflows should be organized around Business Capabilities rather
    than database tables.
7.  Routes, Controller, and Service form the core Workflow structure.
8.  Request is optional.
9.  Views are optional.
10. Controllers extend `MfcController`.
11. Services extend `MfcService`.
12. Requests extend `MfcRequest`.
13. Controllers should remain thin.
14. Business Logic belongs in Services.
15. Services should preferably receive Data rather than HTTP Requests.
16. Services can therefore be used from Web, API, CLI, Jobs, and other
    entry points.
17. Multiple Workflows can reuse the same Model.
18. Models represent data, while Workflows represent Business
    Capabilities.
19. Do not create a new Workflow for every endpoint when operations
    belong to the same Business Capability.
20. Keep Workflows as independent as reasonably possible.
21. Additional Backend layers may be added when genuinely required.
22. Views belonging to a Workflow should remain close to that Workflow.
23. MCF automatically discovers and registers Workflow Routes.
24. Module and Workflow generation commands will be documented
    separately after the framework commands are updated.

------------------------------------------------------------------------

# Architectural Goal

``` text
                         Module
                            │
              ┌─────────────┼─────────────┐
              ↓             ↓             ↓
           Workflow      Workflow      Workflow
              │
        ┌─────┼─────┐
        ↓     ↓     ↓
      Routes Controller Service
                    │
                    ↓
                   Data
                    │
                    ↓
                  Model
                    │
                    ↓
                Database
```

The goal is to maintain a clear separation between:

``` text
Module
    → Organization

Workflow
    → Business Operation

Controller
    → HTTP Direction

Request
    → HTTP Validation (Optional)

Data
    → Data Contract

Service
    → Business Logic

Model
    → Data / Persistence
```

This keeps every responsibility in its appropriate place and allows the
application to grow by adding Modules and Workflows without turning
Models or Controllers into massive collections of unrelated
functionality.

> **Note:** Module and Workflow generation commands and their components
> will be documented separately after the MCF framework commands are
> updated.
