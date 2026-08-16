<?php

declare(strict_types=1);

namespace App\MCF\Modules\User\UserManagement\Backend;

use App\MCF\Base\MfcController;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class UserManagementController extends MfcController
{
    public function __construct(
        private readonly UserManagementService $service,
    ) {
    }

    public function index(): View
    {
        $users = $this->service->users();

        return view(
            'User::UserManagement.index',
            compact('users'),
        );
    }

    public function disable(
        User $user,
    ): RedirectResponse {
        $success = $this->service->disable($user);

        return back()->with(
            $success ? 'success' : 'error',
            __(
                $success
                    ? 'User account disabled successfully.'
                    : 'Failed to disable user account.',
            ),
        );
    }

    public function enable(
        User $user,
    ): RedirectResponse {
        $success = $this->service->enable($user);

        return back()->with(
            $success ? 'success' : 'error',
            __(
                $success
                    ? 'User account enabled successfully.'
                    : 'Failed to enable user account.',
            ),
        );
    }

    public function delete(
        User $user,
    ): RedirectResponse {
        $success = $this->service->delete($user);

        return back()->with(
            $success ? 'success' : 'error',
            __(
                $success
                    ? 'User account deleted successfully.'
                    : 'Failed to delete user account.',
            ),
        );
    }

    public function restore(
        User $user,
    ): RedirectResponse {
        $success = $this->service->restore($user);

        return back()->with(
            $success ? 'success' : 'error',
            __(
                $success
                    ? 'User account restored successfully.'
                    : 'Failed to restore user account.',
            ),
        );
    }
}
