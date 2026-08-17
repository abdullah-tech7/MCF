# Endpoint Generator

The Endpoint Generator is the primary way to add a complete Endpoint to an existing MCF Workflow.

Instead of manually editing the Workflow Controller and Routes, and optionally creating the Endpoint View and Request, MCF performs the structural work automatically.

The generator is responsible for framework boilerplate. Business logic remains the developer's responsibility.

---

# Philosophy

An Endpoint represents one executable action inside a Workflow.

It is more than a Controller method because one Endpoint can affect several parts of the Workflow.

For example:

```text
Authentication
├── login
├── loginPost
├── logout
├── forgotPassword
└── resetPassword
```

Every Endpoint belongs to exactly one Workflow.

An Endpoint is treated as a complete feature path: when generated, all selected structural parts are created and connected together.

---

# Generator First

MCF follows a Generator First philosophy.

Prefer the MCF generator instead of manually editing:

- Controllers
- Routes
- Endpoint Views
- Endpoint Requests

The generator owns the structural changes required for the Endpoint.

Developers should focus on implementing business logic rather than maintaining repetitive framework boilerplate.

---

# Create an Endpoint

Run:

```bash
php artisan mcf:endpoint:create
```

The command is interactive.

The generator collects the information required to create the Endpoint, including the Module, Workflow, Endpoint name, View option, HTTP method, return type, Request option and parameters.

---

# Endpoint Requirements

The selected Module and Workflow must already exist.

The Endpoint Generator adds the Endpoint to the existing Workflow.

It does not create a new Module or Workflow.

Use the dedicated generators first when those structures do not exist:

```bash
php artisan mcf:make:module
```

```bash
php artisan mcf:make:workflow
```

---

# What the Generator Updates

Depending on the selected options, creating an Endpoint can update:

- Workflow Routes
- Workflow Controller
- Endpoint View
- Endpoint Request

The generator updates the existing Workflow structure instead of creating duplicate Controllers or unrelated files.

It does not generate Service business logic.

---

# Request Integration

Requests are independent MCF resources.

The current architecture does not use the old shared Workflow Request concept.

When the Endpoint requires a Request:

- The Request name is derived from the Endpoint name.
- The first character is uppercase and the remaining characters follow the MCF naming convention.
- If the Workflow `Request` directory does not exist, it is created.
- If the Endpoint Request does not exist, it is created.
- The generated Request is connected to the generated Controller method.

For example:

```text
Endpoint:
login
```

creates:

```text
Request/LoginRequest.php
```

The resulting Controller method uses:

```php
public function login(LoginRequest $request)
```

The Endpoint Generator therefore uses the same Request architecture as:

```bash
php artisan mcf:make:request
```

---

# Existing Requests

The Endpoint Generator should not silently replace an existing Request.

If the Request required by the Endpoint already exists, the Endpoint operation must handle that state explicitly rather than overwriting the developer's Request implementation.

The Request can be prepared independently before creating an Endpoint:

```bash
php artisan mcf:make:request User Auth Login
```

This allows developers to design validation and Data handling before connecting the Request to an Endpoint.

---

# Create View

The View option determines whether an Endpoint Blade View is generated.

Choose **Yes** when the Endpoint displays a page.

Examples:

```text
login
register
forgotPassword
resetPassword
```

Choose **No** when the Endpoint performs an action without displaying a dedicated page.

Examples:

```text
loginPost
logout
verifyEmail
delete
export
```

When a View is selected, it belongs to the Workflow's `Views` directory.

---

# HTTP Method

The HTTP method defines the Route verb.

Supported methods include:

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

Choose the method according to the semantics of the Endpoint rather than simply following its name.

---

# Return Type

The Return Type defines the Controller method's response type.

Supported types may include:

```text
View
RedirectResponse
JsonResponse
BinaryFileResponse
StreamedResponse
Response
```

The generator uses the selected type when creating the Controller method.

---

# Request Option

When Request support is selected, the Endpoint Generator connects an Endpoint-specific Request to the Controller.

Without Request:

```php
public function login()
```

With Request:

```php
public function login(LoginRequest $request)
```

Validation remains inside the Request.

The Request may also expose its Data class when the Endpoint requires structured validated input.

---

# Parameters

Endpoint parameters are optional.

Example:

```text
int $id
```

generates:

```php
public function edit(int $id)
```

Another example:

```text
string $token
```

generates:

```php
public function verifyEmail(string $token)
```

Parameters should represent values required by the Endpoint operation.

