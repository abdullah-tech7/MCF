<?php

declare(strict_types=1);

use App\MCF\Realtime\Internal\RealtimeAjax;
use Illuminate\Support\Facades\Route;

Route::get(
    '/mcf/realtime/{channel}',
    static function (
        string $channel,
    ) {
        return RealtimeAjax::response(
            channel: $channel,
        );
    },
)->name('mcf.realtime');
