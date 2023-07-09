<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/getFilesInFolder.php';

$deprecations = json_decode(file_get_contents(__DIR__ . '/deprecations.json'), true);

$scanner = new \DeprecationDetector\DeprecatedUsageScanner();

$plugins = glob('/mnt/c/Users/mati/Documents/elementor-editor-dev/wordpress/wp-content/plugins/*');

foreach ($plugins as $plugin) {
    $pluginParts = explode('/',$plugin);
    $pluginName = $pluginParts[count($pluginParts) - 1];

    if ( $pluginName === 'elementor' || $pluginName === 'elementor-cloud-wp-agent' ) {
        continue;
    }

    $deprecatedFunctionsAndHooks = $scanner->scanFolderForDeprecations($plugin, $deprecations);

    if ( empty($deprecatedFunctionsAndHooks) ) {
        continue;
    }

    $reportFileName = $pluginName . '.json';
    $jsonOutput = json_encode($deprecatedFunctionsAndHooks, JSON_PRETTY_PRINT);

    $resultsPath = __DIR__ . '/results';

    if ( !file_exists($resultsPath) ) {
        mkdir($resultsPath);
    }

    file_put_contents($resultsPath . '/' . $reportFileName, $jsonOutput);
}
