<?php

declare(strict_types=1);

namespace App\MCF\Authentication\Internal\Enum;

enum VerificationMethod: string
{
    case CODE = 'code';

    case LINK = 'link';
}
