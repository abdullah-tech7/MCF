<?php

declare(strict_types=1);

namespace App\MCF\Realtime\Internal;

final class RealtimeRuntime
{
    private const DEFAULT_INTERVAL = 15000;

    private const MAX_ERROR_INTERVAL = 60000;

    private function __construct()
    {
    }

    /**
     * Render the MCF Realtime JavaScript runtime.
     */
    public static function render(): string
    {
        $endpoint = json_encode(
            url('/mcf/realtime'),
            JSON_THROW_ON_ERROR,
        );

        $defaultInterval = self::DEFAULT_INTERVAL;
        $maxErrorInterval = self::MAX_ERROR_INTERVAL;

        return <<<HTML
<script>
window.MCF = window.MCF || {};

MCF.realtime = (function () {

    const channels = {};

    const DEFAULT_INTERVAL = {$defaultInterval};
    const MAX_ERROR_INTERVAL = {$maxErrorInterval};
    const ENDPOINT = {$endpoint};

    function realtime(
        name,
        options = {}
    ) {
        if (! name) {
            throw new Error(
                'MCF realtime channel name is required.'
            );
        }

        if (
            options.onUpdate !== undefined &&
            typeof options.onUpdate !== 'function'
        ) {
            throw new Error(
                'MCF realtime onUpdate must be a function.'
            );
        }

        if (
            options.onError !== undefined &&
            typeof options.onError !== 'function'
        ) {
            throw new Error(
                'MCF realtime onError must be a function.'
            );
        }

        if (channels[name]) {
            channels[name].stop();
        }

        const interval =
            Number(options.interval) > 0
                ? Number(options.interval)
                : DEFAULT_INTERVAL;

        const onUpdate =
            typeof options.onUpdate === 'function'
                ? options.onUpdate
                : function () {};

        const onError =
            typeof options.onError === 'function'
                ? options.onError
                : function () {};

        const channel = {
            timer: null,

            requesting: false,

            running: true,

            lastState: null,

            errorInterval: interval,

            stop: function () {
                this.running = false;

                if (this.timer !== null) {
                    clearTimeout(this.timer);

                    this.timer = null;
                }
            },

            schedule: function (delay) {
                if (! this.running) {
                    return;
                }

                if (this.timer !== null) {
                    clearTimeout(this.timer);
                }

                this.timer = setTimeout(
                    () => this.poll(),
                    delay
                );
            },

            poll: async function () {
                if (! this.running) {
                    return;
                }

                if (this.requesting) {
                    return;
                }

                if (
                    document.visibilityState !==
                    'visible'
                ) {
                    this.schedule(interval);

                    return;
                }

                this.requesting = true;

                try {
                    const response = await fetch(
                        ENDPOINT +
                        '/' +
                        encodeURIComponent(name),
                        {
                            method: 'GET',

                            headers: {
                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },

                            credentials:
                                'same-origin',

                            cache:
                                'no-store',
                        }
                    );

                    if (! response.ok) {
                        throw new Error(
                            'MCF realtime request failed with status ' +
                            response.status
                        );
                    }

                    const payload =
                        await response.json();

                    if (
                        ! payload ||
                        typeof payload !== 'object'
                    ) {
                        throw new Error(
                            'MCF realtime response is invalid.'
                        );
                    }

                    if (
                        typeof payload.state ===
                        'undefined'
                    ) {
                        throw new Error(
                            'MCF realtime response does not contain a state.'
                        );
                    }

                    const state =
                        payload.state;

                    const serialized =
                        JSON.stringify(state);

                    if (
                        serialized !==
                        this.lastState
                    ) {
                        this.lastState =
                            serialized;

                        onUpdate(state);
                    }

                    this.errorInterval =
                        interval;

                    this.schedule(interval);

                } catch (error) {

                    onError(error);

                    this.errorInterval =
                        Math.min(
                            this.errorInterval * 2,
                            MAX_ERROR_INTERVAL
                        );

                    this.schedule(
                        this.errorInterval
                    );

                } finally {

                    this.requesting = false;
                }
            },
        };

        channels[name] = channel;

        channel.poll();

        return {
            stop: function () {
                channel.stop();

                delete channels[name];
            },
        };
    }

    document.addEventListener(
        'visibilitychange',
        function () {

            if (
                document.visibilityState !==
                'visible'
            ) {
                return;
            }

            Object.values(channels)
                .forEach(
                    function (channel) {

                        if (
                            channel.running &&
                            ! channel.requesting
                        ) {
                            channel.poll();
                        }
                    }
                );
        }
    );

    return realtime;

})();
</script>
HTML;
    }
}
