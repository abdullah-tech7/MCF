# MCF Permissions

Authorization in MCF is designed around one principle:

> **The framework defines the authorization architecture. The application defines the authorization data.**

MCF provides a workflow-oriented authorization pipeline without making assumptions about roles, permissions, storage, or identity providers.

This allows the same Workflow to be reused across different projects without modifying its authorization logic.

---

# Authorization Philosophy

MCF treats authorization as an application concern rather than a framework concern.

The framework provides:

- Authorization architecture
- Authorization flow
- Workflow Policies
- Base Policy classes

Applications provide:

- Roles
- Permissions
- Authorization rules
- Storage implementation
- Identity model

This separation keeps business features independent from authorization implementation.

---

# Core Principles

MCF authorization follows five fundamental principles.

## Dynamic

Authorization decisions are evaluated at runtime.

Nothing is hard-coded into the framework.

---

## Centralized

Every authorization request passes through a single authorization service.

Authorization logic is never duplicated across Workflows.

---

## Storage Independent

Permissions may come from any storage mechanism.

Changing the storage backend must never require modifying Workflows or Policies.

---

## Workflow-Oriented

Authorization belongs to Workflows rather than Models.

Each Workflow owns its own Policy.

---

## Extensible

Applications are free to implement any authorization model without changing the framework architecture.

---

# Permissions Are Data

Permissions belong to the application.

They are not part of MCF.

Example:

```text
Create Users
Edit Users
Delete Users
Export Reports
Approve Orders
Manage Inventory
```

Applications define permission identifiers.

MCF never reserves any permission names.

---

# Roles Are Data

Roles are also application data.

Examples:

```text
Administrator
Manager
Employee
Customer
Accountant
```

These names exist only inside the application.

MCF never creates or reserves roles.

---

# Framework Guarantees

MCF intentionally avoids making assumptions about authorization.

The framework never assumes:

- Role names
- Role IDs
- Permission names
- Permission IDs
- Database tables
- Database schemas
- Identity providers

Every authorization decision is delegated to the application's authorization layer.

---

# Never Hard-Code Authorization

Avoid coupling business logic to authorization data.

Never write:

```php
$user->role == 'admin'
```

Never write:

```php
$user->isAdmin()
```

Never write:

```php
Role::ADMIN
```

Never write:

```php
enum Role
```

Never write:

```php
$user->can('manage_users')
```

inside framework components unless permission identifiers are resolved dynamically by the application's authorization service.

Business features should never depend on application-specific role names.

---

# Workflow Authorization

Authorization in MCF begins with the Workflow.

Every generated Workflow owns one Policy.

Example:

```text
Authentication
    └── AuthenticationPolicy
```

```text
Profile
    └── ProfilePolicy
```

```text
User Management
    └── UserManagementPolicy
```

```text
Product Catalog
    └── ProductCatalogPolicy
```

Authorization belongs to the business capability implemented by the Workflow.

It does not belong to database Models.

---

# Endpoint Authorization

A Workflow may expose many endpoints.

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

Every endpoint delegates authorization to the same Workflow Policy.

This keeps authorization centralized while allowing each action to define its own authorization requirements.

---

# Authorization Lifecycle

Every authorization request follows the same pipeline.

```text
HTTP Request

↓

Workflow Endpoint

↓

Workflow Policy

↓

Permission Service

↓

Permission Provider

↓

Permission Source

↓

Authorization Decision

↓

Workflow Service
```

Every layer has a single responsibility.

No layer should bypass another.

---

# Workflow Policies

Every generated Workflow inherits from:

```text
MfcPolicy
```

The Policy acts as the authorization entry point for the Workflow.

Its responsibility is simple:

> Decide whether a Workflow action may continue.

Policies should never implement authorization storage.

Instead they delegate authorization.

Good example:

```php
return $permissionService->allows(...);
```

Avoid:

```php
return $user->role == 'admin';
```

Policies answer authorization questions.

They do not resolve authorization themselves.

---

# Permission Service

The Permission Service is the central authorization engine.

Every authorization request passes through this service.

Responsibilities include:

- Resolve permission requests.
- Apply authorization rules.
- Coordinate permission providers.
- Return authorization decisions.
- Centralize business authorization logic.

Workflows should never duplicate authorization logic outside this service.

---

# Permission Provider

The Permission Provider retrieves authorization data.

The provider hides how authorization information is stored.

Possible providers include:

- Database
- Cache
- REST API
- LDAP
- Identity Provider
- External Authorization Service

Because Policies depend only on the provider abstraction, storage implementations may change without affecting business code.

---

# Permission Source

Authorization information may originate from any source.

Examples include:

- MySQL
- PostgreSQL
- SQLite
- SQL Server
- Redis
- REST APIs
- External Identity Providers
- Cloud Authorization Services

MCF imposes no storage requirements.

Applications remain free to choose the implementation that best fits their needs.

---

# Permission Resolution

Permission checks should always be evaluated dynamically.

The Permission Service may obtain authorization information from:

- Database
- Cache
- Remote APIs
- Identity servers
- Multiple providers simultaneously

