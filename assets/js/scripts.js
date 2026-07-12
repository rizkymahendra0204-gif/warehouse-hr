$(document).ready(function () {
  // Fungsi untuk menyembunyikan atau memunculkan sidebar
  $("#sidebarToggle").on("click", function () {
    $(".sidebar").toggleClass("collapsed");
  });
});

$(document).ready(function () {
  // Trigger modal saat tombol simpan diklik
  $(".btn-submit").on("click", function (e) {
    e.preventDefault(); // Mencegah submit langsung ke server (untuk test saja)

    // Tampilkan modal
    var myModal = new bootstrap.Modal(document.getElementById("successModal"));
    myModal.show();
  });
});

$(document).ready(function () {
  // --- 1. LOGIKA TOMBOL JUMLAH (PLUS / MINUS) ---
  $(".qty-btn").click(function (e) {
    e.preventDefault();

    let inputField = $(this).siblings(".qty");
    let currentVal = parseInt(inputField.val());
    let type = $(this).data("type");

    if (type === "plus") {
      if (currentVal < 3) {
        // Maksimal 3
        inputField.val(currentVal + 1);
      }
    } else if (type === "minus") {
      if (currentVal > 0) {
        // Minimal 0
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
    $(".calc-row").each(function () {
      let price = parseInt($(this).find(".price").val()) || 0;
      let qty = parseInt($(this).find(".qty").val()) || 0;

      total += price * qty;
    });

    // Tampilkan ke layar dengan format Rupiah
    $("#grandTotal").text("Rp " + total.toLocaleString("id-ID"));
  }

  // --- 3. TRIGGER MODAL SAAT KLIK SUBMIT ---
  $(".btn-submit").click(function (e) {
    e.preventDefault(); // Mencegah form langsung me-reload halaman

    // Tampilkan Modal
    var myModal = new bootstrap.Modal(document.getElementById("successModal"));
    myModal.show();
  });
});

// --- Transaksi ---

$(document).ready(function () {
  // Fungsi Tambah Baris
  $("#btn-tambah-item").click(function () {
    // Menggandakan (clone) baris item pertama
    let newRow = $(".item-row:first").clone();

    // Mengosongkan nilai input di baris yang baru di-clone
    newRow.find("input").val("");

    // Menampilkan tombol Hapus di baris baru
    newRow.find(".btn-remove-item").show();

    // Memasukkan baris baru ke dalam container
    $("#dynamic-item-container").append(newRow);

    // Memperbarui penomoran (Item #1, Item #2, dst)
    updateItemNumbers();
  });

  // Fungsi Hapus Baris (Event Delegation untuk elemen dinamis)
  $(document).on("click", ".btn-remove-item", function () {
    $(this).closest(".item-row").remove();
    updateItemNumbers();
  });

  // Fungsi Memperbarui Nomor Item
  function updateItemNumbers() {
    $(".item-row").each(function (index) {
      $(this)
        .find(".item-number")
        .html('<i class="bi bi-box-seam me-2"></i>Item #' + (index + 1));
    });
  }
});
