<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/getFilesInFolder.php';

$deprecations = json_decode(file_get_contents(__DIR__ . '/deprecations.json'), true);

$scanner = new \DeprecationDetector\DeprecatedUsageScanner();

$folders = [
    '/mnt/c/Users/mati/Documents/elementor-editor-dev/wordpress/wp-content/plugins/royal-elementor-addons',
    '/mnt/c/Users/mati/Documents/elementor-editor-dev/wordpress/wp-content/plugins/wordpress-seo',
];

foreach ($folders as $folder) {
    $deprecatedFunctionsAndHooks = $scanner->scanFolderForDeprecations($folder, $deprecations);
    $folderParts = explode('/',$folder);
    $reportFileName = $folderParts[count($folderParts) - 1] . '--deprecation-usage.json';
    $jsonOutput = json_encode($deprecatedFunctionsAndHooks, JSON_PRETTY_PRINT);
    file_put_contents($reportFileName, $jsonOutput);
}