Policies should never know where authorization information comes from.

# Module Independence

Modules must remain independent from authorization implementation.

A Module must never assume:

- Role names
- Role identifiers
- Permission names
- Permission identifiers
- Database schema
- Authorization tables
- Storage implementation

Instead, Modules simply request an authorization decision from the Permission Service.

This guarantees that the same Module can be reused across multiple applications without modification.

---

# Workflow Independence

One of the primary goals of MCF is Workflow portability.

A Workflow should be reusable in another project without changing its authorization logic.

Example:

Project A

```text
Administrator
Manager
Employee
```

Project B

```text
Owner
Supervisor
Operator
```

Although role names differ completely, the Workflow remains unchanged.

Only the application's authorization data changes.

This separation allows Workflows to remain portable and framework-independent.

---

# Database-Driven Authorization

Authorization should be manageable without modifying application source code.

Typical administrative operations include:

- Creating Roles
- Renaming Roles
- Removing Roles
- Creating Permissions
- Updating Permissions
- Assigning Permissions
- Revoking Permissions
- Creating Permission Groups

All of these operations should affect only authorization data.

Business Workflows should not require modification.

---

# Configuration

Authorization behavior should remain configurable.

Applications may choose how authorization is implemented.

Examples include:

- Database authorization
- Cached authorization
- Remote authorization
- Hybrid authorization

Changing the authorization backend should never require modifying Workflow Policies.

---

# Supported Authorization Models

MCF does not enforce a specific authorization strategy.

Applications may implement:

- Role-Based Access Control (RBAC)
- Permission-Based Authorization
- Attribute-Based Access Control (ABAC)
- Claims-Based Authorization
- External Identity Providers
- Hybrid authorization models

As long as authorization remains centralized and dynamic, the implementation is compatible with MCF.

---

# Separation of Responsibilities

MCF divides authorization into four independent layers.

```text
Workflow Policy
        │
        ▼
Permission Service
        │
        ▼
Permission Provider
        │
        ▼
Permission Source
```

Each layer has one responsibility.

This separation keeps authorization predictable, testable and easy to extend.

---

# Workflow Policy

Responsibilities:

- Receive authorization requests.
- Delegate authorization.
- Return authorization results.

Workflow Policies must never:

- Query databases.
- Read cache.
- Read configuration files.
- Know table names.
- Know role names.
- Know permission identifiers.
- Know storage implementation.

Policies should remain thin and predictable.

---

# Permission Service

Responsibilities:

- Resolve authorization requests.
- Evaluate authorization rules.
- Coordinate providers.
- Return authorization decisions.

The Permission Service is the single entry point for authorization inside the application.

Business authorization logic belongs here.

---

# Permission Provider

Responsibilities:

- Retrieve authorization data.
- Hide storage implementation.
- Provide a consistent interface to the Permission Service.

Providers may read authorization information from any supported backend.

Replacing one provider with another must not affect Workflow Policies.

---

# Permission Source

Responsibilities:

- Persist authorization data.

Possible implementations include:

- SQL databases
- Cache systems
- Identity providers
- Remote authorization services
- Enterprise IAM systems

Permission Sources are completely replaceable.

---

# Forbidden Dependencies

Workflow Policies must never depend directly on:

- Database tables
- Eloquent models
- Cache drivers
- Configuration values
- Role names
- Permission names
- Permission identifiers
- Storage implementation

All authorization must flow through the Permission Service.

This keeps business features independent from infrastructure.

---

# Laravel Integration

MCF builds on Laravel's authorization system.

It does not replace Laravel Policies or Gates.

Instead, MCF organizes authorization around Workflows while keeping Laravel's authorization facilities available.

Applications may continue using Laravel's authorization features wherever appropriate.

---

# Design Principles

Authorization in MCF follows these principles.

- Dynamic
- Centralized
- Workflow-Oriented
- Storage Independent
- Provider Based
- Extensible
- Decoupled from Business Logic

Authorization data belongs to the application.

Authorization decisions belong to Workflow Policies.

Authorization resolution belongs to the Permission Service.

Authorization retrieval belongs to the Permission Provider.

Authorization storage belongs to the Permission Source.

Each responsibility exists in exactly one layer.

---

# Benefits

This architecture provides:

- Workflow portability.
- Storage independence.
- Dynamic authorization.
- Centralized permission resolution.
- Decoupled business logic.
- Replaceable authorization providers.
- Database independence.
- Improved maintainability.
- Easier testing.
- Compatibility with enterprise identity systems.
- Future-proof authorization architecture.

---

# Summary

MCF treats authorization as a Workflow concern rather than a database concern.

Every Workflow owns its own Policy.

Every Policy delegates authorization to the Permission Service.

The Permission Service coordinates one or more Permission Providers.

Providers retrieve authorization data from any Permission Source chosen by the application.

This layered architecture keeps Workflows independent from roles, permissions, storage technologies and infrastructure, allowing the same business feature to be reused across different projects with minimal or no changes.

The framework defines the authorization architecture.

The application defines the authorization data.

Together, they provide a flexible, centralized and extensible authorization system that scales with applications of any size.