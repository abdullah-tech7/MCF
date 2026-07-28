<?php

declare(strict_types=1);

namespace MCF\Commands;

use MCF\Database\Models\ModelMakeCommand;

class MakeModelCommand extends ModelMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'mcf:make:model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new MCF model class';
}