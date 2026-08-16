{{--
|--------------------------------------------------------------------------
| MCF Mail - Reset Password Link
|--------------------------------------------------------------------------
|
| Expected Variables:
|
| - resetUrl
|
|--------------------------------------------------------------------------
--}}

<meta charset="UTF-8">

<title>{{ __('Reset Your Password') }}</title>

<style>
    body {
        margin: 0;
        padding: 40px;
        background: #f5f5f5;
        font-family: Arial, sans-serif;
    }

    .mcf-mail-card {
        max-width: 600px;
        margin: auto;
        background: #ffffff;
        border: 1px solid #dddddd;
        border-radius: 8px;
        padding: 40px;
    }

    .mcf-mail-title {
        margin-bottom: 20px;
        text-align: center;
    }

    .mcf-mail-text {
        line-height: 1.7;
        margin-bottom: 30px;
    }

    .mcf-mail-button {
        display: inline-block;
        padding: 12px 24px;
        text-decoration: none;
        border: 1px solid #444;
        border-radius: 6px;
    }

    .mcf-mail-footer {
        margin-top: 35px;
        font-size: 13px;
    }
</style>

<div class="mcf-mail-card">

    <h2 class="mcf-mail-title">
        {{ __('Reset Your Password') }}
    </h2>

    <p class="mcf-mail-text">
        {{ __('You recently requested to reset your password. Click the button below to continue.') }}
    </p>

    <p style="text-align:center;">

        <a
            href="{{ $resetUrl }}"
            class="mcf-mail-button"
        >
            {{ __('Reset Password') }}
        </a>

    </p>

    <p class="mcf-mail-footer">
        {{ __('If you did not request a password reset, you can safely ignore this email.') }}
    </p>

</div>
