<?php

declare(strict_types=1);

namespace MCF\Generators;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

final class InstallGenerator
{
    public function __construct(
        protected Filesystem $files,
    ) {
    }

    /**
     * Install the MCF framework structure
     * into the Laravel application.
     *
     * MCF installation is intended to be performed once
     * on a Laravel application.
     */
    public function install(
        string $basePath,
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Prevent Reinstallation
        |--------------------------------------------------------------------------
        |
        | Check this before performing any filesystem operation.
        | This prevents an already installed MCF application from being
        | modified or having its owned directories replaced again.
        |
        */

        if ($this->isInstalled($basePath)) {
            throw new RuntimeException(
                'MCF is already installed in this Laravel application.',
            );
        }

        $sourcePath = $this->sourcePath();

        $this->validateSource(
            $sourcePath,
        );

        /*
        |--------------------------------------------------------------------------
        | app/MCF
        |--------------------------------------------------------------------------
        |
        | Add the MCF framework to the application's app directory.
        |
        | Existing files outside the MCF source structure remain untouched.
        |
        */

        $this->copyDirectory(
            source: $sourcePath . '/app/MCF',
            destination: $basePath . '/app/MCF',
        );

        /*
        |--------------------------------------------------------------------------
        | app/Models
        |--------------------------------------------------------------------------
        |
        | MCF owns the Models directory.
        |
        | The existing Laravel Models directory is replaced completely.
        |
        */

        $this->replaceDirectory(
            source: $sourcePath . '/app/Models',
            destination: $basePath . '/app/Models',
        );

        /*
        |--------------------------------------------------------------------------
        | database
        |--------------------------------------------------------------------------
        |
        | MCF provides its own complete database structure.
        |
        | The existing Laravel database directory is replaced completely
        | during the initial installation.
        |
        | Database connection configuration and migrations are not handled
        | by the installer.
        |
        */

        $this->replaceDirectory(
            source: $sourcePath . '/database',
            destination: $basePath . '/database',
        );

        /*
        |--------------------------------------------------------------------------
        | config/mcf.php
        |--------------------------------------------------------------------------
        |
        | Add the MCF configuration without touching Laravel's other
        | configuration files.
        |
        */

        $this->copyFile(
            source: $sourcePath . '/config/mcf.php',
            destination: $basePath . '/config/mcf.php',
        );

        /*
        |--------------------------------------------------------------------------
        | config/mail.php
        |--------------------------------------------------------------------------
        |
        | MCF provides its own mail configuration.
        |
        */

        $this->copyFile(
            source: $sourcePath . '/config/mail.php',
            destination: $basePath . '/config/mail.php',
        );

        /*
        |--------------------------------------------------------------------------
        | resources/views
        |--------------------------------------------------------------------------
        |
        | Copy MCF views into Laravel's views directory.
        |
        | Existing files that belong to MCF are replaced.
        | Other project views remain untouched.
        |
        */

        $this->copyDirectory(
            source: $sourcePath . '/resources/views',
            destination: $basePath . '/resources/views',
        );

        /*
        |--------------------------------------------------------------------------
        | bootstrap/app.php
        |--------------------------------------------------------------------------
        |
        | MCF provides the application's bootstrap configuration.
        |
        */

        $this->copyFile(
            source: $sourcePath . '/bootstrap/app.php',
            destination: $basePath . '/bootstrap/app.php',
        );

        /*
        |--------------------------------------------------------------------------
        | Remove Laravel Structures Replaced by MCF
        |--------------------------------------------------------------------------
        |
        | These directories are no longer used by the MCF architecture.
        |
        | Missing directories are ignored.
        |
        */

        $this->removeLaravelDirectories(
            $basePath,
        );

        /*
        |--------------------------------------------------------------------------
        | Installation Marker
        |--------------------------------------------------------------------------
        |
        | The marker is created only after every installation operation
        | above has completed successfully.
        |
        | If any operation throws an exception, the marker is not created
        | and the application is not considered successfully installed.
        |
        */

        $this->createInstallationMarker(
            $basePath,
        );
    }

    /**
     * Determine whether MCF has already been installed.
     */
    protected function isInstalled(
        string $basePath,
    ): bool {
        return $this->files->isFile(
            $this->installationMarkerPath(
                $basePath,
            ),
        );
    }

    /**
     * Get the MCF Laravel source directory.
     */
    protected function sourcePath(): string
    {
        return dirname(__DIR__)
            . DIRECTORY_SEPARATOR
            . 'mcf_laravel';
    }

    /**
     * Validate that the MCF Laravel source exists.
     */
    protected function validateSource(
        string $sourcePath,
    ): void {
        if (! $this->files->isDirectory($sourcePath)) {
            throw new RuntimeException(
                'The MCF Laravel source directory was not found: '
                . $sourcePath,
            );
        }
    }

    /**
     * Copy a directory recursively.
     *
     * Existing files are overwritten.
     * Files outside the source directory are untouched.
     */
    protected function copyDirectory(
        string $source,
        string $destination,
    ): void {
        if (! $this->files->isDirectory($source)) {
            throw new RuntimeException(
                'Required MCF directory was not found: '
                . $source,
            );
        }

        $this->files->ensureDirectoryExists(
            $destination,
        );

        $this->files->copyDirectory(
            $source,
            $destination,
        );
    }

    /**
     * Replace a directory completely.
     */
    protected function replaceDirectory(
        string $source,
        string $destination,
    ): void {
        if (! $this->files->isDirectory($source)) {
            throw new RuntimeException(
                'Required MCF directory was not found: '
                . $source,
            );
        }

        if ($this->files->isDirectory($destination)) {
            $this->files->deleteDirectory(
                $destination,
            );
        }

        $this->files->copyDirectory(
            $source,
            $destination,
        );
    }

    /**
     * Copy a single file.
     */
    protected function copyFile(
        string $source,
        string $destination,
    ): void {
        if (! $this->files->isFile($source)) {
            throw new RuntimeException(
                'Required MCF file was not found: '
                . $source,
            );
        }

        $directory = dirname(
            $destination,
        );

        $this->files->ensureDirectoryExists(
            $directory,
        );

        $this->files->copy(
            $source,
            $destination,
        );
    }

    /**
     * Remove Laravel directories replaced by MCF.
     *
     * Missing directories are ignored.
     */
    protected function removeLaravelDirectories(
        string $basePath,
    ): void {
        $directories = [
            'routes',
            'app/Http/Controllers',
            'app/Http/Requests',
            'app/Http/Middleware',
            'app/Notifications',
        ];

        foreach ($directories as $directory) {
            $path = $basePath
                . DIRECTORY_SEPARATOR
                . $directory;

            if (! $this->files->isDirectory($path)) {
                continue;
            }

            $this->files->deleteDirectory(
                $path,
            );
        }
    }

    /**
     * Get the installation marker path.
     */
    protected function installationMarkerPath(
        string $basePath,
    ): string {
        return $basePath
            . DIRECTORY_SEPARATOR
            . 'app'
            . DIRECTORY_SEPARATOR
            . 'MCF'
            . DIRECTORY_SEPARATOR
            . '.mcf-installed';
    }

    /**
     * Create the MCF installation marker.
     */
    protected function createInstallationMarker(
        string $basePath,
    ): void {
        $markerPath = $this->installationMarkerPath(
            $basePath,
        );

        $this->files->put(
            $markerPath,
            'MCF installed successfully.'
            . PHP_EOL,
        );
    }
}