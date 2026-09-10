<?php
// ONLY for the disposable local test server. Apache uses the application's .htaccess.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (strpos($path, '..') !== false || preg_match('~^/(?:config|includes|services|database|tests|docs|vendor)(?:/|$)|(?:^|/)\.|\.(?:sql|zip|log|ini|bak|env)$~i', $path)) { http_response_code(403); exit; }
$root = dirname(__DIR__);
if ($path === '/') { $path = '/index.php'; }
$file = $root . $path;
if (!is_file($file) && is_file($file . '.php')) { $file .= '.php'; }
if (!is_file($file)) { http_response_code(404); exit; }
if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') { return false; }
if (strpos($path, '/assets/') === 0) { http_response_code(403); exit; }
$_SERVER['SCRIPT_NAME'] = substr($file, strlen($root));
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
chdir($root);
require $file;
