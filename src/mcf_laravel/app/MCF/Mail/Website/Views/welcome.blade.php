{{--
|--------------------------------------------------------------------------
| MCF Mail - Welcome
|--------------------------------------------------------------------------
|
| Expected Variables:
|
| - name
|
|--------------------------------------------------------------------------
--}}

<meta charset="UTF-8">

<title>{{ __('Welcome') }}</title>

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

    .mcf-mail-footer {
        margin-top: 35px;
        font-size: 13px;
    }
</style>

<div class="mcf-mail-card">

    <h2 class="mcf-mail-title">
        {{ __('Welcome') }}
    </h2>

    <p class="mcf-mail-text">
        {{ __('Welcome, :name! Your account has been created successfully.', ['name' => $name]) }}
    </p>

    <p class="mcf-mail-footer">
        {{ __('Thank you for joining us.') }}
    </p>

</div>
