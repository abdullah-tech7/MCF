<?php

declare(strict_types=1);

namespace App\MCF\Authentication\Internal\Enum;

enum VerificationChannel: string
{
    case EMAIL = 'email';
    case PHONE = 'phone';
}
