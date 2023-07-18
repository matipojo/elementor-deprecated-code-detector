<?php

use DeprecationDetector\DeprecatedUsageScanner;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/utils/getFilesInFolder.php';

const PLUGINS_DIR = __DIR__ . '/../wp-content/plugins/';
const SOURCE_REPORT_DIR = __DIR__ . '/../reports/source/';
const PLUGINS_REPORT_DIR = __DIR__ . '/../reports/plugins/';

$cliArgs = getopt('', ['source:']);
$sourceName = $cliArgs['source'];

$deprecations = json_decode(file_get_contents(SOURCE_REPORT_DIR . $sourceName . '.json'), true);

$scanner = new DeprecatedUsageScanner();

$plugins = glob(PLUGINS_DIR . '*');

foreach ($plugins as $plugin) {
    $pluginParts = explode('/', $plugin);
    $pluginName = $pluginParts[count($pluginParts) - 1];

    if ($pluginName === $sourceName) {
        continue;
    }

    echo 'Scanning ' . $pluginName . PHP_EOL;

    $deprecatedFunctionsAndHooks = $scanner->scanFolderForDeprecations($plugin, $deprecations);

    $reportFileName = $pluginName . '.json';
    $jsonOutput = json_encode($deprecatedFunctionsAndHooks, JSON_PRETTY_PRINT);

    $foundCount = count($deprecatedFunctionsAndHooks);

    echo 'Found ' . $foundCount . ' deprecated things in ' . $pluginName . PHP_EOL;

    if ($foundCount === 0) {
        continue;
    }

    file_put_contents(PLUGINS_REPORT_DIR . $reportFileName, $jsonOutput);
}
