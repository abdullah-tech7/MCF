# MCF Authentication

## Overview

`MCF Authentication` is the authentication module of the MCF framework.
It provides a structured layer on top of Laravel's native authentication system instead of replacing it with an independent authentication implementation.

The module uses Laravel as its foundation and adds a unified API, centralized configuration, verification, throttling, password operations, account lifecycle operations, and optional session security features.

It includes:

- Login and logout.
- Authentication state checks.
- Current user and user ID access.
- Login using an existing User instance.
- Credential-based login.
- Login Throttle.
- Email and phone verification requirements.
- Password hashing, changing, and reset.
- Verified email and phone updates.
- Account disable, enable, delete, and restore operations.
- Self-account deletion and restoration.
- Restoration-period handling for self-deleted accounts.
- Forced session logout when an account is disabled or deleted.
- Session Security.
- Concurrent Session Control.

## Laravel Integration

MCF uses Laravel components including:

- `Illuminate\Support\Facades\Auth`
- `Illuminate\Contracts\Auth\Authenticatable`
- Laravel Session
- Laravel Database Transactions
- Eloquent Soft Deletes

The configured user model must implement `Illuminate\Contracts\Auth\Authenticatable`.

---

# McfAuth

`McfAuth` is the main authentication API of MCF and acts as a wrapper around Laravel Auth.

## Authentication State

```php
McfAuth::check();
McfAuth::id();
McfAuth::user();
```

Example:

```php
if (McfAuth::check()) {
    $user = McfAuth::user();
}
```

---

# Login

## Login With a User

```php
$result = McfAuth::loginByUser($user);
```

The operation checks Login Throttle, whether the user is allowed to authenticate, verification requirements, then performs login and applies Session Security and Concurrent Session Control.

## Login With Credentials

```php
$result = McfAuth::loginByCredentials([
    'email' => 'user@example.com',
    'password' => 'password',
]);
```

With Remember:

```php
$result = McfAuth::loginByCredentials(
    [
        'email' => 'user@example.com',
        'password' => 'password',
    ],
    true,
);
```

## Deleted Accounts During Login

Authentication must be able to distinguish a soft-deleted account from a user that does not exist.

The credential lookup therefore includes soft-deleted users when required to determine the account state.

A deleted account is not authenticated. Instead, the authentication result exposes the deletion state so the workflow can decide what to do next.

The deletion source is distinguished between:

```text
DELETED_BY_SELF
DELETED_BY_ACTOR
```

A self-deleted account may additionally be evaluated against its restoration expiration state. The authentication layer is responsible for determining whether the self-restoration period has expired so the controller does not need to independently query the account again.

The controller can therefore:

- Show a final unavailable message when self-restoration has expired.
- Direct the user to the restoration workflow when self-restoration is still available.
- Show the appropriate message for an account deleted by another authorized actor.

The password is not used to authenticate a soft-deleted account before this deleted-account state has been handled.

## Authentication Results

Authentication operations return `McfResult` instead of relying only on a boolean.

```php
if ($result->is(AuthenticationResult::SUCCESS)) {
    // Login successful.
}
```

Authentication result states include the normal authentication states and account lifecycle states used by the implementation, including:

```text
SUCCESS
THROTTLED
INVALID_CREDENTIALS
NOT_ALLOWED
NEED_EMAIL_VERIFICATION
NEED_PHONE_VERIFICATION
DELETED_BY_SELF
DELETED_BY_ACTOR
FAILED
```

If the implementation exposes a separate self-restoration-expired result, it should be handled before presenting the restoration workflow.

## Logout

```php
McfAuth::logout();
```

This uses Laravel `Auth::logout()`.

---

# McfAccount

`McfAccount` contains account-lifecycle operations that are separate from the normal login API.

It is responsible for operations such as:

- Disabling an account.
- Enabling an account.
- Deleting an account.
- Restoring an account.
- Deleting the authenticated user's own account.
- Restoring the authenticated user's own account.
- Handling deletion metadata.
- Handling restoration expiration for self-deleted accounts.
- Invalidating active sessions when required.

The separation keeps account lifecycle logic out of controllers and allows the same operations to be reused by different workflows.

## Actor Account Operations

Operations performed by an authorized actor can target another user.

Conceptually:

```text
Actor
  ↓
McfAccount
  ↓
Target User
  ↓
Update account state
  ↓
Force logout active sessions when required
  ↓
Audit
  ↓
Notification
```

These operations are intended for workflows such as user management.

## Disable

Disabling an account prevents the user from authenticating while preserving the account.

When an account is disabled, active sessions can be forcibly invalidated so that an already authenticated user does not remain active after the account has been disabled.

## Enable

Enabling an account makes the account eligible for authentication again, subject to the normal authentication requirements.

Existing sessions do not need to be created automatically; the user can authenticate again normally.

## Delete

Account deletion uses the application's soft-delete lifecycle.

Deletion records the account deletion state and source so the system can distinguish:

```text
Self deletion
Actor deletion
```

