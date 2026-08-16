<?php

declare(strict_types=1);

namespace App\MCF\Result\Authentication;

use App\MCF\Result\McfResult;

final class UpdateResult extends McfResult
{
    public const UPDATED = 'updated';

    public const USER_NOT_FOUND = 'user_not_found';

    public const FAILED = 'failed';
}
