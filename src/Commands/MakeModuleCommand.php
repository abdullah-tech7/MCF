<?php

namespace MCF\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use MCF\Generators\ModuleGenerator;

class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mcf:make:module {name : The module name}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new MCF module';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            (new ModuleGenerator())->generate(
                $this->argument('name')
            );

            $this->components->info(
                "Module [{$this->argument('name')}] created successfully."
            );

            return self::SUCCESS;

        } catch (InvalidArgumentException $e) {

            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
    }
}