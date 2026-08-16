<?php

declare(strict_types=1);

namespace MCF\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use Throwable;

class RemoveEndpointCommand extends Command
{
    protected $signature =
        'mcf:endpoint:remove
        {module : Module name}
        {workflow : Workflow name}
        {endpoint : Endpoint name}';

    protected $description =
        'Remove an endpoint from an existing workflow.';

    protected Filesystem $files;

    protected string $moduleName;

    protected string $workflowName;

    protected string $endpointName;

    protected string $modulePath;

    protected string $backendPath;

    protected string $viewsPath;

    protected string $requestPath;

    protected string $controllerFile;

    protected string $routesFile;

    protected string $requestName = '';

    protected string $requestFile = '';

    protected bool $hasRequest = false;

    public function __construct(
        Filesystem $files,
    ) {
        parent::__construct();

        $this->files = $files;
    }

    public function handle(): int
    {
        try {
            /*
             * --------------------------------------------------------------
             * Collect arguments
             * --------------------------------------------------------------
             */

            $this->moduleName = trim(
                (string) $this->argument('module'),
            );

            $this->workflowName = trim(
                (string) $this->argument('workflow'),
            );

            $this->endpointName = trim(
                (string) $this->argument('endpoint'),
            );

            /*
             * --------------------------------------------------------------
             * Prepare paths
             * --------------------------------------------------------------
             */

            $this->initializePaths();

            /*
             * --------------------------------------------------------------
             * Validate everything before changing any file.
             * --------------------------------------------------------------
             */

            $this->validateEndpoint();

            /*
             * --------------------------------------------------------------
             * Detect the Request belonging to this Endpoint.
             * --------------------------------------------------------------
             */

            $this->detectRequest();

            /*
             * --------------------------------------------------------------
             * Keep the current state for rollback.
             * --------------------------------------------------------------
             */

            $controllerBackup = $this->files->get(
                $this->controllerFile,
            );

            $routesBackup = $this->files->get(
                $this->routesFile,
            );

            $requestBackup = null;

            if (
                $this->hasRequest &&
                $this->files->isFile($this->requestFile)
            ) {
                $requestBackup = $this->files->get(
                    $this->requestFile,
                );
            }

            $viewFile = $this->viewFile();

            $viewBackup = null;

            if ($this->files->isFile($viewFile)) {
                $viewBackup = $this->files->get(
                    $viewFile,
                );
            }

            try {
                /*
                 * ----------------------------------------------------------
                 * Remove Controller
                 * ----------------------------------------------------------
                 */

                $this->removeControllerMethod();

                /*
                 * ----------------------------------------------------------
                 * Remove Request
                 * ----------------------------------------------------------
                 */

                if ($this->hasRequest) {
                    $this->removeRequest();
                }

                /*
                 * ----------------------------------------------------------
                 * Remove Route
                 * ----------------------------------------------------------
                 */

                $this->removeRoute();

                /*
                 * ----------------------------------------------------------
                 * Remove View
                 * ----------------------------------------------------------
                 */

                $this->removeView();
            } catch (Throwable $exception) {
                /*
                 * ----------------------------------------------------------
                 * Rollback
                 * ----------------------------------------------------------
                 */

                $this->rollback(
                    controllerBackup: $controllerBackup,
                    routesBackup: $routesBackup,
                    requestBackup: $requestBackup,
                    viewBackup: $viewBackup,
                );

                throw $exception;
            }

            $this->showSummary();

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->newLine();

            $this->error(
                'Endpoint removal failed.',
            );

            $this->error(
                $exception->getMessage(),
            );

            return self::FAILURE;
        }
    }

    protected function initializePaths(): void
    {
        $this->modulePath =
            app_path(
                'MCF/Modules/' .
                $this->moduleName .
                '/' .
                $this->workflowName,
            );

        $this->backendPath =
            $this->modulePath .
            '/Backend';

        $this->viewsPath =
            $this->modulePath .
            '/Views';

        $this->requestPath =
            $this->backendPath .
            '/Request';

        $this->controllerFile =
            $this->backendPath .
            '/' .
            $this->workflowName .
            'Controller.php';

        $this->routesFile =
            $this->backendPath .
            '/' .
            $this->workflowName .
            'Routes.php';
    }

    /**
     * Validate the complete Endpoint before modifying anything.
     */
    protected function validateEndpoint(): void
    {
        if (! $this->files->isDirectory($this->modulePath)) {
            throw new RuntimeException(
                "Workflow [{$this->moduleName}/{$this->workflowName}] does not exist.",
            );
        }

        if (! $this->files->isFile($this->controllerFile)) {
            throw new RuntimeException(
                "{$this->workflowName}Controller.php not found.",
            );
        }

        if (! $this->files->isFile($this->routesFile)) {
            throw new RuntimeException(
                "{$this->workflowName}Routes.php not found.",
            );
        }

        $controller = $this->files->get(
            $this->controllerFile,
        );

        if (! $this->controllerMethodExists($controller)) {
            throw new RuntimeException(
                "Endpoint [{$this->endpointName}] was not found in {$this->workflowName}Controller.",
            );
        }

        $routes = $this->files->get(
            $this->routesFile,
        );

        if (! $this->routeExists($routes)) {
            throw new RuntimeException(
                "Route for endpoint [{$this->endpointName}] was not found.",
            );
        }
    }

