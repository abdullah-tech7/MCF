<?php
/*
|--------------------------------------------------------------------------
| Supported Languages
|--------------------------------------------------------------------------
|
| Define the languages supported by your application.
|
| The middleware automatically detects the user's preferred browser
| language on the first visit and stores it in the session.
|
| You may freely add or remove locales.
|
*/
declare(strict_types=1);

namespace App\MCF\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['ar', 'en'];

        // 1. Language stored in session
        $locale = Session::get('locale');

        // 2. First visit -> detect browser language
        if (!$locale) {
            $browserLocale = substr($request->getPreferredLanguage($supportedLocales) ?? config('app.locale'), 0, 2);

            $locale = in_array($browserLocale, $supportedLocales)
                ? $browserLocale
                : config('app.locale');

            Session::put('locale', $locale);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
