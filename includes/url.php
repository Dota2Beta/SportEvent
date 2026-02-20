<?php

function detectBasePath(): string
{
    $projectRoot = realpath(__DIR__ . '/..') ?: '';

    $configuredDocRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $docRoot = $configuredDocRoot !== '' ? (realpath($configuredDocRoot) ?: $configuredDocRoot) : '';

    if ($projectRoot !== '' && $docRoot !== '' && strpos($projectRoot, $docRoot) === 0) {
        $relative = str_replace('\\', '/', substr($projectRoot, strlen($docRoot)));
        return rtrim($relative, '/');
    }

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $scriptDirFromUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    $scriptDirFromUrl = $scriptDirFromUrl === '.' ? '' : $scriptDirFromUrl;

    $scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';
    $scriptDirFromFs = $scriptFilename !== '' ? (realpath(dirname($scriptFilename)) ?: dirname($scriptFilename)) : '';

    if ($projectRoot !== '' && $scriptDirFromFs !== '' && strpos($scriptDirFromFs, $projectRoot) === 0) {
        $relativeScriptDir = str_replace('\\', '/', substr($scriptDirFromFs, strlen($projectRoot)));
        $relativeScriptDir = rtrim($relativeScriptDir, '/');

        if ($relativeScriptDir !== '' && str_ends_with($scriptDirFromUrl, $relativeScriptDir)) {
            $base = substr($scriptDirFromUrl, 0, -strlen($relativeScriptDir));
            return rtrim($base, '/');
        }
    }

    return $scriptDirFromUrl;
}

function siteUrl(string $path = ''): string
{
    static $basePath = null;

    if ($basePath === null) {
        $config = require __DIR__ . '/../config.php';

        $configuredBasePath = trim((string)($config['base_path'] ?? ''));
        if ($configuredBasePath !== '') {
            $basePath = rtrim('/' . trim($configuredBasePath, '/'), '/');
        } else {
            $basePath = detectBasePath();
        }
    }

    $normalizedPath = ltrim($path, '/');

    if ($normalizedPath === '') {
        return $basePath !== '' ? $basePath . '/' : '/';
    }

    if ($basePath === '') {
        return '/' . $normalizedPath;
    }

    return $basePath . '/' . $normalizedPath;
}

function redirectTo(string $path): void
{
    header('Location: ' . siteUrl($path));
    exit;
}
