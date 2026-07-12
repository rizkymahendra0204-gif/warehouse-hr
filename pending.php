<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Request - HR Warehouse</title>
    
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
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="page-title">Pending Request
                    <p class="text-secondary m-0 mt-1" style="font-size: 14px;">Manajemen inventaris seragam dan kelengkapan</p>
                </div>
                
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Cari No. Request...">
                </div>
            </div>
            
            <!-- Table Container -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col" width="15%">No. Request</th>
                                <th scope="col" width="45%">Detail Karyawan & Item</th>
                                <th scope="col" width="15%" class="text-center">Status</th>
                                <th scope="col" width="25%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Baris Data 1 -->
                            <tr>
                                <td><span class="req-badge">#FR-110726</span></td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 15px;">PT. CENTRAL JAYA</div>
                                    <div class="text-secondary mt-1" style="font-size: 13px;">
                                        <i class="bi bi-box-seam me-1"></i> Request Seragam SA Pria
                                        <span class="mx-2 text-muted">|</span> 
                                        <i class="bi bi-calendar-event me-1"></i> 12 Nov 2025
                                    </div>
                                </td>
                                <td class="text-center"><span class="status-badge">Pending</span></td>
                                <td class="text-center">
                                    <!-- Tombol Pemicu Modal -->
                                    <button type="button" class="btn btn-proses w-100" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                        Proses <i class="bi bi-arrow-right-circle ms-2"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </main>
    </div>
</div>

<!-- ================= MODAL REVIEW REQUEST ================= -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0 pt-4 px-4 align-items-center">
                <h4 class="modal-title fw-bold m-0" id="reviewModalLabel" style="color: #4b5563;">Detail Transaksi #FR-110726</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-4">
                <div class="row g-4">
                    
                    <!-- Kolom Kiri: Ringkasan (SECTION A) -->
                    <div class="col-lg-7">
                        <div class="panel-card">
                            
                            <!-- Header Section A -->
                            <div class="section-header-box">
                                <div class="section-icon bg-red-dark">A</div>
                                <div class="section-title bg-red-main">RINGKASAN PESANAN</div>
                            </div>
                            
                            <table class="table table-borderless summary-table m-0">
                                <tr>
                                    <th class="ps-0">Perusahaan</th>
                                    <td>: PT. CENTRAL JAYA</td>
                                </tr>
                                <tr>
                                    <th class="ps-0">Alamat</th>
                                    <td>: Jl. Jend. Sudirman No. 1</td>
                                </tr>
                                <tr>
                                    <th class="ps-0">Item</th>
                                    <td>: Baju SA Pria (Size M)</td>
                                </tr>
                                <tr>
                                    <th class="ps-0">Item</th>
                                    <td>: Celana SA Pria (Size 32)</td>
                                </tr>
                                <tr>
                                    <th class="ps-0 border-0 pb-0">Jumlah</th>
                                    <td class="border-0 pb-0">: 2 Pcs</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Konfirmasi (SECTION B) -->
                    <div class="col-lg-5">
                        <div class="panel-card">
                            
                            <!-- Header Section B -->
                            <div class="section-header-box">
                                <div class="section-icon bg-blue-dark">B</div>
                                <div class="section-title bg-blue-main">KONFIRMASI</div>
                            </div>
                            
                            <div class="mb-4">
                                <p class="fw-bold mb-2" style="font-size: 14px; color: #1f2937;">Bukti Pembayaran:</p>
                                <div class="bukti-box">
                                    <i class="bi bi-file-earmark-image mb-2 d-block" style="font-size: 48px; color: #9ca3af;"></i>
                                    <p class="text-secondary mb-3" style="font-size: 13px;">bukti_transfer.jpg</p>
                                    <button class="btn btn-sm text-white fw-bold px-3 py-2" style="background-color: #556ee6; border-radius: 6px;">
                                        Lihat Gambar
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-auto">
                                <!-- Tombol Approve -->
                                <a href="transaksi.php?id=FR-110726&pt=PT.+CENTRAL+JAYA" class="btn text-white fw-bold py-2" style="background-color: #556ee6; border-radius: 6px;">
                                    Approve Request
                                </a>
                                <!-- Tombol Reject -->
                                <button type="button" class="btn fw-bold py-2" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; border-radius: 6px; transition: 0.2s;" onmouseover="this.style.backgroundColor='#fee2e2'; this.style.color='#b91c1c'; this.style.borderColor='#f87171';" onmouseout="this.style.backgroundColor='#f1f5f9'; this.style.color='#64748b'; this.style.borderColor='#cbd5e1';">
                                    Reject Request
                                </button>
                            </div>
                            
                        </div>
                    </div>

                </div>
            </div>
            
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>