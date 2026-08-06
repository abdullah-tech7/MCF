<?php

declare(strict_types=1);

namespace MCF\Support;

use RuntimeException;

class TranslationLoader
{
    /**
     * Load all framework translations.
     *
     * Supported locations:
     *
     * Modules/
     *   Module/
     *     Workflow/
     *       Lang/
     *         ar.json
     *         en.json
     *
     * MCF/
     *   Mail/
     *     Lang/
     *       ar.json
     *       en.json
     */
    public function load(string $modulesPath): array
    {
        $translations = [];
        $registry = [];

        /*
        |--------------------------------------------------------------------------
        | Workflow Languages
        |--------------------------------------------------------------------------
        */

        if (is_dir($modulesPath)) {

            foreach (glob($modulesPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $modulePath) {

                $module = basename($modulePath);

                foreach (glob($modulePath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $workflowPath) {

                    $workflow = basename($workflowPath);

                    $langPath = $workflowPath . DIRECTORY_SEPARATOR . 'Lang';

                    if (! is_dir($langPath)) {
                        continue;
                    }

                    foreach (glob($langPath . DIRECTORY_SEPARATOR . '*.json') as $jsonFile) {

                        $this->loadJsonFile(
                            jsonFile: $jsonFile,
                            source: "{$module}::{$workflow}",
                            registry: $registry,
                        );
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Framework Mail Languages
        |--------------------------------------------------------------------------
        */

        $frameworkPath = dirname($modulesPath);

        $mailLangPath = $frameworkPath
            . DIRECTORY_SEPARATOR
            . 'Mail'
            . DIRECTORY_SEPARATOR
            . 'Lang';

        if (is_dir($mailLangPath)) {

            foreach (glob($mailLangPath . DIRECTORY_SEPARATOR . '*.json') as $jsonFile) {

                $this->loadJsonFile(
                    jsonFile: $jsonFile,
                    source: 'MCF::Mail',
                    registry: $registry,
                );
            }
        }

                /*
        |--------------------------------------------------------------------------
        | Merge Translations
        |--------------------------------------------------------------------------
        */

        $duplicates = [];

        foreach ($registry as $locale => $keys) {

            foreach ($keys as $key => $occurrences) {

                // Always use the first definition.
                $translations[$locale][$key] = $occurrences[0]['value'];

                // Ignore duplicates when all values are identical.
                $values = array_unique(
                    array_column($occurrences, 'value')
                );

                if (count($values) <= 1) {
                    continue;
                }

                $duplicates[] = [
                    'locale' => $locale,
                    'key' => $key,
                    'occurrences' => $occurrences,
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Conflicting Translations
        |--------------------------------------------------------------------------
        */

        if (! empty($duplicates)) {

            $lines = [];
            $lines[] = 'Conflicting translation keys detected.';
            $lines[] = '';

            foreach ($duplicates as $duplicate) {

                $lines[] = str_repeat('═', 72);
                $lines[] = "Locale : {$duplicate['locale']}";
                $lines[] = "Key    : {$duplicate['key']}";
                $lines[] = '';

                foreach ($duplicate['occurrences'] as $index => $item) {

                    $lines[] = ($index + 1).'. '.$item['source'];
                    $lines[] = "   Value : {$item['value']}";
                    $lines[] = '';
                }
            }

            throw new RuntimeException(
                implode(PHP_EOL, $lines)
            );
        }

        return $translations;
    }

    /**
     * Load a JSON translation file.
     */
    protected function loadJsonFile(
        string $jsonFile,
        string $source,
        array &$registry,
    ): void {

        $content = file_get_contents($jsonFile);

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                "Invalid JSON:\n{$jsonFile}"
            );
        }

        if (! is_array($data)) {
            throw new RuntimeException(
                "Translation file must contain a JSON object:\n{$jsonFile}"
            );
        }

        foreach ($data as $key => $value) {

            $locale = pathinfo($jsonFile, PATHINFO_FILENAME);

            $registry[$locale][$key][] = [
                'source' => $source,
                'value' => $value,
            ];
        }
    }
}