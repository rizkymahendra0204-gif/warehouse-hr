<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Tangkap request ganti bahasa via GET (misal: ?lang=en)
if (isset($_GET['lang'])) {
    $selected_lang = $_GET['lang'] === 'en' ? 'en' : 'id';
    $_SESSION['lang'] = $selected_lang;

    // Simpan ke database jika user sedang login agar tersimpan permanen
    if (isset($_SESSION['username']) && isset($pdo)) {
        try {
            $stmt_lang = $pdo->prepare("UPDATE users SET lang = ? WHERE username = ?");
            $stmt_lang->execute([$selected_lang, $_SESSION['username']]);
        } catch (PDOException $e) {}
    }
}

// 2. Set bahasa default ke 'en' jika belum ada di Session
$current_lang = $_SESSION['lang'] ?? 'en';

// 3. Load file kamus (Fallback diubah ke en.php)
$lang_file = __DIR__ . "/../lang/{$current_lang}.php";
$lang = file_exists($lang_file) ? require $lang_file : require __DIR__ . "/../lang/en.php";
?>