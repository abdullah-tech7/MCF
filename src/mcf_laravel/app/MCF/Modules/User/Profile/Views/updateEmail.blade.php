@extends('Shared::Layout.app')

@section('content')

{{-- --}}
{{-- --------------------------------------------------------------------- --}}
{{-- MCF Profile - Update Email --}}
{{-- --------------------------------------------------------------------- --}}
{{-- --}}
{{-- This is a starter view for updating the authenticated user's email. --}}
{{-- --}}
{{-- The current email is displayed for confirmation. --}}
{{-- A verification code will be sent to the new email address. --}}
{{-- --}}
{{-- Validation errors are displayed next to their corresponding fields. --}}
{{-- General success/error messages are handled by the shared Layout. --}}
{{-- --}}
{{-- --------------------------------------------------------------------- --}}

<style>
    .mcf-profile-form {
        max-width: 500px;
        margin: 40px auto;
        padding: 0 20px;
        font-family: Arial, sans-serif;
    }

    .mcf-profile-form-title {
        margin: 0 0 8px;
        font-size: 28px;
    }

    .mcf-profile-form-description {
        margin: 0 0 25px;
        color: #666;
        line-height: 1.5;
    }

    .mcf-profile-form-card {
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
    }

    .mcf-profile-form-field {
        margin-bottom: 18px;
    }

    .mcf-profile-form-field label {
        display: block;
        margin-bottom: 7px;
        font-weight: 600;
    }

    .mcf-profile-form-current-email {
        padding: 10px 12px;
        border: 1px solid #eee;
        border-radius: 5px;
        background: #f7f7f7;
        word-break: break-word;
    }

    .mcf-profile-form-field input {
        width: 100%;
        box-sizing: border-box;
        padding: 10px 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }

    .mcf-profile-form-field input:focus {
        outline: none;
        border-color: #999;
    }

    .mcf-profile-form-error {
        margin-top: 6px;
        color: #b42318;
        font-size: 14px;
    }

    .mcf-profile-form-button {
        padding: 10px 16px;
        border: 0;
        border-radius: 5px;
        cursor: pointer;
    }

    .mcf-profile-form-footer {
        margin-top: 20px;
    }

    .mcf-profile-form-footer a {
        color: inherit;
        text-decoration: none;
    }

    .mcf-profile-form-footer a:hover {
        text-decoration: underline;
    }
</style>

<div class="mcf-profile-form">

    <h1 class="mcf-profile-form-title">

        {{ __('Update Email') }}

    </h1>

    <p class="mcf-profile-form-description">

        {{ __('Enter your new email address. A verification code will be sent to it.') }}

    </p>

    <div class="mcf-profile-form-card">

        <form method="POST" action="{{ route('user.profile.updateEmailPost') }}">

            @csrf

            <div class="mcf-profile-form-field">

                <label>

                    {{ __('Current Email') }}

                </label>

                <div class="mcf-profile-form-current-email">

                    {{ $user->email }}

                </div>

            </div>

            <div class="mcf-profile-form-field">

                <label for="email">

                    {{ __('New Email') }}

                </label>

                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus>

                @error('email')

                <div class="mcf-profile-form-error">

                    {{ $message }}

                </div>

                @enderror

            </div>


            <button type="submit" class="mcf-profile-form-button">
                {{ __('Send Verification Code') }}
            </button>


        </form>

    </div>

    <div class="mcf-profile-form-footer">

        <a href="{{ route('user.profile.index') }}">

            {{ __('Back to Profile') }}

        </a>

    </div>

</div>

@endsection
