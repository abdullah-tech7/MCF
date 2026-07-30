<?php

namespace MCF\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class RemoveEndpointCommand extends Command
{
    protected $signature =
        'mcf:endpoint:remove
        {module : Module name}
        {workflow : Workflow name}
        {endpoint : Endpoint name}';

    protected $description =
        'Remove an endpoint from an existing workflow.';

    protected Filesystem $files;

    protected string $moduleName;

    protected string $workflowName;

    protected string $endpointName;

    protected string $modulePath;

    protected string $backendPath;

    protected string $viewsPath;

    protected string $controllerFile;

    protected string $routesFile;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle(): int
    {
        $this->moduleName = $this->argument('module');

        $this->workflowName = $this->argument('workflow');

        $this->endpointName = $this->argument('endpoint');

        $this->initializePaths();

        $this->validateWorkflow();

        $this->removeControllerMethod();

        $this->removeRoute();

        $this->removeView();

        $this->showSummary();

        return self::SUCCESS;
    }

        protected function initializePaths(): void
    {
        $this->modulePath =
            app_path(
                'MCF/Modules/' .
                $this->moduleName .
                '/' .
                $this->workflowName
            );

        $this->backendPath =
            $this->modulePath .
            '/Backend';

        $this->viewsPath =
            $this->modulePath .
            '/Views';

        $this->controllerFile =
            $this->backendPath .
            '/' .
            $this->workflowName .
            'Controller.php';

        $this->routesFile =
            $this->backendPath .
            '/' .
            $this->workflowName .
            'Routes.php';
    }

    protected function validateWorkflow(): void
    {
        if (! $this->files->isDirectory($this->modulePath)) {
            throw new RuntimeException(
                "Workflow [{$this->moduleName}/{$this->workflowName}] does not exist."
            );
        }

        if (! $this->files->exists($this->controllerFile)) {
            throw new RuntimeException(
                "{$this->workflowName}Controller.php not found."
            );
        }

        if (! $this->files->exists($this->routesFile)) {
            throw new RuntimeException(
                "{$this->workflowName}Routes.php not found."
            );
        }
    }

        protected function removeControllerMethod(): void
    {
        $controller = $this->files->get(
            $this->controllerFile
        );

        $pattern =
            '/\n\s*public\s+function\s+' .
            preg_quote($this->endpointName, '/') .
            '\s*\(.*?\)\s*:\s*[^{]+\{.*?\n\s*\}/s';

        if (! preg_match($pattern, $controller)) {
            throw new RuntimeException(
                "Endpoint [{$this->endpointName}] was not found in {$this->workflowName}Controller."
            );
        }

        $controller = preg_replace(
            $pattern,
            '',
            $controller,
            1
        );

        $controller = preg_replace(
            "/\n{3,}/",
            PHP_EOL . PHP_EOL,
            $controller
        );

        $this->files->put(
            $this->controllerFile,
            $controller
        );
    }

        protected function removeRoute(): void
    {
        $routes = $this->files->get(
            $this->routesFile
        );

        $pattern =
            '/\n\s*Route::(?:get|post|put|patch|delete)\s*'
            . '\(.*?\)\s*'
            . '->name\(\s*[\'"]'
            . preg_quote(
                strtolower($this->moduleName) .
                '.' .
                strtolower($this->workflowName) .
                '.' .
                $this->endpointName,
                '/'
            )
            . '[\'"]\s*\);/is';

        if (! preg_match($pattern, $routes)) {
            throw new RuntimeException(
                "Route for endpoint [{$this->endpointName}] was not found."
            );
        }

        $routes = preg_replace(
            $pattern,
            '',
            $routes,
            1
        );

        $routes = preg_replace(
            "/\n{3,}/",
            PHP_EOL . PHP_EOL,
            $routes
        );

        $this->files->put(
            $this->routesFile,
            $routes
        );
    }

        protected function removeView(): void
    {
        $viewFile =
            $this->viewsPath .
            '/' .
            $this->endpointName .
            '.blade.php';

        if (! $this->files->exists($viewFile)) {
            return;
        }

        $this->files->delete($viewFile);
    }

    protected function showSummary(): void
    {
        $this->newLine();

        $this->info('Endpoint removed successfully.');

        $this->newLine();

        $this->line(
            "Module    : {$this->moduleName}"
        );

        $this->line(
            "Workflow  : {$this->workflowName}"
        );

        $this->line(
            "Endpoint  : {$this->endpointName}"
        );

        $this->newLine();
    }
}