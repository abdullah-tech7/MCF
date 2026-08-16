<?php

declare(strict_types=1);

namespace App\MCF\Authentication\Internal\Enum;

enum VerificationRequirement: string
{
    case NONE = 'none';

    case EMAIL = 'email';

    case PHONE = 'phone';
}
