<?php

declare(strict_types=1);

namespace App\MCF\Result\Authentication;

use App\MCF\Result\McfResult;

final class ChangePasswordResult extends McfResult
{
    public const UPDATED = 'updated';

    public const INVALID_CURRENT_PASSWORD = 'invalid_current_password';

    public const SAME_PASSWORD = 'same_password';

    public const FAILED = 'failed';
}
