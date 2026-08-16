# MCF Workflow Architecture

## Purpose

This document defines the standard way to build a Workflow in MCF, the
responsibility of each layer, and how data moves from the HTTP Request
through the Service and Model, then back through the Result and
Controller.

The goal is to keep responsibilities explicit and stable, avoid
duplicating data definitions, and prevent one layer from taking over
responsibilities that belong to another layer.

------------------------------------------------------------------------

# 1. Workflow Components

A standard Workflow consists of:

``` text
Route
  ↓
Controller
  ↓
Request
  ↓
Data
  ↓
Service
  ↓
Model / Domain / Framework
  ↓
Result
  ↓
Controller
  ↓
Redirect / View
```

Each layer has a distinct responsibility:

  -----------------------------------------------------------------------
  Layer                               Responsibility
  ----------------------------------- -----------------------------------
  Route                               Routes the request to a Controller

  Controller                          Orchestrates the Workflow and
                                      handles Results and HTTP Responses

  Request                             Authorization, Validation, and Data
                                      creation

  Data                                Represents the data required by the
                                      Service

  Service                             Executes Business Logic

  Model                               Represents database data and
                                      relationships

  Result                              Describes the outcome of an
                                      operation without being tied to the
                                      UI

  View                                Presents data and messages
  -----------------------------------------------------------------------

------------------------------------------------------------------------

# 2. Core Principle

A layer should not take over the responsibility of another layer.

### Request Does Not Execute Business Logic

Request is responsible for:

-   Authorization.
-   Validation.
-   Defining the Data class.
-   Converting validated input into Data.

Request should not contain:

-   User creation.
-   Login execution.
-   Email sending.
-   Verification request creation.
-   Database business operations.

------------------------------------------------------------------------

### Controller Does Not Execute Business Logic

Controller is responsible for:

-   Receiving the Request.
-   Getting the Data.
-   Calling the Service.
-   Interpreting the Result.
-   Deciding whether to redirect or return a View.
-   Sending `success` and `error` messages through Session.

Controller should not contain:

-   `User::create()`.
-   `save()`.
-   Password hashing.
-   Authentication logic.
-   Business rules.

------------------------------------------------------------------------

### Service Executes Business Logic

Service is responsible for:

-   Executing the operation.
-   Creating and modifying Models.
-   Calling Authentication.
-   Calling Verification.
-   Applying business rules.
-   Returning a Result.

Service should not know about:

-   Blade.
-   `session()`.
-   `with('success')`.
-   `with('error')`.
-   Redirects.
-   HTTP input.

------------------------------------------------------------------------

# 3. MfcRequest

The Base Request is:

``` php
abstract class MfcRequest extends FormRequest
{
    protected function dataClass(): ?string
    {
        return null;
    }

    public function getData(): object|array
    {
        $validated = $this->validated();

        $dataClass = $this->dataClass();

        if ($dataClass === null) {
            return $validated;
        }

        if (! class_exists($dataClass)) {
            throw new LogicException(
                sprintf(
                    '%s::dataClass() returned an invalid class: %s.',
                    static::class,
                    $dataClass,
                ),
            );
        }

        try {
            return new $dataClass(
                ...$validated,
            );
        } catch (\Throwable $exception) {
            throw new LogicException(
                sprintf(
                    'Unable to create Data object %s from %s.',
                    $dataClass,
                    static::class,
                ),
                previous: $exception,
            );
        }
    }
}
```

## dataClass()

This method defines whether the Request uses a Data class.

Default:

``` php
protected function dataClass(): ?string
{
    return null;
}
```

If the Request does not need a Data object:

``` php
protected function dataClass(): ?string
{
    return null;
}
```

Then:

``` php
$request->getData();
```

returns:

``` php
array
```

------------------------------------------------------------------------

If the Request has a Data class:

``` php
protected function dataClass(): ?string
{
    return LoginData::class;
}
```

Then:

``` php
$request->getData();
```

returns:

``` php
LoginData
```

------------------------------------------------------------------------

# 4. When to Use a Data Class

Use a Data class when an operation needs a clear data contract between
the Request and Service.

Example:

``` php
final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {
    }
}
```

Then:

``` php
final class LoginRequest extends MfcRequest
{
    protected function dataClass(): ?string
    {
        return LoginData::class;
    }
}
```

