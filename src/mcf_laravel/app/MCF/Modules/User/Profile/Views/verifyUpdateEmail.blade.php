@extends('Shared::Layout.app')

@section('content')

{{-- --}}
{{-- --------------------------------------------------------------------- --}}
{{-- MCF Authentication - Verify Update Email --}}
{{-- --------------------------------------------------------------------- --}}
{{-- --}}
{{-- This page verifies the new  email address using the --}}
{{-- verification code sent to the user's new email address. --}}
{{-- --}}
{{-- The new email address is passed through the route. --}}
{{-- The verification state is stored in the database. --}}
{{-- --}}
{{-- --------------------------------------------------------------------- --}}

<style>
    .mcf-profile-form {
        max-width: 520px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .mcf-profile-form-title {
        margin-bottom: 10px;
        font-size: 28px;
        font-weight: 600;
    }

    .mcf-profile-form-description {
        margin-bottom: 24px;
        color: #666;
        line-height: 1.6;
    }

    .mcf-profile-form-card {
        padding: 24px;
        border: 1px solid #ddd;
        border-radius: 10px;
    }

    .mcf-profile-form-field {
        margin-bottom: 18px;
    }

    .mcf-profile-form-field label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .mcf-profile-form-email {
        padding: 10px 12px;
        background: #f5f5f5;
        border-radius: 6px;
        word-break: break-word;
    }

    .mcf-profile-form-field input {
        width: 100%;
        box-sizing: border-box;
        padding: 11px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
    }

    .mcf-profile-form-field input:focus {
        outline: none;
        border-color: #888;
    }

    .mcf-profile-form-error {
        margin-top: 6px;
        color: #c62828;
        font-size: 14px;
    }

    .mcf-profile-form-button {
        width: 100%;
        padding: 11px 16px;
        border: 0;
        border-radius: 6px;
        cursor: pointer;
        font-size: 15px;
    }

    .mcf-profile-form-button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .mcf-profile-form-resend {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .mcf-profile-form-resend-status {
        margin: 10px 0 0;
        text-align: center;
        color: #666;
        font-size: 14px;
    }

    .mcf-profile-form-footer {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-top: 20px;
    }

    .mcf-profile-form-footer a {
        text-decoration: none;
    }
</style>

<div class="mcf-profile-form">

    {{-- ----------------------------------------------------------------- --}}
    {{-- Header --}}
    {{-- ----------------------------------------------------------------- --}}

    <h1 class="mcf-profile-form-title">

        {{ __('Verify New Email') }}

    </h1>

    <p class="mcf-profile-form-description">

        {{ __('Enter the verification code sent to your new email address.') }}

    </p>

    <div class="mcf-profile-form-card">

        {{-- ------------------------------------------------------------- --}}
        {{-- Current Email --}}
        {{-- ------------------------------------------------------------- --}}

        <div class="mcf-profile-form-field">

            <label>

                {{ __('Current Email') }}

            </label>

            <div class="mcf-profile-form-email">

                {{ $oldEmail }}

            </div>

        </div>

        {{-- ------------------------------------------------------------- --}}
        {{-- New Email --}}
        {{-- ------------------------------------------------------------- --}}

        <div class="mcf-profile-form-field">

            <label>

                {{ __('New Email') }}

            </label>

            <div class="mcf-profile-form-email">

                {{ $newEmail }}

            </div>

        </div>

        {{-- ------------------------------------------------------------- --}}
        {{-- Verification Code --}}
        {{-- ------------------------------------------------------------- --}}

        <form method="POST" action="{{ route(
        'user.profile.verifyUpdateEmailPost',
        ['email' => $newEmail],
    ) }}">

            @csrf

            <div class="mcf-profile-form-field">

                <label for="code">

                    {{ __('Verification Code') }}

                </label>

                <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus>

                @error('code')

                <div class="mcf-profile-form-error">

                    {{ $message }}

                </div>

                @enderror

            </div>

            <button type="submit" class="mcf-profile-form-button">

                {{ __('Verify Email') }}

            </button>

        </form>

        {{-- ------------------------------------------------------------- --}}
        {{-- Resend Verification Code --}}
        {{-- ------------------------------------------------------------- --}}

        <div class="mcf-profile-form-resend">

            <form method="POST" action="{{ route('user.profile.updateEmailPost') }}">

                @csrf

                <input type="hidden" name="email" value="{{ $newEmail }}">

                <button id="mcf-resend-button" type="submit" class="mcf-profile-form-button" disabled>

                    {{ __('Resend Verification Code') }}

                </button>

                <p id="mcf-resend-status" class="mcf-profile-form-resend-status"></p>

            </form>

        </div>

    </div>

    {{-- ----------------------------------------------------------------- --}}
    {{-- Navigation --}}
    {{-- ----------------------------------------------------------------- --}}

    <div class="mcf-profile-form-footer">

        <a href="{{ route('user.profile.updateEmail') }}">

            {{ __('Change Email Address') }}

        </a>

        <a href="{{ route('user.profile.index') }}">

            {{ __('Back to Profile') }}

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
