# MCF Routes

## Overview

MCF reorganizes Laravel Routing so that Routes belong to the
**Workflow** that owns them, instead of keeping application Routes in
Laravel’s traditional central location.

In MCF:

``` text
Module
   ↓
Workflow
   ↓
Backend
   └── WorkflowRoutes.php
```

Each Workflow owns an independent Routes file, and the Workflow Route
files are then collected by:

``` text
mcf_routes.php
```

------------------------------------------------------------------------

# Replacing Laravel Web Routes

MCF does not use Laravel’s traditional:

``` text
routes/web.php
```

as the application’s routing location.

That path is removed from the MCF application structure.

Instead, MCF uses:

``` text
mcf_routes.php
```

as the main entry point for the framework’s application Routes.

This is part of the routing reorganization performed by MCF.

------------------------------------------------------------------------

# Application Bootstrap

To implement this organization, MCF changes the application’s Bootstrap
configuration.

`bootstrap/app.php` is configured so the application does not rely on
Laravel’s traditional Route location and instead loads Routes through
the MCF routing system.

The resulting flow is:

``` text
bootstrap/app.php
        ↓
mcf_routes.php
        ↓
Workflow Route Files
```

instead of:

``` text
bootstrap/app.php
        ↓
routes/web.php
```

This is a fundamental part of MCF’s architecture, not merely an optional
file organization preference.

------------------------------------------------------------------------

# Main Route File

MCF provides:

``` text
mcf_routes.php
```

This is the main file that collects Workflow Routes.

Example:

``` php
require_once __DIR__
    . '/Modules/Shared/Layout/Backend/LayoutRoutes.php';

require_once __DIR__
    . '/Modules/User/Auth/Backend/AuthRoutes.php';

require_once __DIR__
    . '/Modules/User/Profile/Backend/ProfileRoutes.php';
```

The main file does not contain the detailed Routes of every Workflow.

Its primary responsibility is to **load Workflow Route files**.

------------------------------------------------------------------------

# Workflow Route File

Each Workflow owns an independent Routes file inside:

``` text
Backend/
```

Example:

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

This keeps Routes close to the rest of the Workflow Backend.

------------------------------------------------------------------------

# AuthRoutes Example

``` php
<?php

use App\MCF\Modules\User\Auth\Backend\AuthController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/register',
    [AuthController::class, 'register'],
)->name('user.auth.register');

Route::post(
    '/registerpost',
    [AuthController::class, 'registerPost'],
)->name('user.auth.registerPost');

Route::get(
    '/login',
    [AuthController::class, 'login'],
)->name('user.auth.login');

Route::post(
    '/loginpost',
    [AuthController::class, 'loginPost'],
)->name('user.auth.loginPost');

Route::post(
    '/logout',
    [AuthController::class, 'logout'],
)->name('user.auth.logout');
```

These are normal Laravel Routes.

MCF does not replace Laravel’s Router itself. It reorganizes where
Routes are defined and loaded.

------------------------------------------------------------------------

# Route Naming

Route names should preferably reflect the Module and Workflow.

Example:

``` text
user.auth.login
user.auth.loginPost
user.auth.logout

user.profile.index
user.profile.update
```

Example:

``` php
route('user.auth.login');
```

The name immediately communicates:

``` text
Module: User
Workflow: Auth
Operation: login
```

Route names are also used later when registering Access Control.

------------------------------------------------------------------------

# Access Control in Workflow Routes

A Workflow Route file can also register Access Control for the Routes it
owns.

This is **optional**.

If a Workflow needs to define which type of user can access specific
Routes, it can register that information in the same Route file.

Example:

``` php
use App\MCF\AccessControl\Data\AuthRouteAccess;
use App\MCF\AccessControl\Data\GuestRouteAccess;
use App\MCF\AccessControl\Registry\McfRouteDataRegistry;
```

Then:

``` php
$dataRouteList = [

    new GuestRouteAccess(
        routeNames: [
            'user.auth.register',
            'user.auth.registerPost',
            'user.auth.login',
            'user.auth.loginPost',
        ],
    ),

    new AuthRouteAccess(
        routeNames: [
            'user.auth.logout',
        ],
    ),

];

McfRouteDataRegistry::register(
    $dataRouteList,
);
```

The Workflow therefore owns:

``` text
Routes
+
Access Registration
```

in the same location.

------------------------------------------------------------------------

# Why Register Access Control in the Route File?

The relationship is direct:

``` text
Route
   ↓
Who can access this Route?
```

When opening:

``` text
User/Auth/Backend/AuthRoutes.php
```

the developer can see:

``` text
Route Definition
+
Access Definition
```

instead of searching for a Route in one file and then searching
somewhere else for its access policy.

However, the Route file does not execute Access Control itself.

It only registers the access definition.

The complete details of the Access Control system and:

``` text
GuestRouteAccess
AuthRouteAccess
McfRouteDataRegistry
```

are documented separately in **AccessControl**.

------------------------------------------------------------------------

# Complete Example

