<?php

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME]) || !is_string($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function csrf_field(): string
{
    return '<input type="hidden" name="' . e(CSRF_TOKEN_NAME) . '" value="' . e(csrf_token()) . '">';
}

function csrf_is_valid(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION[CSRF_TOKEN_NAME])
        && is_string($_SESSION[CSRF_TOKEN_NAME])
        && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function safe_internal_path(string $path): string
{
    $decoded = ltrim(rawurldecode(trim($path)), '/');
    if ($decoded === '' || str_contains($decoded, '..') || preg_match('#^[a-z][a-z0-9+.-]*://#i', $decoded)) {
        return '';
    }
    return preg_match('#^(home|forms/[a-z0-9_-]+)$#i', $decoded) ? $decoded : '';
}

function client_ip(): string
{
    if (TRUST_PROXY && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $candidate = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        if (filter_var($candidate, FILTER_VALIDATE_IP)) {
            return $candidate;
        }
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

function csv_safe(mixed $value): string
{
    $text = (string) $value;
    if (preg_match('/^[=+\-@]/u', ltrim($text))) {
        return "'" . $text;
    }
    return $text;
}

function flash_set(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function flash_take(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return is_array($messages) ? $messages : [];
}

function text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function text_substr(string $value, int $start, ?int $length = null): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, $start, $length, 'UTF-8');
    }
    return $length === null ? substr($value, $start) : substr($value, $start, $length);
}

function user_initial(string $name): string
{
    $initial = text_substr(trim($name) !== '' ? trim($name) : 'U', 0, 1);
    return function_exists('mb_strtoupper') ? mb_strtoupper($initial, 'UTF-8') : strtoupper($initial);
}

