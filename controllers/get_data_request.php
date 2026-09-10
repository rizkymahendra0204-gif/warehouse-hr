<?php
const WH_JSON = true;
require_once __DIR__ . '/../includes/auth_check.php';
wh_require_post();
try {
    $id = wh_text(wh_input(), 'id', 50);
    $stmt = $pdo->prepare("SELECT gender, qty_top, qty_bottoms FROM request_form WHERE request_id = ? AND status = 'Pending'");
    $stmt->execute([$id]); $request = $stmt->fetch();
    if (!$request) { wh_http_error(422, 'Request tidak ditemukan atau sudah diproses.'); }
    wh_json(['success' => true, 'status' => 'success', 'baju' => (int)$request['qty_top'] . ' Baju', 'celana' => (int)$request['qty_bottoms'] . ' Celana', 'jumlah' => (int)$request['qty_top'] + (int)$request['qty_bottoms']]);
} catch (DomainException $error) { wh_http_error(422, $error->getMessage()); }