Then in the Controller:

``` php
$data = $request->getData();

$result = $this->service->login($data);
```

And the Service receives:

``` php
public function login(
    LoginData $data,
): McfResult
```

This creates a clear contract.

------------------------------------------------------------------------

# 5. Data Classes Should Not Depend on Request Rules

A Data class is not a copy of `rules()`.

Rules define what is allowed from HTTP input and what is required.

Data defines the shape of the data required by the Service and the
default values of optional data.

Example:

``` php
final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {
    }
}
```

Here:

-   `email` is required.
-   `password` is required.
-   `remember` is optional and defaults to `false`.

The Data class does not need to know anything about Laravel validation
rules.

------------------------------------------------------------------------

# 6. Optional Data

If a piece of data is optional, it can have a clear default inside the
Data class.

Example:

``` php
final readonly class RegisterData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $phone = null,
    ) {
    }
}
```

Here:

``` text
name     → required
email    → required
password → required
phone    → optional
```

The Data class does not need to know anything about Laravel Rules.

------------------------------------------------------------------------

# 7. Constructor Parameter Order

In PHP, required parameters should come before optional parameters.

Correct:

``` php
final readonly class RegisterData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $phone = null,
    ) {
    }
}
```

Do not place an optional parameter before a required parameter.

------------------------------------------------------------------------

# 8. Request Rules

The Request is responsible for Validation.

Example:

``` php
public function rules(): array
{
    return [
        'email' => [
            'required',
            'email',
        ],

        'password' => [
            'required',
            'string',
        ],

        'remember' => [
            'sometimes',
            'boolean',
        ],
    ];
}
```

## Note

If a field is optional and has a default in Data:

``` php
public bool $remember = false,
```

it is appropriate to use:

``` php
'sometimes',
'boolean',
```

when `null` is not a meaningful value for the field.

------------------------------------------------------------------------

# 9. Rules Define Request Data

We do not need:

``` php
protected array $dataColumns = [];
```

in the Request.

The reason is that:

``` php
rules()
```

already defines the fields that enter the validated data.

For example:

``` php
public function rules(): array
{
    return [
        'email' => [...],
        'password' => [...],
        'remember' => [...],
    ];
}
```

After:

``` php
$this->validated();
```

the validated fields are returned.

Therefore, we do not duplicate field names in a `dataColumns` array.

------------------------------------------------------------------------

# 10. Validation Field Errors

Validation errors belong to the Request and View.

Example:

``` php
public function messages(): array
{
    return [
        'email.required' => __('Email address is required.'),
        'email.email' => __('Please enter a valid email address.'),
    ];
}
```

In Blade:

``` blade
@error('email')

    <div class="mcf-field-error">
        {{ $message }}
    </div>

@enderror
```

Each Input is responsible for displaying its own validation error.

The Controller should not return Validation errors to an Input.

------------------------------------------------------------------------

# 11. Do Not Use withErrors() for Workflow Messages

Do not use:

``` php
return back()->withErrors([
    'email' => __('Your account is inactive.'),
]);
```

when the problem is not actually related to the email field.

The Controller should not pretend that a general Workflow problem is an
Input validation error.

Instead:

``` php
return back()->with(
    'error',
    __('Your account is inactive or you are not allowed to sign in.'),
);
```

The Layout displays the message.

------------------------------------------------------------------------

# 12. Message Types

There are three clear levels.

## Validation Field Error

Comes from the Request:

``` text
Request
  ↓
Validation
  ↓
$errors
  ↓
@error('field')
```

Example:

``` blade
@error('password')
    <div class="mcf-field-error">
        {{ $message }}
    </div>
@enderror
```

------------------------------------------------------------------------

## General Workflow Error

Use:

``` php
return back()->with(
    'error',
    __('Login failed. Please try again.'),
);
```

The Layout displays it:

``` blade
@if (session('error'))

    <div class="mcf-error">
        {{ session('error') }}
    </div>

@endif
```

------------------------------------------------------------------------

## General Success

Use:

``` php
return back()->with(
    'success',
    __('Operation completed successfully.'),
);
```

The Layout displays it:

``` blade
@if (session('success'))

    <div class="mcf-success">
        {{ session('success') }}
    </div>

@endif
```