``` php
<?php

use App\MCF\AccessControl\Data\AuthRouteAccess;
use App\MCF\AccessControl\Data\GuestRouteAccess;
use App\MCF\AccessControl\Registry\McfRouteDataRegistry;
use App\MCF\Modules\User\Auth\Backend\AuthController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/register',
    [AuthController::class, 'register'],
)->name('user.auth.register');

Route::post(
    '/registerpost',
    [AuthController::class, 'registerPost'],
)->name('user.auth.registerPost');

Route::get(
    '/login',
    [AuthController::class, 'login'],
)->name('user.auth.login');

Route::post(
    '/loginpost',
    [AuthController::class, 'loginPost'],
)->name('user.auth.loginPost');

Route::post(
    '/logout',
    [AuthController::class, 'logout'],
)->name('user.auth.logout');


McfRouteDataRegistry::register([

    new GuestRouteAccess(
        routeNames: [
            'user.auth.register',
            'user.auth.registerPost',
            'user.auth.login',
            'user.auth.loginPost',
        ],
    ),

    new AuthRouteAccess(
        routeNames: [
            'user.auth.logout',
        ],
    ),

]);
```

------------------------------------------------------------------------

# File Relationship

The complete structure is:

``` text
bootstrap/app.php
        │
        ↓
mcf_routes.php
        │
        ├── Modules/Shared/Layout/Backend/LayoutRoutes.php
        │
        ├── Modules/User/Auth/Backend/AuthRoutes.php
        │
        └── Modules/User/Profile/Backend/ProfileRoutes.php
```

Each Workflow remains responsible for its own Routes.

The main file is responsible only for collection.

------------------------------------------------------------------------

# Workflow Routing Pipeline

The general flow is:

``` text
HTTP Request
      ↓
bootstrap/app.php
      ↓
mcf_routes.php
      ↓
WorkflowRoutes.php
      ↓
Laravel Router
      ↓
Workflow Controller
      ↓
Workflow Service
```

If the Workflow uses a Request:

``` text
Workflow Controller
      ↓
Workflow Request
      ↓
Data
      ↓
Service
```

------------------------------------------------------------------------

# Why This Design?

The architecture creates a clear separation:

``` text
Module
   ↓
Workflow
   ↓
Backend
   ├── Routes
   ├── Controller
   ├── Request
   └── Service
```

Instead of:

``` text
routes/web.php
    ├── User
    ├── Shop
    ├── Reports
    ├── System
    └── ...
```

In large projects, one central Routes file eventually becomes a large
aggregation point.

In MCF, every Workflow owns its own Route file.

------------------------------------------------------------------------

# Adding a New Workflow

When creating a Workflow that contains Routes:

``` text
Modules/
└── Shop/
    └── Product/
        └── Backend/
            └── ProductRoutes.php
```

Define its Routes inside that file:

``` php
Route::get(
    '/products',
    [ProductController::class, 'index'],
)->name('shop.product.index');
```

Then add the Workflow Route file to:

``` text
mcf_routes.php
```

For example:

``` php
require_once __DIR__
    . '/Modules/Shop/Product/Backend/ProductRoutes.php';
```

The new Workflow is now part of MCF’s routing system.

------------------------------------------------------------------------

# Design Rules

1.  MCF does not use `routes/web.php` as the application’s Route system.
2.  `mcf_routes.php` is the main Route file in MCF.
3.  `bootstrap/app.php` is configured to use MCF’s routing system.
4.  Each Workflow owns an independent Route file.
5.  The Workflow Route file is located inside `Backend/`.
6.  `mcf_routes.php` collects Workflow Route files.
7.  `mcf_routes.php` is not a place for Business Logic.
8.  Routes remain normal Laravel Routes.
9.  Route names should preferably reflect the Module and Workflow.
10. A Workflow Route file can optionally register Access Control.
11. Access Control registration uses Route names.
12. Access Control details are documented separately in the
    AccessControl module.
13. Controllers handle HTTP, not Business Logic.
14. Each Workflow owns its Route boundaries.
15. Adding a new Workflow requires adding its Route file to the main
    collection point.
16. The goal is to prevent one central Route file from becoming a large
    file as the project grows.

------------------------------------------------------------------------

# Architectural Summary

``` text
                    bootstrap/app.php
                           │
                           ↓
                     mcf_routes.php
                           │
             ┌─────────────┼─────────────┐
             ↓             ↓             ↓
        Workflow A    Workflow B    Workflow C
        Routes        Routes        Routes
             │             │             │
             ↓             ↓             ↓
        Controller     Controller     Controller
             │             │             │
             ↓             ↓             ↓
          Service       Service       Service
```

MCF preserves Laravel’s Routing behavior while moving the **organization
and ownership of Routes** to the Workflow level.

The result is:

``` text
Module
   ↓
Workflow
   ↓
Workflow Routes
   ↓
Controller
   ↓
Service
```

with:

``` text
mcf_routes.php
```

serving as the central collection point for the framework’s Workflow
Routes.
