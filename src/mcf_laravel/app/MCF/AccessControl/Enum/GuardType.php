<?php

declare(strict_types=1);

namespace App\MCF\AccessControl\Enum;

enum GuardType: string
{
    case ANY = 'any';
    case GUEST = 'guest';
    case AUTH = 'auth';
    case ROLE = 'role';
}
