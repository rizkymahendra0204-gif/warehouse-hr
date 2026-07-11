// assets/js/scripts.js

$(document).ready(function () {
  // Fungsi untuk menyembunyikan atau memunculkan sidebar
  $("#sidebarToggle").on("click", function () {
    $(".sidebar").toggleClass("collapsed");
  });
});

$(document).ready(function() {
    
    // Trigger modal saat tombol simpan diklik
    $('.btn-submit').on('click', function(e) {
        e.preventDefault(); // Mencegah submit langsung ke server (untuk test saja)
        
        // Tampilkan modal
        var myModal = new bootstrap.Modal(document.getElementById('successModal'));
        myModal.show();
    });

});


$(document).ready(function() {
    
    // --- 1. LOGIKA TOMBOL JUMLAH (PLUS / MINUS) ---
    $('.qty-btn').click(function(e) {
        e.preventDefault();
        
        let inputField = $(this).siblings('.qty');
        let currentVal = parseInt(inputField.val());
        let type = $(this).data('type');
        
        if (type === 'plus') {
            if (currentVal < 3) { // Maksimal 3
                inputField.val(currentVal + 1);
            }
        } else if (type === 'minus') {
            if (currentVal > 0) { // Minimal 0
                inputField.val(currentVal - 1);
            }
        }
        
        // Panggil fungsi hitung total setiap kali tombol ditekan
        calculateGrandTotal();
    });

    // --- 2. LOGIKA PERHITUNGAN GRAND TOTAL ---
    function calculateGrandTotal() {
        let total = 0;
        
        // Looping setiap baris yang memiliki class 'calc-row'
        $('.calc-row').each(function() {
            let price = parseInt($(this).find('.price').val()) || 0;
            let qty = parseInt($(this).find('.qty').val()) || 0;
            
            total += (price * qty);
        });
        
        // Tampilkan ke layar dengan format Rupiah
        $('#grandTotal').text('Rp ' + total.toLocaleString('id-ID'));
    }

    // --- 3. TRIGGER MODAL SAAT KLIK SUBMIT ---
    $('.btn-submit').click(function(e) {
        e.preventDefault(); // Mencegah form langsung me-reload halaman
        
        // Tampilkan Modal
        var myModal = new bootstrap.Modal(document.getElementById('successModal'));
        myModal.show();
    });

});
