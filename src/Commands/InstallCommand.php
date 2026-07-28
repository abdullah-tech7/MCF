<?php

declare(strict_types=1);

namespace MCF\Commands;

use Illuminate\Console\Command;
use MCF\Generators\InstallGenerator;

class InstallCommand extends Command
{
    protected $signature = 'mcf:install';

    protected $description = 'Install the MCF framework.';

      public function handle(InstallGenerator $installer): int    
      {
        $cleanupLaravel = $this->confirm(
            'Do you want to remove Laravel default directories (Controllers, Requests, Models)?',
            false
        );

        $installer->install(
            base_path(),
            $cleanupLaravel,
        );

        $this->newLine();

        $this->info('MCF installed successfully.');

        return self::SUCCESS;
    }
}