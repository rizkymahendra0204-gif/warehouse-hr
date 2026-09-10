<?php
function wh_config(string $key, $default = null) {
    static $local;
    if ($local === null) {
        $path = __DIR__ . '/../config/local.php';
        $local = is_file($path) ? require $path : [];
        if (!is_array($local)) { throw new RuntimeException('Invalid application configuration.'); }
    }
    $env = getenv($key);
    return $env !== false ? $env : ($local[$key] ?? $default);
}
function wh_bool(string $key, bool $default): bool {
    return filter_var(wh_config($key, $default), FILTER_VALIDATE_BOOLEAN);
}
function wh_base_path(): string {
    $configured = wh_config('APP_BASE_PATH');
    if ($configured !== null) { return '/' . trim((string)$configured, '/'); }
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $directory = preg_replace('~/controllers/.*$~', '', $script);
    if ($directory === $script) { $directory = dirname($script); }
    return '/' . trim($directory, '/.');
}
function wh_url(string $route = ''): string {
    return rtrim(wh_base_path(), '/') . '/' . ltrim($route, '/');
}
function wh_https(): bool {
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443) { return true; }
    $trusted = array_filter(array_map('trim', explode(',', (string)wh_config('APP_TRUSTED_PROXY_IPS', ''))));
    return in_array($_SERVER['REMOTE_ADDR'] ?? '', $trusted, true) && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}
function wh_json_request(): bool {
    return (defined('WH_JSON') && WH_JSON) || stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
        || stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false
        || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}
function wh_json(array $body, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function wh_js($value): string {
    return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
}
function wh_escape($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function wh_http_error(int $status, string $message): void {
    if (wh_json_request()) { wh_json(['success' => false, 'status' => 'error', 'message' => $message], $status); }
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Warehouse HR</title><body style="font-family:system-ui;margin:3rem;max-width:45rem"><h2>Permintaan belum dapat diproses</h2><p>' . wh_escape($message) . '</p><p><a href="' . wh_escape(wh_url('index')) . '">Kembali ke dashboard</a></p></body></html>';
    exit;
}
function wh_input(): array {
    static $input;
    if ($input === null) {
        $input = $_POST;
        if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 1048576) { wh_http_error(413, 'Data permintaan terlalu besar.'); }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) { wh_http_error(400, 'Format JSON tidak valid.'); }
        }
    }
    return $input;
}
function wh_text(array $input, string $key, int $max = 255, bool $required = true): string {
    $value = $input[$key] ?? '';
    if (!is_string($value) && !is_int($value)) { throw new DomainException('Format ' . $key . ' tidak valid.'); }
    $value = trim((string)$value);
    if (($required && $value === '') || strlen($value) > $max) { throw new DomainException('Nilai ' . $key . ' tidak valid.'); }
    return $value;
}
function wh_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf_token'];
}
function wh_csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . wh_escape(wh_csrf_token()) . '">';
}
function wh_verify_csrf(): void {
    if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD', 'OPTIONS'], true)) { return; }
    $input = wh_input();
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($input['csrf_token'] ?? '');
    if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        wh_http_error(403, 'Sesi formulir tidak valid. Muat ulang halaman lalu coba kembali.');
    }
}
function wh_require_post(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Allow: POST'); wh_http_error(405, 'Gunakan formulir untuk melakukan tindakan ini.'); }
}
function wh_require_role(array $roles): void {
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) { wh_http_error(403, 'Akses ditolak untuk tindakan ini.'); }
}
function wh_redirect(string $route): void {
    header('Location: ' . wh_url($route), true, 303); exit;
}
function wh_success(string $message, string $route, array $data = []): void {
    if (wh_json_request()) { wh_json(array_merge(['success' => true, 'message' => $message], $data)); }
    $_SESSION['alert_type'] = 'success';
    $_SESSION['alert_message'] = wh_escape($message);
    wh_redirect($route);
}
function wh_password_valid($password): bool {
    // Bcrypt must not silently truncate a new password beyond 72 bytes.
    return is_string($password) && mb_strlen($password, 'UTF-8') >= 12 && strlen($password) <= 72;
}
function wh_set_login(array $account): void {
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['auth_schema'] = 2;
    $_SESSION['user_id'] = (int)$account['user_id'];
    $_SESSION['auth_version'] = (int)$account['auth_version'];
    foreach (['username', 'nama_lengkap', 'role'] as $key) { $_SESSION[$key] = $account[$key]; }
    $_SESSION['foto_profil'] = basename($account['foto_profil'] ?? 'default.png');
    $_SESSION['lang'] = in_array($account['lang'] ?? '', ['id', 'en'], true) ? $account['lang'] : 'en';
    $_SESSION['login_at'] = $_SESSION['last_seen'] = time();
    wh_csrf_token();
}
date_default_timezone_set((string)wh_config('APP_TIMEZONE', 'Asia/Jakarta'));
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
set_exception_handler(function (Throwable $error): void {
    error_log('[Warehouse HR] ' . $error);
    if (PHP_SAPI === 'cli') { fwrite(STDERR, "Application error; see the server log.\n"); exit(1); }
    wh_http_error(500, 'Terjadi kesalahan server. Hubungi administrator dengan waktu kejadian.');
});
if (PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: base-uri 'self'; object-src 'none'; frame-ancestors 'none'");
    header('Cache-Control: no-store');
    if (wh_config('APP_ENV', 'production') === 'production' && wh_bool('APP_REQUIRE_HTTPS', false) && !wh_https()) {
        wh_http_error(503, 'Aplikasi production harus diakses melalui HTTPS. Hubungi administrator.');
    }
    if (wh_https() && wh_bool('APP_REQUIRE_HTTPS', false)) { header('Strict-Transport-Security: max-age=31536000'); }
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1'); ini_set('session.use_only_cookies', '1'); ini_set('session.use_trans_sid', '0');
        session_name('WHSESSID');
        session_set_cookie_params(['lifetime' => 0, 'path' => wh_url(), 'secure' => wh_https(), 'httponly' => true, 'samesite' => 'Lax']);
        if (!session_start()) { throw new RuntimeException('Session storage is unavailable.'); }
    }
}
