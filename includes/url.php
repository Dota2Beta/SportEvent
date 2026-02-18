<?php

function siteUrl(string $path = ''): string
{
    static $basePath = null;

    if ($basePath === null) {
        $config = require __DIR__ . '/../config.php';
        $basePath = rtrim($config['base_path'] ?? '', '/');
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
