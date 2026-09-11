<?php

/* =========================================================
   SSO LOGIN - WAREHOUSE HR
   =========================================================
   Tujuan:
   - Menerima token dari Dashboard Central
   - Memverifikasi signature token
   - Memeriksa masa berlaku token
   - Memvalidasi department / role
   - Mencari akun Warehouse berdasarkan username
   - Membuat session WHSESSID menggunakan wh_set_login()
   ========================================================= */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/db.php';


/* =========================================================
   1. HELPER BASE64 URL DECODE
   ========================================================= */

function wh_sso_base64url_decode(string $data): string|false
{
    $remainder = strlen($data) % 4;

    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }

    return base64_decode(
        strtr($data, '-_', '+/'),
        true
    );
}


/* =========================================================
   2. AMBIL SECRET SSO
   ========================================================= */

$secret = trim(
    (string) wh_config(
        'SSO_SHARED_SECRET',
        ''
    )
);

if ($secret === '') {
    wh_http_error(
        500,
        'SSO Warehouse belum dikonfigurasi.'
    );
}


/* =========================================================
   3. AMBIL TOKEN DARI DASHBOARD CENTRAL
   ========================================================= */

$token = trim(
    (string)($_GET['token'] ?? '')
);

if ($token === '') {
    wh_http_error(
        401,
        'Token login tidak ditemukan.'
    );
}


/* =========================================================
   4. VALIDASI FORMAT TOKEN
   ========================================================= */

$parts = explode('.', $token);

if (count($parts) !== 2) {
    wh_http_error(
        401,
        'Format token login tidak valid.'
    );
}

[$encodedPayload, $encodedSignature] = $parts;


/* =========================================================
   5. VALIDASI SIGNATURE TOKEN
   ========================================================= */

$receivedSignature =
    wh_sso_base64url_decode(
        $encodedSignature
    );

if ($receivedSignature === false) {
    wh_http_error(
        401,
        'Signature token tidak valid.'
    );
}

$expectedSignature = hash_hmac(
    'sha256',
    $encodedPayload,
    $secret,
    true
);

if (
    !hash_equals(
        $expectedSignature,
        $receivedSignature
    )
) {
    wh_http_error(
        401,
        'Token login tidak sah.'
    );
}


/* =========================================================
   6. DECODE PAYLOAD
   ========================================================= */

$json = wh_sso_base64url_decode(
    $encodedPayload
);

if ($json === false) {
    wh_http_error(
        401,
        'Payload login tidak valid.'
    );
}

$payload = json_decode(
    $json,
    true
);

if (!is_array($payload)) {
    wh_http_error(
        401,
        'Payload login tidak dapat dibaca.'
    );
}


/* =========================================================
   7. VALIDASI WAKTU TOKEN
   ========================================================= */

$issuedAt  = (int)($payload['iat'] ?? 0);
$expiresAt = (int)($payload['exp'] ?? 0);
$now       = time();

if (
    $issuedAt <= 0 ||
    $expiresAt <= 0
) {
    wh_http_error(
        401,
        'Waktu token tidak valid.'
    );
}

if ($now > $expiresAt) {
    wh_http_error(
        401,
        'Token login sudah kedaluwarsa. Silakan buka kembali Warehouse HR dari Dashboard Central.'
    );
}

/*
 * Toleransi clock maksimal 30 detik.
 */
if ($issuedAt > ($now + 30)) {
    wh_http_error(
        401,
        'Waktu token tidak valid.'
    );
}


/* =========================================================
   8. AMBIL IDENTITAS USER
   ========================================================= */

$username = trim(
    (string)($payload['username'] ?? '')
);

$department = strtolower(
    trim(
        (string)($payload['dept'] ?? '')
    )
);

$role = strtolower(
    str_replace(
        [' ', '_', '-'],
        '',
        trim(
            (string)($payload['role'] ?? '')
        )
    )
);

if ($username === '') {
    wh_http_error(
        401,
        'Username Dashboard Central tidak tersedia.'
    );
}


/* =========================================================
   9. VALIDASI AKSES DEPARTMENT
   =========================================================
   Yang diperbolehkan:
   - HR
   - OP / HO Operation
   - SuperAdmin
   ========================================================= */

$isSuperAdmin =
    $role === 'superadmin';

$allowedDepartments = [
    'hr',
    'op'
];

if (
    !$isSuperAdmin &&
    !in_array(
        $department,
        $allowedDepartments,
        true
    )
) {
    wh_http_error(
        403,
        'Warehouse HR hanya dapat diakses oleh HR, HO Operation, atau SuperAdmin.'
    );
}


/* =========================================================
   10. CARI USER DI DATABASE WAREHOUSE
   =========================================================
   Username Warehouse harus sama dengan username
   di Dashboard Central.
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT
        user_id,
        username,
        nama_lengkap,
        role,
        auth_version,
        is_first_login,
        foto_profil,
        lang
    FROM users
    WHERE username = ?
    LIMIT 1
");

$stmt->execute([
    $username
]);

$account = $stmt->fetch(
    PDO::FETCH_ASSOC
);


/* =========================================================
   11. VALIDASI USER WAREHOUSE
   ========================================================= */

if (!$account) {
    wh_http_error(
        403,
        'Akun "' . $username . '" belum terdaftar di Warehouse HR.'
    );
}

if (
    !in_array(
        $account['role'],
        ['admin', 'staff'],
        true
    )
) {
    wh_http_error(
        403,
        'Role akun Warehouse HR tidak valid.'
    );
}

if (
    (int)$account['is_first_login'] !== 0
) {
    wh_http_error(
        403,
        'Akun Warehouse HR belum selesai diaktivasi.'
    );
}


/* =========================================================
   12. BUAT SESSION WAREHOUSE
   =========================================================
   Menggunakan mekanisme asli Warehouse:
   - WHSESSID
   - auth_schema
   - user_id
   - auth_version
   - role
   - login_at
   - last_seen
   - CSRF token
   ========================================================= */

wh_set_login(
    $account
);


/* =========================================================
   13. REDIRECT KE DASHBOARD WAREHOUSE
   ========================================================= */

wh_redirect(
    'index'
);