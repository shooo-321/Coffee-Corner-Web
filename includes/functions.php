<?php

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function url_path($path = '')
{
    $path = ltrim($path, '/');
    $base = BASE_PATH;
    if ($base === '/') {
        $base = '';
    }
    if ($base === '') {
        return '/' . $path;
    }
    return $base . '/' . $path;
}

function redirect_to($path)
{
    header('Location: ' . url_path($path));
    exit;
}

function post($key, $default = null)
{
    return $_POST[$key] ?? $default;
}

function get($key, $default = null)
{
    return $_GET[$key] ?? $default;
}
