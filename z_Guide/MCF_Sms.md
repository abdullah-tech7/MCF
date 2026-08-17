# MCF SMS

## Overview

The **SMS** module in MCF provides a unified layer for sending SMS
messages from Workflows and modules.

The main idea is to separate project logic from the actual SMS provider.

Instead of a Workflow communicating directly with Twilio or another
provider, it uses:

``` php
McfSms::send(
    $to,
    $message,
);
```

The SMS module then selects the configured Provider and performs the
delivery.

------------------------------------------------------------------------

# Architectural Concept

The current structure is:

``` text
Workflow
    ↓
McfSms
    ↓
SmsProviderContract
    ↓
SMS Provider
```

MCF currently includes Provider examples:

``` text
Sms/
├── Provider/
│   ├── SmsProviderContract.php
│   ├── TwilioSmsService.php
│   └── VonageSmsService.php
│
├── McfSms.php
└── SmsMessages.php
```

Having multiple Providers allows the project to change its SMS provider
without changing Workflow call sites.

------------------------------------------------------------------------

# McfSms

The main service is:

``` php
final class McfSms
{
    private function __construct()
    {
    }

    private static function getProvider(): SmsProviderContract
    {
        return new TwilioSmsService();
    }

    public static function send(
        string $to,
        string $message,
    ): void {
        self::getProvider()->send(
            $to,
            $message,
        );
    }
}
```

A Workflow uses:

``` php
McfSms::send(
    $user->phone,
    $message,
);
```

The Workflow does not need to know how the message is delivered or which
Provider is currently being used.

------------------------------------------------------------------------

# Provider Contract

MCF relies on:

``` php
SmsProviderContract
```

This separates the sending interface from the actual implementation.

Every Provider must implement this contract.

For example:

``` text
SmsProviderContract
        ↑
        ├── TwilioSmsService
        └── VonageSmsService
```

MCF can therefore interact with different providers through the same
interface.

------------------------------------------------------------------------

# Changing the Provider

One of the main benefits of this design is that changing the Provider
does not require changing SMS calls throughout the Workflows.

Currently:

``` php
private static function getProvider(): SmsProviderContract
{
    return new TwilioSmsService();
}
```

If the project decides to use Vonage instead of Twilio, only the
Provider selection inside `McfSms` changes:

``` php
private static function getProvider(): SmsProviderContract
{
    return new VonageSmsService();
}
```

The code using:

``` php
McfSms::send(
    $to,
    $message,
);
```

does not need to change.

This is the core purpose of the Provider abstraction.

------------------------------------------------------------------------

# Provider Examples

MCF includes:

``` text
TwilioSmsService
VonageSmsService
```

These examples demonstrate how to create a Provider.

If the project needs another provider, it can create:

``` text
MySmsService
```

and implement:

``` php
SmsProviderContract
```

Then the Provider used by `McfSms` can be changed.

------------------------------------------------------------------------

# SmsMessages

MCF also provides:

``` text
SmsMessages.php
```

as an optional central place for SMS messages used by the system.

Example:

``` php
final class SmsMessages
{
    private function __construct()
    {
    }

    public static function verifyPhone(string $code): string
    {
        return __('Your phone verification code is: :code', [
            'code' => $code,
        ]);
    }

    public static function resetPassword(string $code): string
    {
        return __('Your password reset code is: :code', [
            'code' => $code,
        ]);
    }
}
```

Usage:

``` php
$message = SmsMessages::verifyPhone($code);

McfSms::send(
    $user->phone,
    $message,
);
```

------------------------------------------------------------------------

# Why SmsMessages?

A central message class is **recommended, not mandatory**.

Its main purpose is to keep SMS message definitions organized in one
place.

Instead of searching Workflows for:

``` php
__('Your phone verification code is: :code')
```

the developer can go directly to:

``` text
Sms/SmsMessages.php
```

and modify the message from one clear location.

This becomes particularly useful as the number of SMS messages grows.

------------------------------------------------------------------------

