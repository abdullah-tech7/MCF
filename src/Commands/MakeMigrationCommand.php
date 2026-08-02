<?php

declare(strict_types=1);

namespace MCF\Commands;

use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Collection;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->migrationExists()) {
            $this->components->error(
                "Migration [{$this->getMigrationName()}] already exists."
            );

            return self::FAILURE;
        }

        $result = parent::handle();

        if ($result === self::SUCCESS) {
            $this->organizeMigrations();
        }

        return $result;
    }

    /**
     * Get the migration path.
     */
    protected function getMigrationPath(): string
    {
        $path = app_path('MCF/Database/Migrations');

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    /**
     * Organize migration files.
     */
    protected function organizeMigrations(): void
    {
        $this->renameMigrationFiles(
            $this->getMigrationFiles()
        );
    }

    /**
     * Get all migration files.
     */
    protected function getMigrationFiles(): Collection
    {
        return collect(File::files($this->getMigrationPath()))
            ->sortBy(fn ($file) => $file->getFilename())
            ->values();
    }

    /**
     * Rename migration files using sequential numbering.
     */
    protected function renameMigrationFiles(Collection $files): void
    {
        $path = $this->getMigrationPath();

        foreach ($files as $index => $file) {

            $name = $this->stripMigrationPrefix(
                $file->getFilename()
            );

            $newFilename = sprintf(
                '%04d_%s',
                $index + 1,
                $name
            );

            if ($file->getFilename() === $newFilename) {
                continue;
            }

            File::move(
                $file->getPathname(),
                $path . DIRECTORY_SEPARATOR . $newFilename
            );
        }
    }

    /**
     * Determine if the migration already exists.
     */
    protected function migrationExists(): bool
    {
        $migration = $this->getMigrationName();

        foreach ($this->getMigrationFiles() as $file) {

            $name = pathinfo(
                $this->stripMigrationPrefix($file->getFilename()),
                PATHINFO_FILENAME
            );

            if ($name === $migration) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove Laravel timestamp or MCF numbering.
     */
    protected function stripMigrationPrefix(string $filename): string
    {
        return preg_replace(
            '/^(?:\d{4}_\d{2}_\d{2}_\d{6}|\d{4})_/',
            '',
            $filename
        );
    }

    /**
     * Get normalized migration name.
     */
    protected function getMigrationName(): string
    {
        return Str::snake(
            $this->argument('name')
        );
    }
}