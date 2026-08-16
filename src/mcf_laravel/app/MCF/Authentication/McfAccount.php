<?php

declare(strict_types=1);

namespace App\MCF\Authentication;

use App\MCF\Authentication\Internal\UserService;
use App\MCF\Audit\McfAccountAudit;
use App\MCF\Notification\Internal\NotificationRequest;
use App\MCF\Notification\McfNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\MCF\Notification\NotificationData;


final class McfAccount
{
    /*
    |--------------------------------------------------------------------------
    | Account Deletion
    |--------------------------------------------------------------------------
    */

    private const DELETION_TYPE_SELF = 'self';

    private const DELETION_TYPE_ACTOR = 'actor';

    /**
     * Determines whether self-deleted accounts have
     * a restoration time limit.
     *
     * true  = restoration is limited by restorationWindowDays.
     * false = restoration has no time limit.
     */
    public static bool $selfRestorationTimeout = true;

    /**
     * Determines whether actor-deleted accounts have
     * a restoration time limit.
     *
     * true  = restoration is limited by restorationWindowDays.
     * false = restoration has no time limit.
     */
    public static bool $actorRestorationTimeout = false;

    /**
     * Restoration time window in days.
     *
     * Used only when the corresponding restoration timeout
     * is enabled.
     */
    public static int $restorationWindowDays = 30;

    /**
     * Determines whether account operation notifications
     * should be sent to the affected user's email.
     *
     * true  = send account notifications.
     * false = do not send account notifications.
     */
    public static bool $sendNotifications = true;

    private function __construct()
    {
    }

    /*
    |--------------------------------------------------------------------------
    | Account Status
    |--------------------------------------------------------------------------
    */

    /**
     * Disable a user account.
     *
     * Authorization and any required workflow are the
     * responsibility of the caller.
     */
    public static function disable(
        Model $user,
    ): bool {
        $user->is_active = false;

        if (! $user->save()) {
            return false;
        }

        McfAccountAudit::record(
            action: 'disable',
            target: $user,
            description: 'The user account was disabled by an authorized actor.',
        );

        /*
         * Terminate all active sessions of the disabled user.
         */
        if (! self::forceLogoutSessions($user)) {
            return false;
        }

        self::sendNotification(
            user: $user,
            data: NotificationData::accountDisabled(),
        );

        return true;
    }

