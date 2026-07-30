<?php

declare(strict_types=1);

namespace MCF\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class TranslationLoader
{
    /**
     *
     * [
     *     'ar' => [...],
     *     'en' => [...],
     * ]
     */
    public function load(string $modulesPath): array
    {
        if (! is_dir($modulesPath)) {
            return [];
        }

        $translations = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulesPath)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if ($file->getExtension() !== 'json') {
                continue;
            }

            if (! str_contains($file->getPath(), DIRECTORY_SEPARATOR . 'Lang' . DIRECTORY_SEPARATOR)) {
                continue;
            }

            $locale = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            $content = file_get_contents($file->getRealPath());

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(
                    "Invalid JSON: {$file->getRealPath()}"
                );
            }

            $translations[$locale] ??= [];

            foreach ($data as $key => $value) {

                if (
                    isset($translations[$locale][$key]) &&
                    $translations[$locale][$key] !== $value
                ) {
                    throw new RuntimeException(
                        "Duplicate translation key '{$key}' for locale '{$locale}'."
                    );
                }

                $translations[$locale][$key] = $value;
            }
        }

        return $translations;
    }
}