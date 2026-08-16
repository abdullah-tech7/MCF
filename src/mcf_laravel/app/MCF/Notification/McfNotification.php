<?php

declare(strict_types=1);

namespace App\MCF\Notification;

use App\MCF\Authentication\McfAuth;
use App\MCF\Notification\Internal\McfNotificationCenter;
use App\MCF\Notification\Internal\NotificationRequest;
use App\MCF\Sms\McfSms;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use LogicException;
use Throwable;

final class McfNotification
{
    private function __construct()
    {
    }

    /**
     * Send a notification.
     *
     * Invalid or missing users are ignored.
     * Each user and each channel is handled independently.
     *
     * @throws LogicException
     */
    public static function send(
        NotificationRequest $request,
    ): void {
        $request->validate();

        $users = self::resolveUsers(
            request: $request,
        );

        foreach ($users as $user) {
            self::sendToUser(
                user: $user,
                request: $request,
            );
        }
    }

    /**
     * Open the notification center for the authenticated user.
     *
     * @throws LogicException
     */
    public static function notify(): McfNotificationCenter
    {
        $user = McfAuth::user();

        if ($user === null) {
            throw new LogicException(
                'Unable to access the notification center. No authenticated user was found.',
            );
        }

        return new McfNotificationCenter(
            user: $user,
        );
    }

    /**
     * Resolve notification recipients.
     *
     * Missing users are naturally ignored by the query.
     *
     * @return array<int, Authenticatable>
     */
    private static function resolveUsers(
        NotificationRequest $request,
    ): array {
        $userModel = NotificationSettings::userModel();

        if (! class_exists($userModel)) {
            throw new LogicException(
                sprintf(
                    'Notification user model does not exist: %s.',
                    $userModel,
                ),
            );
        }

        if ($request->target === 'all') {
            return $userModel::query()
                ->get()
                ->all();
        }

        if ($request->target === 'users') {
            return $userModel::query()
                ->whereIn('id', $request->users)
                ->get()
                ->all();
        }

        $users = [];

        foreach ($userModel::query()->get() as $user) {
            $role = NotificationSettings::resolveRole(
                $user,
            );

            if (in_array($role, $request->roles, true)) {
                $users[] = $user;
            }
        }

        return $users;
    }

    /**
     * Send all requested channels to one user.
     *
     * Each channel is independent.
     * A failure in one channel does not affect another channel.
     */
    private static function sendToUser(
        Authenticatable $user,
        NotificationRequest $request,
    ): void {
        if ($request->hasChannel('database')) {
            self::attemptChannel(
                channel: 'database',
                user: $user,
                callback: fn () => self::sendDatabase(
                    user: $user,
                    data: $request->data,
                ),
            );
        }

        if ($request->hasChannel('mail')) {
            self::attemptChannel(
                channel: 'mail',
                user: $user,
                callback: fn () => self::sendMail(
                    user: $user,
                    data: $request->data,
                ),
            );
        }

        if ($request->hasChannel('sms')) {
            self::attemptChannel(
                channel: 'sms',
                user: $user,
                callback: fn () => self::sendSms(
                    user: $user,
                    data: $request->data,
                ),
            );
        }
    }

    /**
     * Attempt to send through one channel.
     *
     * Channel failures are logged and ignored so that
     * the remaining channels and users can continue.
     */
    private static function attemptChannel(
        string $channel,
        Authenticatable $user,
        callable $callback,
    ): void {
        try {
            $callback();
        } catch (Throwable $exception) {
            Log::error(
                'MCF notification channel failed.',
                [
                    'channel' => $channel,
                    'user_id' => $user->getAuthIdentifier(),
                    'exception' => $exception,
                ],
            );
        }
    }

    /**
     * Store the notification using Laravel's database notification channel.
     */
    private static function sendDatabase(
        Authenticatable $user,
        NotificationData $data,
    ): void {
        $notification = new class($data) extends Notification
        {
            public function __construct(
                private readonly NotificationData $data,
            ) {
            }

            public function via(
                object $notifiable,
            ): array {
                return ['database'];
            }

            public function toDatabase(
                object $notifiable,
            ): array {
                return $this->data->toArray();
            }
        };

        $user->notify($notification);
    }

    /**
     * Send the notification by email.
     */
    private static function sendMail(
        Authenticatable $user,
        NotificationData $data,
    ): void {
        $mailClass = NotificationSettings::$notificationMail;

        if (! class_exists($mailClass)) {
            throw new LogicException(
                sprintf(
                    'Notification mail class does not exist: %s.',
                    $mailClass,
                ),
            );
        }

        $email = $user->getAttribute('email');

        if (empty($email)) {
            throw new LogicException(
                sprintf(
                    'Unable to send notification email. User %s has no email address.',
                    $user->getAuthIdentifier(),
                ),
            );
        }

        Mail::to($email)->send(
            new $mailClass($data),
        );
    }

    /**
     * Send the notification by SMS.
     */
    private static function sendSms(
        Authenticatable $user,
        NotificationData $data,
    ): void {
        $phone = $user->getAttribute('phone');

        if (empty($phone)) {
            throw new LogicException(
                sprintf(
                    'Unable to send notification SMS. User %s has no phone number.',
                    $user->getAuthIdentifier(),
                ),
            );
        }

        McfSms::send(
            to: $phone,
            message: $data->message,
        );
    }
}
