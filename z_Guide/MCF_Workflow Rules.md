# Workflow Rules

This document defines the architectural rules for designing and implementing Workflows in MCF.

A Workflow is one of the fundamental architectural units of an MCF application.

A Workflow groups the HTTP endpoints, business logic, validation, views, routes and language resources required to implement one business capability.

---

# What Is a Workflow?

A Workflow represents one complete business capability.

It is not:

- A database table.
- A Model.
- A Controller.
- A single Endpoint.

It is a feature that solves a business problem.

Examples:

```text
Authentication
User Management
Product Catalog
Checkout
Reports
Dashboard
```

A Workflow should describe what the application does, not what the database stores.

---

# One Business Responsibility

Every Workflow should have one clearly defined business responsibility.

Good:

```text
Authentication
Checkout
Product Catalog
User Management
```

Avoid overly broad names such as:

```text
System
Everything
Misc
```

Do not create a new Workflow merely because a new Endpoint is required.

Related Endpoints should normally remain inside the same Workflow.

---

# Organize by Business Capability

Design Workflows around business functionality rather than database entities.

Good:

```text
User
├── Authentication
├── Profile
├── User Management
└── Settings
```

Avoid making database tables the primary Workflow boundaries:

```text
User
Product
Order
```

Models represent application data.

Workflows represent business capabilities.

One Model may therefore be used by multiple Workflows.

---

# Workflow Structure

The standard Workflow structure is:

```text
Workflow
├── Backend
├── Views
└── Lang
```

This structure should remain consistent across the application.

---

# Backend Structure

The Backend directory contains the server-side classes owned by the Workflow.

A typical current Workflow can contain:

```text
Backend
├── WorkflowController.php
├── WorkflowRoutes.php
├── WorkflowService.php
└── Request
    ├── LoginRequest.php
    └── RegisterRequest.php
```

Additional classes may exist when they are genuinely required by the Workflow.

Do not add classes merely to make the structure larger.

---

# One Controller Per Workflow

A Workflow normally owns one Controller.

Example:

```text
ProfileController.php
```

The Controller coordinates HTTP communication.

It should:

- Receive the Request.
- Pass validated input to the appropriate Service.
- Coordinate the operation.
- Return the appropriate Result or Response.

It should not contain business rules.

---

# One Service Per Workflow

A Workflow normally owns one Service.

Example:

```text
ProfileService.php
```

Business logic belongs inside the Workflow Service.

Typical responsibilities include:

- Business rules.
- Domain operations.
- Data processing.
- Workflow coordination.
- Application-specific operations.

Do not move business logic into Controllers merely because an Endpoint is small.

---

# Requests Are Endpoint-Specific

The current MCF architecture does not use the old concept of one shared Request per Workflow.

Requests are independent resources associated with the operation or Endpoint that requires them.

Example:

```text
Backend
└── Request
    ├── LoginRequest.php
    └── RegisterRequest.php
```

This allows different Endpoints to have different validation rules without creating one oversized Request.

A Request may also define a Data class when structured validated input is required.

---

# Creating Requests

Requests can be created independently:

```bash
php artisan mcf:make:request
```

Example:

```bash
php artisan mcf:make:request User Auth Login
```

If the Workflow does not yet contain a `Request` directory, the generator creates it.

If the directory already exists, the generator adds the new Request to it.

The Endpoint Generator can also create or connect an Endpoint-specific Request.

---

# Request Responsibility

A Request is responsible for request-level concerns such as:

- Authorization for the request when applicable.
- Input validation.
- Validation messages.
- Providing structured validated Data when required.

Do not place business operations inside Request classes.

Validation should have one authoritative location.

---

# Request Data

When an operation benefits from a structured input object, the Request can define a Data class.

Example:

```php
protected function dataClass(): ?string
{
    return LoginData::class;
}
```

The Data class should represent validated input.

It should not contain Controller logic, Route handling or unrelated business logic.

---

# One Routes File Per Workflow

Every Workflow owns its Route definitions.

Example:

```text
ProfileRoutes.php
```

