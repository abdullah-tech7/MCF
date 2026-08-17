# MCF Base

## Overview

The **Base** module is the shared foundation of the MCF framework.

It contains the core base classes used by the rest of the framework and
provides a consistent structure for:

- Controllers
- Requests
- Services
- Data → Model

The Base module is **mandatory and foundational** in MCF.

It is not treated as an optional module that can be removed when using
the framework.

------------------------------------------------------------------------

# Structure

The current Base structure is:

``` text
Base/
├── MfcController.php
├── MfcRequest.php
└── MfcService.php
```

Each file has a specific responsibility:

``` text
MfcController
    ↓
Controllers

MfcRequest
    ↓
Requests

MfcService
    ↓
Services
```

------------------------------------------------------------------------

# MfcController

`MfcController` is the common base for MCF Controllers:

``` php
abstract class MfcController extends Controller
{
}
```

It extends Laravel’s:

``` php
Illuminate\Routing\Controller
```

A project Controller can extend it:

``` php
class UserController extends MfcController
{
    // ...
}
```

The purpose of `MfcController` is to provide one unified base point that
MCF can extend in the future without requiring every Controller to
depend directly on Laravel’s Controller class.

------------------------------------------------------------------------

# MfcRequest

`MfcRequest` is the common base for MCF Form Requests.

It extends:

``` php
Illuminate\Foundation\Http\FormRequest
```

Example:

``` php
class CreateUserRequest extends MfcRequest
{
    // ...
}
```

------------------------------------------------------------------------

## Data Contract

`MfcRequest` provides:

``` php
protected function dataClass(): ?string
{
    return null;
}
```

A Request can override this method when it has a dedicated Data object.

Example:

``` php
protected function dataClass(): ?string
{
    return CreateUserData::class;
}
```

The Request can then return the validated input as a Data object
through:

``` php
$request->getData();
```

------------------------------------------------------------------------

## getData()

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

If no Data class is defined:

``` php
return $validated;
```

`getData()` returns an array.

If a Data class is defined, MCF attempts to create it:

``` php
new $dataClass(...$validated);
```

If the Data class does not exist or object creation fails, a
`LogicException` is thrown instead of silently ignoring the problem.

------------------------------------------------------------------------

# MfcService

`MfcService` is the common base for MCF Services:

``` php
abstract class MfcService
{
}
```

It currently provides an important operation for converting a Data
object into an Eloquent Model.

------------------------------------------------------------------------

# Data → Model

The method:

``` php
protected function dataToModel(
    object $data,
    Model $model,
): Model
```

converts a Data object into a Model.

The flow is:

``` text
Data Object
    ↓
Read public properties
    ↓
Read Model columns
    ↓
Validate field names
    ↓
Model::fill()
    ↓
Eloquent Model
```

------------------------------------------------------------------------

# Field Validation

Before filling the Model, `MfcService` reads the columns of the Model’s
database table.

If the Data object contains a field that does not exist in the Model
table, it is treated as a programming error.

Example:

``` text
Data:
    name
    email
    phone
    unknown_field

Model columns:
    id
    name
    email
    phone
```

The following field is detected:

``` text
unknown_field
```

and a:

``` php
LogicException
```

is thrown instead of silently ignoring the field.

This behavior is intentional so development errors are detected early.

------------------------------------------------------------------------

# Why Is Base Mandatory?

Base is not merely a collection of Helpers.

It is a foundational layer of the MCF architecture.

When new project components are created, the appropriate Base classes
are used automatically according to the component type.

For example:

``` text
Workflow
    ↓
MfcService

Request
    ↓
MfcRequest

Controller
    ↓
MfcController
```

This provides a consistent structure across the project.

------------------------------------------------------------------------

# Automatic Use with Generation Commands

MCF generation commands are designed to use the appropriate Base class
automatically.

When creating a new component, the developer does not need to manually
choose the raw Laravel base class every time.

For example, when creating a Request, its base is:

``` php
MfcRequest
```

When creating a Controller:

``` php
MfcController
```

When creating a Service:

``` php
MfcService
```

The details of this behavior will be explained more extensively in the
**Workflow** documentation because Workflow is the level that combines
these components and defines how they work together.

------------------------------------------------------------------------

# Workflow and Its Relationship with Base

A Workflow does not start directly from raw Laravel classes. It uses the
foundational structure provided by MCF.

Conceptually:

``` text
MCF Base
   ↓
Workflow Structure
   ↓
Workflow Components
```

Therefore, when MCF generates Workflow components, the appropriate Base
classes are used automatically.

The complete relationship between Workflow, Controller, Request,
Service, and Data will be documented in the Workflow module.

------------------------------------------------------------------------

# Customization

Using Base does not prevent customization.

Developers can customize Controllers, Requests, and Services according
to project requirements.

Example:

``` php
class UserController extends MfcController
{
    // Custom controller logic
}
```

Or:

``` php
class CreateUserRequest extends MfcRequest
{
    protected function dataClass(): ?string
    {
        return CreateUserData::class;
    }
}
```

The purpose is to start from the MCF foundation and then add the
behavior required by the project.

------------------------------------------------------------------------

# Do Not Delete Base Files

Base files are part of the core MCF architecture.

Therefore:

``` text
MfcController.php
MfcRequest.php
MfcService.php
```

must not be deleted from the framework.

Even if a project does not currently need a particular capability, the
files remain as part of the framework foundation.

The classes can be extended and customized, but Base files themselves
must not be removed.

------------------------------------------------------------------------

# Future Changes

A unified Base layer allows the framework to evolve shared behavior from
one location.

For example, if shared functionality is added to:

``` php
MfcService
```

all Services extending it can benefit from that behavior without
redefining it in every Service.

The same principle applies to:

``` text
MfcController
MfcRequest
```

------------------------------------------------------------------------

# Design Rules

1.  `Base` is a mandatory and foundational MCF module.
2.  Base files must not be deleted from the framework.
3.  `MfcController` is the unified base for Controllers.
4.  `MfcRequest` is the unified base for Form Requests.
5.  `MfcService` is the unified base for Services.
6.  `MfcRequest` supports connecting a Request to a Data object through
    `dataClass()`.
7.  `getData()` returns either the validated array or a Data object
    depending on the configuration.
8.  `MfcService::dataToModel()` verifies that Data fields exist in the
    Model’s database table.
9.  Unknown fields throw `LogicException`.
10. Workflow components automatically use the appropriate Base classes
    when generated.
11. Developers can customize inherited classes according to project
    requirements.
12. Advanced Base usage inside Workflow will be covered in the Workflow
    documentation.

------------------------------------------------------------------------

# Architectural Goal

``` text
                    MCF Base
                       │
        ┌──────────────┼──────────────┐
        ↓              ↓              ↓
MfcController     MfcRequest     MfcService
        ↓              ↓              ↓
 Controllers       Requests       Services
                                      ↓
                                  Data → Model
```

The goal is to provide a **strong, unified, and mandatory foundation**
for all MCF components while allowing developers to customize the
inherited classes without breaking the framework’s overall architecture.
