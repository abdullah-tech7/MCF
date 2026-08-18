<?php

declare(strict_types=1);

namespace App\MCF\Queue;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;

final class McfQueueLock
{
    private const NAME = 'mcf:queue:worker:start';

    private const TTL = 10;

    private function __construct()
    {
    }

    public static function acquire(): ?Lock
    {
        $lock = Cache::lock(
            self::NAME,
            self::TTL,
        );

        if (!$lock->get()) {
            return null;
        }

        return $lock;
    }

    public static function release(
        Lock $lock,
    ): void {
        $lock->release();
    }
}
