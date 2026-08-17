# MCF Notification

## 1. Overview

**MCF Notification** is a wrapper around Laravel's native Notification
system.

The goal is to provide a unified and convenient MCF API while preserving
Laravel's native behavior, rather than building a separate notification
framework.

### Components

-   `NotificationData`: notification content.
-   `NotificationRequest`: temporary delivery information such as
    targets and channels.
-   `McfNotification`: public API for sending notifications and opening
    the notification center.
-   `McfNotificationCenter`: wrapper around Laravel's stored
    notifications.
-   `NotificationSettings`: project-level customization points.
-   `NotificationMail`: notification email implementation inside the
    Mail module.

------------------------------------------------------------------------

## 2. User Model --- Required

MCF follows Laravel's native notification behavior. The project's User
model must use Laravel's `Notifiable` trait:

``` php
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
}
```

This provides Laravel's native relationships:

``` php
$user->notifications;
$user->unreadNotifications;
$user->readNotifications;
```

MCF relies on these relationships instead of implementing a separate
notification relationship system.

The model configured in:

``` php
NotificationSettings::$userModel
```

must be the User model that uses `Notifiable`.

------------------------------------------------------------------------

## 3. Database

MCF uses Laravel's standard notifications table:

``` php
Schema::create('notifications', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('type');
    $table->morphs('notifiable');
    $table->text('data');
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
});
```

MCF does not create a separate notification table.

The `data` column stores the result of:

``` php
NotificationData::toArray();
```

Laravel then handles its JSON representation.

Example:

``` json
{
    "title": "Password updated",
    "message": "Your password was changed. If you did not make this change, please contact support.",
    "url": "http://127.0.0.1:8000/profile"
}
```

Seeing Arabic characters represented as `\uXXXX` in the database is
valid Unicode JSON representation and does not mean the text was lost.

------------------------------------------------------------------------

## 4. NotificationData

`NotificationData` is the unified notification-content contract:

``` text
message
title
url
```

-   `message`: required.
-   `title`: optional and defaults to `null`.
-   `url`: optional and defaults to `null`.

Notification-specific methods define their own required variables,
messages, and URLs.

Example:

``` php
$notification = NotificationData::passwordUpdated();
```

Example with a required variable:

``` php
$notification = NotificationData::profileUpdated(
    userId: 1,
);
```

The caller does not provide `message`, `title`, or `url` to
notification-specific methods. The method itself defines them and
accepts only the variables it actually needs.

### Translation

User-facing notification text should use the project's translation
system:

``` php
$message = __('notification.password_updated');
```

Translations can be stored in:

``` text
lang/ar.json
lang/en.json
```

### `toArray()`

``` php
public function toArray(): array
{
    return [
        'title' => $this->title,
        'message' => $this->message,
        'url' => $this->url,
    ];
}
```

Used when storing notification data in the database.

### `fromArray()`

``` php
public static function fromArray(array $data): self
{
    return new self(
        message: $data['message'],
        title: $data['title'] ?? null,
        url: $data['url'] ?? null,
    );
}
```

Used when converting stored database data back into `NotificationData`.

------------------------------------------------------------------------

## 5. NotificationRequest

`NotificationRequest` contains temporary delivery information, not
notification content:

``` php
new NotificationRequest(
    data: $notification,
    target: 'users',
    users: [1],
    channels: ['database', 'mail'],
);
```

Fields:

``` text
data
target
roles
users
channels
```

### Targets

Supported values:

``` text
all
roles
users
```

#### `all`

Send to all users:

``` php
target: 'all',
```

`roles` and `users` are not used in this mode.

#### `roles`

Send to users matching the specified roles:

``` php
target: 'roles',
roles: [1, 4],
```

#### `users`

Send to specific users:

``` php
target: 'users',
users: [1, 5, 10],
```

### Missing Users or Roles

A missing User or Role is not considered a programming error and is
ignored.

Example:

