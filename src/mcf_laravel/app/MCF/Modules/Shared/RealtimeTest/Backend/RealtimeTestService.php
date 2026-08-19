<?php

declare(strict_types=1);

namespace App\MCF\Modules\Shared\RealtimeTest\Backend;

use App\MCF\Authentication\McfAuth;
use App\MCF\Notification\Internal\NotificationRequest;
use App\MCF\Notification\McfNotification;
use App\MCF\Notification\NotificationData;

final class RealtimeTestService
{
    /**
     * Get the current unread notification state.
     *
     * @return array{
     *     count: int,
     *     notifications: array<int, array{
     *         id: string,
     *         title: string|null,
     *         message: string,
     *         url: string|null,
     *         created_at: string|null
     *     }>
     * }
     */
    public function state(): array
    {
        $center = McfNotification::notify();

        return [
            'count' => $center->count(),

            'notifications' => $center
                ->unread()
                ->map(
                    static function ($notification): array {
                        $data = is_array($notification->data)
                            ? $notification->data
                            : [];

                        return [
                            'id' => (string) $notification->id,

                            'title' => isset($data['title'])
                                ? (string) $data['title']
                                : null,

                            'message' => isset($data['message'])
                                ? (string) $data['message']
                                : '',

                            'url' => isset($data['url'])
                                ? (string) $data['url']
                                : null,

                            'created_at' => $notification
                                ->created_at
                                ?->toISOString(),
                        ];
                    },
                )
                ->values()
                ->all(),
        ];
    }

    /**
     * Add a random notification for the
     * authenticated user.
     */
    public function addRandomNotification(): void
    {
        $number = random_int(
            1000,
            9999,
        );

        $notification = new NotificationData(
            title: 'Realtime Test Notification',

            message: sprintf(
                'Realtime test message number %d.',
                $number,
            ),

            url: route(
                'user.profile.index',
            ),
        );

        $request = new NotificationRequest(
            data: $notification,

            target: 'users',

            users: [
                McfAuth::id(),
            ],

            channels: [
                'database',
            ],
        );

        McfNotification::send(
            $request,
        );
    }

    /**
     * Mark one notification as read.
     */
    public function markAsRead(
        string $notificationId,
    ): void {
        McfNotification::notify()
            ->markAsRead(
                $notificationId,
            );
    }

    /**
     * Mark selected notifications as read.
     *
     * @param array<int, string> $notificationIds
     */
    public function markSelectedAsRead(
        array $notificationIds,
    ): void {
        $center = McfNotification::notify();

        foreach ($notificationIds as $notificationId) {
            $center->markAsRead(
                (string) $notificationId,
            );
        }
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(): void
    {
        McfNotification::notify()
            ->markAllAsRead();
    }

    /**
     * Mark all notifications as unread.
     *
     * This is used by the test page to restore all
     * existing notifications to the unread state.
     */
    public function markAllAsUnread(): void
    {
        McfNotification::notify()
            ->markAllAsUnread();
    }

    /**
     * Delete one notification.
     */
    public function delete(
        string $notificationId,
    ): void {
        McfNotification::notify()
            ->delete(
                $notificationId,
            );
    }

    /**
     * Delete selected notifications.
     *
     * @param array<int, string> $notificationIds
     */
    public function deleteSelected(
        array $notificationIds,
    ): void {
        $center = McfNotification::notify();

        foreach ($notificationIds as $notificationId) {
            $center->delete(
                (string) $notificationId,
            );
        }
    }

    /**
     * Delete all notifications.
     */
    public function deleteAll(): void
    {
        McfNotification::notify()
            ->deleteAll();
    }
}
