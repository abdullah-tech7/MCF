<?php

declare(strict_types=1);

namespace MCF\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class MakeLangCommand extends Command
{
    protected $signature = 'mcf:lang:make
                            {locale : Language locale (e.g. ar, en)}
                            {module? : Module name}
                            {workflow? : Workflow name}
                            {--force : Skip confirmation}';

    protected $description = 'Create language JSON files';

    protected Filesystem $files;

    protected int $created = 0;

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

        if ($module === null) {
            if (
                ! $this->option('force') &&
                ! $this->confirm(
                    "This will create [{$locale}.json] in all workflows. Continue?"
                )
            ) {
                $this->warn('Operation cancelled.');

                return self::SUCCESS;
            }

            $this->createForProject($modulesPath);
        } elseif ($workflow === null) {
            $this->createForModule($modulesPath, $module);
        } else {
            $this->createForWorkflow($modulesPath, $module, $workflow);
        }

        $this->newLine();

        $this->info("Created : {$this->created}");
        $this->line("Skipped : {$this->skipped}");

        return self::SUCCESS;
    }

    protected function createForProject(string $modulesPath): void
    {
        foreach ($this->files->directories($modulesPath) as $modulePath) {
            $moduleName = basename($modulePath);

            $this->createForModule($modulesPath, $moduleName);
        }
    }

    protected function createForModule(
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

            $this->createForWorkflow(
                $modulesPath,
                $moduleName,
                $workflowName
            );
        }
    }

        protected function createForWorkflow(
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

        $this->createLanguageFile(
            $moduleName,
            $workflowName,
            "{$langPath}/{$locale}.json"
        );
    }

    protected function createLanguageFile(
        string $moduleName,
        string $workflowName,
        string $file
    ): void {
        if ($this->files->exists($file)) {
            $this->skipped++;

            $this->line(
                "Skipped : {$moduleName}/{$workflowName}/Lang/" . basename($file)
            );

            return;
        }

        $this->files->put($file, "{}" . PHP_EOL);

        $this->created++;

        $this->info(
            "Created : {$moduleName}/{$workflowName}/Lang/" . basename($file)
        );
    }
}