---

# Endpoint Naming

The Endpoint name should describe one action.

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
verifyEmail
resendVerification
export
archive
```

Avoid vague names such as:

```text
action
process
execute
run
test
temp
```

---

# Page and Action Naming

MCF recommends pairing related page and action Endpoints using the same base name.

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

This convention keeps related operations easy to locate.

---

# Endpoint Structure

A generated Endpoint can produce a structure such as:

```text
Auth
└── Backend
    ├── AuthController.php
    ├── AuthRoutes.php
    ├── AuthService.php
    └── Request
        └── LoginRequest.php
```

If a View is selected:

```text
Auth
├── Backend
│   ├── AuthController.php
│   ├── AuthRoutes.php
│   ├── AuthService.php
│   └── Request
│       └── LoginRequest.php
└── Views
    └── login.blade.php
```

The Service remains untouched by the generator.

---

# Business Logic

The Endpoint Generator intentionally does not generate Service methods.

Business logic differs between applications and cannot be generated reliably.

When an Endpoint requires business logic, implement it in the appropriate Workflow Service.

For example:

```text
Controller
    ↓
Service
    ↓
Business Logic
```

The generator creates the structural connection; the developer implements the behavior.

---

# Endpoint Execution

A typical Endpoint can follow this structure:

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

Each layer should maintain its own responsibility.

The exact pipeline depends on the Endpoint and the application's configuration.

---

# Route Access

Endpoint Routes can include access metadata through MCF's route access mechanism.

When access requirements are needed, keep the access definition associated with the Route and use the supported MCF Route Data Registry.

This keeps the Endpoint's HTTP behavior and its access requirements discoverable together.

---

# Remove an Endpoint

Remove an existing Endpoint with:

```bash
php artisan mcf:endpoint:remove
```

The command requires:

- Module
- Workflow
- Endpoint

The removal operation treats the Endpoint as a complete structural unit.

When an Endpoint is removed, its generated Controller method and Route are removed, and its Endpoint View is removed when present.

Its Endpoint-specific Request should also be removed with the Endpoint when that Request belongs exclusively to the Endpoint.

The remaining Workflow structure is preserved.

---

# Endpoint Removal and Shared Requests

Do not delete a Request automatically if it is shared or was created independently for other uses.

The removal process should distinguish between:

- A Request owned exclusively by the removed Endpoint.
- A Request that already existed independently.
- A Request referenced by another Endpoint.

Only an Endpoint-owned Request that is safe to remove should be deleted.

---

# Example Session

```text
php artisan mcf:endpoint:create

Module:
> User

Workflow:
> Auth

Endpoint:
> login

Create View?
> Yes

HTTP Method:
> GET

Return Type:
> View

Use Request?
> Yes

Parameters:
> None
```

The generator then creates or connects the selected structures.

Typical result:

```text
✓ Route added
✓ Controller method added
✓ Request created/connected
✓ View created
```

The developer then implements the required business logic in the Workflow Service.

---

# Independent Request Workflow

A Request can also be created before the Endpoint.

Example:

```bash
php artisan mcf:make:request User Auth Login
```

This creates the Request independently.

Later, the Endpoint can be created and connected to that Request.

This is useful when validation and Request Data need to be designed before the HTTP Endpoint is generated.

---

# Best Practices

When creating Endpoints:

- Use `mcf:endpoint:create`.
- Make the Endpoint name describe one action.
- Keep related page/action Endpoints under the same base name.
- Use a dedicated Request when the Endpoint requires validation.
- Keep Request validation inside the Request.
- Use a Request Data class when structured validated input is useful.
- Keep business logic inside the Workflow Service.
- Keep Controllers thin.
- Keep Route access metadata close to the Route.
- Do not manually duplicate generated structures.
- Treat an Endpoint as a complete feature path.
- Do not overwrite an existing developer-authored Request.
- Use `mcf:endpoint:remove` to remove an Endpoint instead of manually deleting pieces.

---

# Summary

The Endpoint Generator is one of the core MCF development tools.

It provides a consistent way to create and remove complete Endpoint structures inside existing Workflows.

The generator can coordinate:

```text
Route
Controller
Request
View
```

while leaving:

```text
Service
Business Logic
```

under the developer's control.

Requests are independent resources in the current MCF architecture, and an Endpoint can create or connect its own Request without relying on the old shared Workflow Request model.

This keeps Endpoint creation predictable while allowing developers to build the business behavior required by each application.
