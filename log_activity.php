<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Activity - HR Warehouse</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Memanggil file CSS Anda -->
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>

<div class="app-container">

    <!-- Memanggil file Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        
        <!-- Memanggil file Topbar -->
        <?php include 'includes/topbar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="content-area p-4">
            
            <!-- Header Halaman & Tombol Aksi -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold m-0" style="color: #1e293b;">Log Activity</h4>
                    <p class="text-secondary m-0 mt-1" style="font-size: 14px;">Riwayat aktivitas dan transaksi sistem</p>
                </div>
                <div class="d-flex gap-3">
                    <button class="btn fw-bold text-success" style="background-color: #dcfce7; border: 1px solid #bbf7d0; border-radius: 8px;">
                        <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                    </button>
                </div>
            </div>

            <!-- Area Filter dan Pencarian -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                
                <!-- Filter Tanggal (Date Range) -->
                <div class="date-filter-group shadow-sm">
                    <i class="bi bi-calendar3 text-secondary me-2"></i>
                    <input type="date" title="Mulai Tanggal">
                    <span class="date-separator">-</span>
                    <input type="date" title="Sampai Tanggal">
                    <button class="btn btn-light border-0 ms-2 text-primary fw-bold"><i class="bi bi-funnel-fill"></i></button>
                </div>
                
                <!-- Pencarian -->
                <div class="input-group shadow-sm" style="width: 300px; border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Cari aktivitas atau nama...">
                </div>
            </div>
            
            <!-- Table Container (Card) -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col" width="18%">Waktu</th>
                                <th scope="col" width="22%">Pengguna</th>
                                <th scope="col" width="45%">Aktivitas</th>
                                <th scope="col" width="15%" class="text-center">Modul</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            <!-- Baris Data 1 (Transaksi) -->
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 14px;">12 Jul 2026</div>
                                    <div class="text-secondary" style="font-size: 12px;">15:42 WIB</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar" style="width: 28px; height: 28px; font-size: 12px;">🍔</div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 13px;">Delicious Burger</div>
                                            <div class="text-secondary" style="font-size: 11px;">Admin HR</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="activity-icon icon-transaksi"><i class="bi bi-check2-circle"></i></div>
                                        <div>
                                            <span class="fw-bold text-dark" style="font-size: 14px;">Approve Request & Transaksi</span>
                                            <p class="text-secondary m-0 mt-1" style="font-size: 13px;">Menyetujui dan memproses Request ID <a href="#" class="text-decoration-none fw-bold">#FR-110726</a> (PT. CENTRAL JAYA)</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="module-badge">Transaksi</span>
                                </td>
                            </tr>

                            <!-- Baris Data 2 (Inventory) -->
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 14px;">12 Jul 2026</div>
                                    <div class="text-secondary" style="font-size: 12px;">10:15 WIB</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar" style="width: 28px; height: 28px; font-size: 12px;">🍔</div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 13px;">Delicious Burger</div>
                                            <div class="text-secondary" style="font-size: 11px;">Admin HR</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="activity-icon icon-inventory"><i class="bi bi-box-seam"></i></div>
                                        <div>
                                            <span class="fw-bold text-dark" style="font-size: 14px;">Penambahan Stok Baru</span>
                                            <p class="text-secondary m-0 mt-1" style="font-size: 13px;">Menambahkan 50 Pcs item <span class="fw-bold text-dark">Kemeja SA Pria</span> (Barcode: SRG-AT-001)</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="module-badge">Inventory</span>
                                </td>
                            </tr>

                            <!-- Baris Data 3 (Return) -->
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 14px;">11 Jul 2026</div>
                                    <div class="text-secondary" style="font-size: 12px;">16:30 WIB</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar" style="width: 28px; height: 28px; font-size: 12px;">🍔</div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 13px;">Delicious Burger</div>
                                            <div class="text-secondary" style="font-size: 11px;">Admin HR</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="activity-icon icon-return"><i class="bi bi-arrow-counterclockwise"></i></div>
                                        <div>
                                            <span class="fw-bold text-dark" style="font-size: 14px;">Proses Return Barang</span>
                                            <p class="text-secondary m-0 mt-1" style="font-size: 13px;">Menerima return item <span class="fw-bold text-dark">Celana SA Pria</span> karena cacat produksi.</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="module-badge">Return</span>
                                </td>
                            </tr>

                            <!-- Baris Data 4 (System/Login) -->
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 14px;">11 Jul 2026</div>
                                    <div class="text-secondary" style="font-size: 12px;">08:00 WIB</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar" style="width: 28px; height: 28px; font-size: 12px;">🍔</div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 13px;">Delicious Burger</div>
                                            <div class="text-secondary" style="font-size: 11px;">Admin HR</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="activity-icon icon-system"><i class="bi bi-shield-lock"></i></div>
                                        <div>
                                            <span class="fw-bold text-dark" style="font-size: 14px;">Login Sistem</span>
                                            <p class="text-secondary m-0 mt-1" style="font-size: 13px;">Berhasil login ke dalam Dashboard HR Warehouse via IP 192.168.1.5</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="module-badge">Sistem</span>
                                </td>
                            </tr>

                        </tbody>
                    </table>
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