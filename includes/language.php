<?php
require_once __DIR__ . '/bootstrap.php';
$current_lang = in_array($_SESSION['lang'] ?? '', ['id', 'en'], true) ? $_SESSION['lang'] : 'en';
$lang = require __DIR__ . '/../lang/' . $current_lang . '.php';
