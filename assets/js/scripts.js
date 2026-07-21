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
  $("#btn-tambah-item").click(function () {
    let maxItems = 6;
    let currentItems = $(".item-row").length;

    if (currentItems < maxItems) {
      let newRow = $(".item-row:first").clone();

      newRow.find("input").val("");
      newRow.find(".barcode-detail-text").text("").css("color", "");
      newRow.find(".barcode-detail-hidden").val("");
      newRow.find(".btn-remove-item").show();

      $("#dynamic-item-container").append(newRow);
      updateItemNumbers();
    } else {
      alert("Maksimal hanya boleh ada " + maxItems + " kolom item transaksi!");
    }
  });

  // Fungsi Hapus Baris/Kolom
  $(document).on("click", ".btn-remove-item", function () {
    $(this).closest(".item-row").remove();
    updateItemNumbers();
  });

  function updateItemNumbers() {
    $(".item-row").each(function (index) {
      $(this)
        .find(".item-number")
        .html('<i class="bi bi-box-seam me-2"></i>Item #' + (index + 1));
    });
  }

  // REVISI LOGIKA: TOMBOL VALIDATE GLOBAL (MEMERIKSA SEMUA BARIS SEKALIGUS)
  $("#btn-validate-all").click(function () {
    // Looping memeriksa setiap baris item yang aktif di layar
    $(".item-row").each(function () {
      let rowContainer = $(this);
      let barcodeInput = rowContainer.find('input[name="barcode_item[]"]');
      let barcodeValue = barcodeInput.val().trim();
      let displayText = rowContainer.find(".barcode-detail-text");
      let hiddenInput = rowContainer.find(".barcode-detail-hidden");

      // Jika ada baris yang belum diisi barcode-nya
      if (barcodeValue === "") {
        displayText.text("Barcode belum diisi!").css("color", "#b91c1c");
        return; // Lanjut ke baris berikutnya (continue)
      }

      displayText.text("Memvalidasi...").css("color", "#6b7280");

      // Jalankan AJAX untuk baris ini
      $.ajax({
        url: "cek_master_barcode.php",
        method: "POST",
        data: { barcode: barcodeValue },
        dataType: "json",
        success: function (response) {
          if (response.status == "success") {
            // Jika valid & masuk master data
            displayText.text(response.nama_barang).css("color", "#15803d");
            hiddenInput.val(response.nama_barang);
          } else {
            // Jika tidak terdaftar
            displayText.text("Barang tidak ditemukan!").css("color", "#b91c1c");
            hiddenInput.val("");
          }
        },
        error: function () {
          displayText.text("Gagal koneksi server!").css("color", "#b91c1c");
          hiddenInput.val("");
        },
      });
    });
  });

  // LOGIKA OTOMATIS SAAT SCAN BARCODE LANGSUNG
  $(document).on("change", 'input[name="barcode_item[]"]', function () {
    let currentInput = $(this);
    let barcodeValue = currentInput.val().trim();
    let rowContainer = currentInput.closest(".item-row");

    if (barcodeValue != "") {
      // Mengosongkan status eror/teks lama saat admin menembak ulang laser scanner
      rowContainer.find(".barcode-detail-text").text("");
    }
  });

  // =========================================================================
  // CONTOH LOGIKA JAVASCRIPT SAAT SCAN BARCODE DIJALANKAN (UNTUK MENGISI TEKS)
  // =========================================================================
  $(document).on(
    "change",
    'input[name="barcode_atasan[]"], input[name="barcode_bawahan[]"]',
    function () {
      let currentInput = $(this);
      let barcodeValue = currentInput.val();

      if (barcodeValue != "") {
        let namaItemHasilScan = "BAJU PRIA (M)";

        // 1. Memunculkan teks murni di bawah input kotak
        currentInput
          .closest(".col-md-6")
          .find(".barcode-detail-text")
          .text(namaItemHasilScan);
        // 2. Mengisi hidden input
        currentInput
          .closest(".col-md-6")
          .find(".barcode-detail-hidden")
          .val(namaItemHasilScan);
      }
    },
  );

  // ================= 4. AJAX PLACEHOLDER (Perbaikan Fungsi Success) =================
  $("#id_request").on("blur", function () {
    var idRequest = $(this).val();

    if (idRequest != "") {
      $.ajax({
        url: "get_data_request.php",
        method: "POST",
        data: { id: idRequest },
        dataType: "json",
        success: function (response) {
          if (response.status == "success") {
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
            $("#rincian-item-list").html(rows);
          }
        },
      });
    }
  }); // <-- SEBELUMNYA KURANG TANDA ');' DI SINI

  // ================= 5. LIVE SEARCH PENDING REQUEST (JQUERY VERSION) =================
  if ($("#searchInput").length > 0) {
    $("#searchInput").on("keyup", function () {
      // Ambil teks yang diketik lalu ubah ke huruf kecil
      let filter = $(this).val().toLowerCase();

      // Cek setiap baris (tr) di dalam tabel
      $(".table-responsive tbody tr").each(function () {
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

document.addEventListener("DOMContentLoaded", function () {
  const btnValidate = document.getElementById("btn-validate");

  if (btnValidate) {
    btnValidate.addEventListener("click", function () {
      // 1. Ambil semua input barcode berdasarkan atribut name asli HTML Anda
      const barcodeInputs = document.querySelectorAll(
        'input[name="barcode_item[]"]',
      );

      barcodeInputs.forEach((input) => {
        const barcodeValue = input.value.trim();

        // 2. Cari parent kartu item terdekat (.item-row)
        const itemRow = input.closest(".item-row");
        if (!itemRow) return;

        // 3. Targetkan elemen teks detail dan hidden input bawaan HTML Anda
        const msgContainer = itemRow.querySelector(".barcode-detail-text");
        const hiddenInput = itemRow.querySelector(".barcode-detail-hidden");

        if (!msgContainer) return;

        if (barcodeValue === "") {
          msgContainer.style.color = "#dc2626"; // Warna merah
          msgContainer.innerText = "❌ BARCODE KOSONG!";
          return;
        }

        msgContainer.style.color = "#6b7280"; // Warna abu-abu
        msgContainer.innerText = "MEMVALIDASI...";

        // 4. Kirim data ke API Backend
        fetch("controllers/validate_barcode.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ barcode: barcodeValue }),
        })
          .then((response) => response.json())
          .then((res) => {
            if (res.success) {
              msgContainer.style.color = "#16a34a"; // Warna hijau jika sukses
              msgContainer.innerText = res.message;

              // Isi hidden input agar teks detail ikut terkirim saat form di-submit
              if (hiddenInput) {
                hiddenInput.value = res.message;
              }
            } else {
              msgContainer.style.color = "#dc2626"; // Warna merah jika gagal/tidak terdaftar
              msgContainer.innerText = res.message;

              if (hiddenInput) {
                hiddenInput.value = "";
              }
            }
          })
          .catch((error) => {
            msgContainer.style.color = "#dc2626";
            msgContainer.innerText = "GAGAL KONEKSI SERVER!";
            console.error(error);
          });
      });
    });
  }
});

// ================= 7. GENERATE BARCODE =================

$(document).ready(function () {
  // Fungsi membuat string kode acak berbasis waktu & angka random
  $("#btnRandom").click(function () {
    const timestamp = new Date().getTime().toString().substr(-6);
    const randomNum = Math.floor(100 + Math.random() * 900);
    const codeResult = "HRW" + timestamp + randomNum;
    $("#barcode_value").val(codeResult).trigger("input");
  });

  // Event deteksi perubahan teks pada input kode untuk live preview
  $("#barcode_value").on("input", function () {
    const val = $(this).val().trim();
    if (val.length > 2) {
      $("#btnPrint").removeAttr("disabled");
      // Render gambar garis barcode menggunakan JsBarcode
      JsBarcode("#barcode-canvas", val, {
        format: "CODE128",
        width: 2,
        height: 60,
        displayValue: true,
        fontSize: 14,
        lineColor: "#0f172a",
      });
      $("#label-info").text("Status: Siap Registrasi / Cetak");
    } else {
      $("#btnPrint").attr("disabled", "disabled");
      // Kosongkan kanvas jika input terlalu pendek
      const svg = document.getElementById("barcode-canvas");
      while (svg.lastChild) {
        svg.removeChild(svg.lastChild);
      }
      $("#label-info").text("");
    }
  });

  // Trigger preview otomatis jika variabel PHP terisi sesudah sukses simpan
  if ($("#barcode_value").val() !== "") {
    $("#barcode_value").trigger("input");
    $("#label-info").html(
      "<span class='text-success'><i class='bi bi-check-circle'></i> Terdaftar di Database</span>",
    );
  }
});

// ================= 8. EXPORT TO Excel =================

function exportExcel() {
  // Ambil semua elemen barcode dari layar
  const barcodeElements = document.querySelectorAll(".barcode-element");
  const skus = Array.from(barcodeElements).map((el) =>
    el.getAttribute("data-value"),
  );

  if (skus.length === 0) {
    alert("Tidak ada data barcode untuk diexport!");
    return;
  }

  const excelData = [];
  const columns = 4; // Format 4 kolom ke samping sesuai gambar

  // Looping data dan potong per 4 item
  for (let i = 0; i < skus.length; i += columns) {
    const chunk = skus.slice(i, i + columns);

    // Baris 1: Teks Header (Seragam SA)
    const rowHeader = chunk.map(() => "Seragam SA");
    excelData.push(rowHeader);

    // Baris 2: Barcode Area
    // (Ditambahkan tanda bintang * di awal & akhir agar bisa dibaca oleh scanner jika pakai Font Code 39)
    const rowBarcode = chunk.map((sku) => `*${sku}*`);
    excelData.push(rowBarcode);

    // Baris 3: Teks SKU di bawah barcode
    const rowText = chunk.map((sku) => sku);
    excelData.push(rowText);

    // Baris 4: Baris kosong sebagai jarak antar stiker atas-bawah
    excelData.push([]);
  }

  // Buat Worksheet
  const worksheet = XLSX.utils.aoa_to_sheet(excelData);

  // Styling Dasar: Mengatur lebar 4 kolom agar kotak proporsional
  worksheet["!cols"] = [{ wch: 25 }, { wch: 25 }, { wch: 25 }, { wch: 25 }];

  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, "Lembar Cetak Stiker");

  // Download File
  const fileName =
    "Export_Barcode_Layout_" + new Date().toISOString().slice(0, 10) + ".xlsx";
  XLSX.writeFile(workbook, fileName);
}

$(document).ready(function () {
  $(".barcode-element").each(function () {
    const valueCode = $(this).data("value");
    JsBarcode(this, valueCode, {
      format: "CODE128",
      width: 1.3,
      height: 38,
      displayValue: true,
      fontSize: 10,
      margin: 2,
    });
  });
});

// ================= 9. ADD STOK BARANG DENGAN BARCODE =================

$(document).ready(function () {
  // Reset form tiap kali modal dibuka
  $("#modalTambahBarang").on("shown.bs.modal", function () {
    resetFormDigitParse();
  });

  // Menangkap input scanner (Enter)
  $("#scanBarcodeInput").on("keypress", function (e) {
    if (e.which == 13) {
      e.preventDefault();

      var barcodeVal = $(this).val().trim();
      if (barcodeVal === "") return;

      // Validasi awal wajib berupa angka dan tepat 9 digit
      if (barcodeVal.length === 9 && /^\d+$/.test(barcodeVal)) {
        // 1. Ekstraksi kode berdasarkan posisi substring
        var genderCode = barcodeVal.substring(0, 1); // Digit 1
        var tipeCode = barcodeVal.substring(1, 3); // Digit 2 dan 3
        var sizeCode = barcodeVal.substring(3, 5); // Digit 4 dan 5

        // 2. Kamus Pemetaan (Mapping Object) sesuai spesifikasi gudang
        var mapGender = { 1: "Pria", 2: "Wanita" };
        var mapTipe = { "01": "Baju", "02": "Celana" };
        var mapSize = {
          "01": "S",
          "02": "M",
          "03": "L",
          "04": "XL",
          28: "28",
          30: "30",
          32: "32",
          36: "36",
        };

        // 3. Terjemahkan kode angka menjadi nilai teks string
        var parsedGender = mapGender[genderCode] || null;
        var parsedTipe = mapTipe[tipeCode] || null;
        var parsedSize = mapSize[sizeCode] || null;

        // 4. Inject hasil terjemahan ke elemen dropdown select form
        $("#inputGender").val(parsedGender);
        $("#inputTipe").val(parsedTipe);
        $("#inputSize").val(parsedSize);

        // Pastikan seluruh kode sukses terpetakan dan tidak ada nilai null
        if (parsedGender && parsedTipe && parsedSize) {
          // Lakukan verifikasi database via AJAX untuk menentukan tipe penambahan stok
          $.ajax({
            url: "controllers/cek_barcode.php",
            type: "GET",
            data: { barcode: barcodeVal },
            dataType: "json",
            beforeSend: function () {
              $("#parsingAlertBox")
                .removeClass()
                .addClass(
                  "p-3 border rounded bg-light text-center small text-secondary",
                )
                .html(
                  '<div class="spinner-border text-primary spinner-border-sm mb-1" role="status"></div><br>Menganalisis status pendaftaran kode...',
                );
            },
            success: function (response) {
              $("#btnSimpanStok").prop("disabled", false);

              if (response.success) {
                // KONDISI BARANG LAMA (Sudah ada di database)
                $("#parsingAlertBox")
                  .removeClass()
                  .addClass(
                    "p-3 border rounded bg-success-subtle border-success text-success text-start",
                  )
                  .html(
                    '<i class="bi bi-check-circle-fill me-1"></i> <strong>Item Terdaftar!</strong> Kode cocok dengan sistem. Menambah jumlah stok untuk <strong>' +
                      parsedTipe +
                      " " +
                      parsedGender +
                      " (" +
                      parsedSize +
                      ")</strong>.",
                  );
              } else {
                // KONDISI BARANG BARU (Belum terdaftar di database master)
                $("#parsingAlertBox")
                  .removeClass()
                  .addClass(
                    "p-3 border rounded bg-warning-subtle border-warning text-warning-emphasis text-start",
                  )
                  .html(
                    '<i class="bi bi-stars me-1"></i> <strong>Barcode Baru Ditemukan!</strong> Struktur digit valid. Sistem akan otomatis meregistrasikan item baru <strong>' +
                      parsedTipe +
                      " " +
                      parsedGender +
                      " (" +
                      parsedSize +
                      ")</strong>.",
                  );
              }
              $("#btnSimpanStok").focus();
            },
            error: function () {
              // Fallback jika koneksi terputus, tetap izinkan simpan menggunakan parsing lokal
              $("#btnSimpanStok").prop("disabled", false);
              $("#parsingAlertBox")
                .removeClass()
                .addClass(
                  "p-3 border rounded bg-secondary-subtle border-secondary text-secondary text-start",
                )
                .html(
                  '<i class="bi bi-exclamation-triangle-fill me-1"></i> Validasi database terhambat. Data lokal siap disimpan.',
                );
            },
          });
        } else {
          invalidDigitFallback(
            barcodeVal,
            "Kode komponen tidak dikenali di sistem master mapping.",
          );
        }
      } else {
        invalidDigitFallback(
          barcodeVal,
          "Barcode harus berjumlah tepat 9 digit angka penuh.",
        );
      }
    }
  });

  function invalidDigitFallback(barcodeText, reason) {
    $("#btnSimpanStok").prop("disabled", true);
    $("#parsingAlertBox")
      .removeClass()
      .addClass(
        "p-3 border rounded bg-danger-subtle border-danger text-danger text-center",
      )
      .html(
        '<i class="bi bi-x-circle-fill d-block mb-1 fs-5"></i> <strong>Gagal Mengurai Kode!</strong><br><span class="small">' +
          reason +
          " (Input: <code>" +
          barcodeText +
          "</code>)</span>",
      );

    $("#inputGender").val("");
    $("#inputTipe").val("");
    $("#inputSize").val("");
    $("#scanBarcodeInput").val("").focus();
  }

  function resetFormDigitParse() {
    $("#scanBarcodeInput").val("").focus();
    $("#inputGender").val("");
    $("#inputTipe").val("");
    $("#inputSize").val("");
    $("#btnSimpanStok").prop("disabled", true);
    $("#parsingAlertBox")
      .removeClass()
      .addClass("p-3 border rounded bg-light text-center small text-secondary")
      .html(
        '<i class="bi bi-arrow-left-right d-block mb-1 text-muted fs-5"></i><span>Silakan scan barcode untuk ekstraksi digit otomatis.</span>',
      );
  }
});
