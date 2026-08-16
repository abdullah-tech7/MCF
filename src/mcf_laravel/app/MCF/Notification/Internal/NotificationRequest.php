<?php

declare(strict_types=1);

namespace App\MCF\Notification\Internal;

use App\MCF\Notification\NotificationData;
use LogicException;

final class NotificationRequest
{
    private const TARGETS = [
        'all',
        'roles',
        'users',
    ];

    private const CHANNELS = [
        'database',
        'mail',
        'sms',
    ];

    /**
     * @param array<int, int|string> $roles
     * @param array<int, int|string> $users
     * @param array<int, string> $channels
     */
    public function __construct(
        public readonly NotificationData $data,
        public readonly string $target,
        public readonly array $roles = [],
        public readonly array $users = [],
        public readonly array $channels = [],
    ) {
    }

    /**
     * Validate the notification request.
     *
     * @throws LogicException
     */
    public function validate(): void
    {
        $this->validateTarget();

        if ($this->target === 'roles') {
            $this->validateRoles();
        }

        if ($this->target === 'users') {
            $this->validateUsers();
        }

        $this->validateChannels();
    }

    /**
     * Validate the notification target.
     *
     * @throws LogicException
     */
    private function validateTarget(): void
    {
        if (! in_array($this->target, self::TARGETS, true)) {
            throw new LogicException(
                sprintf(
                    'Invalid notification target "%s". Supported targets: all, roles, users.',
                    $this->target,
                ),
            );
        }
    }

    /**
     * Validate role identifiers.
     *
     * @throws LogicException
     */
    private function validateRoles(): void
    {
        foreach ($this->roles as $role) {
            if (! is_int($role) && ! is_string($role)) {
                throw new LogicException(
                    'Notification roles must contain integers or strings only.',
                );
            }
        }
    }

    /**
     * Validate user identifiers.
     *
     * @throws LogicException
     */
    private function validateUsers(): void
    {
        foreach ($this->users as $user) {
            if (! is_int($user) && ! is_string($user)) {
                throw new LogicException(
                    'Notification users must contain integers or strings only.',
                );
            }
        }
    }

    /**
     * Validate notification channels.
     *
     * @throws LogicException
     */
    private function validateChannels(): void
    {
        foreach ($this->channels as $channel) {
            if (! is_string($channel)) {
                throw new LogicException(
                    'Notification channels must contain strings only.',
                );
            }

            if (! in_array($channel, self::CHANNELS, true)) {
                throw new LogicException(
                    sprintf(
                        'Invalid notification channel "%s". Supported channels: database, mail, sms.',
                        $channel,
                    ),
                );
            }
        }
    }

    /**
     * Determine whether a channel was requested.
     */
    public function hasChannel(string $channel): bool
    {
        return in_array(
            $channel,
            $this->channels,
            true,
        );
    }
}
