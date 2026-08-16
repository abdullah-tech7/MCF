<?php

declare (strict_types = 1);

namespace App\MCF\Modules\User\UserManagement\Backend;

use App\MCF\Authentication\McfAccount;
use App\MCF\Authentication\McfAuth;
use App\MCF\Authentication\UserSettings;
use App\MCF\Base\MfcService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

final class UserManagementService extends MfcService
{
    /**
     * Get all users.
     *
     * Includes soft-deleted users so they can be restored
     * from the User Management workflow.
     */
    public function users(): Collection
    {
        $model = UserSettings::model();

        $currentUser = McfAuth::user();

        $query = $model::query()
            ->withTrashed()
            ->with('role');

        if ($currentUser !== null) {
            $query->where(
                $currentUser->getAuthIdentifierName(),
                '!=',
                $currentUser->getAuthIdentifier(),
            );
        }

        return $query->get();
    }

    /**
     * Disable a user account.
     */
    public function disable(
        Model $user,
    ): bool {
        return McfAccount::disable(
            $user,
        );
    }

    /**
     * Enable a user account.
     */
    public function enable(
        Model $user,
    ): bool {
        return McfAccount::enable(
            $user,
        );
    }

    /**
     * Delete another user's account.
     */
    public function delete(
        Model $user,
    ): bool {
        return McfAccount::deleteUserAccount(
            $user,
        );
    }

    /**
     * Restore another user's account.
     */
    public function restore(
        Model $user,
    ): bool {
        return McfAccount::restoreUserAccount(
            $user,
        );
    }
}
