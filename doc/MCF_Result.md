# MCF Result

## Overview

The **Result** module in MCF is an optional pattern for organizing
operation results inside Workflows instead of using scattered strings or
values.

The idea is that each Workflow can define clear, specific results for
its operation and return the appropriate Result.

The module is **not mandatory**. A Workflow can work without it when
there is no need for it.

However, using Result is recommended in larger projects because it
standardizes result definitions and makes Workflow code clearer and more
organized.

------------------------------------------------------------------------

# Why Use Result?

Instead of spreading result strings throughout the code:

``` php
return 'success';
```

or:

``` php
return 'invalid';
```

a dedicated Result can be defined:

``` php
final class AuthenticationResult extends McfResult
{
    public const SUCCESS = 'success';

    public const INVALID_CREDENTIALS = 'invalid_credentials';
}
```

The Workflow can then use these results explicitly.

This makes results:

- Consistent.
- Reusable.
- Easy to search.
- Easier to read.
- Organized by Workflow.
- Less prone to manual string mistakes.

------------------------------------------------------------------------

# Base McfResult

MCF provides the base class:

``` php
abstract class McfResult
{
    public function __construct(
        protected readonly string $result,
    ) {
    }

    public function result(): string
    {
        return $this->result;
    }

    public function is(string $result): bool
    {
        return $this->result === $result;
    }
}
```

This means every custom Result contains one result value that can be
read or compared.

------------------------------------------------------------------------

# Result Structure

Results can be organized by Workflow:

``` text
Result/
├── Authentication/
│   ├── AuthenticationResult.php
│   ├── ChangePasswordResult.php
│   ├── SendVerificationResult.php
│   ├── UpdateResult.php
│   └── VerificationResult.php
│
└── McfResult.php
```

This organization is **optional**, not a technical requirement.

The project can organize Results however it prefers, but separating them
by Workflow is recommended as the project grows.

------------------------------------------------------------------------

# Authentication Example

MCF includes a practical example connected to the Authentication module,
which can be used as a reference pattern when creating new Results.

Example:

``` php
final class AuthenticationResult extends McfResult
{
    public const SUCCESS = 'success';

    public const INVALID_CREDENTIALS = 'invalid_credentials';

    public const ACCOUNT_INACTIVE = 'account_inactive';
}
```

A Workflow can return:

``` php
return new AuthenticationResult(
    AuthenticationResult::SUCCESS
);
```

The consumer can then check the result:

``` php
if ($result->is(AuthenticationResult::SUCCESS)) {
    // Success
}
```

Or read the value directly:

``` php
$value = $result->result();
```

------------------------------------------------------------------------

# Multiple Results per Workflow

A Workflow does not have to use only one Result class.

Specialized Results can be created for different operations:

``` text
AuthenticationResult
ChangePasswordResult
SendVerificationResult
VerificationResult
UpdateResult
```

This is useful when different operations inside the same module have
different states.

Example:

``` php
final class ChangePasswordResult extends McfResult
{
    public const SUCCESS = 'success';

    public const CURRENT_PASSWORD_INVALID = 'current_password_invalid';

    public const SAME_PASSWORD = 'same_password';

    public const UPDATE_FAILED = 'update_failed';
}
```

------------------------------------------------------------------------

# Using Result Inside a Workflow

The Workflow determines which result actually occurred.

Example:

``` php
public function execute(): ChangePasswordResult
{
    if (! $this->checkCurrentPassword()) {
        return new ChangePasswordResult(
            ChangePasswordResult::CURRENT_PASSWORD_INVALID
        );
    }

    if ($this->isSamePassword()) {
        return new ChangePasswordResult(
            ChangePasswordResult::SAME_PASSWORD
        );
    }

    if (! $this->updatePassword()) {
        return new ChangePasswordResult(
            ChangePasswordResult::UPDATE_FAILED
        );
    }

    return new ChangePasswordResult(
        ChangePasswordResult::SUCCESS
    );
}
```

The calling code remains explicit:

``` php
$result = $workflow->execute();

if ($result->is(ChangePasswordResult::SUCCESS)) {
    // Continue
}
```

------------------------------------------------------------------------

# Result Is Not an Exception

Result is intended to represent an **expected operation outcome**.

Exceptions are intended for unexpected conditions or errors that should
be handled as exceptions.

Therefore:

``` text
Result
    ↓
Known operation outcome
```

while:

``` text
Exception
    ↓
Exceptional error
```

Result should not be used as a general replacement for Exceptions.

------------------------------------------------------------------------

# Result Is Optional

A Workflow can be created without a Result:

``` php
public function execute(): void
{
    // ...
}
```

If the operation does not need a structured set of outcomes, there is no
reason to introduce a Result.

When a Workflow has multiple meaningful states that the caller needs to
distinguish, Result provides a better structure.

------------------------------------------------------------------------

# Recommended Rule

When creating a new Workflow, ask:

> Does this operation have multiple outcomes or states that the caller
> needs to distinguish?

If yes, creating a Result is recommended.

Example:

``` text
Login
├── success
├── invalid_credentials
├── inactive_account
└── too_many_attempts
```

This is a strong use case for Result.

A simple operation that does not require distinguishing multiple
outcomes may not need Result.

------------------------------------------------------------------------

# Relationship with Modules

Result is an independent organizational component in MCF.

Any Module or Workflow can use it when needed.

Example:

``` text
Authentication
    ↓
AuthenticationResult

Mail
    ↓
MailResult

Notification
    ↓
NotificationResult
```

Modules should not be forced to use Result simply because it exists.

------------------------------------------------------------------------

# Architectural Goal

The purpose of Result is to provide a consistent and organized way to
represent operation outcomes when the project actually needs it.

``` text
Workflow
    ↓
Result
    ↓
Known operation state
```

while preserving the project’s freedom to avoid the module when it is
unnecessary.

------------------------------------------------------------------------

# Design Rules

1.  Result is optional.
2.  Every Workflow is not required to use Result.
3.  `McfResult` is the base class for Results.
4.  Specialized Results extend `McfResult`.
5.  Results are preferably organized by Workflow.
6.  Constants should be used for states instead of repeated strings.
7.  The result can be read using `result()`.
8.  The result can be compared using `is()`.
9.  Result represents an expected operation outcome.
10. Exceptions are intended for exceptional errors.
11. The Authentication example included in MCF is the practical
    reference that developers can follow.
