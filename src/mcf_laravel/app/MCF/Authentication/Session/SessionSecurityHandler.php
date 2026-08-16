<?php

declare(strict_types=1);

namespace App\MCF\Authentication\Session;

use App\MCF\Authentication\McfAuth;
use App\MCF\Authentication\SessionSettings;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SessionSecurityHandler
{
    private const SESSION_STARTED_AT = 'mcf_session_started_at';

    private const SESSION_LAST_ACTIVITY_AT = 'mcf_session_last_activity_at';

    /**
     * Initialize Session Security for the current session.
     *
     * This is called after a successful login.
     */
    public function initialize(): void
    {
        if (! SessionSettings::$securityEnabled) {
            return;
        }

        if (! McfAuth::check()) {
            return;
        }

        if (! $this->isApplicable()) {
            return;
        }

        $now = time();

        session()->put(
            self::SESSION_STARTED_AT,
            $now,
        );

        session()->put(
            self::SESSION_LAST_ACTIVITY_AT,
            $now,
        );
    }

    /**
     * Validate the current session.
     *
     * This is called on every web request by
     * McfSessionSecurityMiddleware.
     */
    public function handle(Request $request): ?Response
    {
        if (! SessionSettings::$securityEnabled) {
            return null;
        }

        if (! McfAuth::check()) {
            return null;
        }

        if (! $this->isApplicable()) {
            return null;
        }

        $session = $request->session();

        $now = time();

        $startedAt = $session->get(
            self::SESSION_STARTED_AT,
        );

        /*
         * Safety fallback:
         *
         * If the session was authenticated before Session Security
         * was initialized, initialize it now.
         */
        if ($startedAt === null) {
            $session->put(
                self::SESSION_STARTED_AT,
                $now,
            );

            $session->put(
                self::SESSION_LAST_ACTIVITY_AT,
                $now,
            );

            return null;
        }

        $lastActivityAt = $session->get(
            self::SESSION_LAST_ACTIVITY_AT,
            $startedAt,
        );

        /*
         * When enabled, timeout is calculated from
         * the last user activity.
         *
         * Otherwise, timeout is calculated from
         * the session creation time.
         */
        $referenceTime = SessionSettings::$timeoutResetOnActivity
            ? $lastActivityAt
            : $startedAt;

        $timeout = SessionSettings::$securityTimeoutSeconds;

        if ($timeout > 0 && ($now - $referenceTime) >= $timeout) {
            McfAuth::logout();

            $session->invalidate();

            return redirect()->route(
                SessionSettings::resolveLoginRouteName(),
            );
        }

        /*
         * Current request is valid activity.
         *
         * Only reset the timeout when activity-based
         * timeout is enabled.
         */
        if (SessionSettings::$timeoutResetOnActivity) {
            $session->put(
                self::SESSION_LAST_ACTIVITY_AT,
                $now,
            );
        }

        return null;
    }

    /**
     * Determine whether Session Security applies
     * to the current authenticated user.
     */
    private function isApplicable(): bool
    {
        $scope = strtolower(
            trim(SessionSettings::$securityScope),
        );

        /*
         * Unknown scope falls back to the default:
         * all authenticated users.
         */
        if ($scope !== 'roles') {
            return true;
        }

        $user = McfAuth::user();

        if ($user === null) {
            return false;
        }

        $userRole = SessionSettings::resolveRole(
            $user,
        );

        return in_array(
            $userRole,
            SessionSettings::securityRoles(),
            true,
        );
    }
}
