<?php

declare(strict_types=1);

namespace MCF\Generators;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class WorkflowGenerator
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
        $this->generateRequest($modulePath, $moduleName, $workflowName);
        $this->generatePolicy($modulePath, $moduleName, $workflowName);
        $this->generateRoute($modulePath, $moduleName, $workflowName);
        $this->registerRoute($moduleName, $workflowName);

        $this->generateIndexView($modulePath, $workflowName);
    }

    protected function createDirectories(string $modulePath, string $workflowName): void
    {
        $directories = [
            $workflowName,
            "{$workflowName}/Backend",
            "{$workflowName}/Views",
            "{$workflowName}/Lang",
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
        $this->generateFromStub(
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
        $this->generateFromStub(
            'Service.stub',
            "{$modulePath}/{$workflowName}/Backend/{$workflowName}Service.php",
            $moduleName,
            $workflowName
        );
    }

    protected function generateRequest(
        string $modulePath,
        string $moduleName,
        string $workflowName
    ): void {
        $this->generateFromStub(
            'Request.stub',
            "{$modulePath}/{$workflowName}/Backend/{$workflowName}Request.php",
            $moduleName,
            $workflowName
        );
    }

    protected function generatePolicy(
        string $modulePath,
        string $moduleName,
        string $workflowName
    ): void {
        $this->generateFromStub(
            'Policy.stub',
            "{$modulePath}/{$workflowName}/Backend/{$workflowName}Policy.php",
            $moduleName,
            $workflowName
        );
    }

    protected function generateRoute(
        string $modulePath,
        string $moduleName,
        string $workflowName
    ): void {
        $this->generateFromStub(
            'Route.stub',
            "{$modulePath}/{$workflowName}/Backend/{$workflowName}Routes.php",
            $moduleName,
            $workflowName
        );
    }

    protected function generateIndexView(
        string $modulePath,
        string $workflowName
    ): void {
        $this->files->copy(
            __DIR__ . '/../Stubs/Workflow/index.stub',
            "{$modulePath}/{$workflowName}/Views/index.blade.php"
        );
    }

    protected function generateFromStub(
        string $stub,
        string $destination,
        string $moduleName,
        string $workflowName
    ): void {
        $content = $this->files->get(
            __DIR__ . "/../Stubs/Workflow/{$stub}"
        );

        $content = str_replace([
            '{{ ModuleName }}',
            '{{ WorkflowName }}',
            '{{ ModuleRoute }}',
            '{{ WorkflowRoute }}',
            '{{ ControllerNamespace }}',
            '{{ ServiceNamespace }}',
            '{{ RequestNamespace }}',
            '{{ PolicyNamespace }}',
        ], [
            $moduleName,
            $workflowName,
            lcfirst($moduleName),
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
            throw new RuntimeException('MCF routes file not found.');
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