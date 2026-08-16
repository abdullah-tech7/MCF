@extends('Shared::Layout.app')

@section('content')

{{-- --}}
{{-- --------------------------------------------------------------------- --}}
{{-- MCF Profile Starter --}}
{{-- --------------------------------------------------------------------- --}}
{{-- --}}
{{-- This page is included as a starter example. --}}
{{-- --}}
{{-- It demonstrates: --}}
{{-- --}}
{{-- - Authenticated user --}}
{{-- - Localization --}}
{{-- - Update Email --}}
{{-- - Update Password --}}
{{-- - Delete Account --}}
{{-- - Logout --}}
{{-- --}}
{{-- Feel free to replace this page with your own UI. --}}
{{-- --}}
{{-- --------------------------------------------------------------------- --}}

<style>
    .mcf-profile {
        max-width: 700px;
        margin: 40px auto;
        padding: 0 20px;
        font-family: Arial, sans-serif;
    }

    .mcf-profile-title {
        margin: 0 0 8px;
        font-size: 28px;
    }

    .mcf-profile-description {
        margin: 0 0 25px;
        color: #666;
    }

    .mcf-profile-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }

    .mcf-profile-table {
        width: 100%;
        border-collapse: collapse;
    }

    .mcf-profile-table td {
        padding: 13px 16px;
        border-bottom: 1px solid #eee;
    }

    .mcf-profile-table tr:last-child td {
        border-bottom: none;
    }

    .mcf-profile-table td:first-child {
        width: 40%;
        font-weight: 600;
        color: #555;
    }

    .mcf-profile-section {
        margin-top: 25px;
        padding: 18px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
    }

    .mcf-profile-section h2 {
        margin: 0 0 14px;
        font-size: 19px;
    }

    .mcf-profile-language {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .mcf-profile-language select {
        min-width: 130px;
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background: #fff;
    }

    .mcf-profile-button {
        padding: 8px 14px;
        border: 0;
        border-radius: 5px;
        cursor: pointer;
    }

    .mcf-profile-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 25px;
    }

    .mcf-profile-actions a {
        display: inline-block;
        padding: 10px 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        text-decoration: none;
        color: inherit;
    }

    .mcf-profile-actions a:hover {
        background: #f5f5f5;
    }

    .logout button,
    .delete-account button {
        display: inline-block;
        padding: 10px 15px;
        border: 1px solid transparent;
        border-radius: 5px;
        color: #fff;
        cursor: pointer;
    }

    .logout button {
        background: darkred;
    }

    .delete-account {
        margin-top: 25px;
        padding: 18px;
        border: 1px solid #f1caca;
        border-radius: 8px;
        background: #fff7f7;
    }

    .delete-account h2 {
        margin: 0 0 8px;
        color: #a00000;
        font-size: 19px;
    }

    .delete-account p {
        margin: 0 0 15px;
        color: #666;
        line-height: 1.5;
    }

    .delete-account button {
        background: #dc3545;
    }

    .delete-account button:hover {
        opacity: 0.85;
    }
</style>

<div class="mcf-profile">

    <h1 class="mcf-profile-title">
        {{ __('Profile') }}
    </h1>

    <p class="mcf-profile-description">
        {{ __('You are successfully signed in.') }}
    </p>

    <div class="mcf-profile-card">

        <table class="mcf-profile-table">

            <tr>
                <td>{{ __('ID') }}</td>
                <td>{{ $user->id }}</td>
            </tr>

            <tr>
                <td>{{ __('Name') }}</td>
                <td>{{ $user->name }}</td>
            </tr>

            <tr>
                <td>{{ __('Email') }}</td>
                <td>{{ $user->email }}</td>
            </tr>

            <tr>
                <td>{{ __('Phone') }}</td>
                <td>{{ $user->phone ?: __('Not Provided') }}</td>
            </tr>

            <tr>
                <td>{{ __('Email Verification') }}</td>

                <td>
                    {{
                        $user->email_verified_at
                            ? __('Verified')
                            : __('Not Verified')
                    }}
                </td>
            </tr>

            <tr>
                <td>{{ __('Account Status') }}</td>

                <td>
                    {{
                        $user->is_active
                            ? __('Active')
                            : __('Inactive')
                    }}
                </td>
            </tr>

            <tr>
                <td>{{ __('Created At') }}</td>
                <td>{{ $user->created_at }}</td>
            </tr>

        </table>

    </div>

    <div class="mcf-profile-section">

        <h2>
            {{ __('Language') }}
        </h2>

        <form
            method="POST"
            action="{{ route('shared.layout.switchLanguage') }}"
            class="mcf-profile-language"
        >
            @csrf

            <select name="locale">

                <option
                    value="en"
                    {{ app()->getLocale() === 'en' ? 'selected' : '' }}
                >
                    English
                </option>

                <option
                    value="ar"
                    {{ app()->getLocale() === 'ar' ? 'selected' : '' }}
                >
                    العربية
                </option>

            </select>

            <button
                type="submit"
                class="mcf-profile-button"
            >
                {{ __('Change Language') }}
            </button>

        </form>

    </div>

    <div class="mcf-profile-actions">

        <a href="{{ route('user.profile.updateEmail') }}">
            {{ __('Update Email') }}
        </a>

        <a href="{{ route('user.profile.updatePassword') }}">
            {{ __('Update Password') }}
        </a>

        <form
            method="POST"
            action="{{ route('user.auth.logout') }}"
            class="logout"
        >
            @csrf

            <button type="submit">
                {{ __('Logout') }}
            </button>
        </form>

    </div>

    <div class="delete-account">

        <h2>
            {{ __('Delete Account') }}
        </h2>

        <p>
            {{
                __('Deleting your account will sign you out of all active sessions. A verification code will be sent to your email before the account is deleted.')
            }}
        </p>

        <form
            method="POST"
            action="{{ route('user.profile.deleteAccountPost') }}"
        >
            @csrf

            <button type="submit">
                {{ __('Delete My Account') }}
            </button>
        </form>

    </div>

</div>

@endsection
