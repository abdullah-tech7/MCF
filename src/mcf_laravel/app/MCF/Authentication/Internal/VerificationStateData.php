<?php

declare(strict_types=1);

namespace App\MCF\Authentication\Internal;

use Carbon\Carbon;

final readonly class VerificationStateData
{
    public function __construct(
        public string $target,
        public int $cooldownRemaining,
        public int $sendAttempts,
        public Carbon $expiresAt,
    ) {
    }
}