------------------------------------------------------------------------

# 13. Layout Messages

General `success` and `error` messages should live in the Layout so they
do not have to be repeated in every View.

Example:

``` blade
@if (session('success'))

    <div class="mcf-success">
        {{ session('success') }}
    </div>

@endif

@if (session('error'))

    <div class="mcf-error">
        {{ session('error') }}
    </div>

@endif
```

Field Validation errors remain inside the View next to their fields.

------------------------------------------------------------------------

# 14. CSS in the Stub

The Layout may contain simple default CSS:

``` blade
<style>

    .mcf-success,
    .mcf-error {
        padding: 12px 16px;
        margin-bottom: 20px;
        border: 1px solid;
        border-radius: 6px;
    }

    .mcf-success {
        border-color: #a3cfbb;
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .mcf-error {
        border-color: #f1aeb5;
        background-color: #f8d7da;
        color: #842029;
    }

</style>
```

This is only default styling.

The developer may:

-   Modify it.
-   Move it to a CSS file.
-   Replace it completely.
-   Remove it.

------------------------------------------------------------------------

# 15. MfcService

The Base Service contains shared helpers for Services.

One of the important helpers is:

``` php
dataToModel()
```

Its purpose is:

``` text
Data
  ↓
Data fields
  ↓
Model columns
  ↓
Model
```

The conversion should not be reimplemented in every Service.

------------------------------------------------------------------------

# 16. dataToModel()

Usage:

``` php
$user = $this->dataToModel(
    $data,
    new User(),
);
```

If conversion succeeds, the method returns the Model.

If conversion fails, it should throw an Exception.

We do not want:

``` php
$model = $this->dataToModel(...);

if ($model === null) {
    ...
}
```

We also do not want silent failures.

The rule is:

> Failure to convert Data into a Model is a programming/architecture
> error and must be visible to the developer.

------------------------------------------------------------------------

# 17. Data and Model Relationship

Data does not have to contain every Model column.

Example `User`:

``` text
users table
-------------------------
id
name
email
phone
password
email_verified_at
phone_verified_at
is_active
last_login_at
remember_token
created_at
updated_at
```

While `RegisterData` may contain only:

``` text
name
email
phone
password
```

This is normal.

A Model can have more columns than a Data class.

However, a Data field that does not exist in the Model should be treated
as a design/programming error when `dataToModel()` is used.

------------------------------------------------------------------------

# 18. Model Columns That Are Not in Data

The presence of a column in a Model does not mean it must appear in
every Data class.

For example:

``` text
User
├── name
├── email
├── phone
├── password
├── is_active
├── last_login_at
├── email_verified_at
└── ...
```

Can have:

``` text
RegisterData
├── name
├── email
├── phone
└── password
```

And:

``` text
LoginData
├── email
├── password
└── remember
```

However, `remember` does not necessarily represent a User database
column, so `LoginData` should not be passed through `dataToModel()`.

------------------------------------------------------------------------

# 19. When to Use dataToModel()

Use it when the Data represents information intended to create or update
a Model.

Example:

``` php
$user = $this->dataToModel(
    $data,
    new User(),
);
```

Do not use it for operation-only Data that does not represent a Model
directly.

Examples:

``` text
LoginData
SendVerificationData
ResetPasswordData
ChangePasswordData
```

These may contain information required for an operation without
representing a Model.

------------------------------------------------------------------------

# 20. Register Example

## Request

``` php
final class RegisterRequest extends MfcRequest
{
    protected function dataClass(): ?string
    {
        return RegisterData::class;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'digits:10',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }
}
```

## Data

``` php
final readonly class RegisterData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $phone = null,
    ) {
    }
}
```

## Controller

``` php
$data = $request->getData();

$result = $this->service->register(
    $data,
);
```

## Service

``` php
public function register(
    RegisterData $data,
): McfResult {
    $user = $this->dataToModel(
        $data,
        new User(),
    );

    $user->password = McfAuth::hashPassword(
        $data->password,
    );

    $user->save();

    return McfAuth::loginByUser(
        $user,
    );
}
```

------------------------------------------------------------------------

# 21. Password Hashing

Password hashing belongs to the Service/Authentication layer, not
Request or Data.

Data contains:

``` php
public string $password
```

but does not contain the hash.

The Service transforms it:

``` php
$user->password = McfAuth::hashPassword(
    $data->password,
);
```

Data represents operation input, not necessarily the storage
representation.

------------------------------------------------------------------------

# 22. Login Workflow

Login is different from Register.

## Request

``` php
final class LoginRequest extends MfcRequest
{
    protected function dataClass(): ?string
    {
        return LoginData::class;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],

            'remember' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
```

## Data

``` php
final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {
    }
}
```

## Service

``` php
public function login(
    LoginData $data,
): McfResult {
    return McfAuth::loginByCredentials(
        [
            'email' => $data->email,
            'password' => $data->password,
        ],
        $data->remember,
    );
}
```

Do not use:

``` php
$this->dataToModel(
    $data,
    new User(),
);
```

because LoginData is not Model Data.

------------------------------------------------------------------------

# 23. Result

The Service does not return a UI message.

The Service returns a Result.

Example:

``` php
return new AuthenticationResult(
    AuthenticationResult::INVALID_CREDENTIALS,
);
```

Or:

``` php
return new AuthenticationResult(
    AuthenticationResult::NOT_ALLOWED,
);
```

Or:

``` php
return new AuthenticationResult(
    AuthenticationResult::SUCCESS,
);
```

The Result describes the outcome, not the text displayed to the user.

------------------------------------------------------------------------

# 24. Controller + Result

The Controller interprets the Result and decides the HTTP response.

Example:

``` php
if (
    $result->is(
        AuthenticationResult::SUCCESS,
    )
) {
    return redirect('/');
}
```

Workflow error:

``` php
if (
    $result->is(
        AuthenticationResult::NOT_ALLOWED,
    )
) {
    return back()->with(
        'error',
        __(
            'Your account is inactive or you are not allowed to sign in.',
        ),
    );
}
```

------------------------------------------------------------------------

# 25. The Same Result Can Have Different Messages by Workflow

This is important.

For example:

``` php
AuthenticationResult::NOT_ALLOWED
```

In Login:

``` text
Your account is inactive or you are not allowed to sign in.
```

In Register, if the account was successfully created but automatic login
is not allowed:

``` text
Your account has been created successfully, but it requires administrator activation before you can sign in.
```

The Result does not change.

The Controller/Workflow chooses the appropriate user-facing message.

------------------------------------------------------------------------

# 26. Register Is Not Failed Because Auto Login Failed

Example:

``` text
Validation
    ✓

Create User
    ✓

Save User
    ✓

Auto Login
    NOT_ALLOWED
```

This does not mean:

``` text
Registration FAILED
```

because the account was created successfully.

The Register Controller may therefore return:

``` php
return redirect()
    ->route('user.auth.login')
    ->with(
        'success',
        __(
            'Your account has been created successfully, but it requires administrator activation before you can sign in.',
        ),
    );
```

------------------------------------------------------------------------

# 27. Unique Constraints

Unique constraints such as:

``` text
users.email UNIQUE
users.phone UNIQUE
```

are part of database data integrity.

When uniqueness is part of the current Workflow's input requirements, a
corresponding Validation Rule is recommended.

Example:

``` php
'email' => [
    'required',
    'email',
    'max:255',
    'unique:users,email',
],
```

However, distinguish between:

-   Workflow Validation.
-   Database constraints as the final protection.

The database remains the final authority for uniqueness, including race
conditions.

------------------------------------------------------------------------

# 28. Do Not Put All Business Rules in the Model

The Model knows about:

-   Columns.
-   Casts.
-   Relationships.
-   Eloquent behavior.

The Request knows about:

-   What this HTTP Workflow accepts.
-   What is required.
-   Validation rules.

The Service knows about:

-   Why and when data is created.
-   Business logic.
-   Operation order.

Do not attempt to put everything into the Model.

------------------------------------------------------------------------

# 29. User Model

User should extend:

``` php
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
}
```

because Authentication requires an `Authenticatable` user.

Example:

``` php
class User extends Authenticatable
{
    protected $table = 'users';

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'is_active' => 'bool',
        'last_login_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];
}
```

System-managed fields such as:

``` text
is_active
last_login_at
email_verified_at
phone_verified_at
remember_token
```