A deleted account remains available to account-management logic while it is soft deleted.

## Restore

Actor restoration operates on the target deleted account and restores it from the soft-deleted state.

Self restoration is a separate workflow because the user is no longer authenticated after deleting their own account.

---

# Self Account Deletion

A user can delete their own account through an authenticated workflow.

The operation should:

1. Identify the current authenticated user.
2. Mark the account as deleted.
3. Record that the deletion was performed by the account owner.
4. Set the restoration expiration when self-restoration is configured.
5. Invalidate active sessions.
6. Record the audit event.
7. Send the appropriate account notification when notifications are enabled.

After self deletion, the user is no longer authenticated.

The deletion source is important because it determines whether a restoration workflow may be offered later.

---

# Self Account Restoration

Self restoration is intentionally different from actor restoration.

A deleted user is not authenticated, so restoration cannot depend on the current authenticated session.

The restoration workflow is based on the user's configured login identifier, normally the email address:

```text
Login
  ↓
Deleted by self
  ↓
Check restoration availability
  ↓
Restoration available
  ↓
Request verification code
  ↓
Verify code
  ↓
Restore account
  ↓
Return to Login
```

The verification request identifies the restoration operation through its verification type.

The restoration process must also verify that:

- The account exists as a soft-deleted account.
- The account was deleted by itself.
- The restoration period has not expired.
- The verification request is valid.
- The verification code is valid.

If the restoration period has expired, the restoration workflow must not be offered.

## Restoration Expiration

When self-restoration timeout is enabled, the account stores a deletion expiration timestamp.

The availability rule is conceptually:

```text
No restoration timeout
    → restoration available

Timeout configured + no expiration
    → restoration unavailable

Expiration is in the future
    → restoration available

Expiration is now or in the past
    → restoration expired
```

The expiration timestamp must be handled as a date/time value by the model or its configured cast before calling date methods such as `isFuture()`.

---

# Account State and Soft Deletes

Authentication and account management intentionally use different lookup behavior.

Normal user lookup may exclude deleted users.

Authentication lookup must be capable of finding a deleted account so that the system can return an explicit deletion result instead of treating the account as an unknown user.

For example:

```php
$model::withTrashed()
```

can be used where the deleted state must be inspected.

This distinction is important:

```text
Normal lookup
    → active users

Authentication lookup
    → active + soft-deleted users when needed

Actor restore lookup
    → soft-deleted target user

Self restore lookup
    → soft-deleted account identified by login credentials
```

---

# Verification

Verification is used by authentication workflows and account-sensitive workflows.

Verification requests contain information such as:

```text
type
channel
method
target
code_hash
token_hash
send_attempts
last_sent_at
```

The verification type identifies the operation.

Examples include:

```text
VERIFY_EMAIL
RESET_PASSWORD
UPDATE_EMAIL
RESTORE_ACCOUNT
```

The restoration workflow therefore uses its own verification type instead of reusing password-reset or email-verification state.

## Verification Request Model

```php
public static function verificationRequestModel(): string
{
    return \App\Models\VerificationRequest::class;
}
```

## Code Expiration

```php
public static int $verificationCodeExpirationSeconds = 600;
```

## Verification Throttle

```php
public static int $verificationCooldownSeconds = 60;
public static int $verificationMaxSendAttempts = 5;
public static int $verificationLockoutSeconds = 3600;
```

---

# Verified Data Updates

## Email

```php
$result = McfAuth::updateNewEmailVerified($email);
```

Updates the email and marks it as verified inside a transaction.

## Phone

```php
$result = McfAuth::updateNewPhoneVerified($phone);
```

Updates the phone and marks it as verified inside a transaction.

## Login By Verified User

```php
$result = McfAuth::loginByVerifiedUser($email);
```

Finds the user, marks the email as verified, and then logs in through `loginByUser()`.

---

# Password

## Password Column

```php
public static string $passwordColumn = 'password';
```

## Hash Password

```php
$hash = McfAuth::hashPassword($password);
```

## Change Password

```php
$result = McfAuth::changePassword(
    $currentPassword,
    $newPassword,
);
```

The operation validates the current user and password, prevents using the same password, updates the password, and returns a result.

Main states include:

```text
UPDATED
INVALID_CURRENT_PASSWORD
SAME_PASSWORD
FAILED
```

## Reset Password

```php
$result = McfAuth::resetPassword(
    $email,
    $password,
);
```

The operation uses a Laravel database transaction, updates the password, and consumes the Forgot Password verification.

---

# Login Throttle

User-level settings:

```php
public static int $loginMaxAttempts = 5;
public static int $loginLockoutSeconds = 900;
```

IP-level settings:

```php
public static int $loginIpMaxAttempts = 30;
public static int $loginIpLockoutSeconds = 900;
```

---

# AuthenticationSettings

The main configuration file is:

```text
AuthenticationSettings.php
```

## Login Columns

```php
public static array $loginColumns = [
    'email',
];
```