    /**
     * Detect whether this Endpoint has its own Request.
     *
     * The Request is derived from the Endpoint name:
     *
     * login
     *     ↓
     * LoginRequest
     */
    protected function detectRequest(): void
    {
        $this->requestName = $this->normalizeRequestName(
            $this->endpointName,
        );

        $this->requestFile =
            $this->requestPath .
            '/' .
            $this->requestName .
            'Request.php';

        $controller = $this->files->get(
            $this->controllerFile,
        );

        $requestUse =
            'use App\\MCF\\Modules\\' .
            $this->moduleName .
            '\\' .
            $this->workflowName .
            '\\Backend\\Request\\' .
            $this->requestName .
            'Request;';

        /*
         * The Request belongs to this Endpoint only when
         * both the controller import and Request parameter exist.
         */
        $hasRequestUse = str_contains(
            $controller,
            $requestUse,
        );

        $hasRequestParameter = (bool) preg_match(
            '/\b' .
            preg_quote(
                $this->requestName . 'Request',
                '/',
            ) .
            '\s+\$request\b/',
            $this->endpointMethod(
                $controller,
            ),
        );

        $this->hasRequest =
            $hasRequestUse &&
            $hasRequestParameter;

        if (! $this->hasRequest) {
            return;
        }

        if (! $this->files->isFile($this->requestFile)) {
            throw new RuntimeException(
                "Request file [{$this->requestName}Request.php] is referenced by endpoint [{$this->endpointName}] but was not found.",
            );
        }
    }

    /**
     * Normalize Request name.
     *
     * Example:
     *
     * login
     * Login
     * LOGIN
     *
     * all become:
     *
     * LoginRequest
     */
    protected function normalizeRequestName(
        string $name,
    ): string {
        $name = trim($name);

        if ($name === '') {
            throw new RuntimeException(
                'Endpoint name cannot be empty.',
            );
        }

        $name = preg_replace(
            '/[^A-Za-z0-9]+/',
            '',
            $name,
        );

        if ($name === '') {
            throw new RuntimeException(
                'Invalid endpoint name.',
            );
        }

        return ucfirst(
            strtolower($name),
        );
    }

    protected function controllerMethodExists(
        string $controller,
    ): bool {
        return (bool) preg_match(
            '/public\s+function\s+' .
            preg_quote($this->endpointName, '/') .
            '\s*\(/',
            $controller,
        );
    }

    protected function endpointMethod(
        string $controller,
    ): string {
        $pattern =
            '/public\s+function\s+' .
            preg_quote($this->endpointName, '/') .
            '\s*\([^)]*\).*?\{.*?\n\s*\}/s';

        if (
            ! preg_match(
                $pattern,
                $controller,
                $matches,
            )
        ) {
            throw new RuntimeException(
                "Unable to read endpoint method [{$this->endpointName}].",
            );
        }

        return $matches[0];
    }

    protected function routeExists(
        string $routes,
    ): bool {
        $routeName =
            strtolower($this->moduleName) .
            '.' .
            strtolower($this->workflowName) .
            '.' .
            $this->endpointName;

        return str_contains(
            $routes,
            "->name('{$routeName}')",
        );
    }

    protected function removeControllerMethod(): void
    {
        $controller = $this->files->get(
            $this->controllerFile,
        );

        /*
         * --------------------------------------------------------------
         * Remove the Endpoint method.
         * --------------------------------------------------------------
         */

        $pattern =
            '/\n\s*public\s+function\s+' .
            preg_quote($this->endpointName, '/') .
            '\s*\([^)]*\)\s*:\s*[^{]+\{.*?\n\s*\}/s';

        if (
            ! preg_match(
                $pattern,
                $controller,
            )
        ) {
            throw new RuntimeException(
                "Endpoint [{$this->endpointName}] was not found in {$this->workflowName}Controller.",
            );
        }

        $controller = preg_replace(
            $pattern,
            '',
            $controller,
            1,
        );

        if ($controller === null) {
            throw new RuntimeException(
                'Unable to remove endpoint method.',
            );
        }

        /*
         * --------------------------------------------------------------
         * Remove the Request import if this Endpoint owns a Request.
         * --------------------------------------------------------------
         */

        if ($this->hasRequest) {
            $requestUse =
                'use App\\MCF\\Modules\\' .
                $this->moduleName .
                '\\' .
                $this->workflowName .
                '\\Backend\\Request\\' .
                $this->requestName .
                'Request;';

            $controller = preg_replace(
                '/^\s*' .
                preg_quote($requestUse, '/') .
                '\R?/m',
                '',
                $controller,
                1,
            );

            if ($controller === null) {
                throw new RuntimeException(
                    'Unable to remove Request import from controller.',
                );
            }
        }

        /*
         * --------------------------------------------------------------
         * Normalize excessive blank lines.
         * --------------------------------------------------------------
         */

        $controller = preg_replace(
            "/\n{3,}/",
            PHP_EOL . PHP_EOL,
            $controller,
        );

        if ($controller === null) {
            throw new RuntimeException(
                'Unable to normalize controller file.',
            );
        }

        $this->files->put(
            $this->controllerFile,
            $controller,
        );
    }

