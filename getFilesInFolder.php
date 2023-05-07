<?php
namespace DeprecationDetector;
function getFilesInFolder($folderPath) {
    $phpFilePaths = array();
    $files = scandir($folderPath);
    $exclude = array(
        '.',
        '..',
        '.git',
        'node_modules',
        'vendor',
        'tmp',
        'tests',
    );

    foreach ($files as $file) {
        if (in_array($file, $exclude)) {
            continue;
        }

        $filePath = $folderPath . '/' . $file;

        if (is_dir($filePath)) {
            $phpFilePaths = array_merge($phpFilePaths, getFilesInFolder($filePath));
        } else if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $phpFilePaths[] = $filePath;
        }
    }

    return $phpFilePaths;
}
