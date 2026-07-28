<?php

declare(strict_types=1);

namespace MCF\Commands;

use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Composer;
use MCF\Database\Migrations\MigrateMakeCommand;

class MakeMigrationCommand extends MigrateMakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'mcf:make:migration
        {name : The name of the migration}
        {--create= : The table to be created}
        {--table= : The table to migrate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new MCF migration';

    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        parent::__construct($creator, $composer);
    }

    protected function getMigrationPath()
    {
        $path = app_path('MCF/Database/Migrations');

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }
}