<?php

declare (strict_types = 1);

namespace App\MCF\Notification;

final class NotificationData
{
    public function __construct(
        public readonly string $message,
        public readonly ?string $title = null,
        public readonly ?string $url = null,
    ) {
    }

    /**
     * Welcome notification.
     */
    public static function welcome(
        string $name,
    ): self {
        $title = __('Welcome!');

        $message = __('Welcome, :name! Thank you for joining us.', [
            'name' => $name,
        ]);

        return new self(
            message: $message,
            title: $title,
        );
    }

    /**
     * Notify the user that their email address was updated.
     */
    public static function emailUpdated(
        string $email,
    ): self {
        $title = __('Email address updated');

        $message = __(
            'Your email address was changed to :email. If you did not make this change, please contact support.',
            [
                'email' => $email,
            ],
        );

        $url = route('user.profile.index');

        return new self(
            message: $message,
            title: $title,
            url: $url,
        );
    }

    /**
     * Notify the user that their password was updated.
     */
    public static function passwordUpdated(): self
    {
        $title = __('Password updated');

        $message = __(
            'Your password was changed. If you did not make this change, please contact support.',
        );

        $url = route('user.profile.index');

        return new self(
            message: $message,
            title: $title,
            url: $url,
        );
    }

    /**
     * Notify the user that their phone number was updated.
     */
    public static function phoneUpdated(
        string $phone,
    ): self {
        $title = __('Phone number updated');

        $message = __(
            'Your phone number was changed to :phone. If you did not make this change, please contact support.',
            [
                'phone' => $phone,
            ],
        );

        $url = route('user.profile.index');

        return new self(
            message: $message,
            title: $title,
            url: $url,
        );
    }

    /**
     * Notify the user that their email address was verified.
     */
    public static function emailVerified(): self
    {
        $title = __('Email verified');

        $message = __(
            'Your email address has been successfully verified.',
        );

        $url = route('user.profile.index');

        return new self(
            message: $message,
            title: $title,
            url: $url,
        );
    }

    /**
     * Notify the user that their phone number was verified.
     */
    public static function phoneVerified(): self
    {
        $title = __('Phone number verified');

        $message = __(
            'Your phone number has been successfully verified.',
        );

        $url = route('user.profile.index');

        return new self(
            message: $message,
            title: $title,
            url: $url,
        );
    }

    /**
     * Notify the user about a successful login.
     */
    public static function loginSuccessful(): self
    {
        $title = __('Login successful');

        $message = __(
            'You have successfully logged in to your account.',
        );

        $url = route('user.profile.index');

        return new self(
            message: $message,
            title: $title,
            url: $url,
        );
    }

    /**
     * Notify about a user's profile update.
     *
     * This example intentionally uses a route parameter
     * to verify dynamic URL generation.
     */
    public static function profileUpdated(
        int $userId,
    ): self {
        $title = __('Profile updated');

        $message = __(
            'The user profile has been updated successfully.',
        );

        $url = route(
            'user.profile.view',
            ['id' => $userId],
        );

        return new self(
            message: $message,
            title: $title,
            url: $url,
        );
    }

/**
 * Notify the user that their account was disabled.
 */
    public static function accountDisabled(): self
    {
        $title = __('Account disabled');

        $message = __(
            'Your account has been disabled by the administration. If you believe this was done by mistake, please contact support.',
        );

        return new self(
            message: $message,
            title: $title,
        );
    }

/**
 * Notify the user that their account was enabled.
 */
    public static function accountEnabled(): self
    {
        $title = __('Account enabled');

        $message = __(
            'Your account has been enabled by the administration and you can now sign in again.',
        );

        return new self(
            message: $message,
            title: $title,
        );
    }

/**
 * Notify the user that their account was deleted by themselves.
 */
    public static function accountDeletedBySelf(): self
    {
        $title = __('Account deleted');

        $message = __(
            'Your account has been deleted as requested. You can restore your account within the available restoration period.',
        );

        return new self(
            message: $message,
            title: $title,
        );
    }

/**
 * Notify the user that their account was deleted by the administration.
 */
    public static function accountDeletedByActor(): self
    {
        $title = __('Account deleted');

        $message = __(
            'Your account has been deleted by the administration. If you believe this was done by mistake, please contact support.',
        );

        return new self(
            message: $message,
            title: $title,
        );
    }

/**
 * Notify the user that their account was restored by themselves.
 */
    public static function accountRestoredBySelf(): self
    {
        $title = __('Account restored');

        $message = __(
            'Your account has been restored successfully. You can now sign in again.',
        );

        return new self(
            message: $message,
            title: $title,
        );
    }

/**
 * Notify the user that their account was restored by the administration.
 */
    public static function accountRestoredByActor(): self
    {
        $title = __('Account restored');

        $message = __(
            'Your account has been restored by the administration. You can now sign in again.',
        );

        return new self(
            message: $message,
            title: $title,
        );
    }

/**
 * Convert notification data to an array for storage.
 */
    public function toArray(): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'url'     => $this->url,
        ];
    }

    /**
     * Create notification data from stored data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            message: $data['message'],
            title: $data['title'] ?? null,
            url: $data['url'] ?? null,
        );
    }
}
