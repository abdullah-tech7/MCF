@extends('Shared::Layout.app')

@section('content')

<style>
    .mcf-restore-account {
        max-width: 520px;
        margin: 60px auto;
        padding: 0 20px;
        font-family: Arial, sans-serif;
    }

    .mcf-restore-account-card {
        padding: 25px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
    }

    .mcf-restore-account h1 {
        margin: 0 0 12px;
        font-size: 25px;
    }

    .mcf-restore-account-description {
        margin: 0 0 20px;
        color: #666;
        line-height: 1.6;
    }

    .mcf-restore-account-info {
        margin-bottom: 20px;
        padding: 13px;
        border: 1px solid #cfe2ff;
        border-radius: 6px;
        background: #f5f9ff;
        color: #084298;
        line-height: 1.6;
    }

    .mcf-restore-account-email {
        margin-bottom: 20px;
        padding: 10px 12px;
        background: #f5f5f5;
        border-radius: 5px;
        word-break: break-word;
    }

    .mcf-restore-account-button {
        width: 100%;
        padding: 11px 15px;
        border: 0;
        border-radius: 5px;
        background: #0d6efd;
        color: #fff;
        cursor: pointer;
        font-size: 15px;
    }

    .mcf-restore-account-button:hover {
        opacity: 0.85;
    }

    .mcf-restore-account-cancel {
        display: inline-block;
        margin-top: 15px;
        color: #555;
        text-decoration: none;
    }
</style>

<div class="mcf-restore-account">

    <div class="mcf-restore-account-card">

        <h1>
            {{ __('Restore Your Account') }}
        </h1>

        <p class="mcf-restore-account-description">
            {{
                __('Your account was deleted by you, but it can still be restored.')
            }}
        </p>

        <div class="mcf-restore-account-info">
            {{
                __('Your account is still within the available restoration period. You can request a verification code to restore it.')
            }}
        </div>

        <div class="mcf-restore-account-email">
            <strong>{{ __('Email') }}</strong>

            <br>

            {{ $email }}
        </div>

        <form
            method="POST"
            action="{{ route(
                'user.auth.restoreAccountPost',
                ['email' => $email],
            ) }}"
        >
            @csrf

            <button
                type="submit"
                class="mcf-restore-account-button"
            >
                {{ __('Send Restoration Code') }}
            </button>
        </form>

        <a
            href="{{ route('user.auth.login') }}"
            class="mcf-restore-account-cancel"
        >
            {{ __('Back to Login') }}
        </a>

    </div>

</div>

@endsection
