<?php

declare(strict_types=1);

namespace MCF\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;

class MCFFileLoader extends FileLoader
{
    protected array $translations = [];

    public function __construct(
        Filesystem $files,
        string|array $path,
        TranslationLoader $loader,
        string $modulesPath,
    ) {
        parent::__construct($files, $path);

        $this->translations = $loader->load($modulesPath);
    }

    protected function loadJsonPaths($locale): array
    {
        $translations = parent::loadJsonPaths($locale);

        if (isset($this->translations[$locale])) {
            $translations = array_merge(
                $translations,
                $this->translations[$locale]
            );
        }

        return $translations;
    }
}