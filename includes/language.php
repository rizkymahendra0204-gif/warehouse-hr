<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Tangkap request ganti bahasa via GET (misal: ?lang=en)
if (isset($_GET['lang'])) {
    $selected_lang = $_GET['lang'] === 'en' ? 'en' : 'id';
    $_SESSION['lang'] = $selected_lang;
}

// 2. Set bahasa default ke 'id' jika belum ada di Session
$current_lang = $_SESSION['lang'] ?? 'en';

// 3. Load file kamus yang sesuai
$lang_file = __DIR__ . "/../lang/{$current_lang}.php";
$lang = file_exists($lang_file) ? require $lang_file : require __DIR__ . "/../lang/id.php";
?>