    protected function removeRequest(): void
    {
        if (! $this->files->isFile($this->requestFile)) {
            throw new RuntimeException(
                "Request file [{$this->requestName}Request.php] was not found.",
            );
        }

        $this->files->delete(
            $this->requestFile,
        );

        /*
         * Do not remove Request directory if other Requests exist.
         */
        if (
            $this->files->isDirectory(
                $this->requestPath,
            ) &&
            $this->files->isEmptyDirectory(
                $this->requestPath,
            )
        ) {
            $this->files->deleteDirectory(
                $this->requestPath,
            );
        }
    }

    protected function removeRoute(): void
    {
        $routes = $this->files->get(
            $this->routesFile,
        );

        $routeName =
            strtolower($this->moduleName) .
            '.' .
            strtolower($this->workflowName) .
            '.' .
            $this->endpointName;

        $namePosition = strpos(
            $routes,
            "->name('{$routeName}')",
        );

        if ($namePosition === false) {
            throw new RuntimeException(
                "Route for endpoint [{$this->endpointName}] was not found.",
            );
        }

        /*
         * Locate the Route:: declaration belonging to this route.
         */
        $routeStart = strrpos(
            substr(
                $routes,
                0,
                $namePosition,
            ),
            'Route::',
        );

        if ($routeStart === false) {
            throw new RuntimeException(
                'Unable to locate route start.',
            );
        }

        /*
         * Locate the end of the route.
         */
        $routeEnd = strpos(
            $routes,
            ';',
            $namePosition,
        );

        if ($routeEnd === false) {
            throw new RuntimeException(
                'Unable to locate route end.',
            );
        }

        $routes =
            substr(
                $routes,
                0,
                $routeStart,
            ) .
            substr(
                $routes,
                $routeEnd + 1,
            );

        $routes = preg_replace(
            "/\n{3,}/",
            PHP_EOL . PHP_EOL,
            $routes,
        );

        if ($routes === null) {
            throw new RuntimeException(
                'Unable to normalize routes file.',
            );
        }

        $this->files->put(
            $this->routesFile,
            $routes,
        );
    }

    protected function removeView(): void
    {
        $viewFile = $this->viewFile();

        if (! $this->files->isFile($viewFile)) {
            return;
        }

        $this->files->delete(
            $viewFile,
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
     * Restore every file changed by the removal operation.
     */
    protected function rollback(
        string $controllerBackup,
        string $routesBackup,
        ?string $requestBackup,
        ?string $viewBackup,
    ): void {
        try {
            /*
             * Restore Controller.
             */
            $this->files->put(
                $this->controllerFile,
                $controllerBackup,
            );

            /*
             * Restore Routes.
             */
            $this->files->put(
                $this->routesFile,
                $routesBackup,
            );

            /*
             * Restore Request if it existed before the operation.
             */
            if ($requestBackup !== null) {
                $this->files->ensureDirectoryExists(
                    dirname(
                        $this->requestFile,
                    ),
                );

                $this->files->put(
                    $this->requestFile,
                    $requestBackup,
                );
            }

            /*
             * Restore View if it existed before the operation.
             */
            if ($viewBackup !== null) {
                $this->files->ensureDirectoryExists(
                    dirname(
                        $this->viewFile(),
                    ),
                );

                $this->files->put(
                    $this->viewFile(),
                    $viewBackup,
                );
            }
        } catch (Throwable) {
            /*
             * Keep the original exception as the primary error.
             */
        }
    }

    protected function showSummary(): void
    {
        $this->newLine();

        $this->info(
            'Endpoint removed successfully.',
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
            'Request   : ' .
            ($this->hasRequest
                ? "{$this->requestName}Request removed"
                : 'None'),
        );

        $this->line(
            'View      : ' .
            (
                $this->files->isFile(
                    $this->viewFile(),
                )
                    ? 'No'
                    : 'Removed if existed'
            ),
        );

        $this->newLine();
    }
}