# Relationship with Language

`SmsMessages` does not contain the translations themselves.

It uses:

``` php
__('Your phone verification code is: :code')
```

Therefore translations remain in:

``` text
Language/
├── ar.json
├── en.json
└── ...
```

The architecture becomes:

``` text
SmsMessages
      ↓
Language
      ↓
Translated message
      ↓
McfSms
      ↓
Provider
      ↓
SMS
```

This keeps message definition, translation, and delivery
responsibilities separate.

------------------------------------------------------------------------

# Why Separate the Message from Delivery?

There are three clear responsibilities:

``` text
SmsMessages
    ↓
What is the message?

Language
    ↓
What is the translation?

McfSms
    ↓
How do we send it?

Provider
    ↓
Who actually performs delivery?
```

This separation keeps each component small and focused.

------------------------------------------------------------------------

# Complete Usage Example

Example of sending a verification code:

``` php
$code = '123456';

$message = SmsMessages::verifyPhone($code);

McfSms::send(
    $user->phone,
    $message,
);
```

The flow is:

``` text
Generate Code
      ↓
SmsMessages::verifyPhone()
      ↓
Language
      ↓
McfSms::send()
      ↓
SmsProviderContract
      ↓
Twilio / Vonage
      ↓
SMS
```

------------------------------------------------------------------------

# Is SmsMessages Mandatory?

No.

A developer can send a message directly:

``` php
McfSms::send(
    $user->phone,
    __('Your phone verification code is: :code', [
        'code' => $code,
    ]),
);
```

However, using `SmsMessages` is recommended when the project has
multiple SMS messages because it keeps message definitions centralized
and easier to modify.

------------------------------------------------------------------------

# Is the Provider Fixed?

No.

The Provider can be changed inside `McfSms`.

The important requirement is that the Provider implements:

``` php
SmsProviderContract
```

Therefore, the public usage remains:

``` php
McfSms::send($to, $message);
```

even when the internal implementation changes.

------------------------------------------------------------------------

# Recommended Organization

The current recommended structure is:

``` text
Sms/
├── Provider/
│   ├── SmsProviderContract.php
│   ├── TwilioSmsService.php
│   └── VonageSmsService.php
│
├── McfSms.php
└── SmsMessages.php
```

This organization is **recommended, not mandatory**.

The project can add Providers and messages as needed.

------------------------------------------------------------------------

# Relationship with Authentication

Authentication can use SMS for operations such as:

``` text
Phone Verification
Password Reset
Phone Change
Welcome
```

Therefore, MCF provides example messages in `SmsMessages` as a practical
reference.

For example:

``` php
SmsMessages::verifyPhone($code);
SmsMessages::resetPassword($code);
SmsMessages::changePhone($code);
SmsMessages::welcome($name);
```

Developers can follow the same pattern when adding new SMS messages.

------------------------------------------------------------------------

# Rules

1.  `McfSms` is the unified entry point for sending SMS.
2.  Workflows do not communicate directly with the Provider.
3.  `SmsProviderContract` separates the interface from the
    implementation.
4.  The Provider can be changed without changing Workflow calls.
5.  MCF provides Twilio and Vonage examples.
6.  A new Provider can be added by implementing `SmsProviderContract`.
7.  `SmsMessages` is an optional central place for SMS messages.
8.  Using `SmsMessages` is recommended for organization and easier
    message maintenance.
9.  Translations remain in the Language module.
10. `McfSms` is responsible for delivery, not translation.
11. The Provider is responsible for actual SMS delivery.
12. The current organization is recommended, not mandatory.

------------------------------------------------------------------------

# Architectural Goal

``` text
Workflow
    ↓
SmsMessages
    ↓
Language
    ↓
McfSms
    ↓
SmsProviderContract
    ↓
Twilio / Vonage / Other Provider
    ↓
SMS
```

With this design, the project can change the SMS provider, modify
message text, or add a new language without spreading these
implementation details throughout the Workflows.