``` php
users: [1, 5, 999],
```

If user `999` does not exist:

``` text
1   -> send
5   -> send
999 -> ignore
```

If none of the requested users exist:

``` text
no recipient
→ nothing is sent
→ no exception
```

The same behavior applies to Roles.

Invalid Request structure, such as an unsupported target or channel, is
a programming error and throws `LogicException`.

------------------------------------------------------------------------

## 6. Channels

Current supported channels:

``` text
database
mail
sms
```

One channel:

``` php
channels: ['database'],
```

Two channels:

``` php
channels: [
    'database',
    'mail',
],
```

All three:

``` php
channels: [
    'database',
    'mail',
    'sms',
],
```

An unsupported channel such as:

``` php
channels: ['push'],
```

throws `LogicException`.

------------------------------------------------------------------------

## 7. McfNotification --- Sending

`McfNotification` is the public API.

``` php
McfNotification::send(
    new NotificationRequest(
        data: NotificationData::passwordUpdated(),
        target: 'users',
        users: [1],
        channels: ['database'],
    ),
);
```

All users:

``` php
McfNotification::send(
    new NotificationRequest(
        data: NotificationData::welcome(
            name: 'Ahmed',
        ),
        target: 'all',
        channels: [
            'database',
            'mail',
        ],
    ),
);
```

By roles:

``` php
McfNotification::send(
    new NotificationRequest(
        data: NotificationData::profileUpdated(
            userId: 1,
        ),
        target: 'roles',
        roles: [1, 4],
        channels: ['database'],
    ),
);
```

------------------------------------------------------------------------

## 8. Database Channel

When:

``` php
channels: ['database'],
```

is selected, MCF uses Laravel's native Database Notification channel.

It passes:

``` php
$data->toArray()
```

to Laravel, which stores the data in:

``` text
notifications.data
```

There is no custom MCF Database Notification model.

------------------------------------------------------------------------

## 9. Mail Channel

Mail uses:

``` php
NotificationSettings::$notificationMail
```

The default implementation is:

``` php
NotificationMail::class
```

Location:

``` text
app/
└── MCF/
    └── Mail/
        └── Notification/
            ├── NotificationMail.php
            └── Views/
                └── notification.blade.php
```

The Mail implementation receives `NotificationData` directly:

``` php
$data->title;
$data->message;
$data->url;
```

If `title` is `null`, the Mail implementation uses its configured
default subject.

------------------------------------------------------------------------

## 10. SMS Channel

SMS uses:

``` php
$data->message
```

as the message body.

If `url` exists, the SMS implementation may include it according to the
SMS service design.

------------------------------------------------------------------------

## 11. NotificationSettings

File:

``` text
app/MCF/Notification/NotificationSettings.php
```

This file contains project-level customization points.

Example:

``` php
final class NotificationSettings
{
    public static string $userModel = User::class;

    public static function resolveRole(
        Authenticatable $user,
    ): int|string|null {
        return $user->role_id;
    }

    public static string $notificationMail =
        NotificationMail::class;
}
```

### `userModel`

Defines the User model:

``` php
public static string $userModel = User::class;
```

### `resolveRole()`

Defines how MCF resolves the user's role:

``` php
public static function resolveRole(
    Authenticatable $user,
): int|string|null {
    return $user->role_id;
}
```

If the project uses a different role structure, this method can be
customized.

### `notificationMail`

Defines the Notification Mail class:

``` php
public static string $notificationMail =
    NotificationMail::class;
```

------------------------------------------------------------------------

## 12. Notification Center

Open the current user's notification center:

``` php
McfNotification::notify()
```

The current user is resolved through `McfAuth`.

### `all()`

``` php
$notifications = McfNotification::notify()
    ->all();
```

Returns Laravel's native:

``` php
DatabaseNotificationCollection
```

### `unread()`

``` php
$notifications = McfNotification::notify()
    ->unread();
```

### `read()`

