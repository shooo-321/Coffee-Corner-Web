<?php

session_start();

$config = require __DIR__ . '/../config/config.php';

define('DB_HOST', $config['db']['host']);
define('DB_NAME', $config['db']['name']);
define('DB_USER', $config['db']['user']);
define('DB_PASS', $config['db']['pass']);
define('DB_CHARSET', $config['db']['charset']);

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptParts = explode('/', trim($scriptName, '/'));
$basePath = '';
if (count($scriptParts) > 1) {
    $basePath = '/' . $scriptParts[0];
}
define('BASE_PATH', $basePath);

define('APP_NAME', 'Coffee Corner');

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cart.php';
