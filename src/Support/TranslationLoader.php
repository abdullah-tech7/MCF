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

    $duplicates = [];

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

                    if (isset($translations[$locale][$key])) {

                        if ($translations[$locale][$key] !== $value) {

                            $duplicates[$locale][] = [
                                'key'  => $key,
                                'file' => $jsonFile,
                            ];
                        }

                        continue;
                    }

                    $translations[$locale][$key] = $value;
                }
            }
        }
    }

    if (! empty($duplicates)) {

        $message = "Duplicate translation keys were found.\n\n";

        foreach ($duplicates as $locale => $items) {

            $message .= "Locale: {$locale}\n";
            $message .= str_repeat('-', 60)."\n";

            foreach ($items as $duplicate) {

                $message .= "• {$duplicate['key']}\n";
                $message .= "  File: {$duplicate['file']}\n";
            }

            $message .= "\n";
        }

        throw new RuntimeException(trim($message));
    }

    return $translations;
}
}