Routes should remain close to the feature they belong to.

MCF discovers Workflow Routes through its route integration.

The application also has framework-level route integration through:

```text
app/MCF/mcf_routes.php
```

Do not confuse the Workflow Routes file with the framework-level MCF route file.

---

# Route Access

When a Route requires Access Control metadata, keep that information associated with the Route.

MCF provides the Route Data Registry mechanism for this purpose.

A Workflow Route can therefore describe both:

- The HTTP route.
- The access metadata required by the route.

Keep access definitions close to their routes rather than scattering them across unrelated files.

---

# Endpoints Belong to the Workflow

A Workflow contains related Endpoints that implement its business capability.

Example:

```text
User Management
├── index
├── create
├── store
├── edit
├── update
├── delete
└── export
```

Do not create a separate Workflow for every Endpoint.

An Endpoint is one executable action within a Workflow.

---

# Endpoint Generator

Use the MCF Endpoint Generator when adding an Endpoint:

```bash
php artisan mcf:endpoint:create
```

The Generator can update the Workflow structure by adding:

- Route.
- Controller method.
- Endpoint View when selected.
- Endpoint Request when selected.

It does not generate Service business logic.

The developer implements the required behavior inside the Workflow Service.

---

# Endpoint Removal

Use the MCF Endpoint removal command instead of manually deleting individual pieces:

```bash
php artisan mcf:endpoint:remove
```

Endpoint removal should remove the structural parts belonging exclusively to that Endpoint while preserving the rest of the Workflow.

An Endpoint-specific Request may be removed when it is owned exclusively by the Endpoint and is not shared or independently maintained.

---

# Views Belong to the Workflow

Every Workflow owns its Blade templates.

Example:

```text
Views
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── components
```

Presentation should remain inside the feature that owns it.

Shared presentation infrastructure should be placed in the appropriate shared Layout Workflow rather than duplicated across unrelated Workflows.

---

# Layout Workflow

MCF provides Layout Workflows for shared application presentation.

A Layout Workflow should normally remain available because multiple Views may depend on it.

Do not create duplicate layout systems inside individual Workflows unless there is a genuine architectural reason.

---

# Translations Belong to the Workflow

Every Workflow owns its feature-specific language resources.

Example:

```text
Lang
```

MCF discovers Workflow language resources automatically.

Developers should not manually register Workflow language paths.

Shared language infrastructure belongs to the framework-level MCF structure.

---

# Keep Workflows Independent

A Workflow should be as independent as practical.

Avoid unnecessary coupling between Workflows.

When collaboration is required, use well-defined Services, shared framework components or explicit interfaces.

Avoid reaching directly into another Workflow's internal implementation when a public service-level operation can express the dependency more clearly.

Independent Workflows are easier to maintain and reuse.

---

# Reuse Models

Multiple Workflows may use the same Model.

Example:

```text
Authentication
       ↓
    User Model
       ↑
     Profile
```

Do not create duplicate Models simply because multiple Workflows use the same data.

Models represent data.

Workflows represent business capabilities.

---

# Keep Business Logic in Services

Business rules belong inside Workflow Services.

Typical examples:

- Processing orders.
- Updating profiles.
- Calculating prices.
- Sending notifications.
- Coordinating domain operations.
- Calling shared framework services.

Services should not become Controllers.

Keep HTTP-specific concerns in Controllers and request-specific concerns in Requests.

---

# Keep Controllers Thin

Controllers should remain small.

Good:

```php
public function update(ProfileRequest $request)
{
    return $this->service->update(
        $request->data(),
    );
}
```

Avoid placing the following inside Controllers:

- Complex database operations.
- Complex calculations.
- Validation rules.
- Business rules.
- Reusable authorization logic.

---

# Centralize Validation

Validation belongs inside the appropriate Endpoint Request.

Each validation rule should have one authoritative location.

This is especially important when a Workflow contains several Endpoints with different input requirements.

Do not create one oversized Workflow Request just to avoid creating multiple Endpoint Requests.

---

