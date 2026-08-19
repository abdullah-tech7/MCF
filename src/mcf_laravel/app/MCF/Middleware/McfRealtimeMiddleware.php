<?php

declare(strict_types=1);

namespace App\MCF\Middleware;

use App\MCF\Realtime\Internal\RealtimeRuntime;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class McfRealtimeMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $response = $next($request);

        if (! $this->shouldProcess($response)) {
            return $response;
        }

        $content = $response->getContent();

        if (
            $content === false ||
            $content === ''
        ) {
            return $response;
        }

        /*
         * Only process pages that actually use
         * the public MCF realtime API.
         */
        if (
            ! str_contains(
                $content,
                'MCF.realtime(',
            )
        ) {
            return $response;
        }

        /*
         * Find the <script> tag that contains
         * the MCF.realtime() call.
         */
        $scriptPosition = $this->findScriptPosition(
            content: $content,
        );

        if ($scriptPosition === null) {
            return $response;
        }

        /*
         * Inject the runtime BEFORE the script that
         * calls MCF.realtime().
         */
        $content =
            substr(
                $content,
                0,
                $scriptPosition,
            )
            . RealtimeRuntime::render()
            . substr(
                $content,
                $scriptPosition,
            );

        $response->setContent(
            $content,
        );

        return $response;
    }

    /**
     * Determine whether the response should be processed.
     */
    private function shouldProcess(
        Response $response,
    ): bool {
        if (! $response->isSuccessful()) {
            return false;
        }

        $contentType =
            $response->headers->get(
                'Content-Type',
            );

        if ($contentType === null) {
            return false;
        }

        return str_contains(
            strtolower($contentType),
            'text/html',
        );
    }

    /**
     * Find the opening <script> tag that contains
     * the MCF.realtime() call.
     */
    private function findScriptPosition(
        string $content,
    ): ?int {
        $realtimePosition = strpos(
            $content,
            'MCF.realtime(',
        );

        if ($realtimePosition === false) {
            return null;
        }

        /*
         * Find the last <script tag before
         * MCF.realtime().
         */
        $scriptPosition = strrpos(
            substr(
                $content,
                0,
                $realtimePosition,
            ),
            '<script',
        );

        if ($scriptPosition === false) {
            return null;
        }

        return $scriptPosition;
    }
}
