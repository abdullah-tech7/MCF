# Endpoint Generator

The Endpoint Generator is the primary way to add functionality to an existing Workflow.

Rather than manually editing Controllers, Routes and Views, MCF updates the Workflow automatically while preserving its architecture.

The generator eliminates repetitive work and ensures every Workflow follows the same conventions.

Business logic is intentionally **not generated**. Developers implement it manually according to their application's requirements.

---

# Philosophy

An Endpoint represents **one executable action** inside a Workflow.

It is **not** just a Controller method.

Creating one Endpoint may update multiple files across the Workflow.

For example, inside the Authentication Workflow:

```text
Authentication

├── register
├── registerPost
├── login
├── loginPost
├── logout
├── forgotPassword
├── forgotPasswordPost
├── resetPassword
├── resetPasswordPost
├── updatePassword
├── updatePasswordPost
├── verifyEmail
└── resendVerification
```

Every Endpoint belongs to exactly one Workflow.

Endpoints never exist independently.

---

# Generator First

MCF follows a **Generator First** philosophy.

Developers should generate Endpoints instead of manually editing:

- Controllers
- Routes
- Views

The generator owns the Workflow structure.

Developers focus on implementing business logic rather than maintaining boilerplate code.

---

# Create an Endpoint

Generate a new Endpoint.

```bash
php artisan mcf:endpoint:create
```

The command is fully interactive.

Typical prompts include:

- Module
- Workflow
- Endpoint Name
- Create View
- HTTP Method
- Return Type
- Inject Workflow Request
- Parameters

---

# What Does the Generator Update?

Generating a single Endpoint automatically updates the Workflow.

Depending on the selected options, MCF may update:

- Workflow Routes
- Workflow Controller
- Workflow Views

The generator updates existing files.

It does **not** create duplicate Controllers.

It does **not** generate Service methods.

Business logic always remains under the developer's control.

---

# Example

Assume the following Workflow already exists.

```text
Modules

└── Test
    └── Auth
        ├── Backend
        │   ├── AuthController.php
        │   ├── AuthRoutes.php
        │   ├── AuthService.php
        │   ├── AuthPolicy.php
        │   └── AuthRequest.php
        │
        ├── Views
        └── Lang
```

Create a new Endpoint.

```text
login
```

MCF automatically updates:

```text
AuthController

+ login()
```

```text
AuthRoutes

+ Route::get(...)
```

```text
Views

+ login.blade.php
```

Notice that **AuthService is not modified**.

If business logic is required, developers implement it manually.

---

# Interactive Options

Every question asked by the generator affects the generated code.

---

## Module

Specifies the Module that owns the Workflow.

Example:

```text
Test
```

---

## Workflow

Specifies the Workflow to update.

Example:

```text
Auth
```

---

## Endpoint Name

The Endpoint name should describe a single action.

Good examples:

```text
index

login

loginPost

register

registerPost

logout

forgotPassword

forgotPasswordPost

resetPassword

resetPasswordPost

updatePassword

updatePasswordPost

verifyEmail

resendVerification

export

archive
```

Avoid generic names.

Poor examples:

```text
action

process

execute

run

test

temp
```

---

## Create View

Determines whether a Blade View should be generated.

Choose **Yes** when the Endpoint displays a page.

Examples:

```text
login

register

forgotPassword

resetPassword

updatePassword
```

Choose **No** when the Endpoint performs an action only.

Examples:

```text
loginPost

registerPost

logout

forgotPasswordPost

resetPasswordPost

updatePasswordPost

verifyEmail

resendVerification

delete

export
```

---

## HTTP Method

Defines the Route HTTP verb.

Available options:

```text
GET

POST

PUT

PATCH

DELETE
```

Examples:

```text
login

↓

GET
```

```text
loginPost

↓

POST
```

---

## Return Type

Defines the Controller return type.

Available options include:

```text
View

RedirectResponse

JsonResponse

BinaryFileResponse

StreamedResponse

Response
```

