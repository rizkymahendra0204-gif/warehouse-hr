<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Form - HR Warehouse</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Memanggil file CSS Utama -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .form-container {
            background-color: #ffffff;
            border-radius: 4px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .form-header {
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .logo-central {
            font-family: Georgia, serif; /* Menggunakan font serif untuk logo CENTRAL */
            color: #b91c1c; /* Merah gelap */
            font-weight: 700;
            font-size: 24px;
            letter-spacing: 1.5px;
            margin: 0;
        }
        .section-box {
            background-color: #f4f5f8;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #000;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        /* Style untuk Placeholder (Kotak Abu-abu) */
        .ph-box { background-color: #d1d5db; border-radius: 8px; }
        .ph-pill { background-color: #d1d5db; border-radius: 50rem; }
        .ph-text { background-color: #d1d5db; border-radius: 4px; }
        
        .btn-submit {
            background-color: #b91c1c;
            color: white;
            border-radius: 8px;
            padding: 8px 32px;
            font-weight: 600;
            border: none;
            transition: 0.2s;
        }
        .btn-submit:hover {
            background-color: #991b1b;
        }
        </style>
        


</head>
<body>

<div class="app-container">
    
    <!-- Memanggil file Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-wrapper" style="background-color: #e5e7eb;">
        
        <!-- TOPBAR (Anda bisa buat includes/topbar.php nanti agar lebih rapi) -->
        <header class="topbar" style="justify-content: space-between; background-color: #e5e7eb; border-bottom: none;">
            <button id="sidebarToggle" class="btn btn-light d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid #d1d5db;">
                <i class="bi bi-list fs-5 text-secondary"></i>
            </button>
            <div class="profile-section">
                <div class="user-info">
                    <div class="avatar">🍔</div>
                    <span class="user-name">Delicious Burger</span>
                </div>
            </div>
        </header>

        <!-- CONTENT AREA -->
        <main class="content-area">
            <div class="text-secondary mb-2" style="font-size: 14px;">Request Form</div>
            
            <!-- FORM CONTAINER PUTIH -->
            <div class="form-container">
                
                <!-- Header Form -->
                <div class="d-flex justify-content-between align-items-center form-header">
                    <h5 class="m-0 fw-bold" style="font-size: 16px;">Form Request Seragam</h5>
                    <h2 class="logo-central">CENTRAL</h2>
                </div>

                <div class="row">
                    <!-- KOLOM KIRI -->
                    <div class="col-md-5 pe-md-4">
                        
                        <!-- Box: Keterangan -->
                        <div class="section-box d-flex justify-content-center align-items-center" style="height: 220px;">
                            <span class="fw-bold text-dark">Keterangan</span>
                        </div>

                        <!-- Box: Information -->
                        <div class="section-box" style="height: 260px;">
                            <div class="section-title">INFORMATION</div>
                            <div class="ph-pill mb-4" style="height: 24px; width: 85%;"></div>
                            <div class="ph-pill mb-4" style="height: 24px; width: 85%;"></div>
                            <div class="ph-pill mb-4" style="height: 24px; width: 85%;"></div>
                            <div class="ph-pill" style="height: 24px; width: 85%;"></div>
                        </div>

                    </div>

                    <!-- KOLOM KANAN -->
                    <div class="col-md-7">
                        
                        <!-- Box: Jenis Seragam -->
                        <div class="section-box">
                            <div class="section-title">JENIS SERAGAM DAN UKURAN</div>
                            
                            <!-- Placeholder Radio Buttons -->
                            <div class="d-flex gap-4 mb-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="ph-text" style="width: 14px; height: 14px;"></div>
                                    <div class="ph-text" style="width: 60px; height: 10px;"></div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="ph-text" style="width: 14px; height: 14px;"></div>
                                    <div class="ph-text" style="width: 60px; height: 10px;"></div>
                                </div>
                            </div>

                            <!-- List Item 1 -->
                            <div class="d-flex gap-3 mb-4">
                                <!-- Area gambar: Nantinya bisa diisi foto referensi seperti seragam resmi/batik -->
                                <div class="ph-box" style="width: 80px; height: 80px; flex-shrink: 0;"></div>
                                <div class="flex-grow-1 pt-2">
                                    <div class="ph-text mb-3" style="width: 80px; height: 8px;"></div>
                                    <div class="d-flex gap-3">
                                        <div class="ph-pill" style="height: 24px; width: 160px;"></div>
                                        <div class="ph-pill" style="height: 24px; width: 60px;"></div>
                                        <div class="ph-pill" style="height: 24px; width: 60px;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- List Item 2 -->
                            <div class="d-flex gap-3 mb-4">
                                <div class="ph-box" style="width: 80px; height: 80px; flex-shrink: 0;"></div>
                                <div class="flex-grow-1 pt-2">
                                    <div class="ph-text mb-3" style="width: 80px; height: 8px;"></div>
                                    <div class="d-flex gap-3">
                                        <div class="ph-pill" style="height: 24px; width: 160px;"></div>
                                        <div class="ph-pill" style="height: 24px; width: 60px;"></div>
                                        <div class="ph-pill" style="height: 24px; width: 60px;"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Placeholder Total / Info tambahan bawah -->
                            <div class="d-flex justify-content-end mt-2">
                                <div class="ph-pill" style="height: 24px; width: 180px;"></div>
                            </div>
                        </div>

                        <!-- Box: Jenis Pembayaran -->
                        <div class="section-box mb-2">
                            <div class="section-title">JENIS PEMBAYARAN</div>
                            <!-- Placeholder Radio Buttons -->
                            <div class="d-flex gap-4 mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="ph-text" style="width: 14px; height: 14px;"></div>
                                    <div class="ph-text" style="width: 60px; height: 10px;"></div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="ph-text" style="width: 14px; height: 14px;"></div>
                                    <div class="ph-text" style="width: 60px; height: 10px;"></div>
                                </div>
                            </div>
                            <div class="ph-box" style="width: 140px; height: 60px;"></div>
                        </div>

                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="d-flex justify-content-end mt-3">
                    <button class="btn-submit">Simpan</button>
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