Defines the columns that can be used to identify the user during login.

## User Model

```php
public static function userModel(): string
{
    return User::class;
}
```

Defines the user model used by MCF Authentication.

## canAuthenticate

```php
public static function canAuthenticate(
    object $user,
): bool {
    return $user->is_active === true;
}
```

Determines whether the user is allowed to authenticate.

Account deletion is handled before normal authentication eligibility so a deleted account can return its explicit deletion result.

---

# Session Security

Session Security is an optional feature.

```php
public static bool $securityEnabled = false;
```

When `false`, Session Security is disabled.

## Timeout

```php
public static int $securityTimeoutSeconds = 60;
```

## Reset Timeout On Activity

```php
public static bool $timeoutResetOnActivity = true;
```

`true`:

> The timeout starts normally, and every user activity resets the timer.

`false`:

> The timeout starts from session creation and is not reset by activity.

Example with `true`:

```text
Login
  ↓
Timeout
  ↓
Activity → Reset
  ↓
Activity → Reset
  ↓
No activity
  ↓
Timeout
  ↓
Logout
```

## Scope

```php
public static string $securityScope = 'all';
```

Supported values:

```text
all
roles
```

`all` applies to all authenticated users.

`roles` applies only to selected roles:

```php
public static array $securityRoles = [
    2,
    4,
];
```

Roles may be integers or strings, and comparison is strict.

---

# Concurrent Session Control

Configuration:

```php
public static bool $multipleSessionsPerUser = true;
```

`true`:

> Multiple active sessions are allowed.

`false`:

> Only one active session is allowed.

When set to `false`, the feature relies on Laravel's Database Session Driver and Laravel's `sessions` table.

MCF does not create an independent session system.

Account lifecycle operations such as disable and delete can invalidate active sessions when required.

---

# Session Integration

During login:

```text
McfAuth::login()
    ↓
Laravel Auth::login()
    ↓
Session Security initialization
    ↓
Concurrent Session Control
```

Session Security is executed through `McfSessionSecurityMiddleware`, while its logic is implemented in `SessionSecurityHandler`.

---

# Login Route

```php
public static function resolveLoginRouteName(): string
{
    return 'user.auth.login';
}
```

Session Security uses this route when the session expires and the user must be redirected to login.

---

# Account Lifecycle, Audit, and Notification

Account operations are separated into their respective responsibilities.

```text
McfAccount
    ↓
Account state change

McfAuth / Authentication workflow
    ↓
Authentication decision

Audit
    ↓
Record what happened and who performed it

Notification
    ↓
Inform the affected user when enabled
```

The notification message distinguishes operations performed by the account owner from operations performed by administration or another authorized actor.

Examples:

```text
accountDeletedBySelf()
accountDeletedByActor()

accountRestoredBySelf()
accountRestoredByActor()

accountDisabled()
accountEnabled()
```

This prevents a notification such as "If you did not request this action" from being sent when the user explicitly requested the operation themselves.

---

# Examples

## Login

```php
$result = McfAuth::loginByCredentials([
    'email' => $email,
    'password' => $password,
]);

if ($result->is(AuthenticationResult::SUCCESS)) {
    return redirect('/dashboard');
}
```

The controller should handle authentication results rather than reproducing account-state queries that already belong to the authentication layer.

## Authentication Configuration

```php
public static array $loginColumns = [
    'email',
];

public static string $passwordColumn = 'password';

public static VerificationRequirement $verificationRequirement =
    VerificationRequirement::NONE;

public static int $loginMaxAttempts = 5;
public static int $loginLockoutSeconds = 900;

public static int $loginIpMaxAttempts = 30;
public static int $loginIpLockoutSeconds = 900;

public static int $verificationCodeExpirationSeconds = 600;
public static int $verificationCooldownSeconds = 60;
public static int $verificationMaxSendAttempts = 5;
public static int $verificationLockoutSeconds = 3600;
```

## Session Configuration

```php
public static bool $multipleSessionsPerUser = true;

public static bool $securityEnabled = false;

public static int $securityTimeoutSeconds = 60;

public static bool $timeoutResetOnActivity = true;

public static string $securityScope = 'all';

public static array $securityRoles = [];
```

---

# Summary

MCF Authentication is a structured authentication layer on top of Laravel, not a replacement for Laravel Authentication.

The authentication API remains centered around:

```text
McfAuth
├── check()
├── id()
├── user()
├── loginByUser()
├── loginByCredentials()
├── logout()
├── hashPassword()
├── changePassword()
├── resetPassword()
├── updateNewEmailVerified()
├── updateNewPhoneVerified()
└── loginByVerifiedUser()
```

Account lifecycle operations are handled separately through `McfAccount`, including self and actor account operations.

The authentication layer also understands soft-deleted accounts when required, distinguishes self deletion from actor deletion, and determines whether self restoration is still available.

Session features:

```text
Session Security
Concurrent Session Control
```

Laravel remains responsible for the underlying Authentication and Session mechanisms.
