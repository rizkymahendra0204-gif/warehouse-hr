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
