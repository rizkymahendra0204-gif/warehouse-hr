<?php
session_start();
session_unset();
session_destroy();

session_start();
$_SESSION['alert_message'] = "Anda berhasil keluar dari sistem.";
$_SESSION['alert_type']    = "success";

header("Location: login");
exit;
?>