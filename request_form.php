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
</head>
<body>

<div class="app-container">

        <!-- CONTENT AREA -->
        <main class="content-area p-4">
            
            <!-- FORM CONTAINER PUTIH -->
            <div class="form-container">
                
                <!-- Header Form -->
                <div class="d-flex justify-content-between align-items-center form-header mb-4">
                    <h6 class="m-0 fw-bold" style="font-size: 16px;">Form Request Seragam</h6>
                    <h3 class="logo-central m-0">CENTRAL</h3>
                </div>

                <div class="row">
                    <!-- KOLOM KIRI -->
                    <div class="col-md-5 pe-md-4">
                        
                        <!-- Box: Keterangan -->
                        <div class="section-box d-flex align-items-center" style="min-height: 220px; height: auto;">
                            <div style="margin-top: 14px;">
                                <h4 class="fw-bold mb-3">Tatacara Pengisian Form</h4>
                                <p class="text-secondary" style="font-size: 14px; margin-bottom: 8px;">1. Lengkapi data perusahaan, alamat, brand, dan tanggal.</p>
                                <p class="text-secondary" style="font-size: 14px; margin-bottom: 8px;">2. Pilih jenis seragam, ukuran, dan jumlah.</p>
                                <p class="text-secondary" style="font-size: 14px; margin-bottom: 8px;">3. Pilih metode pembayaran dan unggah bukti pembayaran.</p>
                                <p class="text-secondary" style="font-size: 14px; margin-bottom: 8px;">4. Klik tombol "Submit" untuk mengirimkan request.</p><br><br>
                                <p class="text-secondary" style="font-size: 14px; margin-bottom: 8px;">Mohon untuk memberitahu SA untuk memberikan Nomor Request </p>
                                <p class="text-secondary" style="font-size: 14px; margin-bottom: 8px;">Jika ada pertanyaan, silakan hubungi kami (ESC) </p>
                            </div>
                        </div>

                        <!-- Box: Information -->
                        <div class="section-box">
                            <!-- Header Merah (Saya ubah judulnya menjadi INFORMASI agar sesuai) -->
                            <div class="d-flex align-items-center mb-4" style="background-color: #b91c1c; color: white; border-radius: 4px;">
                                <div class="px-3 py-2 fw-bold" style="background-color: #991b1b;">A</div>
                                <div class="px-3 fw-bold">INFORMASI</div>
                            </div>
                            
                            <form action="proses_request.php" method="POST">
                                <div class="mb-2">
                                    <label class="form-label mb-1" style="font-size: 14px; font-weight: 900; color: #4b5563;">Perusahaan</label>
                                    <input type="text" class="form-control form-control-sm rounded-pill" name="perusahaan" placeholder="Perusahaan" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" style="font-size: 14px; font-weight: 900; color: #4b5563;">Alamat</label>
                                    <input type="text" class="form-control form-control-sm rounded-pill" name="alamat" placeholder="Alamat" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" style="font-size: 14px; font-weight: 900; color: #4b5563;">Brand</label>
                                    <input type="text" class="form-control form-control-sm rounded-pill" name="brand" placeholder="Brand" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" style="font-size: 14px; font-weight: 900; color: #4b5563;">Tanggal</label>
                                    <input type="date" class="form-control form-control-sm rounded-pill" name="tanggal" required>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- KOLOM KANAN -->
                    <div class="col-md-7">
                        
                        <!-- Box: Jenis Seragam -->
                        <div class="section-box">
                            <div class="d-flex align-items-center mb-4" style="background-color: #b91c1c; color: white; border-radius: 4px;">
                                <div class="px-3 py-2 fw-bold" style="background-color: #991b1b;">B</div>
                                <div class="px-3 fw-bold">JENIS SERAGAM DAN UKURAN</div>
                            </div>

                            <div class="mb-4">
                                <label class="fw-bold mb-2" style="font-size: 13px;">Pilih Jenis Seragam:</label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="saPria">
                                        <label class="form-check-label" for="saPria" style="font-size: 14px; font-weight: 600;">SA PRIA</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="saWanita">
                                        <label class="form-check-label" for="saWanita" style="font-size: 14px; font-weight: 600;">SA WANITA</label>
                                    </div>
                                </div>
                            </div>

                            <label class="fw-bold mb-1" style="font-size: 13px;">Pilih Item & Ukuran</label>

                            <!-- Baris Item 1: Top (Ditambahkan class price & qty untuk perhitungan JS) -->
                            <div class="row align-items-center mb-3 calc-row">
                                <div class="col-1 text-center"><img src="assets/img/Baju.png" width="50"></div>
                                <div class="col-4">
                                    <label class="form-label mb-1 fw-bold" style="font-size: 11px;">Ukuran Top:</label>
                                    <select class="form-select form-select-sm">
                                        <option>Pilih Ukuran</option>
                                        <option>S</option><option>M</option><option>L</option>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label class="form-label mb-1 fw-bold" style="font-size: 11px;">Harga:</label>
                                    <input type="text" class="form-control form-control-sm price" value="100000" readonly>
                                </div>
                                <div class="col-4">
                                    <label class="form-label mb-1 fw-bold" style="font-size: 11px;">Jumlah (Maks 3):</label>
                                    <div class="input-group input-group-sm">
                                        <button class="btn btn-outline-secondary qty-btn" data-type="minus" type="button">-</button>
                                        <input type="text" class="form-control text-center qty" value="0" readonly>
                                        <button class="btn btn-outline-secondary qty-btn" data-type="plus" type="button">+</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Baris Item 2: Bottom -->
                            <div class="row align-items-center mb-3 calc-row">
                                <div class="col-1 text-center"><img src="assets/img/Celana.png" width="50"></div>
                                <div class="col-4">
                                    <label class="form-label mb-1 fw-bold" style="font-size: 11px;">Ukuran Bottom:</label>
                                    <select class="form-select form-select-sm">
                                        <option>Pilih Ukuran</option>
                                        <option>28</option><option>30</option><option>32</option>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label class="form-label mb-1 fw-bold" style="font-size: 11px;">Harga:</label>
                                    <input type="text" class="form-control form-control-sm price" value="150000" readonly>
                                </div>
                                <div class="col-4">
                                    <label class="form-label mb-1 fw-bold" style="font-size: 11px;">Jumlah (Maks 3):</label>
                                    <div class="input-group input-group-sm">
                                        <button class="btn btn-outline-secondary qty-btn" data-type="minus" type="button">-</button>
                                        <input type="text" class="form-control text-center qty" value="0" readonly>
                                        <button class="btn btn-outline-secondary qty-btn" data-type="plus" type="button">+</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Baris Grand Total -->
                            <div class="row mt-4 pt-3 border-top">
                                <div class="col-8 text-end fw-bold" style="font-size: 14px;">Grand Total:</div>
                                <div class="col-4 text-start fw-bold text-danger" style="font-size: 14px;" id="grandTotal">Rp 0</div>
                            </div>
                        </div>

                        <!-- Box: Jenis Pembayaran -->
                        <div class="section-box mb-2">
                            <div class="d-flex align-items-center mb-4" style="background-color: #b91c1c; color: white; border-radius: 4px;">
                                <div class="px-3 py-2 fw-bold" style="background-color: #991b1b;">C</div>
                                <div class="px-3 fw-bold">JENIS PEMBAYARAN</div>
                            </div>
                            
                            <!-- Pilihan Pembayaran (Radio) -->
                            <div class="d-flex gap-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_bayar" id="transfer" value="transfer">
                                    <label class="form-check-label fw-bold" for="transfer" style="font-size: 14px; font-weight: 600;">Transfer</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_bayar" id="potongOmzet" value="potong_omzet">
                                    <label class="form-check-label fw-bold" for="potongOmzet" style="font-size: 14px; font-weight: 600;">Potong Omzet</label>
                                </div>
                            </div>

                            <label class="fw-bold mb-2" style="font-size: 14px;">Upload Bukti</label>
                            
                            <label for="buktiFile" class="upload-area d-flex flex-column align-items-center justify-content-center" style="cursor: pointer;">
                                <i class="bi bi-cloud-arrow-up fs-2 text-secondary"></i>
                                <span class="fw-bold mt-1" style="color: #6b7280;">Pilih File</span>
                                <input type="file" id="buktiFile" name="bukti_pembayaran" class="d-none">
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="d-flex justify-content-end mt-3">
                    <button class="btn-submit">Submit</button>
                </div>

            </div>
        </main>
    </div>
</div>

<!-- Modal Success -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="success-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                
                <h3 class="fw-bold mt-3">Terima Kasih!</h3>
                <p class="text-secondary mt-3">Request Anda telah berhasil dikirim. <br> Seragam dapat diambil di ESC Central.</p>
                <p class="text-secondary mt-1">Berikut adalah Tiket Antrian Anda:</p>
                <!-- Tiket otomatis menggunakan format FR-DDMMYY (Opsional: bisa di-generate via PHP nanti) -->
                <p class="ticket-number fw-bold" style="font-size: 24px;">#FR-110726</p>
                
                <button type="button" class="btn btn-danger w-100 mt-3 py-2" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Memanggil File Javascript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/scripts.js"></script>
</body>
</html>