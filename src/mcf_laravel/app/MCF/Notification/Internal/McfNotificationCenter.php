<?php
declare (strict_types = 1);
namespace App\MCF\Notification\Internal;

use App\MCF\Notification\NotificationData;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use LogicException;

final class McfNotificationCenter
{
    public function __construct(private readonly Authenticatable $user)
    {
    }
    /** * Get all notifications. * * This keeps Laravel's original DatabaseNotification * objects and behavior. */
    public function all(): DatabaseNotificationCollection
    {
        return $this->user->notifications;
    }

    /** * Get unread notifications. */
    public function unread(): DatabaseNotificationCollection
    {
        return $this->user->unreadNotifications;
    }

    /** * Get read notifications. */
    public function read(): DatabaseNotificationCollection
    {
        return $this->user->readNotifications;
    }

    /** * Get the number of unread notifications. */public function count(): int
    {
        return $this->user->unreadNotifications()->count();
    }

    /** * Find one notification. * * The returned object is Laravel's original * DatabaseNotification instance. */public function find(
        string $notificationId
    ): ?DatabaseNotification {
        return $this->user
            ->notifications()
            ->whereKey($notificationId)
            ->first();
    }

    /** * Convert a Laravel DatabaseNotification data payload * into the MCF NotificationData object. * * Laravel's original $notification->data remains * available and unchanged. * * @throws LogicException */public function dataObject(
        DatabaseNotification $notification
    ): NotificationData {
        if (
            (string) $notification->notifiable_id !==
            (string) $this->user->getAuthIdentifier()
        ) {
            throw new LogicException(
                "The notification does not belong to the authenticated user."
            );
        }
        if (! is_array($notification->data)) {
            throw new LogicException(
                sprintf(
                    'Unable to convert notification "%s" data to NotificationData. Expected an array.',
                    $notification->id
                )
            );
        }
        return NotificationData::fromArray($notification->data);
    }

    /** * Mark one notification as read. * * @throws LogicException */
    public function markAsRead(
        string $notificationId
    ): void {
        $notification = $this->find(notificationId: $notificationId);
        if ($notification === null) {
            throw new LogicException(
                sprintf('Notification "%s" was not found.', $notificationId)
            );
        }
        $notification->markAsRead();
    }

    /** * Mark all unread notifications as read. */
    public function markAllAsRead(): void
    {
        $this->user->unreadNotifications()->update(["read_at" => now()]);
    }

    /** * Delete one notification. * * @throws LogicException */
    public function delete(
        string $notificationId
    ): void {
        $notification = $this->find(notificationId: $notificationId);
        if ($notification === null) {
            throw new LogicException(
                sprintf('Notification "%s" was not found.', $notificationId)
            );
        }
        $notification->delete();
    }
    /** * Delete all notifications. */
    public function deleteAll(): void
    {
        $this->user->notifications()->delete();
    }
}
