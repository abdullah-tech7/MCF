<?php

declare(strict_types=1);

namespace MCF\Support;

use RuntimeException;

class TranslationLoader
{
    /**
     * Loads all JSON translations from:
     *
     * app/MCF/Modules/
     *   Module/
     *     Workflow/
     *       Lang/
     *         ar.json
     *         en.json
     *
     * Result:
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

        foreach (glob($modulesPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $modulePath) {

            foreach (glob($modulePath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $workflowPath) {

                $langPath = $workflowPath . DIRECTORY_SEPARATOR . 'Lang';

                if (! is_dir($langPath)) {
                    continue;
                }

                foreach (glob($langPath . DIRECTORY_SEPARATOR . '*.json') as $jsonFile) {

                    $locale = pathinfo($jsonFile, PATHINFO_FILENAME);

                    $content = file_get_contents($jsonFile);

                    $data = json_decode($content, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new RuntimeException(
                            "Invalid JSON: {$jsonFile}"
                        );
                    }

                    if (! is_array($data)) {
                        throw new RuntimeException(
                            "Translation file must contain a JSON object: {$jsonFile}"
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
            }
        }

        return $translations;
    }
}