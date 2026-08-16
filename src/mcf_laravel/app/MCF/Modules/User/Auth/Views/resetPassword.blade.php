@extends('Shared::Layout.app')

@section('content')

{{--                                                                       --}}
{{-- --------------------------------------------------------------------- --}}
{{-- MCF Authentication - Reset Password                                  --}}
{{-- --------------------------------------------------------------------- --}}
{{--                                                                       --}}
{{-- This page allows the user to set a new password after successfully   --}}
{{-- verifying their email address through the password reset process.    --}}
{{--                                                                       --}}
{{-- The email address is displayed for context.                           --}}
{{-- The user enters and confirms the new password.                        --}}
{{--                                                                       --}}
{{-- Validation errors are displayed next to the corresponding fields.     --}}
{{-- General success/error messages are handled by the shared Layout.      --}}
{{--                                                                       --}}
{{-- Expected Variables:                                                    --}}
{{--                                                                       --}}
{{-- - email (string)                                                        --}}
{{--                                                                       --}}
{{-- --------------------------------------------------------------------- --}}
{{--                                                                       --}}

<style>
    .mcf-auth-form {
        max-width: 420px;
        margin: 40px auto;
    }

    .mcf-auth-form-title {
        margin-bottom: 10px;
    }

    .mcf-auth-form-description {
        margin-bottom: 24px;
    }

    .mcf-auth-form-card {
        padding: 24px;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    .mcf-auth-form-field {
        margin-bottom: 18px;
    }

    .mcf-auth-form-field label {
        display: block;
        margin-bottom: 8px;
    }

    .mcf-auth-form-email {
        padding: 10px;
        background: #f5f5f5;
        border-radius: 6px;
        word-break: break-word;
    }

    .mcf-auth-form-field input {
        width: 100%;
        padding: 10px;
        box-sizing: border-box;
    }

    .mcf-auth-form-error {
        margin-top: 6px;
        color: #c00;
    }

    .mcf-auth-form-button {
        width: 100%;
        padding: 11px;
        cursor: pointer;
    }

    .mcf-auth-form-footer {
        margin-top: 20px;
        text-align: center;
    }
</style>

<div class="mcf-auth-form">

    <h1 class="mcf-auth-form-title">

        {{ __('Reset Password') }}

    </h1>

    <p class="mcf-auth-form-description">

        {{ __('Enter your new password below.') }}

    </p>

    <div class="mcf-auth-form-card">

        <div class="mcf-auth-form-field">

            <label>

                {{ __('Email') }}

            </label>

            <div class="mcf-auth-form-email">

                {{ $email }}

            </div>

        </div>

        <form
            method="POST"
            action="{{ route(
                'user.auth.resetPasswordPost',
                ['email' => $email],
            ) }}"
        >

            @csrf

            <div class="mcf-auth-form-field">

                <label for="password">

                    {{ __('New Password') }}

                </label>

                <input
                    id="password"
                    type="password"
                    name="password"
                    autocomplete="new-password"
                    autofocus
                >

                @error('password')

                    <div class="mcf-auth-form-error">

                        {{ $message }}

                    </div>

                @enderror

            </div>

            <div class="mcf-auth-form-field">

                <label for="password_confirmation">

                    {{ __('Confirm New Password') }}

                </label>

                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    autocomplete="new-password"
                >

                @error('password_confirmation')

                    <div class="mcf-auth-form-error">

                        {{ $message }}

                    </div>

                @enderror

            </div>

            <button
                type="submit"
                class="mcf-auth-form-button"
            >

                {{ __('Reset Password') }}

            </button>

        </form>

    </div>

    <div class="mcf-auth-form-footer">

        <a href="{{ route('user.auth.login') }}">

            {{ __('Back to Login') }}

        </a>

    </div>

</div>

@endsection
