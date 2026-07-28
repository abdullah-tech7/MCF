<?php

declare(strict_types=1);

namespace MCF\Commands;

use Illuminate\Console\Command;
use MCF\Generators\WorkflowLayoutGenerator;
use Throwable;

class MakeWorkflowLayoutCommand extends Command
{
    protected $signature = 'mcf:make:workflow:layout
                            {module : Module name}
                            {workflow : Workflow name}';

    protected $description = 'Create a new LAYOUT workflow inside an existing MCF module.';

    public function handle(WorkflowLayoutGenerator $generator): int
    {
        $module = trim($this->argument('module'));
        $workflow = trim($this->argument('workflow'));

        try {
            $generator->generate($module, $workflow);

            $this->components->info(
                "LAYOUT workflow [{$workflow}] created successfully in module [{$module}]."
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
    }
}