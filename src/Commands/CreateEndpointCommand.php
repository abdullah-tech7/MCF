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
        $this->moduleName = $this->input('Module');

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
        $this->workflowName = $this->input('Workflow');

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
            "{$this->backendPath}/Controller.php";

        $this->routesFile =
            "{$this->backendPath}/routes.php";
    }

    protected function askEndpointName(): void
    {
        $this->endpointName = $this->input(
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

        $this->parameters = $this->input(
            "Enter Parameters\nExample: int \$id, string \$name"
        );
    }

    protected function input(string $title): string
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
        $controller = $this->files->get(
            $this->controllerFile
        );

        if (
            preg_match(
                '/function\s+' .
                preg_quote(
                    $this->endpointName,
                    '/'
                ) .
                '\s*\(/',
                $controller
            )
        ) {
            throw new RuntimeException(
                "Endpoint [{$this->endpointName}] already exists."
            );
        }

        $method = $this->buildMethod();

        $controller = preg_replace(
            '/}\s*$/',
            $method . PHP_EOL . '}',
            $controller,
            1
        );

        $this->files->put(
            $this->controllerFile,
            $controller
        );
    }

    protected function buildMethod(): string
    {
        $parameters = $this->buildParameters();

        return PHP_EOL .
            '    public function ' .
            $this->endpointName .
            '(' .
            $parameters .
            '): ' .
            $this->returnType .
            PHP_EOL .
            '    {' .
            PHP_EOL .
            $this->buildMethodBody() .
            PHP_EOL .
            '    }' .
            PHP_EOL;
    }

    protected function buildParameters(): string
    {
        $parameters = [];

        if ($this->injectWorkflowRequest) {
            $parameters[] =
                'Request $request';
        }

        if ($this->parameters !== '') {
            $parameters[] =
                $this->parameters;
        }

        return implode(
            ', ',
            $parameters
        );
    }

    protected function buildMethodBody(): string
    {
        switch ($this->returnType) {

            case 'View':
                return $this->buildViewBody();

            case 'RedirectResponse':
                return $this->buildRedirectBody();

            case 'JsonResponse':
                return $this->buildJsonBody();

            case 'BinaryFileResponse':
                return $this->buildBinaryFileBody();

            case 'StreamedResponse':
                return $this->buildStreamedResponseBody();

            case 'Response':
                return $this->buildResponseBody();

            default:
                throw new RuntimeException(
                    'Unsupported return type.'
                );
        }
    }

        protected function buildViewBody(): string
    {
        return
            '        return view(' .
            "'" .
            strtolower($this->endpointName) .
            "'" .
            ');';
    }

    protected function buildRedirectBody(): string
    {
        return
            "        return redirect()->back();";
    }

    protected function buildJsonBody(): string
    {
        return
            "        return response()->json([]);";
    }

    protected function buildBinaryFileBody(): string
    {
        return
            "        abort(501);";
    }

    protected function buildStreamedResponseBody(): string
    {
        return
            "        abort(501);";
    }

    protected function buildResponseBody(): string
    {
        return
            "        return response('');";
    }

    protected function generateRoute(): void
    {
        $routes = $this->files->get(
            $this->routesFile
        );

        if (
            preg_match(
                '/::' .
                preg_quote(
                    strtolower($this->httpMethod),
                    '/'
                ) .
                '\(\s*[\'"]\/' .
                preg_quote(
                    strtolower($this->endpointName),
                    '/'
                ) .
                '[\'"]/',
                $routes
            )
        ) {
            throw new RuntimeException(
                "Route already exists."
            );
        }

        $route = $this->buildRoute();

        $routes .= PHP_EOL . $route;

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
            "'/" .
            strtolower($this->endpointName) .
            "', " .
            '[Controller::class, ' .
            "'" .
            $this->endpointName .
            "'" .
            ']);';
    }



    protected function generateView(): void
    {
        $viewFile =
            $this->viewsPath .
            '/' .
            strtolower($this->endpointName) .
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

        $this->info(
            'Endpoint created successfully.'
        );

        $this->line(
            'Module   : ' .
            $this->moduleName
        );

        $this->line(
            'Workflow : ' .
            $this->workflowName
        );

        $this->line(
            'Endpoint : ' .
            $this->endpointName
        );

        $this->line(
            'Method   : ' .
            $this->httpMethod
        );

        $this->line(
            'Return   : ' .
            $this->returnType
        );

        $this->line(
            'View     : ' .
            ($this->createView ? 'Yes' : 'No')
        );

        $this->line(
            'Request  : ' .
            ($this->injectWorkflowRequest ? 'Yes' : 'No')
        );

        if ($this->parameters !== '') {
            $this->line(
                'Parameters : ' .
                $this->parameters
            );
        }
    }
}
