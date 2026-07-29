# MCF Permissions

## Overview

MCF provides a fully dynamic authorization architecture.

Permissions, Roles, and Access Rules are application data.

They are never defined by the framework.

Authorization decisions are driven entirely by the application's permission provider.

---

# Core Philosophy

Permissions are data.

Roles are data.

Access rules are data.

MCF provides the authorization architecture.

Applications provide the authorization data.

---

# Dynamic Authorization

MCF does not recognize predefined roles.

Examples of forbidden assumptions:

```text
Admin

User

Manager

Employee

SuperAdmin
```

These are application-specific data created by developers.

MCF has no knowledge of them.

---

# No Hard-Coded Roles

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

Never assume any role exists.

---

# No Hard-Coded Permissions

Never write:

```php
$user->can('manage_users')
```

unless the permission identifier is resolved dynamically by the application's authorization system.

Permission identifiers should never become part of the framework's business logic.

---

# Roles

Roles exist only within the application's authorization data.

Example:

```text
Roles

--------------
Administrator
Manager
Accountant
Customer
```

MCF never creates or reserves any role.

Applications define them.

---

# Permissions

Permissions also exist only within the application's authorization data.

Example:

```text
Permissions

--------------
Create Users
Edit Users
Delete Users
View Reports
Manage Inventory
```

MCF never defines permissions.

Applications own them completely.

---

# Role Assignment

Users receive Roles through application-defined relationships.

Example:

```text
User

↓

Role

↓

Permissions
```

MCF does not require a specific database schema or authorization model.

Projects are free to implement their own design.

---

# Authorization Flow

Authorization follows this sequence:

```text
Request

↓

Policy

↓

Permission Service

↓

Permission Provider

↓

Permission Source

↓

Decision
```

Policies never perform authorization directly.

They delegate authorization to the Permission Service.

---

# Policies

Every generated Workflow owns its own Policy.

Generated Policies inherit from:

```text
MfcPolicy
```

Policies answer one question only:

```text
Is this action allowed?
```

Good:

```php
return $permissionService->allows(...);
```

Avoid:

```php
return $user->role == 'admin';
```

Policies must remain independent from authorization storage.

---

# Permission Service

The Permission Service is the central authorization engine.

Responsibilities include:

- Resolve permissions.
- Evaluate authorization rules.
- Apply business authorization policies.
- Return authorization results.

Every authorization request passes through this service.

Business Workflows should never duplicate authorization logic.

---

# Permission Provider

The Permission Provider retrieves authorization information.

Possible providers include:

- Database
- Cache
- REST API
- LDAP
- Identity Provider

Providers are interchangeable.

The Permission Service depends on the provider abstraction rather than a storage implementation.

---

# Permission Source

Authorization data may originate from any source.

Examples:

- MySQL
- PostgreSQL
- SQLite
- Redis
- REST API
- Identity Server

MCF imposes no storage requirements.

---

# Permission Resolution

Permission checks should always be resolved dynamically.

The Permission Service may retrieve authorization information from:

- Database
- Cache
- Remote services
- External identity providers

Policies should never care where authorization data is stored.

---

# Configuration

Authorization behavior should be configurable.

Permission storage must remain decoupled from business logic.

Changing the authorization backend should not require modifying Policies or Workflows.

---

# Module Independence

Modules must never assume:

- Role names
- Role IDs
- Permission names
- Permission IDs
- Database schema

Modules simply request an authorization decision.

---

# Database-Driven Authorization

Authorization should be manageable without modifying application source code.

Typical administrative operations include:

- Creating Roles
- Removing Roles
- Renaming Roles
- Creating Permissions
- Assigning Permissions
- Revoking Permissions

These operations should require only changes to the authorization data.

---

# Extensibility

MCF places no restrictions on authorization models.

Applications may implement:

- Role-Based Access Control (RBAC)
- Permission-Based Authorization
- Attribute-Based Access Control (ABAC)
- External Identity Providers
- Hybrid authorization models

As long as authorization remains dynamic and centralized.

---

# Permission Architecture

MCF separates authorization into four independent layers.

```text
Policy
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

Each layer has one clear responsibility.

---

# Separation of Responsibilities

## Policy

Responsibilities:

- Request authorization.
- Return authorization results.

Policies must never:

- Query the database.
- Read cache.
- Read configuration.
- Know table names.
- Know role names.
- Know permission identifiers.

---

## Permission Service

Responsibilities:

- Resolve authorization requests.
- Apply authorization logic.
- Coordinate authorization providers.
- Return authorization decisions.

This is the single entry point for authorization.

---

## Permission Provider

Responsibilities:

- Retrieve authorization data.
- Abstract the underlying storage mechanism.

Providers may use any supported backend without affecting Policies or Modules.

---

## Permission Source

Responsibilities:

- Store authorization data.

Examples include:

- SQL databases
- Cache systems
- Remote APIs
- Identity providers

The storage implementation remains completely replaceable.

---

# Forbidden Dependencies

Policies must never depend directly on:

- Database tables
- Eloquent Models
- Cache drivers
- Configuration values
- Role names
- Permission names
- Permission IDs
- Storage implementations

Authorization must always flow through the Permission Service.

---

# Design Principles

MCF authorization follows these principles:

- Dynamic
- Database-driven
- Provider-based
- Storage-independent
- Centralized
- Extensible
- Decoupled from business logic

Authorization data belongs to the application.

Authorization decisions belong to Workflow Policies.

Authorization resolution belongs to the Permission Service.

Authorization retrieval belongs to the Permission Provider.

No authorization information should ever be hard-coded into an MCF application.

---

# Benefits

This architecture provides:

- Storage independence.
- Dynamic authorization.
- Centralized permission resolution.
- Decoupled business logic.
- Replaceable authorization providers.
- Database independence.
- Improved testability.
- Future compatibility with external identity systems.
