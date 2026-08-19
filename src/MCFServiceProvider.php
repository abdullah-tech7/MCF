<?php

declare (strict_types = 1);

namespace MCF;

use App\MCF\Audit\AuditSettings;
use App\MCF\Audit\Internal\AuditObserver;
use App\MCF\Realtime\RealtimeChannel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use MCF\Commands\CreateEndpointCommand;
use MCF\Commands\InstallCommand;
use MCF\Commands\MakeMailCommand;
use MCF\Commands\MakeMiddlewareCommand;
use MCF\Commands\MakeModuleCommand;
use MCF\Commands\MakeRequestCommand;
use MCF\Commands\MakeWorkflowCommand;
use MCF\Commands\MakeWorkflowCrudCommand;
use MCF\Commands\MakeWorkflowLayoutCommand;
use MCF\Commands\RemoveEndpointCommand;
use MCF\Commands\RemoveWorkflowCommand;
use MCF\Queue\McfQueueListener;
use MCF\Support\MCFFileLoader;
use MCF\Support\MCFViewFinder;
use MCF\Support\Path;
use MCF\Support\TranslationLoader;

class MCFServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mcf.php',
            'mcf',
        );

        class_alias(
            \App\MCF\Authentication\McfAuth::class,
            'McfAuth',
        );

        class_alias(
            \App\MCF\AccessControl\McfAccess::class,
            'McfAccess',
        );

        $this->app->extend(
            'view.finder',
            function ($finder, $app) {
                $mcfFinder = new MCFViewFinder(
                    $app['files'],
                    $finder->getPaths(),
                    $finder->getExtensions(),
                );

                foreach ($finder->getHints() as $namespace => $paths) {
                    $mcfFinder->replaceNamespace(
                        $namespace,
                        $paths,
                    );
                }

                return $mcfFinder;
            },
        );

        $this->app->extend(
            'translation.loader',
            function ($loader, $app) {
                return new MCFFileLoader(
                    new Filesystem(),
                    $app['path.lang'],
                    new TranslationLoader(),
                    app_path('MCF/Modules'),
                );
            },
        );
    }

    public function boot(): void
    {

        McfQueueListener::register();
        RealtimeChannel::register();

        $filesystem = new Filesystem();

        /*
        |--------------------------------------------------------------------------
        | MCF Audit
        |--------------------------------------------------------------------------
        |
        | Register the Audit listeners globally instead of registering an
        | observer from each Auditable Model during its boot process.
        |
        | This prevents recursive Model booting and keeps Audit independent
        | from individual Model boot methods.
        |
        */

        $this->registerAuditListeners();

        /*
        |--------------------------------------------------------------------------
        | MCF Framework Views
        |--------------------------------------------------------------------------
        */

        $this->loadViewsFrom(
            Path::root(),
            'MCF',
        );

        /*
        |--------------------------------------------------------------------------
        | MCF Modules
        |--------------------------------------------------------------------------
        */

        $modulesPath = app_path('MCF/Modules');

        if ($filesystem->exists($modulesPath)) {
            foreach ($filesystem->directories($modulesPath) as $modulePath) {
                $module = basename($modulePath);

                $this->loadViewsFrom(
                    $modulePath,
                    $module,
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | MCF Console Commands
        |--------------------------------------------------------------------------
        */

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                MakeModuleCommand::class,
                MakeWorkflowCommand::class,
                MakeWorkflowCrudCommand::class,
                MakeWorkflowLayoutCommand::class,
                RemoveWorkflowCommand::class,
                MakeMiddlewareCommand::class,
                MakeMailCommand::class,
                CreateEndpointCommand::class,
                RemoveEndpointCommand::class,
                MakeRequestCommand::class,
            ]);
        }
    }

    /**
     * Register the global Eloquent Audit listeners.
     *
     * The AuditObserver is resolved only when a model event occurs.
     * It is therefore never resolved while a model is being booted.
     */
    protected function registerAuditListeners(): void
    {
        Event::listen(
            'eloquent.created: *',
            function (string $event, array $models): void {
                if (! AuditSettings::$enabled) {
                    return;
                }

                $model = $models[0] ?? null;

                if ($model === null) {
                    return;
                }

                app(AuditObserver::class)->created(
                    $model,
                );
            },
        );

        Event::listen(
            'eloquent.updated: *',
            function (string $event, array $models): void {
                if (! AuditSettings::$enabled) {
                    return;
                }

                $model = $models[0] ?? null;

                if ($model === null) {
                    return;
                }

                app(AuditObserver::class)->updated(
                    $model,
                );
            },
        );

        Event::listen(
            'eloquent.deleted: *',
            function (string $event, array $models): void {
                if (! AuditSettings::$enabled) {
                    return;
                }

                $model = $models[0] ?? null;

                if ($model === null) {
                    return;
                }

                app(AuditObserver::class)->deleted(
                    $model,
                );
            },
        );
    }
}
