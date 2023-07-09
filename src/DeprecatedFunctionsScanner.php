<?php

namespace DeprecationDetector;

class DeprecatedFunctionsScanner
{
    /**
     * Scans the given folder for deprecated functions and hooks.
     *
     * @param string $folderPath The path to the folder to scan.
     *
     * @return array An array of deprecated functions and hooks, with the properties file path, line, deprecated type (function/hook), and deprecated name.
     */
    public function scanFolderForDeprecations($folderPath): array
    {
        $deprecatedFunctionsAndHooks = [];
        $filePaths = getFilesInFolder($folderPath);

        foreach ($filePaths as $filePath) {
            $deprecations =  $this->getDeprecation($filePath);

            if (!empty($deprecations)) {
                $relativeFilePath = str_replace($folderPath, '', $filePath);

                foreach ($deprecations as & $deprecation) {
                    $deprecation['file_path'] = str_replace('\\', '/', $relativeFilePath);
                }

                $deprecatedFunctionsAndHooks = array_merge($deprecatedFunctionsAndHooks, $deprecations);
            }
        }

        return $deprecatedFunctionsAndHooks;
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

    private function getClass(array $tokens)
    {
        $class = '';

        for ($i = 0; $i < 300; $i++) {
            if ($tokens[$i][0] === T_CLASS) {
                $class = $tokens[$i + 2][1];
                break;
            }
        }

        return $class;
    }

    /**
     * @param $filePath
     * @return array
     */
    public function getDeprecation($filePath): array
    {
        $fileContents = file_get_contents($filePath);
        $tokens = token_get_all($fileContents);
        $deprecations = [];

        foreach ($tokens as $index => $token) {
            if ($token[0] === T_DOC_COMMENT && str_contains($token[1], '@deprecated')) {
                $thingTokenIndex = $this->getThingIndex($tokens, $index);

                if (!$thingTokenIndex) {
                    continue;
                }

                // extract all deprecated tags and versions
                $docComment = $token[1];
                $deprecatedVersion = $this->getDeprecatedVersion($docComment);
                $thingNameToken = $this->getThingNameToken($tokens, $thingTokenIndex);
                $namespace = $this->getNamespace($tokens);
                $class = $this->getClass($tokens);
                $replacement = preg_match('/(Use.*instead)/', $docComment, $matches) ? $matches[1] : '';

                if (!$thingNameToken) {
                    var_dump($docComment);
                    continue;
                }

                $deprecations[] = [
                    'type' => $this->getType($tokens[$thingTokenIndex]),
                    'name' => trim($thingNameToken[1], "'\""), // remove quotes for hooks
                    'version' => $deprecatedVersion,
                    'namespace' => $namespace ?? '',
                    'class' => $class ?? '',
                    'line' => $thingNameToken[2],
                    'replacement' => $replacement,
                ];
            }
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
                $thingNameToken = $tokens[$thingIndex+3];
                break;
        }

        return $thingNameToken;
    }
}

