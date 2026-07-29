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

        File::makeDirectory(
            $modulePath,
            0755,
            true
        );
    }
}