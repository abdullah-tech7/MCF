<?php

namespace MCF\Generators;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use MCF\Support\Path;

class ModuleGenerator
{
    /**
     * Generate a new module.
     */
    public function generate(string $name): void
    {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException('Module name cannot be empty.');
        }

        $modulePath = Path::modules() . DIRECTORY_SEPARATOR . $name;

        if (File::exists($modulePath)) {
            throw new InvalidArgumentException("Module [{$name}] already exists.");
        }

        $directories = [
            'Controllers',
            'Services',
            'Requests',
            'Policies',
            'Routes',
            'Views',
            'Lang',
        ];

        foreach ($directories as $directory) {
            File::makeDirectory(
                $modulePath . DIRECTORY_SEPARATOR . $directory,
                0755,
                true
            );
        }

        $this->createLangReadme($modulePath);
    }

    /**
     * Copy the language README into the module.
     */
    protected function createLangReadme(string $modulePath): void
    {
        $source = dirname(__DIR__)
            . DIRECTORY_SEPARATOR . 'Stubs'
            . DIRECTORY_SEPARATOR . 'Module'
            . DIRECTORY_SEPARATOR . 'README.md';

        $destination = $modulePath
            . DIRECTORY_SEPARATOR . 'Lang'
            . DIRECTORY_SEPARATOR . 'README.md';

        if (! File::exists($destination) && File::exists($source)) {
            File::copy($source, $destination);
        }
    }
}