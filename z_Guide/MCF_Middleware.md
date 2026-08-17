# MCF Middleware

## Overview

MCF moves its core Middleware into the framework instead of leaving
framework-level Middleware distributed inside the application.

The core Middleware are located in:

``` text
MCF/
└── Middleware/
    ├── McfAccessMiddleware.php
    ├── McfSessionSecurityMiddleware.php
    └── SetLocaleMiddleware.php
```

MCF ships with these three Middleware ready and registered through the
application’s Bootstrap configuration.

------------------------------------------------------------------------

# Core Middleware

MCF provides three core Middleware:

``` text
McfAccessMiddleware
McfSessionSecurityMiddleware
SetLocaleMiddleware
```

Each Middleware has a clearly defined responsibility and belongs to a
specific framework concern.

------------------------------------------------------------------------

# McfAccessMiddleware

``` text
Middleware/
└── McfAccessMiddleware.php
```

This Middleware is associated with the:

``` text
AccessControl
```

unit.

It is used to execute MCF’s Access Control mechanism for requests that
require access verification.

Its related configuration and behavior are managed by the
**AccessControl** unit.

------------------------------------------------------------------------

# McfSessionSecurityMiddleware

``` text
Middleware/
└── McfSessionSecurityMiddleware.php
```

This Middleware is responsible for Session Security within MCF.

It is included among the framework’s core Middleware, while its related
behavior and configuration are managed by the corresponding framework
configuration.

------------------------------------------------------------------------

# SetLocaleMiddleware

``` text
Middleware/
└── SetLocaleMiddleware.php
```

This Middleware is responsible for setting the Locale used during a
request.

It is associated with MCF’s Language system.

Language configuration is handled through the **Language** unit rather
than placing all language configuration directly inside the Middleware.

------------------------------------------------------------------------

# Bootstrap Integration

MCF ships with Bootstrap configuration that handles these Middleware.

The developer does not need to rebuild the Middleware integration from
scratch.

The general flow is:

``` text
bootstrap/app.php
       ↓
MCF Middleware
       ↓
Application Request
```

The core Middleware therefore have a fixed location inside the
framework.

------------------------------------------------------------------------

# Is Middleware Usage Mandatory?

No.

The fact that MCF provides these Middleware does not mean every project
must use every Middleware.

Their usage is **optional** according to the application’s needs.

However, it is recommended not to delete the framework files themselves.

The application can simply avoid enabling Middleware it does not need or
adjust its configuration as appropriate.

------------------------------------------------------------------------

# Why Avoid Deleting Them?

They are part of the standard MCF structure.

Keeping them provides:

- A ready-made structure.
- Consistent organization.
- A clear location for framework Middleware.
- Direct integration with other MCF units.
- Easy activation of framework features when needed.

The preferred rule is:

``` text
Keep the Middleware
Use when needed
Configure when needed
```

rather than deleting framework files.

------------------------------------------------------------------------

# Each Middleware Has Its Own Configuration

Each Middleware is associated with a specific framework concern, so all
configuration is not placed into one central Middleware configuration.

For example:

``` text
AccessControl
    ↓
McfAccessMiddleware
    ↓
Access Control Configuration
```

and:

``` text
Language
    ↓
SetLocaleMiddleware
    ↓
Language Configuration
```

This maintains separation of responsibilities.

------------------------------------------------------------------------

# Middleware and Workflows

MCF’s core Middleware are different from Middleware that may be required
by an individual Workflow.

Core framework Middleware:

``` text
MCF/
└── Middleware/
```

If a Workflow requires its own Middleware, it can define or use
additional Middleware according to the application’s needs without
mixing Workflow-specific concerns into the framework’s core Middleware.

The purpose is to keep framework-level Middleware separate from
Feature-level Middleware.

------------------------------------------------------------------------

# Relationship with Framework Units

The structure can be viewed as:

``` text
MCF
│
├── AccessControl
│      └── McfAccessMiddleware
│
├── Language
│      └── SetLocaleMiddleware
│
└── Middleware
       ├── McfAccessMiddleware.php
       ├── McfSessionSecurityMiddleware.php
       └── SetLocaleMiddleware.php
```

Each Middleware has a clear responsibility, while its related unit
contains its own configuration and logic.

------------------------------------------------------------------------

# Design Rules

1.  MCF provides three core Middleware.
2.  The Middleware are located under `MCF/Middleware`.
3.  Core Middleware are integrated through the application’s Bootstrap
    configuration.
4.  Middleware usage is optional according to project requirements.
5.  It is recommended not to delete the core Middleware files from the
    framework.
6.  Middleware configuration can be adjusted according to the related
    framework unit.
7.  Access Control configuration remains inside `AccessControl`.
8.  Language configuration remains inside `Language`.
9.  Framework Middleware should remain separate from Workflow-specific
    Middleware.
10. Applications can add additional Middleware when needed.
11. The goal is to provide a ready-made and organized Middleware
    structure while keeping the developer free to decide which
    Middleware to use.

------------------------------------------------------------------------

# Architectural Summary

``` text
                    bootstrap/app.php
                           │
                           ↓
                     MCF Middleware
                           │
             ┌─────────────┼─────────────┐
             ↓             ↓             ↓
        Access Control   Session       Locale
             │           Security        │
             ↓             ↓              ↓
   McfAccessMiddleware  McfSession...  SetLocaleMiddleware
```

MCF provides the core Middleware structure in advance, while the
developer remains free to enable, disable, or configure each Middleware
according to the project’s requirements.
