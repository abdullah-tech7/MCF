<?php

declare(strict_types=1);

namespace MCF\Commands;

use MCF\Database\Seeds\SeederMakeCommand;

class MakeSeederCommand extends SeederMakeCommand
{
    protected $name = 'mcf:make:seeder';

    protected $description = 'Create a new MCF seeder class';

}
