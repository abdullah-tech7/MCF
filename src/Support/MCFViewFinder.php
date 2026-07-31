<?php

declare(strict_types=1);

namespace MCF\Support;

use Illuminate\View\FileViewFinder;

class MCFViewFinder extends FileViewFinder
{
    /**
     * Supports:
     *
     * view('Shared::Layout.app');
     * view('Shared::Website.index');
     *
     * Maps to:
     *
     * Modules/
     * └── Shared/
     *     └── Layout/
     *         └── Views/
     *             └── app.blade.php
     */
    protected function findNamespacedView($name)
    {
        [$namespace, $view] = $this->parseNamespaceSegments($name);

        /*
        |--------------------------------------------------------------------------
        | Default Laravel behaviour
        |--------------------------------------------------------------------------
        |
        | Packages such as:
        |
        | mail::
        | pagination::
        | errors::
        | laravel-exceptions-renderer::
        |
        | must continue to work exactly as Laravel designed.
        |
        */

        if (! isset($this->hints[$namespace])) {
            return parent::findNamespacedView($name);
        }

        /*
        |--------------------------------------------------------------------------
        | Detect MCF Module
        |--------------------------------------------------------------------------
        */

        $isMcfModule = true;

        foreach ($this->hints[$namespace] as $path) {

            if (
                ! str_contains(
                    str_replace('\\', '/', $path),
                    '/app/MCF/Modules/'
                )
            ) {
                $isMcfModule = false;
                break;
            }
        }

        if (! $isMcfModule) {
            return parent::findNamespacedView($name);
        }

        /*
        |--------------------------------------------------------------------------
        | MCF Workflow support
        |--------------------------------------------------------------------------
        */

        $segments = explode('.', $view);

        if (count($segments) >= 2) {

            $workflow = array_shift($segments);

            $view = implode('.', $segments);

            $paths = [];

            foreach ($this->hints[$namespace] as $modulePath) {

                $workflowViewsPath =
                    rtrim($modulePath, DIRECTORY_SEPARATOR)
                    . DIRECTORY_SEPARATOR
                    . $workflow
                    . DIRECTORY_SEPARATOR
                    . 'Views';

                if ($this->files->isDirectory($workflowViewsPath)) {
                    $paths[] = $workflowViewsPath;
                }
            }

            if (! empty($paths)) {
                return $this->findInPaths($view, $paths);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        return parent::findNamespacedView($name);
    }
}