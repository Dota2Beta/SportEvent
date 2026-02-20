<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function refreshCaptcha(): void
{
    $a = random_int(1, 9);
    $b = random_int(1, 9);

    $_SESSION['captcha'] = [
        'a' => $a,
        'b' => $b,
        'answer' => $a + $b,
    ];
}

function getCaptchaQuestion(): string
{
    if (!isset($_SESSION['captcha'])) {
        refreshCaptcha();
    }

    return (int)$_SESSION['captcha']['a'] . ' + ' . (int)$_SESSION['captcha']['b'];
}

function validateCaptcha(string $value): bool
{
    if (!isset($_SESSION['captcha'])) {
        return false;
    }

    $isValid = ((int)$value === (int)$_SESSION['captcha']['answer']);
    refreshCaptcha();
    return $isValid;
}
