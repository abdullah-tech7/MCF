<?php

declare(strict_types=1);

namespace MCF\Generators;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class WorkflowLayoutGenerator
{
    protected Filesystem $files;

    public function __construct()
    {
        $this->files = new Filesystem();
    }

    public function generate(string $moduleName, string $workflowName): void
    {
        $modulePath = app_path("MCF/Modules/{$moduleName}");

        if (! $this->files->exists($modulePath)) {
            throw new RuntimeException(
                "Module [{$moduleName}] does not exist."
            );
        }

        $this->createDirectories($modulePath, $workflowName);

        $this->generateController($modulePath, $moduleName, $workflowName);
        $this->generateService($modulePath, $moduleName, $workflowName);
        $this->generateRoute($modulePath, $moduleName, $workflowName);

        $this->registerRoute($moduleName, $workflowName);

        $this->generateViews($modulePath, $workflowName);
    }

    protected function createDirectories(
        string $modulePath,
        string $workflowName
    ): void {
        $directories = [
            $workflowName,
            "{$workflowName}/Backend",
            "{$workflowName}/Views",
            "{$workflowName}/Views/Components",
        ];

        foreach ($directories as $directory) {
            $this->files->ensureDirectoryExists(
                "{$modulePath}/{$directory}"
            );
        }
    }

    protected function generateController(
        string $modulePath,
        string $moduleName,
        string $workflowName
    ): void {
        $this->copyStub(
            'Controller.stub',
            "{$modulePath}/{$workflowName}/Backend/{$workflowName}Controller.php",
            $moduleName,
            $workflowName
        );
    }

    protected function generateService(
        string $modulePath,
        string $moduleName,
        string $workflowName
    ): void {
        $this->copyStub(
            'Service.stub',
            "{$modulePath}/{$workflowName}/Backend/{$workflowName}Service.php",
            $moduleName,
            $workflowName
        );
    }


    protected function generateRoute(
        string $modulePath,
        string $moduleName,
        string $workflowName
    ): void {
        $this->copyStub(
            'Route.stub',
            "{$modulePath}/{$workflowName}/Backend/{$workflowName}Routes.php",
            $moduleName,
            $workflowName
        );
    }

    protected function generateViews(
        string $modulePath,
        string $workflowName
    ): void {
        $views = [
            'app.stub' => 'app.blade.php',
            'head.stub' => 'Components/head.blade.php',
            'header.stub' => 'Components/header.blade.php',
            'navbar.stub' => 'Components/navbar.blade.php',
            'sidebar.stub' => 'Components/sidebar.blade.php',
            'footer.stub' => 'Components/footer.blade.php',
            'guest.stub' => 'Components/guest.blade.php',
            'auth.stub' => 'Components/auth.blade.php',
        ];

        foreach ($views as $stub => $view) {
            $this->files->copy(
                __DIR__ . "/../Stubs/Layout/{$stub}",
                "{$modulePath}/{$workflowName}/Views/{$view}"
            );
        }
    }

    protected function copyStub(
        string $stub,
        string $destination,
        string $moduleName,
        string $workflowName
    ): void {
        $content = $this->files->get(
            __DIR__ . "/../Stubs/Layout/{$stub}"
        );

        $content = str_replace([
            '{{ ModuleName }}',
            '{{ WorkflowName }}',
            '{{ WorkflowRoute }}',
            '{{ ControllerNamespace }}',
            '{{ ServiceNamespace }}',
        ], [
            $moduleName,
            $workflowName,
            lcfirst($workflowName),
            "App\\MCF\\Modules\\{$moduleName}\\{$workflowName}\\Backend",
            "App\\MCF\\Modules\\{$moduleName}\\{$workflowName}\\Backend",
            "App\\MCF\\Modules\\{$moduleName}\\{$workflowName}\\Backend",
            "App\\MCF\\Modules\\{$moduleName}\\{$workflowName}\\Backend",
        ], $content);

        $this->files->put($destination, $content);
    }

    protected function registerRoute(
        string $moduleName,
        string $workflowName
    ): void {
        $routesFile = app_path('MCF/mcf_routes.php');

        if (! $this->files->exists($routesFile)) {
            throw new RuntimeException(
                'MCF routes file not found.'
            );
        }

        $content = $this->files->get($routesFile);

        $require = "require_once __DIR__.'/Modules/{$moduleName}/{$workflowName}/Backend/{$workflowName}Routes.php';";

        if (str_contains($content, $require)) {
            return;
        }

        $content .= PHP_EOL . $require . PHP_EOL;

        $this->files->put($routesFile, $content);
    }
}