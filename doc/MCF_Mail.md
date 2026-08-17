# MCF Mail

## Overview

The **Mail** module in MCF is a lightweight service that wraps Laravel’s
Mail system and provides a consistent, convenient entry point for
sending email.

The goal is not to replace Laravel Mail, but to provide a simple MCF
layer that makes email usage easier inside Workflows and modules.

------------------------------------------------------------------------

# Core Concept

Instead of calling Laravel Mail directly everywhere:

``` php
Mail::to($to)->send($mail);
```

MCF provides:

``` php
McfMail::send($to, $mail);
```

This gives the framework a consistent entry point for email delivery.

Internally, the module uses Laravel Mail:

``` php
Mail::to($to)->send($mail);
```

It does not recreate Laravel’s mail system.

------------------------------------------------------------------------

# MCF Mail Service

The main service is:

``` php
final class McfMail
{
    private function __construct()
    {
    }

    public static function send(
        string $to,
        Mailable $mail,
    ): void {
        Mail::to($to)->send($mail);
    }

    public static function queue(
        string $to,
        Mailable $mail,
    ): void {
        Mail::to($to)->queue($mail);
    }

    public static function later(
        int $delay,
        string $to,
        Mailable $mail,
    ): void {
        Mail::to($to)
            ->later($delay, $mail);
    }
}
```

It provides three basic operations:

``` text
send
queue
later
```

------------------------------------------------------------------------

# send

To send an email immediately:

``` php
McfMail::send(
    $user->email,
    new VerifyEmailLinkMail($user),
);
```

Use this when the email should be sent during the current operation.

Internally:

``` php
Mail::to($to)->send($mail);
```

------------------------------------------------------------------------

# queue

To queue an email:

``` php
McfMail::queue(
    $user->email,
    new VerifyEmailCodeMail($user),
);
```

This is useful when the current Request should not wait for the complete
email delivery operation.

Internally:

``` php
Mail::to($to)->queue($mail);
```

------------------------------------------------------------------------

# later

To send an email after a delay:

``` php
McfMail::later(
    60,
    $user->email,
    new ResetPasswordCodeMail($user),
);
```

Use this when the Workflow needs delayed delivery.

------------------------------------------------------------------------

# Mailable

MCF does not put email content inside `McfMail`.

Instead, each email is defined as a Laravel `Mailable`.

The current MCF structure includes:

``` text
Mail/
├── Authentication/
│   ├── Views/
│   ├── ChangeEmailCodeMail.php
│   ├── ResetPasswordCodeMail.php
│   ├── ResetPasswordLinkMail.php
│   ├── VerifyEmailCodeMail.php
│   └── VerifyEmailLinkMail.php
│
├── Website/
└── McfMail.php
```

This organization is recommended, not mandatory.

A project can organize its Mailables differently if needed.

------------------------------------------------------------------------

# Authentication Mail

MCF currently provides authentication-related Mailables such as:

``` text
ChangeEmailCodeMail
ResetPasswordCodeMail
ResetPasswordLinkMail
VerifyEmailCodeMail
VerifyEmailLinkMail
```

Keeping these messages under:

``` text
Mail/Authentication
```

makes the relationship between the email and its Workflow explicit.

This is a **recommended organizational pattern**, not a requirement for
every project or Mailable.

------------------------------------------------------------------------

# Why Authentication Is the Example

Authentication commonly needs email messages directly related to its
operations:

``` text
Email Verification
Password Reset
Email Change
```

Therefore, MCF provides these Mailables as a practical reference that
developers can follow when creating new Mailables.

A developer can use the same pattern:

``` text
Mail/
└── MyModule/
    ├── MyActionMail.php
    └── ...
```

or choose another structure appropriate for the project.

------------------------------------------------------------------------

# Mail Service vs Laravel Mail

MCF Mail is not an independent mail system.

The relationship is:

``` text
Workflow
    ↓
McfMail
    ↓
Laravel Mail
    ↓
Mailer / Transport
```

Therefore, the main mail configuration remains in Laravel.

For example:

``` text
MAIL_MAILER
MAIL_HOST
MAIL_PORT
MAIL_USERNAME
MAIL_PASSWORD
```

MCF does not need to rebuild Laravel’s mail configuration.

------------------------------------------------------------------------

# Why Wrap Laravel Mail?

The purpose is **convenient access and a consistent entry point**.

Instead of spreading Laravel Mail calls throughout the project:

``` php
Mail::to(...)->send(...);
Mail::to(...)->queue(...);
Mail::to(...)->later(...);
```

the project can use:

``` php
McfMail::send(...);
McfMail::queue(...);
McfMail::later(...);
```

This gives MCF one clear service layer that can be extended later
without changing every Workflow.

------------------------------------------------------------------------

# Is McfMail Mandatory?

No.

The wrapper exists for convenience, but a project can use Laravel Mail
directly when it needs an API or behavior that `McfMail` does not
expose.

Therefore:

``` text
McfMail
    = Recommended convenience layer
```

not:

``` text
McfMail
    = Required replacement for Laravel Mail
```

------------------------------------------------------------------------

# Recommended Organization

It is recommended to organize Mailables by module or Workflow:

``` text
Mail/
├── Authentication/
│   ├── Views/
│   ├── ChangeEmailCodeMail.php
│   ├── ResetPasswordCodeMail.php
│   ├── ResetPasswordLinkMail.php
│   ├── VerifyEmailCodeMail.php
│   └── VerifyEmailLinkMail.php
│
├── Website/
│   └── ...
│
└── McfMail.php
```

However, this organization is **optional**.

The developer may use another structure as long as Laravel can load the
Mailables.

------------------------------------------------------------------------

# Example Inside a Workflow

``` php
use App\MCF\Mail\McfMail;

McfMail::send(
    $user->email,
    new VerifyEmailCodeMail(
        $user,
        $code,
    ),
);
```

The Workflow focuses on the operation:

``` text
Generate verification code
        ↓
Create Mailable
        ↓
McfMail
        ↓
Laravel Mail
```

The Workflow does not need to deal with transport details.

------------------------------------------------------------------------

# Relationship with Language

Email content can use the MCF Language system:

``` php
__('Verify Your Email Address')
```

Therefore:

``` text
Mail
  ↓
Language
  ↓
ar.json / en.json
```

Mail remains responsible for building and sending the message, while
Language remains responsible for translation.

------------------------------------------------------------------------

# Relationship with Notifications

Mail and Notifications can work together, but they are not the same
thing.

``` text
Mail
    ↓
Email delivery

Notifications
    ↓
Application notification abstraction
```

A Notification can later use Mail when email is one of its notification
channels.

------------------------------------------------------------------------

# Rules

1.  `McfMail` is a wrapper around Laravel Mail.
2.  It does not rebuild Laravel’s mail system.
3.  `send` is for immediate delivery.
4.  `queue` is for queued delivery.
5.  `later` is for delayed delivery.
6.  Mailables remain Laravel `Mailable` classes.
7.  Organizing Mailables by Module or Workflow is recommended, not
    mandatory.
8.  Authentication contains practical examples that developers can
    follow.
9.  Core mail configuration remains in Laravel.
10. `McfMail` is optional; Laravel Mail can be used directly when
    needed.
11. Language is responsible for translations, not Mail.
12. The module’s goal is to provide consistent and convenient access to
    email delivery.

------------------------------------------------------------------------

# Architectural Goal

``` text
Workflow
    ↓
McfMail
    ↓
Laravel Mail
    ↓
Mail Transport
```

This keeps the Mail module small and clear. It does not compete with
Laravel; it provides an organized convenience layer over an existing
Laravel service.
