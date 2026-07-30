# Endpoint Generator

The Endpoint Generator automates the process of adding new endpoints to existing Workflows.

Instead of manually updating Controllers, Routes and related classes, MCF generates the required boilerplate while preserving a consistent project structure.

The generator reduces repetitive work and ensures every endpoint follows the same architectural conventions.

---

# Philosophy

An endpoint represents one action performed by a Workflow.

Examples include:

- List records
- Create records
- Update records
- Delete records
- Export data
- Search
- Archive
- Restore

Endpoints belong to Workflows.

They should never exist independently.

---

# Create an Endpoint

Generate a new endpoint.

```bash
php artisan mcf:endpoint:create
```

The command is interactive and guides the developer through the required input.

Typical prompts include:

- Module
- Workflow
- Endpoint name

---

# Generated Changes

The Endpoint Generator updates the Workflow automatically.

Depending on the endpoint type, generated changes may include:

- Controller methods
- Route definitions
- Service methods
- Request classes (if required)
- Policy methods (if required)

The goal is to eliminate repetitive manual edits.

---

# Example

Assume the following Workflow.

```text
Users
└── Profile
```

Create a new endpoint.

```text
export
```

After generation, the Workflow may contain:

```text
Profile
└── Backend
    ├── ProfileController.php
    ├── ProfileRoutes.php
    ├── ProfileService.php
    ├── ProfileRequest.php
    └── ProfilePolicy.php
```

The generator updates the existing files instead of creating duplicate classes.

---

# Endpoint Naming

Endpoint names should describe an action.

Good examples:

- index
- create
- store
- edit
- update
- delete
- restore
- archive
- export
- search

Use verbs that clearly express the operation being performed.

---

# Avoid Generic Names

Avoid endpoint names that do not communicate intent.

Examples:

- action
- execute
- process
- test
- run
- temp

Meaningful endpoint names improve readability throughout the project.

---

# Workflow Ownership

Every endpoint belongs to exactly one Workflow.

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

Related actions remain together.

Avoid spreading endpoints for the same business capability across multiple Workflows.

---

# Route Registration

Generated endpoints automatically become part of the Workflow's route definitions.

Routes remain inside:

```text
Backend
└── WorkflowRoutes.php
```

MCF discovers and registers Workflow routes automatically during application startup.

No manual route registration is required.

---

# Controller Integration

Generated endpoints add new Controller actions.

Controllers remain responsible for:

- Receiving requests.
- Delegating to Services.
- Returning responses.

Business logic should continue to reside inside Workflow Services.

---

# Service Integration

When appropriate, the generator adds matching Service methods.

Services contain:

- Business rules.
- Workflow coordination.
- Domain operations.

Generated methods provide a consistent starting point for implementation.

---

# Request Integration

Endpoints requiring validation may use the Workflow Request.

Validation remains centralized inside Request classes rather than being duplicated across Controller methods.

---

# Policy Integration

Endpoints requiring authorization should delegate permission checks through the Workflow Policy.

Policies remain the single authorization entry point for the Workflow.

Authorization logic should never be implemented directly inside Controllers.

---

# Endpoint Lifecycle

A generated endpoint follows the normal Workflow execution pipeline.

```text
HTTP Request

↓

Workflow Route

↓

Workflow Controller

↓

Workflow Request

↓

Workflow Policy

↓

Workflow Service

↓

Response
```

Each component performs a single responsibility before passing control to the next.

---

# Remove an Endpoint

Existing endpoints can be removed.

```bash
php artisan mcf:endpoint:remove <module> <workflow> <endpoint>
```

Example:

```bash
php artisan mcf:endpoint:remove Users Profile export
```

Only the selected endpoint is removed.

The remaining Workflow structure remains unchanged.

---

# Benefits

Using the Endpoint Generator provides several advantages.

- Consistent code generation.
- Reduced boilerplate.
- Faster feature development.
- Predictable project structure.
- Fewer manual errors.
- Easier maintenance.

Generated endpoints always follow the architectural conventions established by MCF.

---

# Best Practices

When creating endpoints:

- Keep endpoint names descriptive.
- Group related actions inside one Workflow.
- Keep Controllers thin.
- Place business logic inside Services.
- Centralize validation in Requests.
- Delegate authorization through Policies.
- Prefer generators over manual edits.

Following these practices keeps Workflows consistent and maintainable.

---

# Summary

The Endpoint Generator streamlines the creation of Workflow actions by automatically updating the appropriate backend classes while preserving MCF's architectural conventions.

By generating Controllers, Routes and supporting code consistently, developers can focus on implementing business logic rather than maintaining repetitive framework boilerplate.