$(document).ready(function () {
    
    // ================= 1. GLOBAL & SIDEBAR =================
    // Fungsi untuk menyembunyikan atau memunculkan sidebar
    $("#sidebarToggle").on("click", function () {
        $(".sidebar").toggleClass("collapsed");
    });

    // Trigger modal saat tombol simpan/submit diklik (Cukup Tulis 1 Kali)
    $(".btn-submit").on("click", function (e) {
        e.preventDefault(); // Mencegah submit langsung ke server
        var myModal = new bootstrap.Modal(document.getElementById("successModal"));
        myModal.show();
    });


    // ================= 2. REQUEST FORM LOGIC =================
    // Logika Tombol Jumlah (Plus / Minus)
    $(".qty-btn").click(function (e) {
        e.preventDefault();

        let inputField = $(this).siblings(".qty");
        let currentVal = parseInt(inputField.val()) || 0;
        let type = $(this).data("type");

        if (type === "plus") {
            if (currentVal < 3) { // Maksimal 3
                inputField.val(currentVal + 1);
            }
        } else if (type === "minus") {
            if (currentVal > 0) { // Minimal 0
                inputField.val(currentVal - 1);
            }
        }
        // Hitung total setiap kali tombol ditekan
        calculateGrandTotal();
    });

    // Logika Perhitungan Grand Total
    function calculateGrandTotal() {
        let total = 0;
        $(".calc-row").each(function () {
            let price = parseInt($(this).find(".price").val()) || 0;
            let qty = parseInt($(this).find(".qty").val()) || 0;
            total += price * qty;
        });
        $("#grandTotal").text("Rp " + total.toLocaleString("id-ID"));
    }


   // ================= 3. TRANSAKSI LOGIC (HORIZONTAL & LIMIT) =================
    // Fungsi Tambah Baris Horizontal (Max 6)
    $('#btn-tambah-item').click(function () {
        let maxItems = 6; 
        let currentItems = $('.item-row').length;

        if (currentItems < maxItems) {
            let newRow = $('.item-row:first').clone();
            
            newRow.find('input').val(''); 
            newRow.find('.barcode-detail-text').text('').css('color', '');
            newRow.find('.barcode-detail-hidden').val('');
            newRow.find('.btn-remove-item').show(); 
            
            $('#dynamic-item-container').append(newRow);
            updateItemNumbers();
        } else {
            alert('Maksimal hanya boleh ada ' + maxItems + ' kolom item transaksi!');
        }
    });

    // Fungsi Hapus Baris/Kolom
    $(document).on('click', '.btn-remove-item', function() {
        $(this).closest('.item-row').remove();
        updateItemNumbers();
    });

    function updateItemNumbers() {
        $('.item-row').each(function(index) {
            $(this).find('.item-number').html('<i class="bi bi-box-seam me-2"></i>Item #' + (index + 1));
        });
    }

    // REVISI LOGIKA: TOMBOL VALIDATE GLOBAL (MEMERIKSA SEMUA BARIS SEKALIGUS)
    $('#btn-validate-all').click(function() {
        
        // Looping memeriksa setiap baris item yang aktif di layar
        $('.item-row').each(function() {
            let rowContainer = $(this);
            let barcodeInput = rowContainer.find('input[name="barcode_item[]"]');
            let barcodeValue = barcodeInput.val().trim();
            let displayText = rowContainer.find('.barcode-detail-text');
            let hiddenInput = rowContainer.find('.barcode-detail-hidden');

            // Jika ada baris yang belum diisi barcode-nya
            if (barcodeValue === '') {
                displayText.text('Barcode belum diisi!').css('color', '#b91c1c');
                return; // Lanjut ke baris berikutnya (continue)
            }

            displayText.text('Memvalidasi...').css('color', '#6b7280');

            // Jalankan AJAX untuk baris ini
            $.ajax({
                url: 'cek_master_barcode.php', 
                method: 'POST',
                data: { barcode: barcodeValue },
                dataType: 'json',
                success: function(response) {
                    if (response.status == 'success') {
                        // Jika valid & masuk master data
                        displayText.text(response.nama_barang).css('color', '#15803d');
                        hiddenInput.val(response.nama_barang);
                    } else {
                        // Jika tidak terdaftar
                        displayText.text('Barang tidak ditemukan!').css('color', '#b91c1c');
                        hiddenInput.val('');
                    }
                },
                error: function() {
                    displayText.text('Gagal koneksi server!').css('color', '#b91c1c');
                    hiddenInput.val('');
                }
            });
        });
    });

    // LOGIKA OTOMATIS SAAT SCAN BARCODE LANGSUNG (OPSIONAL JIKA DIBUTUHKAN)
    $(document).on('change', 'input[name="barcode_item[]"]', function() {
        let currentInput = $(this);
        let barcodeValue = currentInput.val().trim();
        let rowContainer = currentInput.closest('.item-row');
        
        if(barcodeValue != '') {
            // Mengosongkan status eror/teks lama saat admin menembak ulang laser scanner
            rowContainer.find('.barcode-detail-text').text('');
        }
    });


        // =========================================================================
        // CONTOH LOGIKA JAVASCRIPT SAAT SCAN BARCODE DIJALANKAN (UNTUK MENGISI TEKS)
        // =========================================================================
        // Ketika admin selesai melakukan scan pada input barcode atasan/bawahan
        $(document).on('change', 'input[name="barcode_atasan[]"], input[name="barcode_bawahan[]"]', function() {
            let currentInput = $(this);
            let barcodeValue = currentInput.val();
            
            if(barcodeValue != '') {
                // Di sini letak proses AJAX Anda mencari nama item ke database, ini simulasi hasilnya:
                let namaItemHasilScan = "BAJU PRIA (M)"; 
                
                // 1. Memunculkan teks murni di bawah input kotak (Sesuai Gambar)
                currentInput.closest('.col-md-6').find('.barcode-detail-text').text(namaItemHasilScan);
                
                // 2. Mengisi hidden input agar datanya bisa terbaca oleh $_POST PHP saat disimpan
                currentInput.closest('.col-md-6').find('.barcode-detail-hidden').val(namaItemHasilScan);
            }
        });

    });


    // ================= 4. AJAX PLACEHOLDER (Perbaikan Fungsi Success) =================
    // Contoh implementasi: Ketika input ID Request diketik, jalankan AJAX
    $('#id_request').on('blur', function() {
        var idRequest = $(this).val();
        
        if(idRequest != '') {
            $.ajax({
                url: 'get_data_request.php',
                method: 'POST',
                data: {id: idRequest},
                dataType: 'json',
                success: function (response) {
                    if(response.status == 'success') {
                        let rows = `
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td class="fw-bold py-3 ps-0 text-secondary" width="15%">Item</td>
                                <td class="py-3 text-dark">: ${response.baju}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td class="fw-bold py-3 ps-0 text-secondary">Item</td>
                                <td class="py-3 text-dark">: ${response.celana}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold py-3 ps-0 text-secondary">Jumlah</td>
                                <td class="py-3 text-dark">: ${response.jumlah} Pcs</td>
                            </tr>
                        `;
                        // Masukkan ke dalam tabel secara instan
                        $('#rincian-item-list').html(rows);
                    }
                }
            });
        }
    });