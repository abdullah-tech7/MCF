{{--                                                                       --}}
{{-- --------------------------------------------------------------------- --}}
{{-- MCF Layout                                                           --}}
{{-- --------------------------------------------------------------------- --}}
{{--                                                                       --}}
{{-- This is the main layout entry point for all generated workflows.     --}}
{{--                                                                       --}}
{{-- The Components folder is an optional suggested structure.             --}}
{{-- You may:                                                              --}}
{{-- - Keep everything in this file.                                       --}}
{{-- - Split the layout into components.                                   --}}
{{-- - Delete the Components folder completely.                            --}}
{{-- - Replace it with your own architecture.                              --}}
{{--                                                                       --}}
{{-- This layout includes basic success and error messages.                --}}
{{-- The included CSS is intentionally minimal and may be replaced        --}}
{{-- or moved to your own stylesheet.                                      --}}
{{--                                                                       --}}
{{-- It is recommended to keep this file named "app.blade.php"             --}}
{{-- so generated workflows continue using:                                --}}
{{--                                                                       --}}
{{-- @extends('Shared.Layout.app')                                         --}}
{{--                                                                       --}}
{{-- --------------------------------------------------------------------- --}}
{{-- --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        @yield('title', config('app.name'))
    </title>

    <style>

        /*
        |--------------------------------------------------------------------------
        | MCF Layout Messages
        |--------------------------------------------------------------------------
        |
        | Basic default styles only.
        | Developers may replace these styles or move them to their own
        | stylesheet when customizing the application design.
        |
        */

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

    @includeIf('Shared::Layout.Components.head')

</head>

<body>

    @includeIf('Shared::Layout.Components.header')

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

    @yield('content')

    @includeIf('Shared::Layout.Components.footer')

</body>

</html>