MCF automatically generates the correct Controller method signature.

---

## Inject Workflow Request

Determines whether the Workflow Request should be injected.

Without Request:

```php
public function login()
```

With Request:

```php
public function login(AuthRequest $request)
```

Validation remains centralized inside the Workflow Request.

---

## Parameters

Optional Endpoint parameters.

Example input:

```text
int $id
```

Generated Controller method:

```php
public function edit(int $id)
```

Another example:

```text
string $token
```

Generates:

```php
public function verifyEmail(string $token)
```

---

# Generated Workflow

Suppose the Workflow already exists.

```text
Auth

Backend

    AuthController.php

    AuthRoutes.php

    AuthService.php
```

Generate:

```text
login
```

The generator performs every structural change automatically.

Result:

```text
AuthController

+ login()
```

```text
AuthRoutes

+ Route::get(...)
```

```text
Views

+ login.blade.php
```

No manual route registration is required.

No manual Controller registration is required.

Business logic remains inside the Workflow Service and is implemented manually by the developer.

---

# Services

The Endpoint Generator intentionally does **not** generate Service methods.

Business logic differs greatly between applications and cannot be generated reliably.

When an Endpoint requires business logic, developers should create or extend the Workflow Service manually.

This keeps generated code clean while allowing Services to evolve naturally with the application's requirements.

---

# Endpoint Lifecycle

Every generated Endpoint follows the same execution pipeline.

```text
HTTP Request

↓

Workflow Route

↓

Workflow Controller

↓

Workflow Request (Optional)

↓

Workflow Policy

↓

Workflow Service (Developer Implementation)

↓

Response
```

Each layer performs one responsibility before passing execution to the next.

---

# Endpoint Naming Convention

MCF recommends pairing page Endpoints and action Endpoints using the same base name.

Examples:

```text
login
loginPost
```

```text
register
registerPost
```

```text
forgotPassword
forgotPasswordPost
```

```text
resetPassword
resetPasswordPost
```

```text
updatePassword
updatePasswordPost
```

This convention keeps related actions together and makes large Workflows easier to navigate.

---

# Remove an Endpoint

Remove an existing Endpoint.

```bash
php artisan mcf:endpoint:remove
```

The command interactively asks for:

- Module
- Workflow
- Endpoint

Only the selected Endpoint is removed.

The remaining Workflow structure remains unchanged.

---

# Example Session

```text
php artisan mcf:endpoint:create

Module:
> Test

Workflow:
> Auth

Endpoint Name:
> login

Create View?
Yes

HTTP Method
GET

Return Type
View

Inject Workflow Request?
No

Parameters
None
```

Generated automatically:

```text
✓ Route added

✓ Controller method added

✓ View created
```

The developer only implements business logic inside the Workflow Service when required.

---

# Benefits

Using the Endpoint Generator provides several advantages.

- Consistent code generation.
- Zero manual route registration.
- Zero manual Controller registration.
- Automatic View generation.
- Predictable Workflow structure.
- Reduced boilerplate.
- Faster feature development.
- Easier maintenance.

---

# Best Practices

When creating Endpoints:

- Create Endpoints using the Generator.
- Keep Endpoint names descriptive.
- One Endpoint should perform one action.
- Pair GET and POST Endpoints using the same base name.
- Keep Controllers thin.
- Place business logic inside Services.
- Centralize validation in Workflow Requests.
- Delegate authorization through Workflow Policies.
- Avoid manually editing Workflow structure.

---

# Summary

The Endpoint Generator is one of the core features of MCF.

Rather than manually editing Controllers, Routes and Views, developers describe the Endpoint through a short interactive wizard.

MCF automatically updates the Workflow structure, generates the required boilerplate and keeps every Workflow consistent with the framework architecture.

Business logic is intentionally left to the developer, allowing each application to implement its own requirements while MCF maintains a predictable and maintainable project structure.