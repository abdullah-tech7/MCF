# MCF Resources & Public

## Overview

MCF follows Laravel’s standard policy for **Resources** and **Public
Assets**.

MCF does not create a separate file system for these resources and does
not move them into directories inside `MCF`.

Instead, files remain in Laravel’s standard locations so Laravel and MCF
can work with them naturally.

The basic structure is:

``` text
resources/
└── ...

public/
└── ...
```

------------------------------------------------------------------------

# Resources

The `resources` directory is the natural location for application
resources used during building or rendering.

It can contain, for example:

``` text
resources/
├── views/
├── css/
├── js/
└── ...
```

MCF modules can place or use views and other resources through this
standard project location according to the application’s organization.

------------------------------------------------------------------------

# Views

Blade files remain under:

``` text
resources/views/
```

For example:

``` text
resources/views/
├── errors/
│   ├── 401.blade.php
│   ├── 403.blade.php
│   ├── 404.blade.php
│   ├── 419.blade.php
│   ├── 429.blade.php
│   ├── 500.blade.php
│   └── 503.blade.php
│
├── layout.blade.php
└── minimal.blade.php
```

These files are part of the Laravel environment and support its standard
rendering behavior.

------------------------------------------------------------------------

# Laravel Error Views

The project includes Laravel error view files in their standard
location.

For example:

``` text
resources/views/errors/401.blade.php
resources/views/errors/403.blade.php
resources/views/errors/404.blade.php
resources/views/errors/419.blade.php
resources/views/errors/429.blade.php
resources/views/errors/500.blade.php
resources/views/errors/503.blade.php
```

Their purpose is to support Laravel’s standard error rendering
mechanism.

For example, when:

``` php
abort(401);
```

is used, Laravel can render:

``` text
resources/views/errors/401.blade.php
```

as the corresponding error page.

------------------------------------------------------------------------

# Why Keep Them in Their Original Location?

MCF does not redefine Laravel’s Resource policy.

Therefore:

``` text
Laravel
    ↓
resources/views
    ↓
Error Views
```

rather than:

``` text
MCF
    ↓
Custom Error Views Location
```

This allows developers to know exactly where Laravel’s standard views
are located and customize them without searching inside the framework.

------------------------------------------------------------------------

# Using Resources with MCF

MCF code can use resources available in the application.

For example, a module or Workflow can depend on:

``` text
resources/views/...
resources/css/...
resources/js/...
```

depending on the component.

This keeps application resources and framework resources within
Laravel’s standard system.

------------------------------------------------------------------------

# Public

The:

``` text
public/
```

directory is the natural location for files that must be directly
accessible by the browser.

For example:

``` text
public/
├── images/
├── css/
├── js/
└── ...
```

It can contain:

- Images.
- CSS files.
- JavaScript files.
- Fonts.
- Icons.
- Other static assets that must be directly accessible by the browser.

------------------------------------------------------------------------

# Resources vs Public

In simple terms:

``` text
resources/
    ↓
Application resources
    ↓
Views / source assets / templates
```

while:

``` text
public/
    ↓
Public assets
    ↓
Images / CSS / JS / fonts / static files
```

Not every file inside `resources` should be directly accessible by the
browser.

Files that need direct browser access belong under `public` according to
Laravel’s standard structure.

------------------------------------------------------------------------

# Using Assets from MCF

MCF modules can reference resources that exist in the application.

For example:

``` text
MCF Module
    ↓
resources/views
    ↓
Blade View
```

or:

``` text
MCF Module
    ↓
public/images
    ↓
Image
```

or:

``` text
MCF Module
    ↓
public/css
    ↓
Stylesheet
```

This allows developers to customize MCF-related presentation and assets
without modifying the framework’s core code.

------------------------------------------------------------------------

# Customization

These files are intended to be customizable by the application.

For example, the developer can modify:

``` text
resources/views/errors/401.blade.php
```

to customize the page displayed by:

``` php
abort(401);
```

The developer can also add or modify:

``` text
public/images/
public/css/
public/js/
```

according to application requirements.

------------------------------------------------------------------------

# Relationship with MCF

MCF provides the application logic, while views and static assets remain
in Laravel’s standard locations.

The relationship is:

``` text
MCF Code
   ↓
Laravel Resources
   ↓
Views / Assets
```

This allows MCF code to depend on project resources without imposing a
custom framework-specific file structure.

------------------------------------------------------------------------

# Important Rule

MCF does not create:

``` text
MCF/Resources/
MCF/Public/
```

as replacements for Laravel’s standard directories.

The primary locations are:

``` text
resources/
public/
```

------------------------------------------------------------------------

# Examples

### Error Page

Code:

``` php
abort(401);
```

View:

``` text
resources/views/errors/401.blade.php
```

### Image

``` text
public/images/logo.png
```

### CSS

``` text
public/css/app.css
```

### JavaScript

``` text
public/js/app.js
```

------------------------------------------------------------------------

# Design Rules

1.  MCF follows Laravel’s policy for `resources` and `public`.
2.  Resources are not moved into `MCF`.
3.  Public assets are not moved into `MCF`.
4.  Blade Views remain in `resources/views`.
5.  Laravel Error Views remain in `resources/views/errors`.
6.  MCF can use these resources.
7.  Developers can customize Resources and Public Assets according to
    project requirements.
8.  `public` is intended for files that need direct browser access.
9.  `resources` is intended for application resources used by Laravel
    during rendering or building.
10. MCF does not create a separate Resources/Public system.

------------------------------------------------------------------------

# Architectural Goal

``` text
                    Laravel
                       │
          ┌────────────┴────────────┐
          ↓                         ↓
     resources/                  public/
          │                         │
     Views / Source            Static Assets
          │                         │
          └────────────┬────────────┘
                       ↓
                      MCF
```

The goal is to keep MCF aligned with Laravel instead of creating a new
file-location policy, while giving developers clear locations for
customizing views, assets, and resources used by the framework modules.