should not generally be exposed as mass-assignable Register Data.

------------------------------------------------------------------------

# 30. VerificationRequest Model

`VerificationRequest` represents a verification request record in the
database.

Example:

``` php
class VerificationRequest extends Model
{
    protected $table = 'verification_requests';

    protected $casts = [
        'user_id' => 'int',
        'send_attempts' => 'int',
        'last_sent_at' => 'datetime',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'type',
        'channel',
        'method',
        'target',
        'code_hash',
        'token_hash',
        'send_attempts',
        'last_sent_at',
        'expires_at',
        'verified_at',
        'revoked_at',
    ];

    public function user()
    {
        return $this->belongsTo(
            User::class,
        );
    }
}
```

------------------------------------------------------------------------

# 31. Controller Organization

Controllers should be organized by Workflow:

``` php
/*
|--------------------------------------------------------------------------
| Register
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/
```

Each method should follow:

``` text
Request
  ↓
getData()
  ↓
Service
  ↓
Result
  ↓
Response
```

It should not execute Business Logic itself.

------------------------------------------------------------------------

# 32. Service Organization

Services should be organized around their operations:

``` php
/*
|--------------------------------------------------------------------------
| Register
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/
```

Each method receives its appropriate Data:

``` php
public function register(
    RegisterData $data,
): McfResult
```

``` php
public function login(
    LoginData $data,
): McfResult
```

Operations that do not require Data should not receive an artificial
Data class without a reason.

------------------------------------------------------------------------

# 33. Request Without Data

The Data class is optional.

If an operation does not need a Data object:

``` php
final class SomeRequest extends MfcRequest
{
    protected function dataClass(): ?string
    {
        return null;
    }
}
```

Then:

``` php
$data = $request->getData();
```

returns:

``` php
array
```

This is allowed.

However, when the Service represents a clear contract, a Data class is
recommended.

------------------------------------------------------------------------

# 34. Request With Data

If a Request uses a Data class:

``` php
protected function dataClass(): ?string
{
    return SomeData::class;
}
```

the validated field names should match the Data constructor parameter
names.

Example:

``` text
Request Rules:

email
password
remember
```

Corresponding Data:

``` php
__construct(
    string $email,
    string $password,
    bool $remember = false,
)
```

There is no need for manual mapping:

``` php
return new LoginData(
    email: $data['email'],
    password: $data['password'],
    remember: $data['remember'] ?? false,
);
```

because `MfcRequest::getData()` performs the construction automatically.

------------------------------------------------------------------------

# 35. If Input and Data Names Differ

Do not rely on hidden mappings.

For example:

``` text
Request:
user_email

Data:
email
```

This does not match the contract.

Rename the input:

``` text
email
```

or perform an explicit, intentional transformation in the appropriate
place.

The Base Request should not start guessing field mappings.

------------------------------------------------------------------------

# 36. Password Confirmation

`password_confirmation` is an important example.

The Request may contain:

``` php
'password' => [
    'required',
    'confirmed',
],
```

and HTTP input:

``` text
password
password_confirmation
```

But `password_confirmation` should not enter `RegisterData`.

It exists to validate:

``` text
password === password_confirmation
```

and is not part of the Data contract.

Therefore:

> Confirmation fields are Validation concerns, not Domain/Data concerns.

The Request/validation implementation must ensure that confirmation-only
fields do not break Data construction.

------------------------------------------------------------------------

# 37. No Columns Array

Do not use:

``` php
protected array $dataColumns = [
    'name',
    'email',
    'phone',
];
```

because it duplicates information already defined by:

``` php
rules()
```

and the Data constructor.

The architecture already has:

``` text
Request Rules
    ↓
Validated fields

Data Constructor
    ↓
Expected Data contract

Model
    ↓
Database columns
```

Each layer knows its own responsibility.

------------------------------------------------------------------------

# 38. Data-to-Model Contract

When using:

``` php
$this->dataToModel(
    $data,
    new User(),
);
```

the purpose is to guarantee that the Data can be converted into the
target Model correctly.

Principle:

``` text
Data field
    ↓
Must be a valid Model column
    ↓
Can be converted
```

If Data contains a field that does not exist in the Model:

``` text
LogicException
```

Do not silently ignore it.

This catches mistakes such as:

``` text
Data:
email
password
username

Model:
email
password
```

`username` should expose a developer error.

------------------------------------------------------------------------

# 39. Additional Model Columns

The reverse is not an error.

A Model may contain:

``` text
id
created_at
updated_at
is_active
last_login_at
email_verified_at
...
```

while Data contains only:

``` text
name
email
password
phone
```

This is normal.

Data does not need to represent every Model column.

------------------------------------------------------------------------

# 40. System-Managed Fields

Fields managed by the system should normally not come from HTTP Request
input.

Examples:

``` text
created_at
updated_at
last_login_at
email_verified_at
phone_verified_at
is_active
remember_token
```

They are controlled by:

-   Service.
-   Authentication system.
-   Eloquent.
-   Verification Workflow.

Example:

``` php
$user->is_active = true;
```

or:

``` php
$user->last_login_at = now();
```

depending on the Business Workflow.

------------------------------------------------------------------------

# 41. Data Rule

Do not make Data merely a copy of the database table.

Data represents the operation, not the table.

Example:

``` text
RegisterData
```

represents the information required to register.

While:

``` text
User
```

represents the User database/domain Model.

Therefore, they are expected to differ.

------------------------------------------------------------------------

# 42. Service Rule

Do not manually rewrite Data fields into a Model when the Data already
matches the Model.

Avoid:

``` php
$user->fill([
    'name' => $data->name,
    'email' => $data->email,
    'phone' => $data->phone,
]);
```

If the Data matches the Model for the operation, prefer:

``` php
$user = $this->dataToModel(
    $data,
    new User(),
);
```

Then perform only Workflow-specific transformations.

Example:

``` php
$user->password = McfAuth::hashPassword(
    $data->password,
);
```

------------------------------------------------------------------------

# 43. When Manual Model Assignment Is Appropriate

Manual assignment is appropriate when the Model value is not the same as
the Data value.

Example:

``` text
Data:
password = plain text

Model:
password = hashed password
```

Therefore:

``` php
$user->password = McfAuth::hashPassword(
    $data->password,
);
```

This is not redundant field mapping; it is a Data transformation
required by the Workflow.

------------------------------------------------------------------------

# 44. Controller Does Not Convert Data to Model

Do not do this in Controller:

``` php
$user = new User();
$user->fill(...);
```

Model conversion belongs to the Service.

Controller should work with:

``` text
Request
Data
Result
Response
```

only.

------------------------------------------------------------------------

# 45. Service Does Not Receive Request

Do not do:

``` php
public function login(
    LoginRequest $request,
)
```

Prefer:

``` php
public function login(
    LoginData $data,
)
```

This keeps the Service independent from HTTP.

------------------------------------------------------------------------

# 46. View Does Not Know the Service

Blade should not call Service or Authentication directly.

The View displays:

-   Forms.
-   Inputs.
-   Validation errors.
-   Session messages.
-   Links.

------------------------------------------------------------------------

# 47. Complete Register Flow

``` text
POST /registerpost
        ↓
RegisterRequest
        ↓
authorize()
        ↓
rules()
        ↓
Laravel Validation
        ↓
getData()
        ↓
RegisterData
        ↓
AuthController
        ↓
AuthService::register()
        ↓
dataToModel()
        ↓
User
        ↓
Hash password
        ↓
save()
        ↓
McfAuth::loginByUser()
        ↓
AuthenticationResult
        ↓
AuthController
        ↓
redirect / session message
        ↓
View
```

------------------------------------------------------------------------

# 48. Complete Login Flow

``` text
POST /loginpost
        ↓
LoginRequest
        ↓
rules()
        ↓
Validation
        ↓
getData()
        ↓
LoginData
        ↓
AuthController
        ↓
AuthService::login()
        ↓
McfAuth::loginByCredentials()
        ↓
AuthenticationResult
        ↓
AuthController
        ↓
redirect / session error / success
        ↓
View
```

------------------------------------------------------------------------

# 49. Error Handling Philosophy

Distinguish between:

## User Input Error

Examples:

``` text
Email is invalid.
Password is required.
Phone must contain 10 digits.
```

Responsible layer:

``` text
Request Validation
```

Displayed next to the Input.

------------------------------------------------------------------------

## Workflow Error

