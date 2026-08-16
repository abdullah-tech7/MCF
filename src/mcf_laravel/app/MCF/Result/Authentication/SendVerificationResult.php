<?php

declare(strict_types=1);

namespace App\MCF\Result\Authentication;

use App\MCF\Result\McfResult;

final class SendVerificationResult extends McfResult
{
    public const SENT = 'sent';

    public const USER_NOT_FOUND = 'user_not_found';

    public const SAME_TARGET = 'same_target';

    public const THROTTLED = 'throttled';

    public const FAILED = 'failed';
}
