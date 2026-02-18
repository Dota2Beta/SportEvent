<?php

function siteUrl(string $path = ''): string
{
    static $basePath = null;

    if ($basePath === null) {
        $config = require __DIR__ . '/../config.php';

        $configuredBasePath = trim((string)($config['base_path'] ?? ''));
        if ($configuredBasePath !== '') {
            $basePath = rtrim('/' . trim($configuredBasePath, '/'), '/');
        } else {
            $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
            $detectedBasePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
            $basePath = $detectedBasePath === '.' ? '' : $detectedBasePath;
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
