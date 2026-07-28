<?php

declare(strict_types=1);

namespace MCF\Generators;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class MiddlewareGenerator
{
    protected Filesystem $files;

    public function __construct()
    {
        $this->files = new Filesystem();
    }

    public function generate(string $middlewareName): void
    {
        $this->generateMiddleware($middlewareName);

        $this->registerMiddleware($middlewareName);

        $this->registerUseStatement($middlewareName);
    }

    protected function generateMiddleware(string $middlewareName): void
    {
        $stub = __DIR__ . '/../Stubs/Middleware/Middleware.stub';

        $destination = app_path(
            "MCF/Middleware/{$middlewareName}Middleware.php"
        );

        $content = $this->files->get($stub);

        $content = str_replace(
            '{{ MiddlewareName }}',
            $middlewareName,
            $content
        );

        $this->files->put($destination, $content);
    }

 protected function registerMiddleware(string $middlewareName): void
{
    $bootstrap = base_path('bootstrap/app.php');

    if (! $this->files->exists($bootstrap)) {
        throw new RuntimeException('bootstrap/app.php not found.');
    }

    $content = $this->files->get($bootstrap);

    $alias = strtolower($middlewareName);

    $registration = "    \$middleware->alias([\n"
        . "        '{$alias}' => \\App\\MCF\\Middleware\\{$middlewareName}Middleware::class,\n"
        . "    ]);";

    if (str_contains($content, $registration)) {
        return;
    }

    $updated = preg_replace(
        '/->withMiddleware\s*\(\s*function\s*\(Middleware\s+\$middleware\)\s*(?::\s*void)?\s*\{\s*/',
        "$0\n{$registration}\n\n",
        $content,
        1,
        $count
    );

    if ($count === 0) {
        throw new RuntimeException(
            'Unable to register middleware in bootstrap/app.php.'
        );
    }

    $this->files->put($bootstrap, $updated);
}

protected function registerUseStatement(string $middlewareName): void
{
    $routes = app_path('MCF/mcf_routes.php');

    if (! $this->files->exists($routes)) {
        throw new RuntimeException(
            'MCF routes file not found.'
        );
    }

    $content = $this->files->get($routes);

    $use = "use App\\MCF\\Middleware\\{$middlewareName}Middleware;";

    if (str_contains($content, $use)) {
        return;
    }

    $updated = preg_replace(
        '/<\?php\s*/',
        "<?php\n\n{$use}\n\n",
        $content,
        1,
        $count
    );

    if ($count === 0) {
        throw new RuntimeException(
            'Unable to register middleware use statement.'
        );
    }

    $this->files->put($routes, $updated);
}
}