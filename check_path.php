<?php

require_once __DIR__ . '/includes/bootstrap.php';

echo '<pre>';

echo "getenv APP_BASE_PATH : ";
var_dump(getenv('APP_BASE_PATH'));

echo "wh_config APP_BASE_PATH : ";
var_dump(wh_config('APP_BASE_PATH'));

echo "wh_base_path : ";
var_dump(wh_base_path());

echo "wh_url login : ";
var_dump(wh_url('login'));

echo '</pre>';