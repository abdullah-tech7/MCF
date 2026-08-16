@extends('Shared::Layout.app')

@section('content')

{{--                                                                       --}}
{{-- --------------------------------------------------------------------- --}}
{{-- MCF Authentication - Forgot Password                                 --}}
{{-- --------------------------------------------------------------------- --}}
{{--                                                                       --}}
{{-- This page allows the user to enter their email address to start       --}}
{{-- the password reset verification process.                              --}}
{{--                                                                       --}}
{{-- A verification code is sent to the provided email address.            --}}
{{-- The user is then redirected to the email verification page.           --}}
{{--                                                                       --}}
{{-- Validation errors are displayed next to the corresponding field.      --}}
{{-- General success/error messages are handled by the shared Layout.      --}}
{{--                                                                       --}}
{{-- Expected Variables:                                                    --}}
{{--                                                                       --}}
{{-- - None                                                                 --}}
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

        {{ __('Forgot Password') }}

    </h1>

    <p class="mcf-auth-form-description">

        {{ __('Enter your email address to receive a password reset verification code.') }}

    </p>

    <div class="mcf-auth-form-card">

        <form
            method="POST"
            action="{{ route('user.auth.forgotPasswordPost') }}"
        >

            @csrf

            <div class="mcf-auth-form-field">

                <label for="email">

                    {{ __('Email') }}

                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    autofocus
                >

                @error('email')

                    <div class="mcf-auth-form-error">

                        {{ $message }}

                    </div>

                @enderror

            </div>

            <button
                type="submit"
                class="mcf-auth-form-button"
            >

                {{ __('Send Verification Code') }}

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
