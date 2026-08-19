<?php

declare(strict_types=1);

namespace App\MCF\Realtime\Internal;

use Illuminate\Http\JsonResponse;

final class RealtimeAjax
{
    private function __construct()
    {
    }

    /**
     * Return the current state of a realtime channel.
     */
    public static function response(
        string $channel,
    ): JsonResponse {
        return response()->json(
            RealtimeRegistry::payload(
                $channel,
            ),
        );
    }
}