Examples:

``` text
Your account is inactive.
Too many login attempts.
Unable to send verification code.
```

Responsible layer:

``` text
Controller + Session
```

Displayed by the Layout.

------------------------------------------------------------------------

## Programming Error

Examples:

``` text
Data field does not exist in Model.
Data class cannot be constructed.
Invalid Data class.
Invalid Model conversion.
```

Responsible mechanism:

``` text
Exception
```

It should not be hidden.

------------------------------------------------------------------------

# 50. Do Not Hide Programming Errors With a Generic Catch

Avoid:

``` php
try {
    ...
} catch (\Throwable) {
    return new AuthenticationResult(
        AuthenticationResult::FAILED,
    );
}
```

This can hide:

-   Database bugs.
-   Model bugs.
-   Namespace errors.
-   Type errors.
-   Framework errors.
-   Programming mistakes.

If an Exception must be converted into a Result, catch the specific
expected Exception whenever possible.

Framework and architecture errors should remain visible to the
developer.

------------------------------------------------------------------------

# 51. Exception Rule

If the error means:

> The developer implemented something incorrectly.

It should be an Exception.

If it means:

> The user performed a valid operation, but the business outcome has a
> particular state.

It should be a Result.

Examples:

``` text
User inactive
→ Result::NOT_ALLOWED

Data cannot be converted to Model
→ LogicException

Invalid Data class
→ LogicException

Invalid user Model for authentication
→ Programming/contract error
```

------------------------------------------------------------------------

# 52. Service Result Contract

Each Service method should have a clear contract.

Example:

``` php
public function register(
    RegisterData $data,
): McfResult
```

And:

``` php
public function login(
    LoginData $data,
): McfResult
```

This makes it clear that the Controller receives a Result rather than
arbitrary values.

------------------------------------------------------------------------

# 53. Naming

Recommended names:

``` text
MfcRequest
MfcService
MfcController

getData()
dataClass()
dataToModel()

RegisterRequest
RegisterData

LoginRequest
LoginData

AuthService
AuthController
```

`dataClass()` is the method overridden by a Request to define its Data
class.

`getData()` is the public method called by the Controller.

------------------------------------------------------------------------

# 54. Developer Workflow Checklist

When creating a new Workflow:

## 1. Create the Request

``` php
class CreatePostRequest extends MfcRequest
```

Define:

``` text
authorize()
rules()
messages()
dataClass()
```

------------------------------------------------------------------------

## 2. Create the Data

``` php
final readonly class CreatePostData
```

Put in it the data required by the Service.

Required fields should not have defaults.

Optional fields should have meaningful defaults.

------------------------------------------------------------------------

## 3. Create the Service Method

``` php
public function create(
    CreatePostData $data,
): McfResult
```

Put Business Logic here.

------------------------------------------------------------------------

## 4. Use dataToModel When Appropriate

``` php
$post = $this->dataToModel(
    $data,
    new Post(),
);
```

Then perform any Workflow-specific transformations.

------------------------------------------------------------------------

## 5. Controller

``` php
$data = $request->getData();

$result = $this->service->create(
    $data,
);
```

Then handle the Result:

``` php
if ($result->is(...)) {
    return redirect(...);
}
```

------------------------------------------------------------------------

## 6. Messages

Field validation:

``` text
Request messages()
```

General error:

``` php
->with('error', ...)
```

Success:

``` php
->with('success', ...)
```

------------------------------------------------------------------------

# 55. Final Architecture Rule

The entire architecture can be summarized as:

``` text
REQUEST
"What input from the user is valid?"
        ↓
DATA
"What data does this Workflow require?"
        ↓
SERVICE
"What should actually happen?"
        ↓
MODEL
"How is the data represented and persisted?"
        ↓
RESULT
"What was the outcome of the operation?"
        ↓
CONTROLLER
"How should this Result become an HTTP response?"
        ↓
VIEW / SESSION
"How should the result be presented to the user?"
```

Therefore:

``` text
Request
    = Input + Validation

Data
    = Data Contract

Service
    = Business Logic

Model
    = Persistence / Domain Representation

Result
    = Operation Outcome

Controller
    = HTTP Workflow Orchestration

View
    = Presentation
```

This is the recommended reference architecture for MCF Workflows.
