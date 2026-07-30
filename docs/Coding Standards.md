# Coding Standards

This document defines the recommended coding standards for applications built with MCF.

These guidelines help maintain consistency across projects, improve readability and simplify long-term maintenance.

While individual projects may introduce additional conventions, following these standards is strongly recommended.

---

# General Principles

Every piece of code should be:

- Simple
- Readable
- Predictable
- Consistent
- Maintainable

Code is written once but read many times.

Always optimize for readability.

---

# Follow the Framework Structure

Always place files in their intended location.

Good:

```text
Profile
├── Backend
├── Views
└── Lang
```

Avoid moving Workflow files into unrelated directories.

Following the standard structure makes every MCF project immediately familiar.

---

# Respect Class Responsibilities

Each backend class has exactly one responsibility.

| Class | Responsibility |
|--------|----------------|
| Controller | HTTP communication |
| Service | Business logic |
| Request | Validation |
| Policy | Authorization |
| Routes | Route definitions |

Avoid mixing responsibilities between classes.

---

# Keep Controllers Thin

Controllers should contain as little code as possible.

Good:

```php
public function update(ProfileRequest $request)
{
    return $this->service->update($request->validated());
}
```

Avoid:

- Business rules
- Database queries
- Complex calculations
- Permission checks
- Validation logic

Controllers should coordinate, not implement business logic.

---

# Keep Services Focused

Services should contain business logic only.

Typical responsibilities include:

- Business rules
- Workflow coordination
- Domain operations
- Data processing

Avoid placing HTTP or presentation logic inside Services.

---

# Centralize Validation

Validation belongs inside Request classes.

Good:

```text
ProfileRequest
```

Avoid repeating validation rules across multiple Controller methods.

Every validation rule should exist in one place.

---

# Centralize Authorization

Authorization belongs inside Workflow Policies.

Avoid writing:

```php
if ($user->role == 'admin')
```

inside Controllers or Services.

Policies should delegate authorization through the application's authorization layer.

---

# Prefer Dependency Injection

Always depend on abstractions through dependency injection whenever possible.

Good:

```php
public function __construct(ProfileService $service)
{
    $this->service = $service;
}
```

Avoid creating dependencies manually inside methods.

Dependency injection improves testability and flexibility.

---

# Keep Methods Small

Methods should perform one task.

Good:

```php
updateProfile()
```

Avoid methods that:

- Validate data
- Process business logic
- Send emails
- Generate reports
- Return responses

all at once.

Small methods are easier to understand and test.

---

# Use Meaningful Names

Names should clearly describe intent.

Good:

```text
UserManagementService
```

```text
ProductCatalogController
```

```text
SalesReportRequest
```

Avoid:

```text
Helper
```

```text
Manager
```

```text
Data
```

```text
TestClass
```

Descriptive names reduce the need for comments.

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

Avoid scattering feature files across unrelated directories.

---

# Avoid Duplicate Code

If code is reused by multiple Workflows, move it into a shared component.

Possible shared locations include:

- Base classes
- Middleware
- Rules
- Notifications
- Mail

Avoid copying the same implementation between Workflows.

---

# Keep Routes Close to Features

Routes should remain inside their owning Workflow.

Good:

```text
ProfileRoutes.php
```

Avoid maintaining one large application route file containing unrelated features.

---

# Keep Views Organized

Blade templates should remain inside the Workflow that owns them.

Example:

```text
Views
├── index.blade.php
├── create.blade.php
└── edit.blade.php
```

Avoid mixing templates from unrelated features.

---

# Keep Translations Local

Translations belong to the Workflow they describe.

Good:

```text
Profile
└── Lang
```

MCF automatically discovers Workflow language resources during application startup.

No manual registration is required.

---

# Avoid Hard-Coded Values

Avoid embedding application-specific values directly in code.

Examples include:

- Role names
- Permission names
- Database identifiers
- Business constants

Prefer configuration, services or centralized definitions where appropriate.

---

# Follow Naming Conventions

Use clear and consistent naming throughout the project.

Examples:

Controllers:

```text
ProfileController
```

Services:

```text
ProfileService
```

Requests:

```text
ProfileRequest
```

Policies:

```text
ProfilePolicy
```

Routes:

```text
ProfileRoutes
```

Consistency improves discoverability.

---

# Keep Business Logic Independent

Business logic should not depend on:

- HTTP requests
- Blade templates
- Database schema
- Route definitions

Business logic should remain reusable across different interfaces.

---

# Write Predictable Code

Developers should be able to predict where functionality exists.

Questions such as:

- Where is validation?
- Where is authorization?
- Where is business logic?
- Where are translations?

should always have the same answer.

Predictability reduces onboarding time and maintenance costs.

---

# Prefer Framework Generators

Use MCF generators whenever possible.

Generated classes already follow the framework architecture and naming conventions.

Manual file creation should be the exception rather than the rule.

---

# Keep Shared Logic in Base Classes

When multiple Workflows require identical behavior, extend the MCF base classes instead of duplicating implementations.

This keeps shared functionality centralized and easier to maintain.

---

# Document Complex Logic

Most code should be self-explanatory.

However, when business rules are complex or non-obvious, add concise comments explaining *why* the code exists rather than *what* it does.

Avoid excessive or redundant comments.

---

# Maintain Consistency

Consistency is more important than personal preference.

Follow the same:

- Naming
- File organization
- Architecture
- Coding style
- Workflow structure

throughout the entire project.

A consistent codebase is easier for every developer to understand.

---

# Summary

MCF coding standards emphasize clarity, consistency and separation of responsibilities.

By keeping Controllers thin, Services focused, Requests responsible for validation, Policies responsible for authorization and Workflows self-contained, applications remain predictable, maintainable and scalable throughout their lifecycle.