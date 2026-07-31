<?php

declare(strict_types=1);

namespace MCF\Support;

use RuntimeException;

class TranslationLoader
{
    /**
     * Load all module translations.
     *
     * Structure:
     *
     * Modules/
     *   Module/
     *     Workflow/
     *       Lang/
     *         ar.json
     *         en.json
     */
    public function load(string $modulesPath): array
    {
        if (! is_dir($modulesPath)) {
            return [];
        }

        $translations = [];
        $registry = [];

        foreach (glob($modulesPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $modulePath) {

            $module = basename($modulePath);

            foreach (glob($modulePath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $workflowPath) {

                $workflow = basename($workflowPath);

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
                            "Invalid JSON:\n{$jsonFile}"
                        );
                    }

                    if (! is_array($data)) {
                        throw new RuntimeException(
                            "Translation file must contain a JSON object:\n{$jsonFile}"
                        );
                    }

                    foreach ($data as $key => $value) {

                        $registry[$locale][$key][] = [
                            'module' => $module,
                            'workflow' => $workflow,
                            'value' => $value,
                        ];
                    }
                }
            }
        }

        $duplicates = [];

        foreach ($registry as $locale => $keys) {

            foreach ($keys as $key => $occurrences) {

                // أول تعريف هو الذي يستخدم فعلياً
                $translations[$locale][$key] = $occurrences[0]['value'];

                if (count($occurrences) > 1) {

                    $duplicates[] = [
                        'locale' => $locale,
                        'key' => $key,
                        'occurrences' => $occurrences,
                    ];
                }
            }
        }

        if (! empty($duplicates)) {

            $lines = [];
            $lines[] = 'Duplicate translation keys detected.';
            $lines[] = '';

            foreach ($duplicates as $duplicate) {

                $lines[] = str_repeat('═', 72);
                $lines[] = "Locale : {$duplicate['locale']}";
                $lines[] = "Key    : {$duplicate['key']}";
                $lines[] = '';

                foreach ($duplicate['occurrences'] as $index => $item) {

                    $lines[] = ($index + 1).". {$item['module']}::{$item['workflow']}";
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
}