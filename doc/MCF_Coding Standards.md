# Coding Standards

This document defines the recommended coding standards for applications built with MCF.

These standards are designed to keep MCF projects consistent, readable, predictable and maintainable.

Individual applications may introduce additional conventions, but project-specific conventions should remain compatible with the MCF architecture.

---

# General Principles

Every piece of code should be:

- Simple
- Readable
- Predictable
- Consistent
- Maintainable

Code is written once but read many times.

Always optimize for readability and clear responsibility.

---

# Follow the MCF Structure

Always place files in their intended MCF location.

A typical Workflow is organized as:

```text
Workflow
├── Backend
├── Views
└── Lang
```

Do not move Workflow resources into unrelated application directories.

The standard structure allows developers to understand an MCF project without learning a different organization for every project.

---

# Understand the MCF Levels

Do not treat `Modules` as the whole MCF architecture.

MCF has shared framework infrastructure under:

```text
app/MCF
```

and application business features under:

```text
app/MCF/Modules
```

Shared components such as Authentication, Audit, Access Control, Mail, Notification, Result and SMS belong to the framework-level structure.

Feature-specific implementation belongs inside Modules and their Workflows.

---

# Keep Workflows Self-Contained

Everything related to a feature should remain inside its Workflow whenever practical.

Example:

```text
Profile
├── Backend
├── Views
└── Lang
```

A Workflow should contain the resources needed to implement its business capability without scattering them across unrelated directories.

Shared functionality should only be moved outside the Workflow when it is genuinely reusable.

---

# Respect Class Responsibilities

Each class should have one clear primary responsibility.

| Component | Responsibility |
|---|---|
| Controller | HTTP coordination |
| Service | Business logic and workflow operations |
| Request | Input validation and request authorization when applicable |
| Data | Structured validated input |
| Routes | Route definitions and route-related access metadata |
| Shared framework components | Cross-feature infrastructure |

Do not mix unrelated responsibilities between these components.

---

# Keep Controllers Thin

Controllers should contain as little business logic as possible.

Their normal responsibilities are:

- Receiving the Request.
- Passing validated input to the Service.
- Coordinating the operation.
- Returning the appropriate Result or response.

Good:

```php
public function update(ProfileRequest $request)
{
    return $this->service->update(
        $request->data(),
    );
}
```

Avoid putting the following directly inside Controllers:

- Business rules.
- Complex database operations.
- Complex calculations.
- Reusable authorization logic.
- Validation rules.

Controllers should coordinate, not implement the business domain.

---

# Keep Services Focused

Services contain the business logic and Workflow operations.

Typical responsibilities include:

- Business rules.
- Workflow coordination.
- Domain operations.
- Data processing.
- Application-specific operations.

Avoid putting HTTP presentation concerns inside Services.

A Service should be usable by the Workflow without requiring the Controller to contain business logic.

---

# Centralize Validation in Requests

Validation belongs inside Request classes.

Requests are not required to be one shared Request for the entire Workflow.

Current MCF Requests are created for the operation or Endpoint that needs them.

Example:

```text
Backend
└── Request
    └── LoginRequest.php
```

If several operations require different input rules, create separate Request classes.

Avoid duplicating the same validation rules across Controller methods.

---

# Use Request Data for Structured Input

When a Request defines a Data class, the Data class represents validated input.

Example:

```php
protected function dataClass(): ?string
{
    return LoginData::class;
}
```

Data classes should carry validated input.

They should not contain Controller logic, route handling or unrelated business operations.

Keep Data objects small and explicit.

---

# Request Naming

Request names should describe the operation they validate.

Examples:

```text
LoginRequest.php
CreateUserRequest.php
UpdateProfileRequest.php
```

Use the operation name rather than a generic name such as:

```text
Request.php
Data.php
Input.php
```

The first letter of generated Request names should be uppercase and the remaining name should follow the MCF naming convention.

---

# Authorization and Access Control

Do not hard-code role checks throughout Controllers and Services.

Avoid patterns such as:

```php
if ($user->role == 'admin')
```

when the decision belongs to the application's authorization or access-control layer.

Use the MCF authorization and Access Control mechanisms provided for the relevant feature.

Route access requirements should remain associated with the relevant route definitions and MCF access-control infrastructure.

Authorization should be explicit, centralized and understandable.

---

# Prefer Dependency Injection

Use dependency injection whenever practical.

Good:

```php
public function __construct(
    ProfileService $service,
) {
    $this->service = $service;
}
```

Avoid creating application dependencies manually inside methods when the dependency can be injected.

Dependency injection improves testability, flexibility and separation of responsibilities.

---

# Keep Methods Small

A method should perform one coherent task.

Good:

```text
updateProfile()
```

Avoid methods that simultaneously:

- Validate input.
- Process business rules.
- Perform unrelated database operations.
- Send notifications.
- Generate reports.
- Build presentation responses.

Break complex operations into focused methods or services.

---

# Use Meaningful Names

Names should clearly describe intent.

Good:

```text
UserManagementService
ProductCatalogController
SalesReportRequest
LoginData
```

