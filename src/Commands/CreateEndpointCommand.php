<?php

declare(strict_types=1);

namespace MCF\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class CreateEndpointCommand extends Command
{
    protected $signature = 'mcf:endpoint:create';

    protected $description = 'Create a new endpoint';

    protected Filesystem $files;

    protected string $moduleName;

    protected string $workflowName;

    protected string $modulePath;

    protected string $workflowPath;

    protected string $backendPath;

    protected string $viewsPath;

    protected string $controllerFile;

    protected string $routesFile;

    protected string $endpointName;

    protected bool $createView;

    protected string $httpMethod;

    protected string $returnType;

    protected bool $injectWorkflowRequest;

    protected string $parameters = '';

    public function __construct()
    {
        parent::__construct();

        $this->files = new Filesystem();
    }

    public function handle(): int
    {
        $this->askModule();

        $this->askWorkflow();

        $this->preparePaths();

        $this->askEndpointName();

        $this->askCreateView();

        $this->askHttpMethod();

        $this->askReturnType();

        $this->askWorkflowRequest();

        $this->askParameters();

        $this->generateControllerMethod();

        $this->generateRoute();

        if ($this->createView) {
            $this->generateView();
        }

        $this->showSummary();

        return self::SUCCESS;
    }

    protected function askModule(): void
    {
        $this->moduleName = $this->readInput('Module');

        $this->modulePath = app_path(
            "MCF/Modules/{$this->moduleName}"
        );

        if (! $this->files->exists($this->modulePath)) {
            throw new RuntimeException(
                "Module [{$this->moduleName}] does not exist."
            );
        }
    }

    protected function askWorkflow(): void
    {
        $this->workflowName = $this->readInput('Workflow');

        $this->workflowPath =
            "{$this->modulePath}/{$this->workflowName}";

        if (! $this->files->exists($this->workflowPath)) {
            throw new RuntimeException(
                "Workflow [{$this->workflowName}] does not exist in module [{$this->moduleName}]."
            );
        }
    }

protected function preparePaths(): void
{
    $this->backendPath =
        "{$this->workflowPath}/Backend";

    $this->viewsPath =
        "{$this->workflowPath}/Views";

    $this->controllerFile =
        "{$this->backendPath}/{$this->workflowName}Controller.php";

    $this->routesFile =
        "{$this->backendPath}/{$this->workflowName}Routes.php";
}

    protected function askEndpointName(): void
    {
        $this->endpointName = $this->readInput(
            'Endpoint Name'
        );
    }

    protected function askCreateView(): void
    {
        $this->createView = $this->menu(
            'Create View?',
            [
                'Yes',
                'No',
            ],
            1
        ) === 1;
    }

    protected function askHttpMethod(): void
    {
        $methods = [
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE',
        ];

        $this->httpMethod = $methods[
            $this->menu(
                'HTTP Method',
                $methods
            ) - 1
        ];
    }

        protected function askReturnType(): void
    {
        $types = [
            'View',
            'RedirectResponse',
            'JsonResponse',
            'BinaryFileResponse',
            'StreamedResponse',
            'Response',
        ];

        $this->returnType = $types[
            $this->menu(
                'Return Type',
                $types
            ) - 1
        ];
    }

    protected function askWorkflowRequest(): void
    {
        $this->injectWorkflowRequest = $this->menu(
            'Inject Workflow Request?',
            [
                'No',
                'Yes',
            ],
            1
        ) === 2;
    }

    protected function askParameters(): void
    {
        if (
            $this->menu(
                'Parameters',
                [
                    'None',
                    'Add Parameters',
                ],
                1
            ) === 1
        ) {
            return;
        }

        $this->parameters = $this->readInput(
            "Enter Parameters\nExample: int \$id, string \$name"
        );
    }

    protected function readInput(string $title): string
    {
        while (true) {
            $this->newLine();

            $value = trim(
                (string) $this->ask($title)
            );

            if ($value !== '') {
                return $value;
            }

            $this->error(
                'Value cannot be empty.'
            );
        }
    }

