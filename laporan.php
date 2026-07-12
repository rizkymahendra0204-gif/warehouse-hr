<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Analitik - HR Warehouse</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Memanggil file CSS Anda -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* Styling Khusus Halaman Laporan */
        .report-filter-card {
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 20px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        
        .kpi-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .kpi-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        
        /* Varian Warna KPI */
        .kpi-in { background-color: #dcfce7; color: #166534; }
        .kpi-out { background-color: #e0e7ff; color: #4338ca; }
        .kpi-return { background-color: #fee2e2; color: #b91c1c; }
        
        /* Area Grafik (Placeholder) */
        .chart-container {
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 24px;
            margin-bottom: 24px;
            min-height: 300px;
            display: flex;
            flex-direction: column;
        }

        .chart-placeholder {
            flex-grow: 1;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 39px,
                #f3f4f6 39px,
                #f3f4f6 40px
            );
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            padding-top: 40px;
        }

        .bar {
            width: 40px;
            background-color: #556ee6;
            border-radius: 4px 4px 0 0;
            opacity: 0.8;
            transition: 0.3s;
        }
        
        .bar:hover { opacity: 1; }

        /* Tombol Export Utama */
        .btn-excel {
            background-color: #107c41; /* Warna hijau khas MS Excel */
            color: white;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 600;
            border: none;
            transition: background-color 0.2s;
        }
        .btn-excel:hover {
            background-color: #0c5e31;
            color: white;
        }
    </style>
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
                    <h4 class="fw-bold m-0" style="color: #1e293b;">Laporan & Analitik</h4>
                    <p class="text-secondary m-0 mt-1" style="font-size: 14px;">Ringkasan pergerakan stok dan transaksi</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary fw-bold" style="border-radius: 8px;">
                        <i class="bi bi-printer me-2"></i>Cetak
                    </button>
                    <!-- Tombol Export Excel ditonjolkan -->
                    <button class="btn btn-excel shadow-sm">
                        <i class="bi bi-file-earmark-excel-fill me-2"></i>Export ke Excel
                    </button>
                </div>
            </div>

            <!-- Area Filter Laporan -->
            <div class="report-filter-card shadow-sm">
                <form class="row g-3 align-items-end">
                    <!--<div class="col-md-3">
                        <label class="form-label fw-bold" style="font-size: 13px; color: #4b5563;">Jenis Laporan</label>
                        <select class="form-select shadow-sm" style="font-size: 14px;">
                            <option value="transaksi">Laporan Transaksi Keluar</option>
                            <option value="masuk">Laporan Barang Masuk</option>
                            <option value="return">Laporan Return/Rusak</option>
                            <option value="stok">Laporan Opname Stok</option>
                        </select>
                    </div> -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold" style="font-size: 13px; color: #4b5563;">Mulai Tanggal</label>
                        <input type="date" class="form-control shadow-sm" style="font-size: 14px;" value="2026-07-01">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold" style="font-size: 13px; color: #4b5563;">Sampai Tanggal</label>
                        <input type="date" class="form-control shadow-sm" style="font-size: 14px;" value="2026-07-31">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn text-white fw-bold w-100 shadow-sm" style="background-color: #556ee6;">
                            <i class="bi bi-funnel me-2"></i>Tampilkan Data
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tiga Kartu KPI (Key Performance Indicator) -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-in"><i class="bi bi-box-arrow-in-down"></i></div>
                        <div>
                            <div class="text-secondary fw-bold mb-1" style="font-size: 13px; text-transform: uppercase;">Barang Masuk</div>
                            <h3 class="fw-bold m-0 text-dark">450 <span style="font-size: 14px; color: #64748b; font-weight: 500;">Pcs</span></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-out"><i class="bi bi-box-arrow-up"></i></div>
                        <div>
                            <div class="text-secondary fw-bold mb-1" style="font-size: 13px; text-transform: uppercase;">Barang Keluar</div>
                            <h3 class="fw-bold m-0 text-dark">328 <span style="font-size: 14px; color: #64748b; font-weight: 500;">Pcs</span></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-return"><i class="bi bi-arrow-counterclockwise"></i></div>
                        <div>
                            <div class="text-secondary fw-bold mb-1" style="font-size: 13px; text-transform: uppercase;">Total Return</div>
                            <h3 class="fw-bold m-0 text-dark">12 <span style="font-size: 14px; color: #64748b; font-weight: 500;">Pcs</span></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Area Chart/Grafik & Tabel Data -->
            <!-- <div class="row g-4"> -->
                
                <!-- Kolom Kiri: Chart (Contoh Visualisasi) 
                <div class="col-lg-12">
                    <div class="chart-container shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold m-0" style="color: #1e293b;">Grafik Distribusi Seragam (Juli 2026)</h6>
                        </div> -->
                        
                        <!-- Ini adalah Placeholder Grafik Menggunakan murni HTML/CSS 
                        <div class="chart-placeholder">
                            <div class="bar" style="height: 40%;" title="Minggu 1: 40 Pcs"></div>
                            <div class="bar" style="height: 70%;" title="Minggu 2: 70 Pcs"></div>
                            <div class="bar" style="height: 90%; background-color: #4338ca;" title="Minggu 3: 90 Pcs (Puncak)"></div>
                            <div class="bar" style="height: 55%;" title="Minggu 4: 55 Pcs"></div>
                        </div>
                        <div class="d-flex justify-content-around mt-3 text-secondary" style="font-size: 12px; font-weight: 600;">
                            <span>Minggu 1</span>
                            <span>Minggu 2</span>
                            <span>Minggu 3</span>
                            <span>Minggu 4</span>
                        </div>
                    </div>
                </div> -->

                <!-- Kolom Bawah: Tabel Data Detail (Siap untuk diexport) -->
                <div class="col-lg-12">
                    <div class="table-card shadow-sm">
                        <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
                            <h6 class="fw-bold m-0" style="color: #1e293b;">Rincian Transaksi Keluar</h6>
                            <span class="badge bg-light text-secondary border">Menampilkan 5 Data Teratas</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">Tanggal</th>
                                        <th scope="col">ID Req</th>
                                        <th scope="col">Perusahaan</th>
                                        <th scope="col">Item Diberikan</th>
                                        <th scope="col" class="text-center">Total (Pcs)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><div class="fw-bold text-dark">12/07/2026</div></td>
                                        <td>#FR-110726</td>
                                        <td>PT. CENTRAL JAYA</td>
                                        <td class="text-secondary">Kemeja Pria (2), Celana (2)</td>
                                        <td class="text-center fw-bold text-primary">4</td>
                                    </tr>
                                    <tr>
                                        <td><div class="fw-bold text-dark">10/07/2026</div></td>
                                        <td>#FR-110718</td>
                                        <td>CV. ABADI JAYA</td>
                                        <td class="text-secondary">Seragam SA Wanita (1)</td>
                                        <td class="text-center fw-bold text-primary">1</td>
                                    </tr>
                                    <tr>
                                        <td><div class="fw-bold text-dark">08/07/2026</div></td>
                                        <td>#FR-110705</td>
                                        <td>PT. MAJU BERSAMA</td>
                                        <td class="text-secondary">Kemeja Pria (5), Rok (2)</td>
                                        <td class="text-center fw-bold text-primary">7</td>
                                    </tr>
                                </tbody>
                            </table>
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