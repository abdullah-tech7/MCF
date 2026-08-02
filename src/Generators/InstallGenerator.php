<?php

declare(strict_types=1);

namespace MCF\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

class InstallGenerator
{
    public function __construct(
        protected Filesystem $files
    ) {
    }

    public function install(
        string $basePath,
        bool $cleanupLaravel = false,
    ): void {
        if ($cleanupLaravel) {
            $this->cleanupLaravelDirectories($basePath);
        }

        $this->publishConfig($basePath);

        $directories = [
            'app/MCF',
            'app/MCF/Modules',
            'app/MCF/Middleware',
            'app/MCF/Notifications',
            'app/MCF/Rules',
            'app/MCF/Mail',
        ];

        foreach ($directories as $directory) {
            $this->files->ensureDirectoryExists(
                $basePath . DIRECTORY_SEPARATOR . $directory
            );
        }

        $this->publishBase($basePath);
        $this->publishErrors($basePath);
        $this->publishRoutes($basePath);

        $this->updateBootstrapApp($basePath);

        $this->createReadme($basePath);
        $this->createQuickStart($basePath);

        Artisan::call('mcf:make:module', [
    'name' => 'Shared',
]);

Artisan::call('mcf:make:workflow:layout', [
    'module' => 'Shared',
    'workflow' => 'Layout',
]);

    }

    protected function publishConfig(string $basePath): void
    {
        $source = dirname(__DIR__, 2) . '/config/mcf.php';

        $destination = $basePath . '/config/mcf.php';

        if (! $this->files->exists($destination)) {
            $this->files->copy($source, $destination);
        }
    }

protected function publishRoutes(string $basePath): void
{
    $source = dirname(__DIR__, 2) . '/src/Stubs/Routes/mcf_routes.php';

    $destination = $basePath . '/app/MCF/mcf_routes.php';

    $this->files->copy($source, $destination);
}

protected function updateBootstrapApp(string $basePath): void
{
    $bootstrap = $basePath . '/bootstrap/app.php';

    if (! $this->files->exists($bootstrap)) {
        return;
    }

    $content = $this->files->get($bootstrap);

if (str_contains($content, "app/MCF/mcf_routes.php")) {
    return;
}

    $updated = preg_replace(
        "/web:\s*__DIR__\s*\.\s*'\/\.\.\/routes\/web\.php'/",
        "web: __DIR__.'/../app/MCF/mcf_routes.php'",
        $content,
        1,
        $count
    );

    if ($count === 0) {
        throw new \RuntimeException(
            'Unable to update bootstrap/app.php. Laravel routing configuration was not found.'
        );
    }

    $this->files->put($bootstrap, $updated);
}

protected function createReadme(string $basePath): void
{
    $source = dirname(__DIR__, 2) . '/README.md';

    $destination = $basePath . '/app/MCF/README.md';

    if (! $this->files->exists($destination) && $this->files->exists($source)) {
        $this->files->copy($source, $destination);
    }
}

protected function createQuickStart(string $basePath): void
{
    $source = dirname(__DIR__, 2) . '/Quick Start.md';

    $destination = $basePath . '/app/MCF/Quick Start.md';

    if (! $this->files->exists($destination) && $this->files->exists($source)) {
        $this->files->copy($source, $destination);
    }
}

    protected function cleanupLaravelDirectories(string $basePath): void
    {
        $directories = [
            'app/Http/Controllers',
            'app/Http/Requests',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = $basePath . DIRECTORY_SEPARATOR . $directory;

            if ($this->files->isDirectory($path)) {
                $this->files->deleteDirectory($path);
            }
        }
    }

    protected function publishBase(string $basePath): void
{
    $source = dirname(__DIR__, 2) . '/src/Base';

    $destination = $basePath . '/app/MCF/Base';

    if (! $this->files->isDirectory($source)) {
        return;
    }

    $this->files->ensureDirectoryExists($destination);

    $this->files->copyDirectory($source, $destination);
}

protected function publishErrors(string $basePath): void
{
    $source = dirname(__DIR__, 2) . '/src/errors';

    $destination = $basePath . '/resources/views/errors';

    if (! $this->files->isDirectory($source)) {
        return;
    }

    $this->files->ensureDirectoryExists($destination);

    $this->files->copyDirectory($source, $destination);
}

}