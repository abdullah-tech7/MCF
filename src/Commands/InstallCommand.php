<?php

declare(strict_types=1);

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
            'This operation is not intended to be reversible automatically.',
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
                'MCF installation cancelled.',
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

        return self::SUCCESS;
    }
}