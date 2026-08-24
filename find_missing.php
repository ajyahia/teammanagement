<?php
$jsonPath = "c:/laragon/www/teammanagement/lang/ar.json";
$translations = json_decode(file_get_contents($jsonPath), true);
if (!$translations) $translations = [];
$missing = [];

$directories = [
    "c:/laragon/www/teammanagement/resources/views",
    "c:/laragon/www/teammanagement/app/Http/Controllers"
];

foreach ($directories as $dirPath) {
    $dir = new RecursiveDirectoryIterator($dirPath);
    $ite = new RecursiveIteratorIterator($dir);
    $files = new RegexIterator($ite, "/^.+\.(php)$/i", RecursiveRegexIterator::GET_MATCH);

    foreach($files as $file) {
        $content = file_get_contents($file[0]);
        // match __("string") or __('string')
        if (preg_match_all("/__\(\s*['\"]([^'\"]+)['\"]\s*\)/u", $content, $matches)) {
            foreach($matches[1] as $key) {
                if (!isset($translations[$key]) && !in_array($key, $missing)) {
                    $missing[] = $key;
                }
            }
        }
    }
}

// Map the missing keys to their empty Arabic translations, or just print them
$newTranslations = [];
foreach ($missing as $key) {
    echo $key . "\n";
}
