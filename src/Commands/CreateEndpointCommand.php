<?php

declare(strict_types=1);

namespace MCF\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use MCF\Generators\RequestGenerator;
use RuntimeException;
use Throwable;

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

    protected bool $createRequest;

    protected string $requestName = '';

    protected string $requestFile = '';

    protected string $parameters = '';

    public function __construct()
    {
        parent::__construct();

        $this->files = new Filesystem();
    }

    public function handle(
        RequestGenerator $requestGenerator,
    ): int {
        try {
            /*
             * --------------------------------------------------------------
             * Collect input
             * --------------------------------------------------------------
             */

            $this->askModule();

            $this->askWorkflow();

            $this->preparePaths();

            $this->askEndpointName();

            $this->askCreateView();

            $this->askHttpMethod();

            $this->askReturnType();

            $this->askCreateRequest();

            $this->prepareRequest();

            $this->askParameters();

            /*
             * --------------------------------------------------------------
             * Validate everything before changing any file.
             * --------------------------------------------------------------
             */

            $this->validateBeforeCreation();

            /*
             * --------------------------------------------------------------
             * Keep the current state so an unexpected failure during
             * generation can restore the files that were modified.
             * --------------------------------------------------------------
             */

            $controllerBackup = $this->files->get(
                $this->controllerFile,
            );

            $routesBackup = $this->files->get(
                $this->routesFile,
            );

            $requestCreated = false;

            $viewCreated = false;

            try {
                /*
                 * ----------------------------------------------------------
                 * Request
                 * ----------------------------------------------------------
                 */

                if ($this->createRequest) {
                    $requestGenerator->generate(
                        moduleName: $this->moduleName,
                        workflowName: $this->workflowName,
                        requestName: $this->requestName,
                    );

                    $requestCreated = true;
                }

                /*
                 * ----------------------------------------------------------
                 * Controller
                 * ----------------------------------------------------------
                 */

                $this->generateControllerMethod();

                /*
                 * ----------------------------------------------------------
                 * Route
                 * ----------------------------------------------------------
                 */

                $this->generateRoute();

                /*
                 * ----------------------------------------------------------
                 * View
                 * ----------------------------------------------------------
                 */

                if ($this->createView) {
                    $this->generateView();

                    $viewCreated = true;
                }
            } catch (Throwable $exception) {
                /*
                 * ----------------------------------------------------------
                 * Roll back every change made during this operation.
                 * ----------------------------------------------------------
                 */

                $this->rollbackCreation(
                    controllerBackup: $controllerBackup,
                    routesBackup: $routesBackup,
                    requestCreated: $requestCreated,
                    viewCreated: $viewCreated,
                );

                throw $exception;
            }

            $this->showSummary();

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->newLine();

            $this->error(
                'Endpoint creation failed.',
            );

            $this->error(
                $exception->getMessage(),
            );

            return self::FAILURE;
        }
    }

    protected function askModule(): void
    {
        $this->moduleName = $this->readInput(
            'Module',
        );

        $this->modulePath = app_path(
            "MCF/Modules/{$this->moduleName}",
        );

        if (! $this->files->isDirectory($this->modulePath)) {
            throw new RuntimeException(
                "Module [{$this->moduleName}] does not exist.",
            );
        }
    }

    protected function askWorkflow(): void
    {
        $this->workflowName = $this->readInput(
            'Workflow',
        );

        $this->workflowPath =
            "{$this->modulePath}/{$this->workflowName}";

        if (! $this->files->isDirectory($this->workflowPath)) {
            throw new RuntimeException(
                "Workflow [{$this->workflowName}] does not exist in module [{$this->moduleName}].",
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
            'Endpoint Name',
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
            1,
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
                $methods,
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
                $types,
            ) - 1
        ];
    }

    protected function askCreateRequest(): void
    {
        $this->createRequest = $this->menu(
            'Create Request?',
            [
                'No',
                'Yes',
            ],
            1,
        ) === 2;
    }

    /**
     * Prepare the Request name and path from the Endpoint name.
     *
     * Example:
     *
     * login
     *     ↓
     * LoginRequest.php
     */
    protected function prepareRequest(): void
    {
        if (! $this->createRequest) {
            return;
        }

        $this->requestName = $this->normalizeRequestName(
            $this->endpointName,
        );

        $this->requestFile =
            "{$this->backendPath}/Request/{$this->requestName}Request.php";
    }

    /**
     * Normalize the Request name.
     *
     * The first character is uppercase and all remaining
     * characters are lowercase.
     */
    protected function normalizeRequestName(
        string $requestName,
    ): string {
        $requestName = trim(
            $requestName,
        );

        if ($requestName === '') {
            throw new RuntimeException(
                'Request name cannot be empty.',
            );
        }

        $requestName = preg_replace(
            '/[^A-Za-z0-9]+/',
            '',
            $requestName,
        );

        if ($requestName === '') {
            throw new RuntimeException(
                'Invalid request name.',
            );
        }

        return ucfirst(
            strtolower(
                $requestName,
            ),
        );
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
                1,
            ) === 1
        ) {
            return;
        }

        $this->parameters = $this->readInput(
            "Enter Parameters\nExample: int \$id, string \$name",
        );
    }

    /**
     * Validate every possible conflict before writing anything.
     */
    protected function validateBeforeCreation(): void
    {
        if (! $this->files->isDirectory($this->backendPath)) {
            throw new RuntimeException(
                "Backend directory for workflow [{$this->workflowName}] does not exist.",
            );
        }

        if (! $this->files->isDirectory($this->viewsPath)) {
            if ($this->createView) {
                throw new RuntimeException(
                    "Views directory for workflow [{$this->workflowName}] does not exist.",
                );
            }
        }

        if (! $this->files->isFile($this->controllerFile)) {
            throw new RuntimeException(
                "Controller file [{$this->controllerFile}] does not exist.",
            );
        }

        if (! $this->files->isFile($this->routesFile)) {
            throw new RuntimeException(
                "Routes file [{$this->routesFile}] does not exist.",
            );
        }

        $controller = $this->files->get(
            $this->controllerFile,
        );

        if (
            preg_match(
                '/public\s+function\s+' .
                preg_quote($this->endpointName, '/') .
                '\s*\(/',
                $controller,
            )
        ) {
            throw new RuntimeException(
                "Endpoint [{$this->endpointName}] already exists in controller.",
            );
        }

        $routes = $this->files->get(
            $this->routesFile,
        );

        if (
            $this->routeAlreadyExists(
                $routes,
            )
        ) {
            throw new RuntimeException(
                "Endpoint [{$this->endpointName}] already exists in routes.",
            );
        }

        if ($this->createView) {
            $viewFile = $this->viewFile();

            if ($this->files->exists($viewFile)) {
                throw new RuntimeException(
                    "View [{$this->endpointName}] already exists.",
                );
            }
        }

        if ($this->createRequest) {
            if ($this->files->exists($this->requestFile)) {
                throw new RuntimeException(
                    "Request [{$this->requestName}Request] already exists.",
                );
            }
        }
    }

    protected function routeAlreadyExists(
        string $routes,
    ): bool {
        return (bool) preg_match(
            '/->name\(\s*[\'"][^\'"]*\.'
            . preg_quote($this->endpointName, '/')
            . '[\'"]\s*\)/',
            $routes,
        );
    }

    protected function readInput(
        string $title,
    ): string {
        while (true) {
            $this->newLine();

            $value = trim(
                (string) $this->ask(
                    $title,
                ),
            );

            if ($value !== '') {
                return $value;
            }

            $this->error(
                'Value cannot be empty.',
            );
        }
    }

    protected function menu(
        string $title,
        array $options,
        int $default = 1,
    ): int {
        while (true) {
            $this->newLine();

            $this->line(
                $title,
            );

            $this->newLine();

            foreach ($options as $index => $option) {
                $this->line(
                    '[' . ($index + 1) . '] ' . $option,
                );
            }

            $this->newLine();

            $choice = trim(
                (string) $this->ask(
                    '>',
                    (string) $default,
                ),
            );

            if (
                ctype_digit($choice) &&
                (int) $choice >= 1 &&
                (int) $choice <= count($options)
            ) {
                return (int) $choice;
            }

            $this->error(
                'Invalid selection.',
            );
        }
    }

    protected function generateControllerMethod(): void
    {
        $controller = $this->files->get(
            $this->controllerFile,
        );

        $controller = $this->addControllerUses(
            $controller,
        );

        $method = $this->buildControllerMethod();

        $controller = preg_replace(
            '/}\s*$/',
            PHP_EOL .
            $method .
            PHP_EOL .
            '}',
            $controller,
            1,
        );

        if ($controller === null) {
            throw new RuntimeException(
                'Unable to add the endpoint method to the controller.',
            );
        }

        $this->files->put(
            $this->controllerFile,
            $controller,
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

        if ($this->createRequest) {
            $parameters[] =
                $this->requestName .
                'Request $request';
        }

        if ($this->parameters !== '') {
            $parameters[] = $this->parameters;
        }

        return implode(
            ', ',
            $parameters,
        );
    }

    protected function buildControllerBody(): string
    {
        if ($this->createView) {
            return
                "        return view('{$this->moduleName}::{$this->workflowName}.{$this->endpointName}');";
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

            'View' =>
                "        abort(501);",

            default => throw new RuntimeException(
                'Unsupported return type.',
            ),
        };
    }

    protected function addControllerUses(
        string $controller,
    ): string {
        $uses = [];

        if ($this->createRequest) {
            $uses[] =
                'use App\\MCF\\Modules\\' .
                $this->moduleName .
                '\\' .
                $this->workflowName .
                '\\Backend\\Request\\' .
                $this->requestName .
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
                1,
            );

            if ($controller === null) {
                throw new RuntimeException(
                    'Unable to add controller imports.',
                );
            }
        }

        return $controller;
    }

    protected function generateRoute(): void
    {
        $routes = $this->files->get(
            $this->routesFile,
        );

        $routes = $this->addRouteControllerUse(
            $routes,
        );

        $routes .=
            PHP_EOL .
            PHP_EOL .
            $this->buildRoute();

        $this->files->put(
            $this->routesFile,
            $routes,
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
            $this->endpointName,
        );

        if ($this->parameters === '') {
            return $uri;
        }

        preg_match_all(
            '/\$([A-Za-z_][A-Za-z0-9_]*)/',
            $this->parameters,
            $matches,
        );

        foreach ($matches[1] as $parameter) {
            $uri .= '/{' . $parameter . '}';
        }

        return $uri;
    }

    protected function addRouteControllerUse(
        string $routes,
    ): string {
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
                PREG_OFFSET_CAPTURE,
            )
        ) {
            $last = end(
                $matches[0],
            );

            $position =
                $last[1] +
                strlen(
                    $last[0],
                );

            return substr_replace(
                $routes,
                PHP_EOL . $use,
                $position,
                0,
            );
        }

        $result = preg_replace(
            '/^<\?php\s*/',
            "<?php\n\n{$use}\n\n",
            $routes,
            1,
        );

        if ($result === null) {
            throw new RuntimeException(
                'Unable to add controller import to routes.',
            );
        }

        return $result;
    }

    protected function generateView(): void
    {
        $viewFile = $this->viewFile();

        $stub = $this->files->get(
            __DIR__ . '/../Stubs/Endpoint/view.stub',
        );

        $this->files->put(
            $viewFile,
            $stub,
        );
    }

    protected function viewFile(): string
    {
        return
            $this->viewsPath .
            '/' .
            $this->endpointName .
            '.blade.php';
    }

    /**
     * Restore modified files and remove files created by this operation.
     */
    protected function rollbackCreation(
        string $controllerBackup,
        string $routesBackup,
        bool $requestCreated,
        bool $viewCreated,
    ): void {
        try {
            $this->files->put(
                $this->controllerFile,
                $controllerBackup,
            );

            $this->files->put(
                $this->routesFile,
                $routesBackup,
            );

            if (
                $viewCreated &&
                $this->files->exists(
                    $this->viewFile(),
                )
            ) {
                $this->files->delete(
                    $this->viewFile(),
                );
            }

            if (
                $requestCreated &&
                $this->files->exists(
                    $this->requestFile,
                )
            ) {
                $this->files->delete(
                    $this->requestFile,
                );

                $requestDirectory = dirname(
                    $this->requestFile,
                );

                if (
                    $this->files->isDirectory(
                        $requestDirectory,
                    ) &&
                    $this->files->isEmptyDirectory(
                        $requestDirectory,
                    )
                ) {
                    $this->files->deleteDirectory(
                        $requestDirectory,
                    );
                }
            }
        } catch (Throwable) {
            /*
             * The original generation exception is more useful to the
             * developer than a secondary rollback exception.
             */
        }
    }

    protected function showSummary(): void
    {
        $this->newLine();

        $this->info(
            'Endpoint created successfully.',
        );

        $this->newLine();

        $this->line(
            "Module    : {$this->moduleName}",
        );

        $this->line(
            "Workflow  : {$this->workflowName}",
        );

        $this->line(
            "Endpoint  : {$this->endpointName}",
        );

        $this->line(
            "Method    : {$this->httpMethod}",
        );

        $this->line(
            "Return    : {$this->returnType}",
        );

        $this->line(
            'View      : ' .
            ($this->createView ? 'Yes' : 'No'),
        );

        $this->line(
            'Request   : ' .
            ($this->createRequest ? 'Yes' : 'No'),
        );

        if ($this->createRequest) {
            $this->line(
                "Request Name: {$this->requestName}Request",
            );
        }

        if ($this->parameters !== '') {
            $this->line(
                "Parameters: {$this->parameters}",
            );
        }

        $this->newLine();

        $this->comment(
            'To remove this endpoint, run:',
        );

        $this->line(
            "php artisan mcf:endpoint:remove " .
            "{$this->moduleName} " .
            "{$this->workflowName} " .
            "{$this->endpointName}",
        );

        $this->newLine();
    }
}