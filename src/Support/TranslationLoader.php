<?php

declare(strict_types=1);

namespace MCF\Support;

use RuntimeException;

class TranslationLoader
{
    /**
     * Load all framework translations.
     *
     * Translation files are stored centrally in:
     *
     * MCF/
     *   Language/
     *     ar.json
     *     en.json
     *     ...
     *
     * Each locale must have one JSON file only.
     */
    public function load(string $modulesPath): array
    {
        $translations = [];
        $registry = [];

        /*
        |--------------------------------------------------------------------------
        | Framework Language
        |--------------------------------------------------------------------------
        */

        $frameworkPath = dirname($modulesPath);

        $languagePath = $frameworkPath
            .DIRECTORY_SEPARATOR
            .'Language';

        if (is_dir($languagePath)) {

            foreach (
                glob(
                    $languagePath
                    .DIRECTORY_SEPARATOR
                    .'*.json'
                ) as $jsonFile
            ) {

                $this->loadJsonFile(
                    jsonFile: $jsonFile,
                    source: 'MCF::Language',
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
                $translations[$locale][$key] =
                    $occurrences[0]['value'];

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

                foreach (
                    $duplicate['occurrences']
                    as $index => $item
                ) {

                    $lines[] =
                        ($index + 1)
                        .'. '
                        .$item['source'];

                    $lines[] =
                        "   Value : {$item['value']}";

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

        if ($content === false) {
            throw new RuntimeException(
                "Unable to read translation file:\n{$jsonFile}"
            );
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                "Invalid JSON:\n{$jsonFile}"
                .PHP_EOL
                .'Error: '
                .json_last_error_msg()
            );
        }

        if (! is_array($data)) {
            throw new RuntimeException(
                "Translation file must contain a JSON object:\n{$jsonFile}"
            );
        }

        $locale = pathinfo(
            $jsonFile,
            PATHINFO_FILENAME
        );

        foreach ($data as $key => $value) {

            /*
            |--------------------------------------------------------------------------
            | Section Markers
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | "--- User | Authentication ---":
            |     "----------------------------------------"
            |
            | These entries are only for organizing the language file.
            | They are not translation keys.
            |
            */

            if ($this->isSectionMarker($key)) {
                continue;
            }

            $registry[$locale][$key][] = [
                'source' => $source,
                'value' => $value,
            ];
        }
    }

    /**
     * Determine whether a JSON key is a language section marker.
     */
    protected function isSectionMarker(string $key): bool
    {
        return str_starts_with($key, '--- ')
            && str_ends_with($key, ' ---');
    }
}