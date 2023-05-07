<?php

require __DIR__ . '/vendor/squizlabs/php_codesniffer/autoload.php';
require __DIR__ . '/getFilesInFolder.php';

$deprecations = json_decode(file_get_contents(__DIR__ . '/deprecations.json'), true);


$scanner = new \DeprecationDetector\DeprecatedUsageScanner();
$deprecatedFunctionsAndHooks = $scanner->scanFolderForDeprecations(
    '/mnt/c/Users/mati/Documents/elementor-editor-dev/wordpress/wp-content/plugins',
    $deprecations
);

$jsonOutput = json_encode($deprecatedFunctionsAndHooks, JSON_PRETTY_PRINT);
file_put_contents('deprecation-usage.json', $jsonOutput);

exit(0);
