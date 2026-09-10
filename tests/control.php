<?php
// Test-only CLI helper. Refuses any database not freshly named by tests/run.py.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$name = getenv('DB_NAME');
if (!preg_match('/^wh_test_[a-f0-9]{12}$/D', $name ?: '')) { fwrite(STDERR, "Disposable test database required.\n"); exit(1); }
$host = getenv('DB_HOST') ?: '127.0.0.1';
if (!in_array($host, ['127.0.0.1','localhost','::1'], true)) { fwrite(STDERR, "Use a local database server for this test suite.\n"); exit(1); }
$pdo = new PDO('mysql:host=' . $host . ';port=' . (getenv('DB_PORT') ?: '3306') . ';charset=utf8mb4', getenv('DB_USER'), getenv('DB_PASSWORD'), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$action = $argv[1] ?? '';
if ($action === 'setup') {
    $pdo->exec("CREATE DATABASE `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE `{$name}`");
    $pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));
    $q=$pdo->prepare('INSERT INTO users (user_id,username,password,nama_lengkap,role,is_first_login) VALUES (?,?,?,?,?,?)');
    $hash=password_hash('Fixture-password-2026',PASSWORD_BCRYPT,['cost'=>12]);
    $q->execute([0,'admin_fixture',$hash,'Administrator Uji','admin',0]);
    // Legacy role-empty and user_id=0 are intentional migration regression cases.
    $q->execute([2,'staff_fixture',$hash,'Staff Uji','',0]);
    $q->execute([3,'first_fixture',$hash,'Aktivasi Uji','staff',1]);
    $pdo->exec("INSERT INTO log_activity (id,nama_user,aktivitas) VALUES (0,'Uji','Legacy A'),(0,'Uji','Legacy B')");
    $pdo->exec("INSERT INTO settings (setting_key,setting_value) VALUES ('audit_active','0')");
    exit;
}
if ($action === 'drop') { $pdo->exec("DROP DATABASE `{$name}`"); exit; }
$pdo->exec("USE `{$name}`");
if ($action === 'query') {
    $input=json_decode(stream_get_contents(STDIN),true);
    $q=$pdo->prepare($input['sql']); $q->execute($input['params'] ?? []);
    echo json_encode($q->columnCount() ? $q->fetchAll() : ['affected'=>$q->rowCount()]); exit;
}
if ($action === 'worker') {
    require_once __DIR__ . '/../includes/bootstrap.php';
    require_once __DIR__ . '/../services/inventory.php';
    $_SESSION=['user_id'=>2,'nama_lengkap'=>'Staff Uji','role'=>'staff'];
    $pdo->exec("SET SESSION sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    $input=json_decode(stream_get_contents(STDIN),true);
    if (isset($input['start_at'])) { while (microtime(true)<$input['start_at']) { usleep(1000); } }
    try {
        $result=$input['action']==='return' ? wh_return($pdo,$input['data']) : wh_transaction($pdo,$input['data']);
        echo json_encode(['ok'=>true,'result'=>$result]);
    } catch (Throwable $e) { echo json_encode(['ok'=>false,'error'=>$e instanceof DomainException ? $e->getMessage() : get_class($e)]); }
}
