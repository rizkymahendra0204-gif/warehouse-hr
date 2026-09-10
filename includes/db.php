<?php
require_once __DIR__ . '/bootstrap.php';
function wh_db(): PDO {
    static $connection;
    if (!$connection) {
        $host = (string)wh_config('DB_HOST', '127.0.0.1');
        $port = (int)wh_config('DB_PORT', 3306);
        $database = (string)wh_config('DB_NAME', 'db_warehouse');
        $username = (string)wh_config('DB_USER', 'root');
        $password = (string)wh_config('DB_PASSWORD', '');
        if ($username === '' || $password === '' || !preg_match('/^[a-zA-Z0-9_]+$/D', $database)) {
            throw new RuntimeException('Set DB_NAME, DB_USER and DB_PASSWORD in config/local.php or environment.');
        }
        if (wh_config('APP_ENV', 'production') === 'production' && strtolower($username) === 'root') {
            throw new RuntimeException('A dedicated database account is required in production.');
        }
        $connection = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $connection->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
        $connection->exec("SET SESSION time_zone = '+07:00'");
    }
    return $connection;
}
function wh_mysqli(): mysqli {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = new mysqli((string)wh_config('DB_HOST', '127.0.0.1'), (string)wh_config('DB_USER'), (string)wh_config('DB_PASSWORD'), (string)wh_config('DB_NAME', 'db_warehouse'), (int)wh_config('DB_PORT', 3306));
    $connection->set_charset('utf8mb4');
    $connection->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    $connection->query("SET SESSION time_zone = '+07:00'");
    return $connection;
}
$pdo = wh_db();
$sidebar_cookie = $_COOKIE['sidebar_state'] ?? 'collapsed';
$sidebar_class = $sidebar_cookie === 'collapsed' ? 'sidebar-collapsed' : '';
