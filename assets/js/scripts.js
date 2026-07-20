$(document).ready(function () {
    
    // ================= 1. GLOBAL & SIDEBAR =================
    // Fungsi untuk menyembunyikan atau memunculkan sidebar
    $("#sidebarToggle").on("click", function () {
        $(".sidebar").toggleClass("collapsed");
    });

    // Trigger modal saat tombol simpan/submit diklik
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
        // Hitung total setiap kali tombol dis
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

    // LOGIKA OTOMATIS SAAT SCAN BARCODE LANGSUNG
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
    $(document).on('change', 'input[name="barcode_atasan[]"], input[name="barcode_bawahan[]"]', function() {
        let currentInput = $(this);
        let barcodeValue = currentInput.val();
        
        if(barcodeValue != '') {
            let namaItemHasilScan = "BAJU PRIA (M)"; 
            
            // 1. Memunculkan teks murni di bawah input kotak
            currentInput.closest('.col-md-6').find('.barcode-detail-text').text(namaItemHasilScan);
            // 2. Mengisi hidden input 
            currentInput.closest('.col-md-6').find('.barcode-detail-hidden').val(namaItemHasilScan);
        }
    });


    // ================= 4. AJAX PLACEHOLDER (Perbaikan Fungsi Success) =================
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
    }); // <-- SEBELUMNYA KURANG TANDA ');' DI SINI


    // ================= 5. LIVE SEARCH PENDING REQUEST (JQUERY VERSION) =================
    if ($("#searchInput").length > 0) {
        $("#searchInput").on("keyup", function() {
            // Ambil teks yang diketik lalu ubah ke huruf kecil
            let filter = $(this).val().toLowerCase();
            
            // Cek setiap baris (tr) di dalam tabel
            $(".table-responsive tbody tr").each(function() {
                // Abaikan baris peringatan "Belum ada request" (biasanya memiliki kolom kurang dari 4)
                if ($(this).find("td").length < 4) return;
                
                // Ambil semua teks dari baris tersebut
                let textContent = $(this).text().toLowerCase();
                
                // Jika teks mengandung kata kunci, munculkan. Jika tidak, sembunyikan barisnya.
                if (textContent.includes(filter)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    }

});

// ================= 6. VALIDATE BARCODE =================

document.addEventListener('DOMContentLoaded', function () {
    const btnValidate = document.getElementById('btn-validate');
    
    if (btnValidate) {
        btnValidate.addEventListener('click', function () {
            // 1. Ambil semua input barcode berdasarkan atribut name asli HTML Anda
            const barcodeInputs = document.querySelectorAll('input[name="barcode_item[]"]');
            
            barcodeInputs.forEach(input => {
                const barcodeValue = input.value.trim();
                
                // 2. Cari parent kartu item terdekat (.item-row)
                const itemRow = input.closest('.item-row');
                if (!itemRow) return;
                
                // 3. Targetkan elemen teks detail dan hidden input bawaan HTML Anda
                const msgContainer = itemRow.querySelector('.barcode-detail-text');
                const hiddenInput = itemRow.querySelector('.barcode-detail-hidden');
                
                if (!msgContainer) return;

                if (barcodeValue === '') {
                    msgContainer.style.color = '#dc2626'; // Warna merah
                    msgContainer.innerText = "❌ BARCODE KOSONG!";
                    return;
                }

                msgContainer.style.color = '#6b7280'; // Warna abu-abu
                msgContainer.innerText = "MEMVALIDASI...";

                // 4. Kirim data ke API Backend
                fetch('controllers/validate_barcode.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ barcode: barcodeValue })
                })
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        msgContainer.style.color = '#16a34a'; // Warna hijau jika sukses
                        msgContainer.innerText = res.message; 
                        
                        // Isi hidden input agar teks detail ikut terkirim saat form di-submit
                        if (hiddenInput) {
                            hiddenInput.value = res.message;
                        }
                    } else {
                        msgContainer.style.color = '#dc2626'; // Warna merah jika gagal/tidak terdaftar
                        msgContainer.innerText = res.message;
                        
                        if (hiddenInput) {
                            hiddenInput.value = '';
                        }
                    }
                })
                .catch(error => {
                    msgContainer.style.color = '#dc2626';
                    msgContainer.innerText = "GAGAL KONEKSI SERVER!";
                    console.error(error);
                });
            });
        });
    }
});

// ================= 7. GENERATE BARCODE =================

$(document).ready(function() {
    // Fungsi membuat string kode acak berbasis waktu & angka random
    $('#btnRandom').click(function() {
        const timestamp = new Date().getTime().toString().substr(-6);
        const randomNum = Math.floor(100 + Math.random() * 900);
        const codeResult = "HRW" + timestamp + randomNum;
        $('#barcode_value').val(codeResult).trigger('input');
    });

    // Event deteksi perubahan teks pada input kode untuk live preview
    $('#barcode_value').on('input', function() {
        const val = $(this).val().trim();
        if(val.length > 2) {
            $('#btnPrint').removeAttr('disabled');
            // Render gambar garis barcode menggunakan JsBarcode
            JsBarcode("#barcode-canvas", val, {
                format: "CODE128",
                width: 2,
                height: 60,
                displayValue: true,
                fontSize: 14,
                lineColor: "#0f172a"
            });
            $('#label-info').text("Status: Siap Registrasi / Cetak");
        } else {
            $('#btnPrint').attr('disabled', 'disabled');
            // Kosongkan kanvas jika input terlalu pendek
            const svg = document.getElementById('barcode-canvas');
            while (svg.lastChild) { svg.removeChild(svg.lastChild); }
            $('#label-info').text("");
        }
    });

    // Trigger preview otomatis jika variabel PHP terisi sesudah sukses simpan
    if($('#barcode_value').val() !== '') {
        $('#barcode_value').trigger('input');
        $('#label-info').html("<span class='text-success'><i class='bi bi-check-circle'></i> Terdaftar di Database</span>");
    }
});