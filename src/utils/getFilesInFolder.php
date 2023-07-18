<?php

namespace DeprecationDetector\Utils;

function getFilesInFolder($folderPath): array
{
    $phpFilePaths = [];

    if (!is_dir($folderPath)) {
        return $phpFilePaths;
    }

    $files = scandir($folderPath);
    $exclude = [
        '.',
        '..',
        '.git',
        'node_modules',
        'vendor',
        'tmp',
        'tests',
    ];

    if (!is_array($files)) {
        return $phpFilePaths;
    }

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