# Centralize Authorization and Access Control

Authorization should not be scattered across Controllers and Services.

Use the MCF authorization and Access Control mechanisms appropriate to the application.

Avoid direct role or permission checks throughout business logic when the decision belongs to the authorization layer.

Route-level access requirements should remain associated with the relevant Route.

---

# Naming Rules

Workflow names should describe business functionality.

Good:

```text
Authentication
ProductCatalog
SalesReports
UserManagement
```

Avoid:

```text
Main
Temp
Default
Test
Misc
```

Names should communicate the Workflow's purpose immediately.

Classes should follow the project's naming conventions.

Examples:

```text
AuthenticationController.php
AuthenticationService.php
LoginRequest.php
LoginData.php
AuthenticationRoutes.php
```

---

# Avoid Duplicate Workflows

Before creating a new Workflow, determine whether the functionality belongs to an existing business capability.

Creating unnecessary Workflows fragments related functionality and increases maintenance cost.

A new Workflow is justified when the functionality represents a distinct business capability with a clear responsibility.

---

# Workflow Lifecycle

A typical current MCF Endpoint can follow this execution structure:

```text
HTTP Request
↓
Workflow Route
↓
Workflow Controller
↓
Endpoint Request (Optional)
↓
Access Control / Authorization
↓
Workflow Service
↓
Result / Response
```

Not every Endpoint necessarily uses every layer.

For example, an Endpoint may not require a Request, and some operations may use different response mechanisms.

The important rule is that each layer maintains its own responsibility.

---

# Workflow Portability

A well-designed Workflow should be as portable as practical.

Moving a Workflow to another MCF project should require minimal modification when its dependencies are available.

Avoid unnecessary dependencies on:

- Unrelated Workflows.
- Specific application configuration.
- Hard-coded role names.
- Hard-coded permission identifiers.
- Accidental database assumptions.

A Workflow may legitimately depend on application Models and shared MCF infrastructure when that dependency is part of its business capability.

Portability means clear dependencies, not artificial isolation.

---

# Do Not Delete Required Framework Structure

MCF Workflows and shared framework components may depend on one another.

Do not delete framework directories merely because a component is not currently being used.

If MCF provides a configuration or setting to disable a feature, prefer disabling it through the supported mechanism.

This keeps the project structurally compatible and reduces the risk of breaking dependencies.

---

# Use MCF Generators

Prefer the MCF generators whenever an appropriate generator exists.

Examples:

```bash
php artisan mcf:make:module
php artisan mcf:make:workflow
php artisan mcf:make:request
php artisan mcf:endpoint:create
php artisan mcf:endpoint:remove
```

Generated structures follow the MCF conventions and reduce manual structural errors.

Manual file creation is appropriate when the required structure is not covered by a generator or when a deliberate custom implementation is needed.

---

# Best Practices

When designing a Workflow:

- Give it one clear business responsibility.
- Organize it around business capability.
- Keep related Endpoints together.
- Keep Controllers thin.
- Put business logic in Services.
- Use Endpoint-specific Requests.
- Keep validation inside Requests.
- Use Data classes for structured validated input when useful.
- Keep Routes close to the Workflow.
- Keep Route Access metadata close to Routes.
- Keep Views local to the Workflow.
- Keep translations local to the Workflow.
- Reuse Models instead of duplicating them.
- Prefer clear dependencies between Workflows.
- Use MCF Generators.
- Avoid unnecessary Workflow duplication.
- Do not delete required framework structure to disable features.

---

# Summary

A Workflow is one of the core architectural units of MCF.

It represents a single business capability and groups the resources required to implement that capability:

```text
Workflow
├── Backend
│   ├── Controller
│   ├── Routes
│   ├── Service
│   └── Request
│       └── Endpoint Requests
├── Views
└── Lang
```

The current architecture deliberately avoids the old assumption of one Request per Workflow.

Instead, Requests are created for the operations that need them, while the Workflow remains the boundary that groups related business functionality.

This keeps Workflows focused, predictable, maintainable and scalable.
