<?php

declare(strict_types=1);

namespace MCF\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class RemoveWorkflowCommand extends Command
{
    protected $signature = 'mcf:remove:workflow
                            {module : Module name}
                            {workflow : Workflow name}
                            {--force : Skip confirmation}';

    protected $description = 'Remove a workflow from a module';

    protected Filesystem $files;

    public function __construct()
    {
        parent::__construct();

        $this->files = new Filesystem();
    }

    public function handle(): int
    {
        $moduleName = $this->argument('module');
        $workflowName = $this->argument('workflow');

        $modulePath = app_path("MCF/Modules/{$moduleName}");

        if (! $this->files->exists($modulePath)) {
            throw new RuntimeException(
                "Module [{$moduleName}] does not exist."
            );
        }

        if (
            ! $this->option('force') &&
            ! $this->confirm(
                "Are you sure you want to remove workflow [{$workflowName}] from module [{$moduleName}]?"
            )
        ) {
            $this->warn('Operation cancelled.');

            return self::SUCCESS;
        }

        $this->deleteFile("{$modulePath}/Controllers/{$workflowName}Controller.php");
        $this->deleteFile("{$modulePath}/Services/{$workflowName}Service.php");
        $this->deleteFile("{$modulePath}/Requests/{$workflowName}Request.php");
        $this->deleteFile("{$modulePath}/Policies/{$workflowName}Policy.php");
        $this->deleteFile("{$modulePath}/Routes/{$workflowName}.php");

        $this->deleteDirectory("{$modulePath}/Views/{$workflowName}");
        $this->deleteDirectory("{$modulePath}/Lang/{$workflowName}");

        $this->removeRoute($moduleName, $workflowName);

        $this->info(
            "Workflow [{$workflowName}] removed successfully from module [{$moduleName}]."
        );

        return self::SUCCESS;
    }

    protected function deleteFile(string $path): void
    {
        if ($this->files->exists($path)) {
            $this->files->delete($path);
        }
    }

    protected function deleteDirectory(string $path): void
    {
        if ($this->files->isDirectory($path)) {
            $this->files->deleteDirectory($path);
        }
    }

    protected function removeRoute(
        string $moduleName,
        string $workflowName
    ): void {
        $routesFile = app_path('MCF/mcf_routes.php');

        if (! $this->files->exists($routesFile)) {
            return;
        }

        $content = $this->files->get($routesFile);

        $require = "require_once __DIR__.'/Modules/{$moduleName}/Routes/{$workflowName}.php';";

        $content = str_replace(
            [
                PHP_EOL . $require,
                $require . PHP_EOL,
                $require,
            ],
            '',
            $content
        );

        $this->files->put($routesFile, $content);
    }
}