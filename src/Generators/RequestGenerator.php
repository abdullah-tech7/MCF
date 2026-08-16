<?php

declare (strict_types = 1);

namespace MCF\Generators;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

final class RequestGenerator
{
    protected Filesystem $files;

    public function __construct()
    {
        $this->files = new Filesystem();
    }

    /**
     * Generate a Request for an existing MCF workflow.
     */
    public function generate(
        string $moduleName,
        string $workflowName,
        string $requestName,
    ): void {
        $modulePath = app_path(
            "MCF/Modules/{$moduleName}",
        );

        if (! $this->files->isDirectory($modulePath)) {
            throw new RuntimeException(
                "Module [{$moduleName}] does not exist.",
            );
        }

        $workflowPath = $modulePath
            . DIRECTORY_SEPARATOR
            . $workflowName;

        if (! $this->files->isDirectory($workflowPath)) {
            throw new RuntimeException(
                "Workflow [{$workflowName}] does not exist in module [{$moduleName}].",
            );
        }

        $backendPath = $workflowPath
            . DIRECTORY_SEPARATOR
            . 'Backend';

        if (! $this->files->isDirectory($backendPath)) {
            throw new RuntimeException(
                "Backend directory for workflow [{$workflowName}] does not exist.",
            );
        }

        $requestName = $this->normalizeRequestName(
            $requestName,
        );

        $requestDirectory = $backendPath
            . DIRECTORY_SEPARATOR
            . 'Request';

        $requestFile = $requestDirectory
            . DIRECTORY_SEPARATOR
            . $requestName
            . 'Request.php';

        if ($this->files->exists($requestFile)) {
            throw new RuntimeException(
                "Request [{$requestName}Request] already exists.",
            );
        }

        /*
         * Create Request directory only when it does not exist.
         */
        $this->files->ensureDirectoryExists(
            $requestDirectory,
        );

        $this->generateRequest(
            requestFile: $requestFile,
            moduleName: $moduleName,
            workflowName: $workflowName,
            requestName: $requestName,
        );
    }

    /**
     * Normalize the request name to PascalCase.
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

    /**
     * Generate the Request file from the MCF stub.
     */
    protected function generateRequest(
        string $requestFile,
        string $moduleName,
        string $workflowName,
        string $requestName,
    ): void {
        $stubPath = __DIR__
            . '/../Stubs/Endpoint/Request.stub';

        if (! $this->files->isFile($stubPath)) {
            throw new RuntimeException(
                "Request stub was not found: {$stubPath}",
            );
        }

        $content = $this->files->get(
            $stubPath,
        );

        $requestNamespace =
            "App\\MCF\\Modules\\{$moduleName}\\{$workflowName}\\Backend\\Request";

        $content = str_replace(
            [
                '{{ RequestNamespace }}',
                '{{ RequestName }}',
            ],
            [
                $requestNamespace,
                $requestName,
            ],
            $content,
        );

        $this->files->put(
            $requestFile,
            $content,
        );
    }
}