    /**
     * Enable a user account.
     *
     * Authorization and any required workflow are the
     * responsibility of the caller.
     */
    public static function enable(
        Model $user,
    ): bool {
        $user->is_active = true;

        if (! $user->save()) {
            return false;
        }

        McfAccountAudit::record(
            action: 'enable',
            target: $user,
            description: 'The user account was enabled by an authorized actor.',
        );

        self::sendNotification(
            user: $user,
            data: NotificationData::accountEnabled(),
        );

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Force Logout
    |--------------------------------------------------------------------------
    */

    /**
     * Force all active sessions of the given user to end.
     *
     * This is different from McfAuth::logout().
     *
     * McfAuth::logout()
     *     Logs out the current authenticated session.
     *
     * forceLogoutSessions()
     *     Forces all sessions belonging to the given user to end.
     */
    public static function forceLogoutSessions(
        Authenticatable $user,
    ): bool {
        $userId = $user->getAuthIdentifier();

        if ($userId === null) {
            return false;
        }

        /*
         * Session termination currently requires
         * Laravel's database session driver.
         */
        if (config('session.driver') !== 'database') {
            return false;
        }

        DB::table(
            config('session.table', 'sessions'),
        )
            ->where('user_id', $userId)
            ->delete();

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete My Account
    |--------------------------------------------------------------------------
    */

    /**
     * Soft delete the currently authenticated user's account.
     *
     * The caller is responsible for any required verification
     * or workflow before calling this method.
     */
    public static function deleteMyAccount(): bool
    {
        $user = McfAuth::user();

        if ($user === null) {
            return false;
        }

        $user->deletion_type = self::DELETION_TYPE_SELF;

        /*
         * No actor performed this deletion.
         */
        $user->deleted_by = null;

        $user->deletion_expires_at =
            self::resolveDeletionExpiration(
                self::DELETION_TYPE_SELF,
            );

        /*
         * Clear previous restoration information.
         */
        $user->restored_at = null;
        $user->restored_by = null;

        if (! $user->save()) {
            return false;
        }

        /*
         * Send the notification before the soft delete.
         *
         * The affected user is still a normal notification
         * recipient at this point.
         */
        self::sendNotification(
            user: $user,
            data: NotificationData::accountDeletedBySelf(),
        );

        if (! $user->delete()) {
            return false;
        }

        /*
         * Record the audit while the authenticated user
         * is still available to McfAccountAudit.
         */
        McfAccountAudit::record(
            action: 'delete',
            target: $user,
            description: 'The user deleted their own account.',
        );

        /*
         * Terminate all sessions after the account
         * has been successfully deleted.
         */
        if (! self::forceLogoutSessions($user)) {
            return false;
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete User Account
    |--------------------------------------------------------------------------
    */

    /**
     * Soft delete another user's account.
     *
     * The caller is responsible for authorization
     * and any required workflow.
     */
    public static function deleteUserAccount(
        Model $user,
    ): bool {
        $user->deletion_type = self::DELETION_TYPE_ACTOR;

        /*
         * Record the authenticated actor responsible
         * for the deletion.
         */
        $actor = McfAuth::user();

        $user->deleted_by = $actor?->getAuthIdentifier();

        $user->deletion_expires_at =
            self::resolveDeletionExpiration(
                self::DELETION_TYPE_ACTOR,
            );

        /*
         * Clear previous restoration information.
         */
        $user->restored_at = null;
        $user->restored_by = null;

        if (! $user->save()) {
            return false;
        }

        /*
         * Send notification before the soft delete.
         */
        self::sendNotification(
            user: $user,
            data: NotificationData::accountDeletedByActor(),
        );

        if (! $user->delete()) {
            return false;
        }

        McfAccountAudit::record(
            action: 'delete',
            target: $user,
            description: 'The user account was deleted by an authorized actor.',
        );

        /*
         * Terminate all sessions of the deleted user.
         */
        if (! self::forceLogoutSessions($user)) {
            return false;
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Restore My Account
    |--------------------------------------------------------------------------
    */

    /**
     * Restore a self-deleted account using its email address.
     *
     * This workflow is intended for unauthenticated users.
     * The caller is responsible for completing the required
     * verification before calling this method.
     */
    public static function restoreMyAccount(
        string $email,
    ): bool {

        $user = UserService::findUserWithTrashedByEmail(
            $email,
        );

        if ($user === null) {
            return false;
        }

        return self::restoreMyAccountInternal(
            $user,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Restore User Account
    |--------------------------------------------------------------------------
    */

    /**
     * Restore another user's deleted account.
     *
     * The caller is responsible for authorization
     * and any required workflow.
     */
    public static function restoreUserAccount(
        Model $user,
    ): bool {
        return self::restoreUserAccountInternal(
            $user,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Restore My Account - Internal
    |--------------------------------------------------------------------------
    */

    /**
     * Perform the actual self account restoration.
     */
    private static function restoreMyAccountInternal(
        Model $user,
    ): bool {
        if (
            $user->deletion_type
            !== self::DELETION_TYPE_SELF
        ) {
            return false;
        }

        if (! self::isRestorationAvailable($user)) {
            return false;
        }

        $user->restored_at = now();

        /*
         * Self restoration has no actor.
         */
        $user->restored_by = null;

        $user->deletion_expires_at = null;

        if (! $user->save()) {
            return false;
        }

        if (! $user->restore()) {
            return false;
        }

        McfAccountAudit::record(
            action: 'restore',
            target: $user,
            description: 'The user restored their own account.',
        );

        self::sendNotification(
            user: $user,
            data: NotificationData::accountRestoredBySelf(),
        );

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Restore User Account - Internal
    |--------------------------------------------------------------------------
    */

    /**
     * Perform the actual actor account restoration.
     */
    private static function restoreUserAccountInternal(
        Model $user,
    ): bool {
        if (
            $user->deletion_type
            !== self::DELETION_TYPE_ACTOR
        ) {
            return false;
        }

        if (! self::isRestorationAvailable($user)) {
            return false;
        }

        $actor = McfAuth::user();

        $user->restored_at = now();

        /*
         * Record the actor who restored the account.
         */
        $user->restored_by = $actor?->getAuthIdentifier();

        $user->deletion_expires_at = null;

        if (! $user->save()) {
            return false;
        }

        if (! $user->restore()) {
            return false;
        }

        McfAccountAudit::record(
            action: 'restore',
            target: $user,
            description: 'The user account was restored by an authorized actor.',
        );

        self::sendNotification(
            user: $user,
            data: NotificationData::accountRestoredByActor(),
        );

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Current User
    |--------------------------------------------------------------------------
    */

    /**
     * Resolve the current authenticated user,
     * including soft-deleted records.
     *
     * This is intentionally kept for authenticated
     * account workflows such as self-delete.
     *
     * It is NOT used by guest account restoration.
     */
    private static function findCurrentUserWithTrashed(): ?Model
    {
        $user = McfAuth::user();

        if ($user === null) {
            return null;
        }

        if (
            method_exists($user, 'trashed')
            && $user->trashed()
        ) {
            return $user;
        }

        $model = UserSettings::model();

        if (! method_exists($model, 'withTrashed')) {
            return null;
        }

        return $model::withTrashed()->find(
            $user->getAuthIdentifier(),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Restoration
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether the deleted account can
     * still be restored.
     *
     * The applicable timeout is determined by
     * deletion_type.
     */
    private static function isRestorationAvailable(
        Model $user,
    ): bool {
        if (
            ! method_exists($user, 'trashed')
            || ! $user->trashed()
        ) {
            return false;
        }

        $deletionType = $user->deletion_type;

        /*
         * Self deletion.
         */
        if (
            $deletionType
            === self::DELETION_TYPE_SELF
        ) {
            if (! self::$selfRestorationTimeout) {
                return true;
            }

            return self::isWithinRestorationWindow(
                $user,
            );
        }

        /*
         * Actor deletion.
         */
        if (
            $deletionType
            === self::DELETION_TYPE_ACTOR
        ) {
            if (! self::$actorRestorationTimeout) {
                return true;
            }

            return self::isWithinRestorationWindow(
                $user,
            );
        }

        return false;
    }

    /**
     * Determine whether the account is still within
     * its configured restoration window.
     */
    private static function isWithinRestorationWindow(
        Model $user,
    ): bool {
        if ($user->deletion_expires_at === null) {
            return false;
        }

        return $user->deletion_expires_at->isFuture();
    }

    /*
    |--------------------------------------------------------------------------
    | Deletion Expiration
    |--------------------------------------------------------------------------
    */

    /**
     * Resolve the expiration date according to
     * the type of deletion.
     *
     * null means there is no restoration timeout.
     */
    private static function resolveDeletionExpiration(
        string $deletionType,
    ): ?Carbon {
        $timeoutEnabled = match ($deletionType) {
            self::DELETION_TYPE_SELF =>
                self::$selfRestorationTimeout,

            self::DELETION_TYPE_ACTOR =>
                self::$actorRestorationTimeout,

            default => false,
        };

        if (! $timeoutEnabled) {
            return null;
        }

        if (self::$restorationWindowDays <= 0) {
            return null;
        }

        return now()->addDays(
            self::$restorationWindowDays,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    /**
     * Send an account notification to the affected user.
     *
     * Notifications are optional and are delivered by email only.
     *
     * Notification failure must not change the result of
     * the account operation itself.
     */
    private static function sendNotification(
        Model $user,
        NotificationData $data,
    ): void {
        if (! self::$sendNotifications) {
            return;
        }

        $userId = $user->getAuthIdentifier();

        if ($userId === null) {
            return;
        }

        try {
            McfNotification::send(
                new NotificationRequest(
                    data: $data,
                    target: 'users',
                    users: [$userId],
                    channels: [
                        'mail',
                    ],
                ),
            );
        } catch (Throwable) {
            /*
             * Notification delivery is a secondary operation.
             *
             * It must not invalidate the account operation
             * that has already succeeded.
             */
        }
    }
}
