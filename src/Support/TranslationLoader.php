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

        /*
        |--------------------------------------------------------------------------
        | Registry
        |--------------------------------------------------------------------------
        |
        | [
        |   'ar' => [
        |       'Save' => [
        |           ['file'=>..., 'value'=>...],
        |           ['file'=>..., 'value'=>...],
        |       ]
        |   ]
        | ]
        |
        */

        $registry = [];

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
                            'file'  => $jsonFile,
                            'value' => $value,
                        ];
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Build translations + Detect duplicates
        |--------------------------------------------------------------------------
        */

        $errors = [];

        foreach ($registry as $locale => $keys) {

            foreach ($keys as $key => $occurrences) {

                /*
                |--------------------------------------------------------------------------
                | First value becomes the active translation.
                |--------------------------------------------------------------------------
                */

                $translations[$locale][$key] = $occurrences[0]['value'];

                if (count($occurrences) <= 1) {
                    continue;
                }

                $errors[] = [
                    'locale' => $locale,
                    'key' => $key,
                    'occurrences' => $occurrences,
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Throw one exception containing every duplicate.
        |--------------------------------------------------------------------------
        */

        if (! empty($errors)) {

            $message = [];
            $message[] = 'Duplicate translation keys detected.';
            $message[] = '';

            foreach ($errors as $error) {

                $message[] = str_repeat('=', 80);
                $message[] = "Locale : {$error['locale']}";
                $message[] = "Key    : {$error['key']}";
                $message[] = 'Occurrences: '.count($error['occurrences']);
                $message[] = '';

                foreach ($error['occurrences'] as $index => $entry) {

                    $message[] = ($index + 1).') '.$entry['file'];
                    $message[] = '   Value : '.var_export($entry['value'], true);
                    $message[] = '';
                }
            }

            throw new RuntimeException(
                implode(PHP_EOL, $message)
            );
        }

        return $translations;
    }
}