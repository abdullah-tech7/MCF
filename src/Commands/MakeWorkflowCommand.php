<?php

declare(strict_types=1);

namespace MCF\Commands;

use Illuminate\Console\Command;
use MCF\Generators\WorkflowGenerator;
use Throwable;

class MakeWorkflowCommand extends Command
{
    protected $signature = 'mcf:make:workflow
                            {module : Module name}
                            {workflow : Workflow name}';

    protected $description = 'Create a new workflow inside an existing MCF module.';

    public function handle(WorkflowGenerator $generator): int
    {
        $module = trim($this->argument('module'));
        $workflow = trim($this->argument('workflow'));

        try {
            $generator->generate($module, $workflow);

            $this->components->info(
                "Workflow [{$workflow}] created successfully in module [{$module}]."
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
    }
}