<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $notification->title ?? __('You have a new notification.') }}
    </title>
</head>

<body>

    <h2>
        {{ $notification->title ?? __('You have a new notification.') }}
    </h2>

    <p>
        {{ $notification->message }}
    </p>

    @if ($notification->url !== null)
        <p>
            <a href="{{ $notification->url }}">
                {{ __('View details') }}
            </a>
        </p>
    @endif

</body>
</html>