    protected function menu(
        string $title,
        array $options,
        int $default = 1
    ): int {
        while (true) {
            $this->newLine();

            $this->line($title);

            $this->newLine();

            foreach ($options as $index => $option) {
                $this->line(
                    '[' . ($index + 1) . '] ' . $option
                );
            }

            $this->newLine();

            $choice = trim(
                (string) $this->ask(
                    '>',
                    (string) $default
                )
            );

            if (
                ctype_digit($choice) &&
                (int) $choice >= 1 &&
                (int) $choice <= count($options)
            ) {
                return (int) $choice;
            }

            $this->error(
                'Invalid selection.'
            );
        }
    }

  
protected function generateControllerMethod(): void
{
    if (! $this->files->exists($this->controllerFile)) {
        throw new RuntimeException(
            "Controller file [{$this->controllerFile}] does not exist."
        );
    }

    $controller = $this->files->get(
        $this->controllerFile
    );

    if (
        preg_match(
            '/public\s+function\s+' .
            preg_quote($this->endpointName, '/') .
            '\s*\(/',
            $controller
        )
    ) {
        throw new RuntimeException(
            "Endpoint [{$this->endpointName}] already exists."
        );
    }

    $controller = $this->addControllerUses(
        $controller
    );

    $method = $this->buildControllerMethod();

    $controller = preg_replace(
        '/}\s*$/',
        PHP_EOL .
        $method .
        PHP_EOL .
        '}',
        $controller,
        1
    );

    $this->files->put(
        $this->controllerFile,
        $controller
    );
}


protected function buildControllerMethod(): string
{
    return
        '    public function ' .
        $this->endpointName .
        '(' .
        $this->buildControllerParameters() .
        '): ' .
        $this->returnType .
        PHP_EOL .
        '    {' .
        PHP_EOL .
        $this->buildControllerBody() .
        PHP_EOL .
        '    }';
}

protected function buildControllerParameters(): string
{
    $parameters = [];

    if ($this->injectWorkflowRequest) {
        $parameters[] =
            $this->workflowName .
            'Request $request';
    }

    if ($this->parameters !== '') {
        $parameters[] = $this->parameters;
    }

    return implode(
        ', ',
        $parameters
    );
}

protected function buildControllerBody(): string
{

if ($this->createView) {
    return
        "return view('{$this->moduleName}::{$this->workflowName}.{$this->endpointName}');";
}

    return match ($this->returnType) {
        'RedirectResponse' =>
            "        return redirect()->back();",

        'JsonResponse' =>
            "        return response()->json([]);",

        'BinaryFileResponse' =>
            "        abort(501);",

        'StreamedResponse' =>
            "        abort(501);",

        'Response' =>
            "        return response('');",

        default => throw new RuntimeException(
            'Unsupported return type.'
        ),
    };
}

protected function addControllerUses(
    string $controller
): string
{
    $uses = [];

    if ($this->injectWorkflowRequest) {
        $uses[] =
            'use App\\MCF\\Modules\\' .
            $this->moduleName .
            '\\' .
            $this->workflowName .
            '\\Backend\\' .
            $this->workflowName .
            'Request;';
    }

    $uses[] = match ($this->returnType) {

        'View' =>
            'use Illuminate\Contracts\View\View;',

        'RedirectResponse' =>
            'use Illuminate\Http\RedirectResponse;',

        'JsonResponse' =>
            'use Illuminate\Http\JsonResponse;',

        'BinaryFileResponse' =>
            'use Symfony\Component\HttpFoundation\BinaryFileResponse;',

        'StreamedResponse' =>
            'use Symfony\Component\HttpFoundation\StreamedResponse;',

        'Response' =>
            'use Illuminate\Http\Response;',
    };

    foreach ($uses as $use) {

        if (str_contains($controller, $use)) {
            continue;
        }

        $controller = preg_replace(
            '/^(namespace\s+.*?;\R)/m',
            "$1\n{$use}\n",
            $controller,
            1
        );
    }

    return $controller;
}

protected function generateRoute(): void
{
    if (! $this->files->exists($this->routesFile)) {
        throw new RuntimeException(
            "Routes file [{$this->routesFile}] does not exist."
        );
    }

    $routes = $this->files->get(
        $this->routesFile
    );

    if (
        str_contains(
            $routes,
            "'" . $this->endpointName . "'"
        )
    ) {
        throw new RuntimeException(
            "Endpoint [{$this->endpointName}] already exists in routes."
        );
    }

    $routes = $this->addRouteControllerUse(
        $routes
    );

    $routes .= PHP_EOL .
        PHP_EOL .
        $this->buildRoute();

    $this->files->put(
        $this->routesFile,
        $routes
    );
}

protected function buildRoute(): string
{
    return
        'Route::' .
        strtolower($this->httpMethod) .
        '(' .
        "'" .
        $this->buildRouteUri() .
        "', " .
        '[' .
        $this->workflowName .
        "Controller::class, '" .
        $this->endpointName .
        "'])" .
        PHP_EOL .
        '    ->name(' .
        "'" .
        strtolower($this->moduleName) .
        '.' .
        strtolower($this->workflowName) .
        '.' .
        $this->endpointName .
        "'" .
        ');';
}

protected function buildRouteUri(): string
{
    $uri = '/' . strtolower(
        $this->endpointName
    );

    if ($this->parameters === '') {
        return $uri;
    }

    preg_match_all(
        '/\$([A-Za-z_][A-Za-z0-9_]*)/',
        $this->parameters,
        $matches
    );

    foreach ($matches[1] as $parameter) {
        $uri .= '/{' . $parameter . '}';
    }

    return $uri;
}

protected function addRouteControllerUse(
    string $routes
): string
{
    $use =
        'use App\\MCF\\Modules\\' .
        $this->moduleName .
        '\\' .
        $this->workflowName .
        '\\Backend\\' .
        $this->workflowName .
        'Controller;';

    if (str_contains($routes, $use)) {
        return $routes;
    }

    if (
        preg_match_all(
            '/^use\s+.*?;$/m',
            $routes,
            $matches,
            PREG_OFFSET_CAPTURE
        )
    ) {
        $last = end($matches[0]);

        $position =
            $last[1] + strlen($last[0]);

        return substr_replace(
            $routes,
            PHP_EOL . $use,
            $position,
            0
        );
    }

    return preg_replace(
        '/^<\?php\s*/',
        "<?php\n\n{$use}\n\n",
        $routes,
        1
    );
}


protected function generateView(): void
{
    $viewFile =
        $this->viewsPath .
        '/' .
        $this->endpointName .
        '.blade.php';

    if ($this->files->exists($viewFile)) {
        throw new RuntimeException(
            "View [{$this->endpointName}] already exists."
        );
    }

    $this->files->put(
        $viewFile,
        ''
    );
}


protected function showSummary(): void
{
    $this->newLine();

    $this->info('Endpoint created successfully.');

    $this->newLine();

    $this->line("Module    : {$this->moduleName}");
    $this->line("Workflow  : {$this->workflowName}");
    $this->line("Endpoint  : {$this->endpointName}");
    $this->line("Method    : {$this->httpMethod}");
    $this->line("Return    : {$this->returnType}");
    $this->line('View      : ' . ($this->createView ? 'Yes' : 'No'));
    $this->line('Request   : ' . ($this->injectWorkflowRequest ? 'Yes' : 'No'));

    if ($this->parameters !== '') {
        $this->line("Parameters: {$this->parameters}");
    }

$this->newLine();

$this->comment('To remove this endpoint, run:');

$this->line(
    "php artisan mcf:endpoint:remove " .
    "{$this->moduleName} " .
    "{$this->workflowName} " .
    "{$this->endpointName}"
);

$this->newLine();
}


}
