@extends('Shared::Layout.app')

@section('content')

<style>
    .mcf-restore-verification {
        max-width: 520px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .mcf-restore-verification-title {
        margin-bottom: 10px;
        font-size: 28px;
        font-weight: 600;
    }

    .mcf-restore-verification-description {
        margin-bottom: 24px;
        color: #666;
        line-height: 1.6;
    }

    .mcf-restore-verification-card {
        padding: 24px;
        border: 1px solid #ddd;
        border-radius: 10px;
    }

    .mcf-restore-verification-field {
        margin-bottom: 18px;
    }

    .mcf-restore-verification-field label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .mcf-restore-verification-email {
        padding: 10px 12px;
        background: #f5f5f5;
        border-radius: 6px;
        word-break: break-word;
    }

    .mcf-restore-verification-field input {
        width: 100%;
        box-sizing: border-box;
        padding: 11px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
    }

    .mcf-restore-verification-button {
        width: 100%;
        padding: 11px 16px;
        border: 0;
        border-radius: 6px;
        cursor: pointer;
        font-size: 15px;
    }

    .mcf-restore-verification-resend {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .mcf-restore-verification-status {
        margin: 10px 0 0;
        text-align: center;
        color: #666;
        font-size: 14px;
    }

    .mcf-restore-verification-footer {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-top: 20px;
    }

    .mcf-restore-verification-footer a {
        text-decoration: none;
    }
</style>

<div class="mcf-restore-verification">

    <h1 class="mcf-restore-verification-title">
        {{ __('Verify Account Restoration') }}
    </h1>

    <p class="mcf-restore-verification-description">
        {{
            __('Enter the verification code sent to your email address to restore your account.')
        }}
    </p>

    <div class="mcf-restore-verification-card">

        <div class="mcf-restore-verification-field">

            <label>
                {{ __('Email') }}
            </label>

            <div class="mcf-restore-verification-email">
                {{ $email }}
            </div>

        </div>

        <form
            method="POST"
            action="{{ route(
                'user.auth.verifyRestoreAccountPost',
                ['email' => $email],
            ) }}"
        >
            @csrf

            <div class="mcf-restore-verification-field">

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
                    required
                >

                @error('code')

                    <div style="margin-top: 6px; color: #c62828;">
                        {{ $message }}
                    </div>

                @enderror

            </div>

            <button
                type="submit"
                class="mcf-restore-verification-button"
            >
                {{ __('Restore Account') }}
            </button>

        </form>

        <div class="mcf-restore-verification-resend">

            <form
                method="POST"
                action="{{ route(
                    'user.auth.resendRestoreAccountVerification',
                    ['email' => $email],
                ) }}"
            >
                @csrf

                <button
                    id="mcf-resend-button"
                    type="submit"
                    class="mcf-restore-verification-button"
                    disabled
                >
                    {{ __('Resend Restoration Code') }}
                </button>

                <p
                    id="mcf-resend-status"
                    class="mcf-restore-verification-status"
                ></p>

            </form>

        </div>

    </div>

    <div class="mcf-restore-verification-footer">

        <a href="{{ route('user.auth.restoreAccount', ['email' => $email]) }}">
            {{ __('Back to Restore Account') }}
        </a>

        <a href="{{ route('user.auth.login') }}">
            {{ __('Back to Login') }}
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
