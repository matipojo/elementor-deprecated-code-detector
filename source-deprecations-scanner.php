<?php

require __DIR__ . '/vendor/squizlabs/php_codesniffer/autoload.php';
require __DIR__ . '/getFilesInFolder.php';

$scanner = new \DeprecationDetector\DeprecatedFunctionsScanner();
$deprecatedFunctionsAndHooks = $scanner->scanFolderForDeprecations('/mnt/c/Users/mati/Documents/elementor-editor-dev/plugins/elementor');

$jsonOutput = json_encode($deprecatedFunctionsAndHooks, JSON_PRETTY_PRINT);
file_put_contents('deprecations.json', $jsonOutput);

exit(0);
