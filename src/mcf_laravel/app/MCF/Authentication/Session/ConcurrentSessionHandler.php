<?php

declare(strict_types=1);

namespace App\MCF\Authentication\Session;

use App\MCF\Authentication\McfAuth;
use App\MCF\Authentication\SessionSettings;
use Illuminate\Support\Facades\DB;
use LogicException;

final class ConcurrentSessionHandler
{
    /**
     * Handle concurrent sessions after a successful login.
     *
     * When multiple sessions are disabled, all previous
     * sessions belonging to the authenticated user are removed.
     */
    public function handle(): void
    {
        if (SessionSettings::$multipleSessionsPerUser) {
            return;
        }

        if (! McfAuth::check()) {
            return;
        }

        $this->ensureDatabaseDriver();

        $user = McfAuth::user();

        if ($user === null) {
            return;
        }

        $currentSessionId = session()->getId();

        $userId = $user->getAuthIdentifier();

        $table = config(
            'session.table',
            'sessions',
        );

        $connection = config(
            'session.connection',
        );

        $query = DB::connection($connection)
            ->table($table)
            ->where('user_id', $userId);

        if ($currentSessionId !== '') {
            $query->where(
                'id',
                '!=',
                $currentSessionId,
            );
        }

        $query->delete();
    }

    /**
     * Ensure that Laravel is using the database session driver.
     *
     * Concurrent Session Control relies on Laravel's
     * database session table.
     */
    private function ensureDatabaseDriver(): void
    {
        if (config('session.driver') === 'database') {
            return;
        }

        throw new LogicException(
            'Concurrent Session Control requires the Laravel database session driver.',
        );
    }
}
