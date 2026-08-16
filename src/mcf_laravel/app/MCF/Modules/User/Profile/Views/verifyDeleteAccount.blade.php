@extends('Shared::Layout.app')

@section('content')

<style>
    .mcf-delete-verification {
        max-width: 520px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .mcf-delete-verification-title {
        margin-bottom: 10px;
        font-size: 28px;
        font-weight: 600;
    }

    .mcf-delete-verification-description {
        margin-bottom: 24px;
        color: #666;
        line-height: 1.6;
    }

    .mcf-delete-verification-card {
        padding: 24px;
        border: 1px solid #ddd;
        border-radius: 10px;
    }

    .mcf-delete-verification-field {
        margin-bottom: 18px;
    }

    .mcf-delete-verification-field label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .mcf-delete-verification-email {
        padding: 10px 12px;
        background: #f5f5f5;
        border-radius: 6px;
        word-break: break-word;
    }

    .mcf-delete-verification-field input {
        width: 100%;
        box-sizing: border-box;
        padding: 11px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
    }

    .mcf-delete-verification-field input:focus {
        outline: none;
        border-color: #888;
    }

    .mcf-delete-verification-error {
        margin-top: 6px;
        color: #c62828;
        font-size: 14px;
    }

    .mcf-delete-verification-warning {
        margin-bottom: 20px;
        padding: 12px;
        border: 1px solid #f1caca;
        border-radius: 6px;
        background: #fff7f7;
        color: #a00000;
        line-height: 1.5;
    }

    .mcf-delete-verification-button {
        width: 100%;
        padding: 11px 16px;
        border: 0;
        border-radius: 6px;
        cursor: pointer;
        font-size: 15px;
        background: #dc3545;
        color: #fff;
    }

    .mcf-delete-verification-button:hover {
        opacity: 0.9;
    }

    .mcf-delete-verification-button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .mcf-delete-verification-resend {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .mcf-delete-verification-resend-status {
        margin: 10px 0 0;
        text-align: center;
        color: #666;
        font-size: 14px;
    }

    .mcf-delete-verification-footer {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-top: 20px;
    }

    .mcf-delete-verification-footer a {
        text-decoration: none;
    }
</style>

<div class="mcf-delete-verification">

    <h1 class="mcf-delete-verification-title">

        {{ __('Delete Account') }}

    </h1>

    <p class="mcf-delete-verification-description">

        {{
            __('A verification code has been sent to your email address. Enter the verification code to confirm that you want to delete your account.')
        }}

    </p>

    <div class="mcf-delete-verification-card">

        {{-- ------------------------------------------------------------- --}}
        {{-- Warning --}}
        {{-- ------------------------------------------------------------- --}}

        <div class="mcf-delete-verification-warning">

            {{
                __('This action will delete your account and sign you out of all active sessions.')
            }}

        </div>

        {{-- ------------------------------------------------------------- --}}
        {{-- Email --}}
        {{-- ------------------------------------------------------------- --}}

        <div class="mcf-delete-verification-field">

            <label>

                {{ __('Email') }}

            </label>

            <div class="mcf-delete-verification-email">

                {{ $email }}

            </div>

        </div>

        {{-- ------------------------------------------------------------- --}}
        {{-- Verification Code --}}
        {{-- ------------------------------------------------------------- --}}

        <form
            method="POST"
            action="{{ route('user.profile.verifyDeleteAccountPost') }}"
        >

            @csrf

            <div class="mcf-delete-verification-field">

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

                    <div class="mcf-delete-verification-error">

                        {{ $message }}

                    </div>

                @enderror

            </div>

            <button
                type="submit"
                class="mcf-delete-verification-button"
            >

                {{ __('Confirm Account Deletion') }}

            </button>

        </form>

        {{-- ------------------------------------------------------------- --}}
        {{-- Resend Verification Code --}}
        {{-- ------------------------------------------------------------- --}}

        <div class="mcf-delete-verification-resend">

            <form
                method="POST"
                action="{{ route('user.profile.resendDeleteAccountVerification') }}"
            >

                @csrf

                <button
                    id="mcf-resend-button"
                    type="submit"
                    class="mcf-delete-verification-button"
                    disabled
                >

                    {{ __('Resend Verification Code') }}

                </button>

                <p
                    id="mcf-resend-status"
                    class="mcf-delete-verification-resend-status"
                ></p>

            </form>

        </div>

    </div>

    {{-- ------------------------------------------------------------- --}}
    {{-- Navigation --}}
    {{-- ------------------------------------------------------------- --}}

    <div class="mcf-delete-verification-footer">

        <a href="{{ route('user.profile.index') }}">

            {{ __('Back to Profile') }}

        </a>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const resendButton =
            document.getElementById('mcf-resend-button');

        const resendStatus =
            document.getElementById('mcf-resend-status');

        let remaining =
            {{ max(0, (int) $cooldownRemaining) }};

        const cooldownMessage = @json(
            __('You can request a new code in :seconds seconds.')
        );

        const readyMessage = @json(
            __('You can request a new verification code now.')
        );

        function renderCountdown() {

            if (remaining <= 0) {

                resendButton.disabled = false;

                resendStatus.textContent =
                    readyMessage;

                return false;
            }

            resendButton.disabled = true;

            resendStatus.textContent =
                cooldownMessage.replace(
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
