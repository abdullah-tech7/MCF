<?php

declare(strict_types=1);

namespace MCF\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class RemoveLangCommand extends Command
{
    protected $signature = 'mcf:lang:remove
                            {locale : Language locale (e.g. ar, en)}
                            {module? : Module name}
                            {workflow? : Workflow name}
                            {--force : Skip confirmation}';

    protected $description = 'Remove language JSON files';

    protected Filesystem $files;

    protected int $deleted = 0;

    protected int $skipped = 0;

    public function __construct()
    {
        parent::__construct();

        $this->files = new Filesystem();
    }

    public function handle(): int
    {
        $locale = strtolower(trim($this->argument('locale')));
        $module = $this->argument('module');
        $workflow = $this->argument('workflow');

        if (! preg_match('/^[a-z]{2,10}$/', $locale)) {
            throw new RuntimeException(
                "Invalid locale [{$locale}]."
            );
        }

        $modulesPath = app_path('MCF/Modules');

        if (! $this->files->exists($modulesPath)) {
            throw new RuntimeException(
                'Modules directory does not exist.'
            );
        }

        if (! $this->option('force')) {
            if ($module === null) {
                if (! $this->confirm(
                    "This will remove [{$locale}.json] from all workflows. Continue?"
                )) {
                    $this->warn('Operation cancelled.');

                    return self::SUCCESS;
                }
            } elseif ($workflow === null) {
                if (! $this->confirm(
                    "This will remove [{$locale}.json] from module [{$module}]. Continue?"
                )) {
                    $this->warn('Operation cancelled.');

                    return self::SUCCESS;
                }
            } else {
                if (! $this->confirm(
                    "This will remove [{$locale}.json] from workflow [{$workflow}] in module [{$module}]. Continue?"
                )) {
                    $this->warn('Operation cancelled.');

                    return self::SUCCESS;
                }
            }
        }

        if ($module === null) {
            $this->removeForProject($modulesPath);
        } elseif ($workflow === null) {
            $this->removeForModule($modulesPath, $module);
        } else {
            $this->removeForWorkflow(
                $modulesPath,
                $module,
                $workflow
            );
        }

        $this->newLine();

        $this->info("Deleted : {$this->deleted}");
        $this->line("Skipped : {$this->skipped}");

        return self::SUCCESS;
    }

    protected function removeForProject(string $modulesPath): void
    {
        foreach ($this->files->directories($modulesPath) as $modulePath) {
            $moduleName = basename($modulePath);

            $this->removeForModule($modulesPath, $moduleName);
        }
    }

    protected function removeForModule(
        string $modulesPath,
        string $moduleName
    ): void {
        $modulePath = "{$modulesPath}/{$moduleName}";

        if (! $this->files->exists($modulePath)) {
            throw new RuntimeException(
                "Module [{$moduleName}] does not exist."
            );
        }

        foreach ($this->files->directories($modulePath) as $workflowPath) {
            $workflowName = basename($workflowPath);

            $this->removeForWorkflow(
                $modulesPath,
                $moduleName,
                $workflowName
            );
        }
    }

        protected function removeForWorkflow(
        string $modulesPath,
        string $moduleName,
        string $workflowName
    ): void {
        $workflowPath = "{$modulesPath}/{$moduleName}/{$workflowName}";

        if (! $this->files->exists($workflowPath)) {
            throw new RuntimeException(
                "Workflow [{$workflowName}] does not exist in module [{$moduleName}]."
            );
        }

        $langPath = "{$workflowPath}/Lang";

        if (! $this->files->exists($langPath)) {
            throw new RuntimeException(
                "Lang directory does not exist for workflow [{$workflowName}]."
            );
        }

        $locale = strtolower(trim($this->argument('locale')));

        $this->removeLanguageFile(
            $moduleName,
            $workflowName,
            "{$langPath}/{$locale}.json"
        );
    }

    protected function removeLanguageFile(
        string $moduleName,
        string $workflowName,
        string $file
    ): void {
        if (! $this->files->exists($file)) {
            $this->skipped++;

            $this->line(
                "Skipped : {$moduleName}/{$workflowName}/Lang/" . basename($file)
            );

            return;
        }

        $this->files->delete($file);

        $this->deleted++;

        $this->info(
            "Deleted : {$moduleName}/{$workflowName}/Lang/" . basename($file)
        );
    }
}