``` php
$notifications = McfNotification::notify()
    ->read();
```

### `count()`

``` php
$count = McfNotification::notify()
    ->count();
```

Returns the number of unread notifications.

### `find()`

``` php
$notification = McfNotification::notify()
    ->find($id);
```

Returns `null` when the notification does not exist.

------------------------------------------------------------------------

## 13. Native Laravel Data + MCF Data Object

MCF does not change Laravel's native behavior.

The developer can use:

``` php
$notification->data;
```

which remains Laravel's original Array.

When a typed `NotificationData` object is needed:

``` php
$data = McfNotification::notify()
    ->dataObject($notification);
```

Then:

``` php
$data->title;
$data->message;
$data->url;
```

Therefore:

``` text
Laravel:
$notification->data
→ Array
```

and:

``` text
MCF:
dataObject()
→ NotificationData
```

The `DatabaseNotification` object is not replaced and the original
`data` type is not changed.

------------------------------------------------------------------------

## 14. Read State

Mark one notification as read:

``` php
McfNotification::notify()
    ->markAsRead($id);
```

Mark all unread notifications as read:

``` php
McfNotification::notify()
    ->markAllAsRead();
```

------------------------------------------------------------------------

## 15. Delete

Delete one notification:

``` php
McfNotification::notify()
    ->delete($id);
```

If the notification does not exist, `LogicException` is thrown.

Delete all notifications:

``` php
McfNotification::notify()
    ->deleteAll();
```

------------------------------------------------------------------------

## 16. Complete Example

``` php
$notification = NotificationData::passwordUpdated();

$request = new NotificationRequest(
    data: $notification,
    target: 'users',
    users: [1],
    channels: [
        'database',
        'mail',
    ],
);

McfNotification::send($request);
```

Read:

``` php
$notification = McfNotification::notify()
    ->unread()
    ->first();

if ($notification !== null) {
    $data = McfNotification::notify()
        ->dataObject($notification);

    echo $data->title;
    echo $data->message;
    echo $data->url;
}
```

Then:

``` php
McfNotification::notify()
    ->markAsRead($notification->id);
```

------------------------------------------------------------------------

## 17. Design Rules

### `NotificationData` is responsible for

``` text
title
message
url
notification-specific variables
translations
toArray()
fromArray()
```

It does not know about:

``` text
target
users
roles
channels
mail
sms
database
```

### `NotificationRequest` is responsible for

``` text
data
target
users
roles
channels
validation
```

It does not build notification content.

### `McfNotification` is responsible for

``` text
send()
notify()
recipient resolution
channel execution
```

### `McfNotificationCenter` is responsible for

``` text
all()
unread()
read()
count()
find()
dataObject()
markAsRead()
markAllAsRead()
delete()
deleteAll()
```

### `NotificationSettings` is responsible for

``` text
User Model
Role Resolver
Notification Mail
```

------------------------------------------------------------------------

## 18. Architecture

``` text
NotificationData
       │
       ▼
NotificationRequest
       │
       ▼
McfNotification
       │
       ├───────────────┬───────────────┐
       ▼               ▼               ▼
   Database           Mail            SMS
       │               │               │
       ▼               ▼               ▼
   Laravel DB      NotificationMail   McfSms


McfNotification::notify()
       │
       ▼
McfNotificationCenter
       │
       ▼
Laravel DatabaseNotification
       │
       ├── data → Array
       │
       └── dataObject() → NotificationData
```

------------------------------------------------------------------------

## 19. Core Principle

MCF is a wrapper over Laravel, not a replacement for Laravel
Notifications.

Laravel remains responsible for:

``` text
notifications table
DatabaseNotification
Notifiable
read_at
notifiable_type
notifiable_id
```

MCF adds:

``` text
Unified NotificationData
Unified sending API
Target resolution
Channel selection
Notification Center
Typed data conversion
Project-level settings
```

The result is a thin MCF layer over Laravel, not a second notification
framework.
