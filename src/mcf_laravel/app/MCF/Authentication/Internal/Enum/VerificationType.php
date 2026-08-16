<?php

declare(strict_types=1);

namespace App\MCF\Authentication\Internal\Enum;

enum VerificationType: string
{
    case VERIFY_EMAIL = 'verify_email';

    case VERIFY_PHONE = 'verify_phone';

    case RESET_PASSWORD = 'reset_password';

    case UPDATE_EMAIL = 'update_email';

    case UPDATE_PHONE = 'update_phone';

    case DELETE_ACCOUNT = 'delete_account';

    case RESTORE_ACCOUNT = 'restore_account';
}
