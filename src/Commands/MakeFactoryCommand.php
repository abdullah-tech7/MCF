<?php

declare(strict_types=1);

namespace MCF\Commands;

use MCF\Database\Factories\FactoryMakeCommand;

class MakeFactoryCommand extends FactoryMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'mcf:make:factory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new MCF model factory';
}