<?php

declare(strict_types=1);

namespace MCF\Commands;

use Illuminate\Console\Command;
use MCF\Generators\RequestGenerator;
use Throwable;

class MakeRequestCommand extends Command
{
    protected $signature = 'mcf:make:request
                            {module : Module name}
                            {workflow : Workflow name}
                            {request : Request name}';

    protected $description = 'Create a new request inside an existing MCF workflow.';

    public function handle(
        RequestGenerator $generator,
    ): int {
        $module = trim(
            $this->argument('module'),
        );

        $workflow = trim(
            $this->argument('workflow'),
        );

        $request = trim(
            $this->argument('request'),
        );

        try {
            $generator->generate(
                moduleName: $module,
                workflowName: $workflow,
                requestName: $request,
            );

            $this->components->info(
                "Request [{$request}] created successfully in workflow [{$workflow}] of module [{$module}].",
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->components->error(
                $e->getMessage(),
            );

            return self::FAILURE;
        }
    }
}