<?php

declare(strict_types=1);

namespace App\MCF\Result\Authentication;

use App\MCF\Result\McfResult;

final class AuthenticationResult extends McfResult
{
    public const SUCCESS = 'success';

    public const INVALID_CREDENTIALS = 'invalid_credentials';

    public const NEED_EMAIL_VERIFICATION = 'need_email_verification';

    public const NEED_PHONE_VERIFICATION = 'need_phone_verification';

    public const NOT_ALLOWED = 'not_allowed';

    /**
     * The account was deleted by the account owner
     * and can still be restored.
     */
    public const DELETED_BY_SELF_RESTORABLE = 'deleted_by_self_restorable';

    /**
     * The account was deleted by the account owner
     * and its restoration period has expired.
     */
    public const DELETED_BY_SELF_EXPIRED = 'deleted_by_self_expired';

    /**
     * The account was deleted by another authorized actor.
     */
    public const DELETED_BY_ACTOR = 'deleted_by_actor';

    public const THROTTLED = 'throttled';

    public const FAILED = 'failed';
}
