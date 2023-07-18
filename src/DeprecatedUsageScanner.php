<?php

namespace DeprecationDetector;

use function DeprecationDetector\Utils\getFilesInFolder;

class DeprecatedUsageScanner
{
    /**
     * Scans the given folder for deprecated functions and hooks.
     *
     * @param string $folderPath The path to the folder to scan.
     * @param array $deprecations The array of deprecated functions and hooks. [
     *    [
     * "type": "function" | "hook" | "class" | "const",
     * "name": "localize_feedback_dialog_settings",
     * "version": "3.1.0",
     * "namespace": "Elementor\\Core\\Admin",
     * "line": 77,
     * "file_path": "elementor/includes/admin.php"
     * ],
     *
     * @return array An array of deprecated functions and hooks, with the properties file path, line, deprecated type (function/hook), and deprecated name.
     */
    public function scanFolderForDeprecations($folderPath, $deprecations): array
    {
        $deprecatedFunctionsAndHooks = [];
        $filePaths = getFilesInFolder($folderPath);

        $deprecationsToCheck = $this->getDeprecationToCheck($deprecations);

        foreach ($filePaths as $filePath) {
            $deprecations = $this->checkDeprecation($filePath, $deprecationsToCheck);

            if (!empty($deprecations)) {
                $relativeFilePath = str_replace($folderPath, '', $filePath);

                foreach ($deprecations as & $deprecation) {
                    $deprecation['sourcePath'] = str_replace('\\', '/', $relativeFilePath);
                }

                $deprecatedFunctionsAndHooks = array_merge($deprecatedFunctionsAndHooks, $deprecations);
            }
        }

        return $deprecatedFunctionsAndHooks;
    }

    private function getDeprecationToCheck($deprecations): array
    {
        $deprecationsToCheck = [];

        foreach ($deprecations as $deprecation) {
            // if hook
            if ($deprecation['type'] === 'hook') {
                $deprecation['lookFor'] = $deprecation['name'];
                $deprecationsToCheck[] = $deprecation;
            } elseif ($deprecation['type'] === 'function' || $deprecation['type'] === 'const' || $deprecation['type'] === 'class') {
                $deprecation['lookFor'] = $deprecation['namespace'] . '\\' . $deprecation['class'];
                $deprecationsToCheck[] = $deprecation;
            } else {
                $deprecation['lookFor'] = $deprecation['namespace'];
                $deprecationsToCheck[] = $deprecation;
            }
        }

        return $deprecationsToCheck;
    }

    private function getThingIndex($tokens, $commentIndex)
    {
        $types = [
            T_FUNCTION,
            T_CLASS,
            T_CONST,
        ];

        $thingIndex = false;

        for ($i = $commentIndex; $i < $commentIndex + 5; $i++) {
            // check if token contains `do_action` or `apply_filters`
            if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_STRING]) && in_array($tokens[$i][1], ['do_action', 'apply_filters'])) {
                $thingIndex = $i;
                break;
            }


            if (is_array($tokens[$i]) && in_array($tokens[$i][0], $types)) {
                $thingIndex = $i;
                break;
            }
        }

        return $thingIndex;
    }

    /**
     * Gets all the PHP file paths in the given folder.
     *
     * @param string $folderPath The path to the folder to scan.
     *
     * @return array An array of PHP file paths.
     */


    /**
     * Determines if the given token is a function or hook token.
     *
     * @param array $token The token to check.
     *
     * @return string The token type.
     */
    private function getType(array $token)
    {
        switch ($token[0]) {
            case T_FUNCTION:
                return 'function';
            case T_CLASS:
                return 'class';
            case T_CONST:
                return 'const';
            case T_STRING:
                return 'hook';

            default:
                return '';
        }
    }

    /**
     * Gets the version number from the `deprecated` tag in the given tokens.
     *
     * @param string $docComment The doc comment to search.
     * @return string The version number, or null if it cannot be found.
     */
    private function getDeprecatedVersion(string $docComment): string
    {
        $docCommentLines = explode("\n", $docComment);
        $docCommentLines = array_map('trim', $docCommentLines);
        $docCommentLines = array_filter($docCommentLines, function ($line) {
            return str_contains($line, '@deprecated');
        });

        $versions = [];

        foreach ($docCommentLines as $docCommentLine) {

            if (preg_match('/([\d\.]+)/', $docCommentLine, $matches)) {
                $versions[] = $matches[1];
            }
        }

        if (empty($versions)) {
            return '';
        }

        // sort versions in ascending order
        usort($versions, function ($a, $b) {
            return version_compare($a, $b);
        });

        return $versions[0];
    }


    private function getNamespace(array $tokens)
    {
        $namespace = '';

        for ($i = 0; $i < 10; $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = $tokens[$i + 2][1];
                break;
            }
        }

        return $namespace;
    }

    /**
     * @param $filePath
     * @param $deprecationsToCheck
     * @return array
     */
    public function checkDeprecation($filePath, $deprecationsToCheck): array
    {
        $fileContents = file_get_contents($filePath);
        $contentWithoutSpaces = str_replace([' ', "\t", "\n", "\r"], '', $fileContents);
        $deprecations = [];

        foreach ($deprecationsToCheck as $details) {
            $exist = strpos($fileContents, $details['lookFor']) !== false;

            if (!$exist) {
                continue;
            }

            if ($details['type'] === 'function') {
                $exist = strpos($contentWithoutSpaces, '::' . $details['name'] . '(') !== false || strpos($contentWithoutSpaces, '->' . $details['name'] . '(') !== false;

                if (!$exist) {
                    continue;
                }
            }

            if ($details['type'] === 'const') {
                $exist = strpos($contentWithoutSpaces, $details['class'] . '::' . $details['name']) !== false;

                if (!$exist) {
                    continue;
                }
            }

            if ($details['type'] !== 'hook') {
                $exist = strpos($fileContents, $details['name']) !== false;

                if (!$exist) {
                    continue;
                }
            }

            unset($details['lookFor']);
            $deprecations[] = [
                'details' => $details,
            ];
        }

        return $deprecations;
    }

    private function getThingNameToken($tokens, $thingIndex)
    {
        $thingNameToken = false;

        switch ($tokens[$thingIndex][0]) {
            case T_FUNCTION:
            case T_CLASS:
            case T_CONST:
                $thingNameToken = $tokens[$thingIndex + 2];
                break;
            case T_STRING:
                $thingNameToken = $tokens[$thingIndex + 3];
                break;
        }

        return $thingNameToken;
    }
}

