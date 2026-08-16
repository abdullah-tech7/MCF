<?php

declare(strict_types=1);

namespace App\MCF\Modules\User\Auth\Backend\Request;

use App\MCF\Base\MfcRequest;

final class RestoreAccountRequest extends MfcRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [];
    }
}
