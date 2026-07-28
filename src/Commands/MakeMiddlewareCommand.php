<?php

declare(strict_types=1);

namespace MCF\Commands;

use Illuminate\Console\Command;
use MCF\Generators\MiddlewareGenerator;

class MakeMiddlewareCommand extends Command
{
    protected $signature = 'mcf:make:middleware {name}';

    protected $description = 'Create a new MCF middleware';

    public function handle(
        MiddlewareGenerator $generator
    ): int {
        $generator->generate(
            $this->argument('name')
        );

        $this->components->info(
            "Middleware [{$this->argument('name')}] created successfully."
        );

        return self::SUCCESS;
    }
}