Avoid vague names such as:

```text
Helper
Manager
Data
Handler
TestClass
```

unless the name is genuinely precise within its context.

Descriptive names reduce the need for comments.

---

# Keep Routes Close to Features

Routes should remain inside their owning Workflow.

Example:

```text
Profile
└── Backend
    └── ProfileRoutes.php
```

Do not create one large application route file containing unrelated Workflow routes.

MCF also provides its framework-level route integration through:

```text
app/MCF/mcf_routes.php
```

Keep feature routes and framework route integration conceptually separate.

---

# Keep Route Access Metadata Close to Routes

When a route requires Access Control metadata, define that information alongside the route through the supported MCF route registry.

This makes it clear:

- Which route exists.
- What access it requires.
- Which feature owns the route.

Avoid scattering route access definitions into unrelated files.

---

# Keep Views Organized

Blade templates should remain inside the Workflow that owns them.

Example:

```text
Views
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── components
```

Avoid mixing templates from unrelated business features.

Shared presentation infrastructure should remain in the appropriate shared Layout Workflow.

---

# Keep Translations Local

Feature-specific translations belong to the Workflow they describe.

Example:

```text
Profile
└── Lang
```

MCF discovers Workflow language resources automatically.

Do not create unnecessary central translation files for feature-specific text when the text belongs to one Workflow.

Shared language infrastructure belongs under:

```text
app/MCF/Language
```

---

# Avoid Hard-Coded Application Values

Avoid embedding application-specific values directly throughout the code.

Examples include:

- Role identifiers.
- Permission identifiers.
- Database identifiers.
- Repeated business constants.
- Environment-specific values.

Prefer the appropriate configuration, settings, service or centralized definition.

Do not over-centralize values that are genuinely local to one operation.

---

# Naming Conventions

Use clear and consistent names.

Examples:

```text
Controllers:
ProfileController.php

Services:
ProfileService.php

Requests:
LoginRequest.php

Data:
LoginData.php

Routes:
ProfileRoutes.php
```

Keep class names in PascalCase.

Keep filenames consistent with their class names.

Consistency improves discoverability and reduces mistakes when using MCF generators.

---

# Keep Business Logic Independent

Business logic should not depend unnecessarily on:

- Blade templates.
- Route definitions.
- Controller presentation details.
- Raw HTTP handling.

Business operations should remain reusable within the application architecture.

Database access may be required by business operations, but database-specific concerns should not be mixed with unrelated presentation logic.

---

# Prefer MCF Generators

Use MCF generators whenever a generator exists for the required component.

Examples include generators for:

- Modules.
- Workflows.
- Workflow layouts.
- Requests.
- Endpoints.
- Middleware.
- Mail.

Generated code already follows the MCF architecture and naming conventions.

Manual file creation should be used when a generator does not cover the required case or when an intentional custom implementation is needed.

---

# Keep Shared Logic in Shared Components

If code is reused by multiple Workflows, consider moving it into an appropriate shared MCF component.

Possible locations include:

- `Base`
- `Authentication`
- `AccessControl`
- `Audit`
- `Language`
- `Mail`
- `Middleware`
- `Notification`
- `Result`
- `Sms`

Do not move code into a shared component merely because it can be reused once.

Share code when the responsibility is genuinely cross-feature.

---

# Do Not Delete Framework Components to Disable Features

An MCF installation provides a complete framework structure.

If a component is not currently required, do not automatically delete its framework directory.

When MCF provides a supported setting to enable or disable a feature, prefer that mechanism.

This keeps the project structurally compatible with the framework and avoids breaking dependencies between MCF components.

---

# Prefer Generated and Existing Structure Over Duplication

Before creating a new framework-level component, check whether MCF already provides the required infrastructure.

Before creating a new shared utility, check whether the behavior already exists in an appropriate MCF component.

The goal is not to create the fewest files; the goal is to keep responsibilities clear and avoid unnecessary duplication.

---

# Document Complex Logic

Most code should be self-explanatory.

When a business rule or implementation detail is complex or non-obvious, add a concise comment explaining why the code exists.

Prefer:

```php
// Keep the account active until the verification window expires.
```

over comments that simply repeat the code.

Avoid excessive comments that make simple code harder to read.

---

# Maintain Consistency

Consistency is more important than personal preference within an existing MCF project.

Follow the same:

- Naming conventions.
- Folder structure.
- Class responsibilities.
- Workflow boundaries.
- Request conventions.
- Route conventions.
- Coding style.

A consistent codebase is easier for every developer to understand and maintain.

---

# Summary

MCF coding standards emphasize:

- Clear responsibilities.
- Self-contained Workflows.
- Thin Controllers.
- Focused Services.
- Endpoint-specific Requests.
- Explicit Data objects when useful.
- Centralized validation.
- Centralized authorization and Access Control.
- Feature-local Routes, Views and translations.
- Appropriate shared framework components.
- Dependency injection.
- Consistent naming.
- MCF generators.
- Minimal duplication.

Following these standards keeps MCF applications predictable, maintainable and scalable as the project grows.
