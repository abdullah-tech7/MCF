@extends('Shared::Layout.app')

@section('content')

{{--                                                                       --}}
{{-- --------------------------------------------------------------------- --}}
{{-- MCF Authentication - Verify Forgot Password                          --}}
{{-- --------------------------------------------------------------------- --}}
{{--                                                                       --}}
{{-- This page verifies the email address before allowing the user         --}}
{{-- to reset their password.                                               --}}
{{--                                                                       --}}
{{-- The email address is displayed for context.                            --}}
{{-- The verification code is the only user input.                         --}}
{{--                                                                       --}}
{{-- The user can request a new verification code after the cooldown       --}}
{{-- period has elapsed.                                                    --}}
{{--                                                                       --}}
{{-- Validation errors are displayed next to the code field.               --}}
{{-- General success/error messages are handled by the shared Layout.      --}}
{{--                                                                       --}}
{{-- Expected Variables:                                                    --}}
{{--                                                                       --}}
{{-- - email (string)                                                        --}}
{{-- - cooldownRemaining (int)                                              --}}
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

    .mcf-auth-form-resend {
        margin-top: 20px;
        text-align: center;
    }

    .mcf-auth-form-resend-status {
        margin-top: 8px;
        font-size: 14px;
    }

    .mcf-auth-form-footer {
        margin-top: 20px;
        text-align: center;
    }

    .mcf-auth-form-footer a {
        margin: 0 8px;
    }
</style>

<div class="mcf-auth-form">

    <h1 class="mcf-auth-form-title">

        {{ __('Verify Password Reset') }}

    </h1>

    <p class="mcf-auth-form-description">

        {{ __('Enter the verification code sent to your email address.') }}

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
                'user.auth.verifyForgotPasswordPost',
                ['email' => $email],
            ) }}"
        >

            @csrf

            <div class="mcf-auth-form-field">

                <label for="code">

                    {{ __('Verification Code') }}

                </label>

                <input
                    id="code"
                    type="text"
                    name="code"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    autofocus
                >

                @error('code')

                    <div class="mcf-auth-form-error">

                        {{ $message }}

                    </div>

                @enderror

            </div>

            <button
                type="submit"
                class="mcf-auth-form-button"
            >

                {{ __('Verify Code') }}

            </button>

        </form>

        <div class="mcf-auth-form-resend">

            <form
                method="POST"
                action="{{ route(
                    'user.auth.resendForgotPasswordVerification',
                    ['email' => $email],
                ) }}"
            >

                @csrf

                <button
                    id="mcf-resend-button"
                    type="submit"
                    class="mcf-auth-form-button"
                    disabled
                >

                    {{ __('Resend Verification Code') }}

                </button>

                <p
                    id="mcf-resend-status"
                    class="mcf-auth-form-resend-status"
                >

                    {{ __('You can request a new code in :seconds seconds.', [
                        'seconds' => $cooldownRemaining,
                    ]) }}

                </p>

            </form>

        </div>

    </div>

    <div class="mcf-auth-form-footer">

        <a href="{{ route('user.auth.forgotPassword') }}">

            {{ __('Change Email Address') }}

        </a>

        <a href="{{ route('user.auth.login') }}">

            {{ __('Back to Login') }}

        </a>

    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const resendButton = document.getElementById('mcf-resend-button');
        const resendStatus = document.getElementById('mcf-resend-status');

        let remaining = {{ max(0, (int) $cooldownRemaining) }};

        const cooldownMessage = @json(
            __('You can request a new code in :seconds seconds.')
        );

        const readyMessage = @json(
            __('You can request a new verification code now.')
        );

        function renderCountdown() {
            if (remaining <= 0) {
                resendButton.disabled = false;
                resendStatus.textContent = readyMessage;

                return false;
            }

            resendButton.disabled = true;

            resendStatus.textContent = cooldownMessage.replace(
                ':seconds',
                remaining
            );

            return true;
        }

        if (!renderCountdown()) {
            return;
        }

        const timer = setInterval(function () {
            remaining--;

            if (!renderCountdown()) {
                clearInterval(timer);
            }
        }, 1000);
    });
</script>
@endsection
