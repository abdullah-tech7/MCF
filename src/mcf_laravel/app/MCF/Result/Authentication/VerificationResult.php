<?php

declare(strict_types=1);

namespace App\MCF\Result\Authentication;

use App\MCF\Result\McfResult;

final class VerificationResult extends McfResult
{
    public const VERIFIED = 'verified';

    public const REQUEST_NOT_FOUND = 'request_not_found';

    public const EXPIRED = 'expired';

    public const INVALID_CODE = 'invalid_code';

    public const INVALID_TOKEN = 'invalid_token';

    public const FAILED = 'failed';
}
