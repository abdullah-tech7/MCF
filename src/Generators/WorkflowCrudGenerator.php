<?php

declare (strict_types = 1);

namespace MCF\Generators;

use Illuminate\Filesystem\Filesystem;
use MCF\Generators\RequestGenerator;
use RuntimeException;

class WorkflowCrudGenerator extends WorkflowGenerator
{

    protected RequestGenerator $requestGenerator;

    public function __construct(RequestGenerator $requestGenerator)
    {
        $this->requestGenerator = $requestGenerator;
    }

    public function generate(string $moduleName, string $workflowName): void
    {
        $modulePath = app_path("MCF/Modules/{$moduleName}");

        $this->createDirectories($modulePath, $workflowName);

        $this->generateCrudController($modulePath, $moduleName, $workflowName);

        $this->generateService($modulePath, $moduleName, $workflowName);
        $this->generateCrudRoute($modulePath, $moduleName, $workflowName);
        $this->registerRoute($moduleName, $workflowName);

        $this->generateIndexView($modulePath, $workflowName);
        $this->generateCreateView($modulePath, $workflowName);
        $this->generateEditView($modulePath, $workflowName);
        $this->generateDetailsView($modulePath, $workflowName);

        $this->generateRequests($modulePath, $moduleName, $workflowName);
    }

    protected function generateCrudController(
        string $modulePath,
        string $moduleName,
        string $workflowName
    ): void {
        $this->generateFromStub(
            'ControllerCrud.stub',
            "{$modulePath}/{$workflowName}/Backend/{$workflowName}Controller.php",
            $moduleName,
            $workflowName
        );
    }

    protected function generateCreateView(
        string $modulePath,
        string $workflowName
    ): void {
        $this->files->copy(
            __DIR__ . '/../Stubs/Workflow/create.stub',
            "{$modulePath}/{$workflowName}/Views/create.blade.php"
        );
    }

    protected function generateEditView(
        string $modulePath,
        string $workflowName
    ): void {
        $this->files->copy(
            __DIR__ . '/../Stubs/Workflow/edit.stub',
            "{$modulePath}/{$workflowName}/Views/edit.blade.php"
        );
    }

    protected function generateDetailsView(
        string $modulePath,
        string $workflowName
    ): void {
        $this->files->copy(
            __DIR__ . '/../Stubs/Workflow/details.stub',
            "{$modulePath}/{$workflowName}/Views/details.blade.php"
        );
    }

    protected function generateCrudRoute(
        string $modulePath,
        string $moduleName,
        string $workflowName
    ): void {
        $this->generateFromStub(
            'RouteCrud.stub',
            "{$modulePath}/{$workflowName}/Backend/{$workflowName}Routes.php",
            $moduleName,
            $workflowName
        );
    }

    protected function generateRequests(
        string $modulePath,
        string $moduleName,
        string $workflowName
            ) {
         $this->requestGenerator->generate(
            moduleName: $moduleName,
            workflowName: $workflowName,
            requestName: "Store",
        );

         $this->requestGenerator->generate(
            moduleName: $moduleName,
            workflowName: $workflowName,
            requestName: "Update",
        );
        $uses[] =
            'use App\\MCF\\Modules\\' .
            $moduleName .
            '\\' .
            $workflowName .
            '\\Backend\\Request\\' .
            'StoreRequest;';
        $uses[] =
            'use App\\MCF\\Modules\\' .
            $moduleName .
            '\\' .
            $workflowName .
            '\\Backend\\Request\\' .
            'UpdateRequest;';

        $controllerFile =
            "{$modulePath}/{$workflowName}Controller.php";

        $files      = new Filesystem();
        $controller = $files->get(
            $this->controllerFile,
        );

        foreach ($uses as $use) {
            if (str_contains($controller, $use)) {
                continue;
            }

            $controller = preg_replace(
                '/^(namespace\s+.*?;\R)/m',
                "$1\n{$use}\n",
                $controller,
                1,
            );

            if ($controller === null) {
                throw new RuntimeException(
                    'Unable to add controller imports.',
                );
            }
        }
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
