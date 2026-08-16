<?php

declare(strict_types=1);

namespace App\MCF\Middleware;

use App\MCF\Authentication\Session\SessionSecurityHandler;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class McfSessionSecurityMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $response = app(SessionSecurityHandler::class)->handle($request);

        if ($response !== null) {
            return $response;
        }

        return $next($request);
    }
}
