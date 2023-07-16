<?php

use DeprecationDetector\DeprecatedFunctionsScanner;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../getFilesInFolder.php';

const PLUGINS_DIR = __DIR__ . '/../wp-content/plugins/';
const SOURCE_REPORT_DIR = __DIR__ . '/../reports/source/';

$cliArgs = getopt('', ['source:']);
$sourceName = $cliArgs['source'];

$scanner = new DeprecatedFunctionsScanner();
$deprecatedFunctionsAndHooks = $scanner->scanFolderForDeprecations(PLUGINS_DIR . $sourceName);

$jsonOutput = json_encode($deprecatedFunctionsAndHooks, JSON_PRETTY_PRINT);
file_put_contents(SOURCE_REPORT_DIR . $sourceName . '.json', $jsonOutput);

echo 'Found ' . count($deprecatedFunctionsAndHooks) . ' deprecated things in ' . $sourceName . PHP_EOL;
