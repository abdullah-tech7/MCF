# MCF Permissions

## Overview

MCF uses a fully dynamic permission system.

Permissions, Roles, and Access Rules are application data.

They are not part of the application source code.

Authorization is driven entirely by the database.

---

# Core Philosophy

Permissions are data.

Roles are data.

Access rules are data.

The application must never hard-code authorization logic.

---

# Dynamic Authorization

MCF does not recognize predefined roles.

Examples of forbidden assumptions:

```
Admin

User

Manager

Employee

SuperAdmin
```

These are application data created by developers.

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
if ($user->can('manage_users'))
```

unless the permission name originates from the database.

Permission names must never be embedded as application logic.

---

# Roles

Roles exist only inside the database.

Example:

```
Roles

--------------
1 Administrator
2 Manager
3 Accountant
4 Customer
```

MCF never creates these roles.

The application owns them.

---

# Permissions

Permissions also exist only inside the database.

Example:

```
Permissions

--------------
Create Users

Edit Users

Delete Users

View Reports

Manage Inventory
```

MCF never defines permissions.

Applications define them.

---

# Role Assignment

Users receive Roles through database relationships.

Example:

```
User

↓

Role

↓

Permissions
```

MCF does not require a specific database schema.

Projects are free to design their own authorization model.

---

# Authorization Flow

Authorization follows this sequence:

```
Request

↓

Policy

↓

Permission Service

↓

Database

↓

Decision
```

Authorization decisions are made using application data.

Never using hard-coded values.

---

# Policies

Policies are responsible for asking whether an action is allowed.

Policies should never contain role names.

Good:

```
return $permissionService->allows(...);
```

Avoid:

```php
return $user->role == 'admin';
```

---

# Permission Resolution

Permission checks should always resolve permissions dynamically.

Possible lookup sources include:

- Database
- Cache
- External authorization provider

Policies should not care where permissions are stored.

---

# Configuration

Permission behavior should be configurable.

Permission storage must not be tightly coupled to implementation details.

---

# Module Independence

Modules should never assume:

- role names
- role IDs
- permission IDs
- permission names

Modules simply ask whether an action is permitted.

---

# Shared Permission Service

Permission resolution should be centralized.

Applications are encouraged to use a shared service responsible for:

- Loading permissions
- Caching permissions
- Resolving access
- Returning authorization results

Business Workflows should never duplicate permission logic.

---

# Database Driven

Authorization should be manageable without changing source code.

Typical changes include:

- Creating roles
- Removing roles
- Renaming roles
- Adding permissions
- Revoking permissions

These operations should require database updates only.

No code modifications.

---

# Extensibility

MCF places no restrictions on permission models.

Applications may implement:

- RBAC
- Permission-based authorization
- Attribute-based authorization
- External IAM providers

As long as authorization remains dynamic.

---

# Design Principles

MCF authorization follows these principles:

- Database-driven
- Dynamic
- Extensible
- Centralized
- Decoupled from business logic

Authorization data belongs to the application.

Authorization behavior belongs to Policies.

Authorization resolution belongs to a shared Permission Service.

No authorization information should ever be hard-coded into the application.


# Permission Architecture

MCF separates authorization into three independent responsibilities.

```
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

Each layer has a single responsibility.

---

## Policy

Policies answer one question only:

```
Is this action allowed?
```

Policies never:

- Query the database.
- Read cache.
- Read configuration.
- Know table names.
- Know role names.
- Know permission IDs.

Policies delegate authorization to the Permission Service.

---

## Permission Service

The Permission Service is the central authorization engine.

Responsibilities:

- Resolve permissions.
- Evaluate access rules.
- Apply authorization logic.
- Return authorization results.

Every authorization request passes through this service.

Business Modules must never implement permission resolution themselves.

---

## Permission Provider

The Permission Provider retrieves authorization data.

Possible providers include:

- Database
- Cache
- Remote API
- LDAP
- Identity Provider

The Permission Service does not depend on a specific storage mechanism.

Providers are interchangeable.

---

## Permission Source

The actual permission data may come from any source.

Examples:

- MySQL
- PostgreSQL
- Redis
- REST API
- Identity Server

MCF does not enforce a storage implementation.

---

# Separation of Responsibilities

```
Policy

↓

Permission Service

↓

Permission Provider

↓

Database
```

Changing the database implementation must not require modifying Policies.

Changing the authorization provider must not require modifying Modules.

Changing permission storage must not require modifying business logic.

---

# Forbidden Dependencies

Policies must never depend directly on:

- Database tables
- Eloquent models
- Cache drivers
- Configuration values
- Role names
- Permission IDs

Authorization must always be resolved through the Permission Service.

---

# Benefits

This architecture provides:

- Storage independence
- Dynamic authorization
- Testability
- Centralized permission logic
- Replaceable authorization providers
- Future compatibility