<?php

declare (strict_types = 1);

namespace MCF\Commands;

use Illuminate\Console\Command;
use MCF\Generators\InstallGenerator;

final class InstallCommand extends Command
{
    protected $signature = 'mcf:install';

    protected $description = 'Install the MCF framework structure into the Laravel application.';

    public function handle(
        InstallGenerator $installer,
    ): int {
        $this->newLine();

        $this->warn(
            'MCF installation will modify the structure of this Laravel application.',
        );

        $this->line(
            'The installation will replace or remove specific Laravel files and directories.',
        );

        $this->line(
            'This operation is not automatically reversible.',
        );

        $this->line(
            'It is strongly recommended to install MCF on a new Laravel project.',
        );

        $this->newLine();

        $confirmed = $this->confirm(
            'Are you sure you want to install MCF?',
            false,
        );

        if (! $confirmed) {
            $this->newLine();

            $this->info(
                'MCF installation cancelled. No changes were made.',
            );

            return self::SUCCESS;
        }

        $this->newLine();

        try {
            $installer->install(
                basePath: base_path(),
            );
        } catch (\Throwable $exception) {
            $this->newLine();

            $this->error(
                'MCF installation failed.',
            );

            $this->error(
                $exception->getMessage(),
            );

            return self::FAILURE;
        }

        $this->newLine();

        $this->info(
            'MCF installed successfully.',
        );

        $this->newLine();

        $this->warn(
            'Database configuration is not managed by MCF.',
        );

        $this->line(
            'Configure your database connection in the .env file before using the application.',
        );

        $this->line(
            'After configuring the database, run:',
        );

        $this->line(
            'php artisan migrate --seed',
        );

        $this->newLine();

        $this->warn(
            'Mail configuration is not managed automatically by MCF.',
        );

        $this->line(
            'Configure your mail settings in the .env file to enable email verification, authentication emails, and notifications.',
        );

        $this->line(
            'Mail configuration is optional if your application does not require email features.',
        );

        $this->newLine();

        $this->info(
            'MCF documentation is available in app/MCF/z_Guide.',
        );

        $this->line(
            'The Guide contains documentation for getting started and detailed documentation for each MCF component.',
        );

        $this->line(
            'It is recommended to start with the README and Quick Start guides.',
        );

        $this->newLine();

        $this->info(
            'You may modify your database, mail, and other environment settings as needed.',
        );
        return self::SUCCESS;
    }
}
