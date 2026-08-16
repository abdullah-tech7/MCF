<?php

declare(strict_types=1);

namespace App\MCF\Middleware;

use App\MCF\AccessControl\Internal\McfAccessHandler;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class McfAccessMiddleware
{
    public function __construct(
        protected McfAccessHandler $handler,
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $response = $this->handler->handle($request);

        if ($response !== null) {
            return $response;
        }

        return $next($request);
    }
}
