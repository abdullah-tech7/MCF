# MCF Mail

## Overview

The **Mail** module in MCF is a lightweight service that wraps Laravel’s
Mail system and provides a consistent, convenient entry point for
sending email.

The goal is not to replace Laravel Mail, but to provide a simple MCF
layer that makes email usage easier inside Workflows and modules.

------------------------------------------------------------------------

# Core Concept

MCF provides one stable entry point for email delivery:

```php
McfMail::send($to, $mail);
```

The important design decision is that **`send()` is the default MCF
delivery entry point**, while its implementation is controlled centrally
inside `McfMail`.

The current default is queued delivery:

```php
public static function send(
    string $to,
    Mailable $mail,
): void {
    self::queued(
        $to,
        $mail,
    );
}
```

Therefore:

```text
McfMail::send()
        ↓
McfMail::queued()
        ↓
Laravel Mail Queue
```

This makes the delivery strategy dynamic without changing every caller
throughout the framework.

---

# Dynamic `send()` Behavior

The framework uses:

```php
McfMail::send(...)
```

as its normal email API.

If the queue mechanism does not work or is not suitable for a particular
deployment, the developer does **not** need to search the entire project
and replace every occurrence of:

```php
McfMail::send(...)
```

The developer changes the implementation of `send()` in **one place**.

Queued:

```php
public static function send(
    string $to,
    Mailable $mail,
): void {
    self::queued(
        $to,
        $mail,
    );
}
```

Direct:

```php
public static function send(
    string $to,
    Mailable $mail,
): void {
    self::direct(
        $to,
        $mail,
    );
}
```

All existing calls remain:

```php
McfMail::send(...)
```

This is intentional.

```text
Many framework calls
        ↓
McfMail::send()
        ↓
ONE implementation point
        ↓
Queued OR Direct
```

This keeps the framework API stable while allowing the delivery strategy
to change centrally.

---

# Important: Configure Mail First

Before using or testing MCF Mail, Laravel's mail configuration must be
valid.

MCF does not replace Laravel's mail configuration.

The application must configure the required mail environment values,
for example:

```text
MAIL_MAILER
MAIL_HOST
MAIL_PORT
MAIL_USERNAME
MAIL_PASSWORD
MAIL_ENCRYPTION
MAIL_FROM_ADDRESS
MAIL_FROM_NAME
```

The exact values depend on the mail provider.

After changing environment or mail configuration, clear Laravel's
cached configuration when necessary:

```bash
php artisan optimize:clear
```

The first test should confirm that Laravel itself can send email.

MCF Mail is a layer on top of Laravel Mail. If Laravel mail configuration
is incorrect, MCF Mail cannot deliver the email correctly.

---

# Queue Integration

When `send()` uses its default queued implementation:

```text
McfMail::send()
        ↓
McfMail::queued()
        ↓
Laravel Mail Queue
        ↓
jobs table
        ↓
MCF Queue Listener
        ↓
Background Process
        ↓
queue:work --once
```

The `jobs` table and automatic background processing are documented in:

```text
MCF_QUEUE.md
```

Read that document when investigating queue behavior, job storage, or
background processing.

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
        self::queued(
            $to,
            $mail,
        );
    }

    public static function queued(
        string $to,
        Mailable $mail,
    ): void {
        Mail::to($to)->queue($mail);
    }

    public static function direct(
        string $to,
        Mailable $mail,
    ): void {
        Mail::to($to)->send($mail);
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

It provides the main delivery operations:

```text
send
queued
direct
later
```

`send()` is the stable default entry point. The actual strategy can be
changed centrally between queued and direct delivery.

------------------------------------------------------------------------

# send

`send()` is the main MCF email API:

```php
McfMail::send(
    $user->email,
    new VerifyEmailLinkMail($user),
);
```

By default, `send()` uses queued delivery.

```text
send()
  ↓
queued()
  ↓
Laravel Queue
```

If the queue is unsuitable for a deployment, change `send()` centrally
to call `direct()` instead. Existing calls throughout the framework do
not need to be changed.

------------------------------------------------------------------------

# queued

To explicitly request queued delivery:

```php
McfMail::queued(
    $user->email,
    new VerifyEmailCodeMail($user),
);
```

Internally:

```php
Mail::to($to)->queue($mail);
```

The job is stored in Laravel's `jobs` table and processed by the MCF
Queue integration.

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

1. `McfMail` is a wrapper around Laravel Mail.
2. It does not rebuild Laravel's mail system.
3. `send()` is the stable default MCF entry point.
4. `send()` currently defaults to queued delivery.
5. `queued()` explicitly requests Laravel Queue.
6. `direct()` explicitly requests synchronous delivery.
7. `later()` is for delayed queued delivery.
8. If queue processing is unsuitable for a deployment, change the
   implementation of `send()` in one place instead of modifying every
   caller.
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


---

# Recommended Default

For MCF framework code and normal application workflows, prefer:

```php
McfMail::send(...)
```

The default strategy is queue-based.

This keeps email delivery separate from the HTTP request and allows the
MCF Queue layer to process the job independently.

If the deployment cannot use the MCF automatic queue mechanism, change
only the implementation of `send()` to use `direct()`.

Do not replace every `McfMail::send()` call throughout the project.

The purpose of the abstraction is specifically to avoid that maintenance
cost.
