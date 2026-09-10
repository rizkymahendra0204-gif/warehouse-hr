<?php
require_once __DIR__ . '/../includes/activity.php';
function wh_parse_barcode(string $barcode): array {
    if (!preg_match('/^[12]0[12][0-9]{6}$/D', $barcode) || (int)substr($barcode, 5) === 0) { throw new DomainException('Barcode harus 9 digit dengan kode item dan nomor urut yang valid.'); }
    $gender = $barcode[0] === '1' ? 'Pria' : 'Wanita';
    $type = substr($barcode, 1, 2) === '01' ? 'Baju' : 'Celana';
    $code = substr($barcode, 3, 2);
    $sizes = ($type === 'Celana' && $gender === 'Pria') ? ['28'=>'28','30'=>'30','32'=>'32','34'=>'34','36'=>'36'] : ['01'=>'S','02'=>'M','03'=>'L','04'=>'XL'];
    if (!isset($sizes[$code])) { throw new DomainException('Ukuran barcode tidak sesuai dengan jenis barang.'); }
    return ['barcode' => $barcode, 'gender' => $gender, 'tipe' => $type, 'size' => $sizes[$code]];
}
function wh_barcodes($values, int $limit = 300): array {
    if (!is_array($values) || count($values) < 1 || count($values) > $limit) { throw new DomainException('Jumlah barcode harus 1–' . $limit . '.'); }
    $codes = [];
    foreach ($values as $value) {
        if (!is_string($value)) { throw new DomainException('Format barcode tidak valid.'); }
        $code = trim($value); wh_parse_barcode($code);
        if (in_array($code, $codes, true)) { throw new DomainException('Barcode yang sama tidak boleh dikirim dua kali.'); }
        $codes[] = $code;
    }
    return $codes;
}
function wh_gender(string $raw): string {
    $raw = strtolower(trim($raw));
    if (in_array($raw, ['1','pria','male'], true)) { return 'Pria'; }
    if (in_array($raw, ['2','wanita','female'], true)) { return 'Wanita'; }
    throw new DomainException('Gender pada data request/barang tidak valid.');
}
function wh_lock_items(PDO $pdo, array $codes): array {
    sort($codes, SORT_STRING); $items = [];
    $stmt = $pdo->prepare('SELECT * FROM master_item WHERE barcode = ? FOR UPDATE');
    foreach ($codes as $code) {
        $stmt->execute([$code]); $item = $stmt->fetch();
        if (!$item) { throw new DomainException('Barcode ' . $code . ' tidak terdaftar.'); }
        $items[$code] = $item;
    }
    return $items;
}
function wh_transaction(PDO $pdo, array $input): string {
    $requestId = wh_text($input, 'id_request', 50);
    $salesId = wh_text($input, 'id_sales', 10);
    if (!ctype_digit($salesId) || (int)$salesId < 1 || (int)$salesId > 2147483647) { throw new DomainException('ID Sales harus angka positif sesuai kapasitas database.'); }
    $codes = wh_barcodes($input['barcode_item'] ?? null);
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT * FROM request_form WHERE request_id = ? FOR UPDATE');
        $stmt->execute([$requestId]); $request = $stmt->fetch();
        if (!$request || strtolower(trim($request['status'])) !== 'pending') { throw new DomainException('Request tidak ditemukan atau sudah diproses.'); }
        $stmt = $pdo->prepare('SELECT transaction_id FROM transaksi WHERE request_id = ?');
        $stmt->execute([$requestId]);
        if ($stmt->fetch()) { throw new DomainException('Request sudah memiliki transaksi.'); }
        $gender = wh_gender($request['gender'] ?? '');
        $targetTop = (int)$request['qty_top']; $targetBottom = (int)$request['qty_bottoms'];
        if ($targetTop < 0 || $targetBottom < 0 || count($codes) !== $targetTop + $targetBottom) { throw new DomainException('Jumlah barang tidak sesuai request.'); }
        $items = wh_lock_items($pdo, $codes); $top = 0; $bottom = 0;
        foreach ($items as $item) {
            if (strtolower(trim($item['status_transaksi'])) !== 'available' || strtolower(trim($item['status_barang'])) !== 'active') { throw new DomainException('Barang ' . $item['barcode'] . ' tidak tersedia/aktif.'); }
            if (wh_gender($item['gender']) !== $gender) { throw new DomainException('Gender barang tidak sesuai request.'); }
            if (strtolower(trim($item['tipe'])) === 'baju') { $top++; }
            elseif (in_array(strtolower(trim($item['tipe'])), ['celana', 'rok'], true)) { $bottom++; }
            else { throw new DomainException('Jenis barang tidak dikenali.'); }
        }
        if ($top !== $targetTop || $bottom !== $targetBottom) { throw new DomainException('Jumlah Baju/Celana harus sama persis dengan request, termasuk kategori dengan jumlah nol.'); }
        // A locked sequence row replaces MAX + 1; existing numbers are seeded by migration.
        $year = (int)date('Y');
        $pdo->prepare('INSERT INTO transaction_sequences (year, last_value) VALUES (?, 0) ON DUPLICATE KEY UPDATE year = VALUES(year)')->execute([$year]);
        $stmt = $pdo->prepare('SELECT last_value FROM transaction_sequences WHERE year = ? FOR UPDATE');
        $stmt->execute([$year]); $next = (int)$stmt->fetchColumn() + 1;
        $pdo->prepare('UPDATE transaction_sequences SET last_value = ? WHERE year = ?')->execute([$next, $year]);
        $id = sprintf('TRX-%04d-%04d', $year, $next);
        $pdo->prepare('INSERT INTO transaksi (transaction_id, request_id, id_sales, tgl_transaksi) VALUES (?, ?, ?, NOW())')->execute([$id, $requestId, $salesId]);
        $detail = $pdo->prepare('INSERT INTO transaksi_detail (transaction_id, barcode) VALUES (?, ?)');
        $update = $pdo->prepare("UPDATE master_item SET status_transaksi = 'Sold Out' WHERE barcode = ? AND LOWER(TRIM(status_transaksi)) = 'available' AND LOWER(TRIM(status_barang)) = 'active'");
        foreach ($codes as $code) {
            $detail->execute([$id, $code]); $update->execute([$code]);
            if ($update->rowCount() !== 1) { throw new DomainException('Status barang berubah. Muat ulang data.'); }
        }
        $pdo->prepare("UPDATE request_form SET status = 'Done' WHERE request_id = ?")->execute([$requestId]);
        wh_activity($pdo, 'Approve Request & Transaksi', 'Request #' . $requestId . ' menjadi ' . $id . ' (' . count($codes) . ' item)', 'Transaksi');
        $pdo->commit(); return $id;
    } catch (Throwable $error) { if ($pdo->inTransaction()) { $pdo->rollBack(); } throw $error; }
}
function wh_return(PDO $pdo, array $input): int {
    $id = wh_text($input, 'no_return', 50);
    $codes = wh_barcodes($input['barcode_return'] ?? null);
    $reasons = $input['kondisi_return'] ?? [];
    if (!is_array($reasons) || count($reasons) !== count($codes)) { throw new DomainException('Kondisi tiap barang wajib dipilih.'); }
    $allowed = ['Kebesaran' => 'Bagus', 'Kekecilan' => 'Bagus', 'Cacat Produksi' => 'Rusak'];
    foreach ($reasons as $reason) { if (!is_string($reason) || !isset($allowed[$reason])) { throw new DomainException('Alasan retur tidak valid.'); } }
    $reasons = array_values($reasons);
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT transaction_id FROM transaksi WHERE transaction_id = ? FOR UPDATE');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) { throw new DomainException('Transaksi asal tidak ditemukan.'); }
        $items = wh_lock_items($pdo, $codes);
        foreach ($codes as $i => $code) {
            $item = $items[$code];
            if (strtolower(trim($item['status_transaksi'])) !== 'sold out' || strtolower(trim($item['status_barang'])) !== 'active') { throw new DomainException('Barang tidak sedang keluar atau telah diretur.'); }
            $stmt = $pdo->prepare('SELECT transaction_id FROM transaksi_detail WHERE barcode = ? ORDER BY id_detail DESC LIMIT 1');
            $stmt->execute([$code]);
            if ($stmt->fetchColumn() !== $id) { throw new DomainException('Barcode bukan milik transaksi keluar terakhir yang dipilih.'); }
            $stmt = $pdo->prepare('SELECT return_id FROM return_items WHERE transaction_id = ? AND barcode = ?');
            $stmt->execute([$id, $code]);
            if ($stmt->fetch()) { throw new DomainException('Barang pada transaksi ini sudah pernah diretur.'); }
            $pdo->prepare('INSERT INTO return_items (transaction_id, barcode, alasan_return, kondisi_barang, tgl_return) VALUES (?, ?, ?, ?, NOW())')->execute([$id, $code, $reasons[$i], $allowed[$reasons[$i]]]);
            $pdo->prepare("UPDATE master_item SET status_transaksi = 'Available', status_barang = 'Inactive' WHERE barcode = ?")->execute([$code]);
        }
        wh_activity($pdo, 'Proses Return Barang', 'Retur ' . count($codes) . ' item dari ' . $id, 'Return');
        $pdo->commit(); return count($codes);
    } catch (Throwable $error) { if ($pdo->inTransaction()) { $pdo->rollBack(); } throw $error; }
}
function wh_add_stock(PDO $pdo, array $codes): array {
    $codes = wh_barcodes($codes); sort($codes, SORT_STRING);
    $pdo->beginTransaction(); $added = 0; $skipped = 0;
    try {
        $insert = $pdo->prepare("INSERT INTO master_item (barcode, gender, tipe, size, status_transaksi, status_barang) VALUES (?, ?, ?, ?, 'Available', 'Active')");
        foreach ($codes as $code) {
            $item = wh_parse_barcode($code);
            try { $insert->execute([$code, $item['gender'], $item['tipe'], $item['size']]); $added++; }
            catch (PDOException $error) { if (($error->errorInfo[1] ?? 0) !== 1062) { throw $error; } $skipped++; }
        }
        if ($added > 0) { wh_activity($pdo, 'Tambah Stok', $added . ' barcode ditambahkan; ' . $skipped . ' sudah terdaftar.', 'Inventory'); }
        $pdo->commit(); return ['inserted' => $added, 'skipped' => $skipped];
    } catch (Throwable $error) { if ($pdo->inTransaction()) { $pdo->rollBack(); } throw $error; }
}
