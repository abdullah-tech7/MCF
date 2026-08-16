{{--
|--------------------------------------------------------------------------
| MCF Mail - Delete Account Code
|--------------------------------------------------------------------------
|
| Expected Variables:
|
| - code
|
|--------------------------------------------------------------------------
--}}

<meta charset="UTF-8">

<title>{{ __('Confirm Account Deletion') }}</title>

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
        margin-bottom: 20px;
    }

    .mcf-mail-warning {
        margin: 25px 0;
        padding: 14px;
        border: 1px solid #f1caca;
        border-radius: 6px;
        background: #fff7f7;
        color: #a00000;
        line-height: 1.7;
    }

    .mcf-mail-code {
        margin: 30px 0;
        padding: 16px;
        text-align: center;
        font-size: 32px;
        font-weight: bold;
        letter-spacing: 8px;
        border: 1px solid #444;
        border-radius: 6px;
    }

    .mcf-mail-footer {
        margin-top: 35px;
        font-size: 13px;
        line-height: 1.7;
    }
</style>

<div class="mcf-mail-card">

    <h2 class="mcf-mail-title">
        {{ __('Confirm Account Deletion') }}
    </h2>

    <p class="mcf-mail-text">
        {{
            __('Use the verification code below to confirm that you want to delete your account.')
        }}
    </p>

    <div class="mcf-mail-warning">
        {{
            __('Deleting your account will sign you out of all active sessions.')
        }}
    </div>

    <div class="mcf-mail-code">
        {{ $code }}
    </div>

    <p class="mcf-mail-footer">
        {{
            __('If you did not request account deletion, you can safely ignore this email.')
        }}
    </p>

</div>
