<?php

declare(strict_types=1);

namespace App\MCF\Modules\Shared\RealtimeTest\Backend;

use App\MCF\Base\MfcController;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class RealtimeTestController extends MfcController
{
    public function __construct(
        private readonly RealtimeTestService $service,
    ) {
    }

    public function index(): View
    {
        return view(
            'Shared::RealtimeTest.index',
            [
                'state' => $this->service->state(),
            ],
        );
    }

    public function addNotification(): RedirectResponse
    {
        $this->service->addRandomNotification();

        return back();
    }

    public function markAsRead(
        string $notification,
    ): RedirectResponse {
        $this->service->markAsRead(
            $notification,
        );

        return back();
    }

    public function markAllAsRead(): RedirectResponse
    {
        $this->service->markAllAsRead();

        return back();
    }

    public function markAllAsUnread(): RedirectResponse
    {
        $this->service->markAllAsUnread();

        return back();
    }

    public function delete(
        string $notification,
    ): RedirectResponse {
        $this->service->delete(
            $notification,
        );

        return back();
    }

    public function deleteAll(): RedirectResponse
    {
        $this->service->deleteAll();

        return back();
    }
}
