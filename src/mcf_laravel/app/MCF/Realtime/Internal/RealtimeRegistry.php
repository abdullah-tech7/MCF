<?php

declare(strict_types=1);

namespace App\MCF\Realtime\Internal;

use Closure;
use LogicException;

final class RealtimeRegistry
{
    /**
     * @var array<string, Closure>
     */
    private static array $channels = [];

    private function __construct()
    {
    }

    /**
     * Register a realtime channel.
     *
     * @param Closure(): mixed $state
     *
     * @throws LogicException
     */
    public static function register(
        string $name,
        Closure $state,
    ): void {
        if ($name === '') {
            throw new LogicException(
                'Realtime channel name cannot be empty.',
            );
        }

        if (isset(self::$channels[$name])) {
            throw new LogicException(
                sprintf(
                    'Realtime channel "%s" is already registered.',
                    $name,
                ),
            );
        }

        self::$channels[$name] = $state;
    }

    /**
     * Determine whether a realtime channel is registered.
     */
    public static function has(
        string $name,
    ): bool {
        return isset(self::$channels[$name]);
    }

    /**
     * Read the current state of a realtime channel.
     *
     * @throws LogicException
     */
    public static function read(
        string $name,
    ): mixed {
        if (! self::has($name)) {
            throw new LogicException(
                sprintf(
                    'Realtime channel "%s" is not registered.',
                    $name,
                ),
            );
        }

        return (self::$channels[$name])();
    }

    /**
     * Build the realtime channel payload.
     *
     * @return array{
     *     channel: string,
     *     state: mixed
     * }
     */
    public static function payload(
        string $name,
    ): array {
        return [
            'channel' => $name,
            'state' => self::read($name),
        ];
    }
}
