<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - HR Warehouse</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <!-- TOPBAR -->
        <header class="topbar" style="justify-content: space-between;">
            <button id="sidebarToggle" class="btn btn-light d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid #e5e7eb;">
                <i class="bi bi-list fs-5 text-secondary"></i>
            </button>
            <div class="user-info">
                <div class="avatar">🍔</div>
                <span class="user-name">Delicious Burger</span>
            </div>
        </header>

        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">
            <div class="d-flex align-items-center mb-4">
                <a href="pending.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
                <h4 class="fw-bold m-0" style="color: #4b5563;">Detail Transaksi #FR-110726</h4>
            </div>
            
            <div class="row">
                <!-- Kolom Kiri: Detail Permintaan -->
                <div class="col-md-7">
                    <div class="section-box">
                        <div class="d-flex align-items-center mb-4" style="background-color: #b91c1c; color: white; border-radius: 4px;">
                            <div class="px-3 py-2 fw-bold" style="background-color: #991b1b;">A</div>
                            <div class="px-3 fw-bold">RINGKASAN PESANAN</div>
                        </div>
                        
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Perusahaan</th>
                                <td>: PT. CENTRAL JAYA</td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td>: Jl. Jend. Sudirman No. 1</td>
                            </tr>
                            <tr>
                                <th>Item</th>
                                <td>: Seragam SA Pria (Size M)</td>
                            </tr>
                            <tr>
                                <th>Jumlah</th>
                                <td>: 2 Pcs</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Kolom Kanan: Aksi & Bukti -->
                <div class="col-md-5">
                    <div class="section-box">
                        <div class="d-flex align-items-center mb-4" style="background-color: #5145cd; color: white; border-radius: 4px;">
                            <div class="px-3 py-2 fw-bold" style="background-color: #3e35a1;">B</div>
                            <div class="px-3 fw-bold">KONFIRMASI</div>
                        </div>

                        <div class="mb-4">
                            <label class="fw-bold mb-2">Bukti Pembayaran:</label>
                            <div class="p-3 border rounded text-center" style="background-color: #f9fafb;">
                                <i class="bi bi-file-earmark-image fs-1 text-secondary"></i>
                                <p class="small text-muted mt-1">bukti_transfer.jpg</p>
                                <a href="#" class="btn btn-sm btn-outline-primary">Lihat Gambar</a>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-success py-2 fw-bold">Approve Request</button>
                            <button class="btn btn-danger py-2 fw-bold">Reject Request</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>