<?php

declare(strict_types=1);

namespace MCF;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use MCF\Commands\MakeMigrationCommand;
use MCF\Commands\MakeModelCommand;
use MCF\Commands\MakeFactoryCommand;
use MCF\Commands\MakeSeederCommand;
use MCF\Commands\MakeMailCommand;
use MCF\Commands\MakeNotificationCommand;
use MCF\Commands\MakeRuleCommand;
use MCF\Support\MCFFileLoader;
use MCF\Support\TranslationLoader;
use MCF\Commands\InstallCommand;
use MCF\Commands\MakeModuleCommand;
use MCF\Commands\MakeWorkflowCommand;
use MCF\Commands\MakeWorkflowCrudCommand;
use MCF\Commands\RemoveWorkFlowCommand;
use MCF\Commands\MakeWorkflowLayoutCommand;
use MCF\Commands\MakeMiddlewareCommand;
use MCF\Commands\MakeLangCommand;
use MCF\Commands\RemoveLangCommand;
use MCF\Commands\CreateEndpointCommand;
use MCF\Commands\RemoveEndpointCommand;

class MCFServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mcf.php',
            'mcf'
        );

        $this->app->singleton(MakeMigrationCommand::class, function ($app) {
            return new MakeMigrationCommand(
                $app['migration.creator'],
                $app['composer'],
            );
        });



        $this->app->extend('translation.loader', function ($loader, $app) {
            return new MCFFileLoader(
                new Filesystem(),
                $app['path.lang'],
                new TranslationLoader(),
                app_path('MCF/Modules'),
            );
        });
    }

   public function boot(): void
{
       
$filesystem = new Filesystem();

$modulesPath = app_path('MCF/Modules');

if ($filesystem->exists($modulesPath)) {
    foreach ($filesystem->directories($modulesPath) as $modulePath) {
        $module = basename($modulePath);

        $viewPaths = [];

        foreach ($filesystem->directories($modulePath) as $workflowPath) {
            $viewsPath = $workflowPath . DIRECTORY_SEPARATOR . 'Views';

            if ($filesystem->isDirectory($viewsPath)) {
                $viewPaths[] = $viewsPath;
            }
        }

        if ($viewPaths !== []) {
            $this->loadViewsFrom($viewPaths, $module);
        }
    }
}

    if ($this->app->runningInConsole()) {
        $this->commands([
            InstallCommand::class,
            MakeModuleCommand::class,
            MakeWorkflowCommand::class,
            MakeWorkflowCrudCommand::class,
            MakeWorkflowLayoutCommand::class,
            RemoveWorkFlowCommand::class,
            MakeMiddlewareCommand::class,
            MakeMigrationCommand::class,
            MakeModelCommand::class,
            MakeFactoryCommand::class,
            MakeSeederCommand::class,
            MakeMailCommand::class,
            MakeRuleCommand::class,
            MakeNotificationCommand::class,
            RemoveLangCommand::class,
            MakeLangCommand::class,
            CreateEndpointCommand::class,
            RemoveEndpointCommand::class
        ]);
    }
}
}