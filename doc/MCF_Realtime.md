# MCF Realtime

## Overview

MCF Realtime provides a lightweight and simple realtime mechanism based
on AJAX polling. It allows application interfaces to receive updated
data without requiring WebSockets, SSE, or an external realtime service.

The core idea is to separate data responsibility from the UI:

-   MCF registers realtime channels and provides their current state.
-   The developer registers the channels that should be available inside
    `RealtimeChannel`.
-   The developer only invokes the required channel from the UI through
    `MCF.realtime()`.
-   Runtime, polling, and AJAX requests are handled internally by MCF.
-   The default polling interval is **15000ms (15 seconds)**.
-   The developer may override the interval when needed.

------------------------------------------------------------------------

## 1. Middleware Registration

During MCF installation, `McfRealtimeMiddleware` is registered in the
`web` middleware group in `bootstrap/app.php`.

Example:

``` php
->withMiddleware(
    function (Middleware $middleware): void {
        $middleware->web([
            'setlocale' =>
                \App\MCF\Middleware\SetLocaleMiddleware::class,

            'session.security' =>
                \App\MCF\Middleware\McfSessionSecurityMiddleware::class,

            'access' =>
                \App\MCF\Middleware\McfAccessMiddleware::class,

            'realtime' =>
                \App\MCF\Middleware\McfRealtimeMiddleware::class,
        ]);
    },
)
```

The middleware does not start realtime polling on every page. It first
checks the response and only processes successful HTML responses. It
then looks for:

``` js
MCF.realtime(
```

If the page does not use Realtime, the response is returned immediately.

------------------------------------------------------------------------

## 2. Registering Realtime Channels

MCF realtime channels are registered in:

``` text
app/MCF/Realtime/RealtimeChannel.php
```

Example:

``` php
<?php

declare(strict_types=1);

namespace App\MCF\Realtime;

use App\MCF\Notification\McfNotification;
use App\MCF\Realtime\Internal\RealtimeRegistry;

final class RealtimeChannel
{
    private function __construct()
    {
    }

    public static function register(): void
    {
        RealtimeRegistry::register(
            name: 'notifications',
            state: static fn (): array =>
                McfNotification::notify()->readState(),
        );
    }
}
```

### Concept

Each channel consists of:

``` text
channel name
+
a callback that returns its current state
```

Example:

``` php
RealtimeRegistry::register(
    name: 'notifications',
    state: static fn (): array =>
        McfNotification::notify()->readState(),
);
```

The channel name is:

``` text
notifications
```

The state callback provides the data that will be sent to the browser.

------------------------------------------------------------------------

## 3. Automatic Channel Registration

The developer does not need to call:

``` php
RealtimeChannel::register();
```

manually throughout the application.

It is registered centrally by `MCFServiceProvider`:

``` php
McfQueueListener::register();
RealtimeChannel::register();
```

This makes the MCF realtime channels available when the application
boots.

------------------------------------------------------------------------

## 4. Using Realtime in the UI

The developer does not manually render the internal runtime.

Do not write:

``` blade
{!! \App\MCF\Realtime\Internal\RealtimeRuntime::render() !!}
```

There is also no need to manually load a JavaScript file.

Simply use:

``` html
<script>
    MCF.realtime('notifications', {
        onUpdate: function (state) {
            console.log(state);
        }
    });
</script>
```

When `MCF.realtime()` is present in an HTML page,
`McfRealtimeMiddleware` automatically injects the runtime before the
script that uses it.

------------------------------------------------------------------------

## 5. Receiving State

The value passed to `onUpdate` is the current state of the channel:

``` js
MCF.realtime('notifications', {
    onUpdate: function (state) {
        console.log(state);
    }
});
```

For the notifications channel, the state may look like:

``` json
{
    "count": 7,
    "notifications": [
        {
            "id": "notification-id",
            "title": "Test Notification",
            "message": "Realtime test message.",
            "url": null,
            "created_at": "2026-08-19T00:46:51.000000Z"
        }
    ]
}
```

The developer controls how the state is rendered.

MCF does not impose a specific UI component or HTML structure.

------------------------------------------------------------------------

## 6. Complete Example

``` html
<h1>MCF Realtime Test</h1>

<div>
    Unread:
    <strong id="notification-count">0</strong>
</div>

<div id="notification-list"></div>

<script>
    MCF.realtime('notifications', {

        onUpdate: function (state) {

            document.getElementById(
                'notification-count'
            ).textContent = state.count;

            const list =
                document.getElementById(
                    'notification-list'
                );

            list.innerHTML = '';

            state.notifications.forEach(
                function (notification) {

                    const item =
                        document.createElement('div');

                    item.textContent =
                        (
                            notification.title || ''
                        ) +
                        ' - ' +
                        notification.message;

                    list.appendChild(item);
                }
            );
        },

        onError: function (error) {
            console.error(
                'MCF Realtime Error:',
                error
            );
        },

    });
</script>
```

------------------------------------------------------------------------

## 7. Changing the Interval

The default interval is:

``` text
15000ms
```

That is:

``` text
15 seconds
```

The developer does not need to specify it:

``` js
MCF.realtime('notifications', {
    onUpdate: function (state) {
        // ...
    }
});
```

If a different interval is required, it can be provided:

``` js
MCF.realtime('notifications', {
    interval: 30000,

    onUpdate: function (state) {
        // ...
    }
});
```

This changes the interval to:

``` text
30000ms = 30 seconds
```

------------------------------------------------------------------------

## 8. Stopping a Realtime Channel

`MCF.realtime()` returns a controller that can be used to stop the
channel:

``` js
const realtime =
    MCF.realtime('notifications', {
        onUpdate: function (state) {
            console.log(state);
        }
    });

realtime.stop();
```

After calling `stop()`, polling for that channel stops.

------------------------------------------------------------------------

## 9. Error Handling

Use `onError` when error handling is required:

``` js
MCF.realtime('notifications', {
    onUpdate: function (state) {
        console.log(state);
    },

    onError: function (error) {
        console.error(
            'Realtime error:',
            error
        );
    }
});
```

When a request fails, the runtime automatically retries using an
increasing delay up to the internally defined maximum interval.

------------------------------------------------------------------------

## 10. Architecture

``` text
MCFServiceProvider
        |
        +-- RealtimeChannel::register()
        |          |
        |          +-- notifications
        |          +-- other channels
        |
        +-- MCF services


bootstrap/app.php
        |
        +-- McfRealtimeMiddleware
                    |
                    +-- HTML response?
                    |
                    +-- contains MCF.realtime()?
                              |
                         yes  |
                              v
                       RealtimeRuntime


Browser
   |
   +-- MCF.realtime('notifications')
              |
              v
       AJAX Polling
              |
              v
   /mcf/realtime/notifications
              |
              v
       RealtimeRoutes
              |
              v
       RealtimeAjax
              |
              v
       RealtimeRegistry
              |
              v
        channel state
              |
              v
          onUpdate()
```

------------------------------------------------------------------------

## 11. Design Principles

MCF Realtime follows these principles:

1.  **No WebSocket or SSE dependency.**
2.  **No external realtime service is required.**
3.  **No additional JavaScript library is imposed on the developer.**
4.  **Channels are registered centrally inside `RealtimeChannel`.**
5.  **Internal implementation details remain inside `Internal`.**
6.  **The developer interacts with a single public API:
    `MCF.realtime()`.**
7.  **The interval is optional and defaults to 15 seconds.**
8.  **Pages that do not use Realtime do not start polling.**
9.  **MCF provides the state; the developer owns the UI.**
