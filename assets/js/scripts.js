/**
 * WAREHOUSE-HR - Main Application JavaScript
 * File ini berisi seluruh logika interaktif sistem (100% Pure JS)
 */

// =========================================================================
// 1. GLOBAL CONFIGURATION & MAPPINGS
// =========================================================================
const APP_CONFIG = {
  GENDER: { 1: "Pria", 2: "Wanita" },
  TYPE: { "01": "Baju", "02": "Celana" },
  SIZE: {
    "01": "S",
    "02": "M",
    "03": "L",
    "04": "XL",
    28: "28",
    30: "30",
    32: "32",
    34: "34",
    36: "36",
  },
};

let itemCount = 0;
let scannedBarcodes = new Set();

// --- FLAG & FUNGSI RESET VALIDASI ---
let isValidated = false;

function invalidateForm() {
  isValidated = false;
  // Cari tombol proses transaksi di halaman transaksi
  const btnProses = document.getElementById("btnProses") || document.querySelector("#formTransaksi button[type='submit']");
  if (btnProses) {
    btnProses.disabled = true;
  }
}

// =========================================================================
// 2. DOM READY LISTENERS
// =========================================================================
$(document).ready(function () {
  // --- A. GLOBAL & SIDEBAR (DIPERBAIKI DENGAN LOCALSTORAGE) ---
  
  // 1. Cek memori browser saat halaman dimuat
  if (localStorage.getItem("sidebar_collapsed") === "true") {
    $(".sidebar").addClass("collapsed");
  }

  // 2. Event Toggle Sidebar + Simpan Status
  $("#sidebarToggle").on("click", function () {
    $(".sidebar").toggleClass("collapsed");
    
    // Simpan status terbaru ke localStorage
    const isCollapsed = $(".sidebar").hasClass("collapsed");
    localStorage.setItem("sidebar_collapsed", isCollapsed);
  });

  if ($(".floating-alert-container .alert, #alertContainer .alert").length > 0) {
    setTimeout(function () {
      $(".floating-alert-container .alert, #alertContainer .alert").fadeOut("slow", function () {
        $(this).remove();
      });
    }, 4000); // Hilang otomatis setelah 4 detik
  }

  $(".btn-submit").on("click", function (e) {
    e.preventDefault();
    const successModal = document.getElementById("successModal");
    if (successModal) {
      var myModal = new bootstrap.Modal(successModal);
      myModal.show();
    }
  });

  // --- B. REQUEST FORM LOGIC ---
  $(".qty-btn").click(function (e) {
    e.preventDefault();
    let inputField = $(this).siblings(".qty");
    let currentVal = parseInt(inputField.val()) || 0;
    let type = $(this).data("type");

    if (type === "plus" && currentVal < 3) {
      inputField.val(currentVal + 1);
    } else if (type === "minus" && currentVal > 0) {
      inputField.val(currentVal - 1);
    }
    calculateGrandTotal();
  });

  // --- C. TRANSAKSI MODULE INIT ---
  if (
    $("#dynamic-item-container").length > 0 &&
    $("#formTransaksi").length > 0
  ) {
    // 1. Kunci tombol proses transaksi secara default saat load
    invalidateForm();

    // Event listener Tombol Validate
    $("#btn-validate, #btnValidate")
      .off("click")
      .on("click", function () {
        validateAllItems();
      });

    // Event listener Submit Form (Proteksi Ganda)
    $("#formTransaksi").on("submit", function (e) {
      if (!isValidated) {
        e.preventDefault();
        showAlert("<strong>Gagal Submit:</strong> Harap lakukan <b>Validate Items</b> terlebih dahulu!", "danger");
      }
    });

    // Reset validasi jika ada perubahan/ketikan manual pada input barcode
    $("#dynamic-item-container").on("input", ".barcode-item-input", function () {
      invalidateForm();
    });
  }

  // Listener Auto-Scan Input Utama
  const mainScanInput = document.getElementById("mainBarcodeInput");
  if (mainScanInput) {
    mainScanInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        const val = this.value.trim();
        if (val) {
          processAutoScan(val);
          this.value = "";
          this.focus();
        }
      }
    });
  }

  // --- D. AJAX PLACEHOLDER REQUEST FORM ---
  $("#id_request").on("blur", function () {
    var idRequest = $(this).val();
    if (idRequest !== "") {
      $.ajax({
        url: "get_data_request.php",
        method: "POST",
        data: { id: idRequest },
        dataType: "json",
        success: function (response) {
          if (response.status === "success") {
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
            $("#rincian-item-list").html(rows);
          }
        },
      });
    }
  });

  // --- E. LIVE SEARCH PENDING REQUEST ---
  if ($("#searchInput").length > 0) {
    $("#searchInput").on("keyup", function () {
      let filter = $(this).val().toLowerCase();
      $(".table-responsive tbody tr").each(function () {
        if ($(this).find("td").length < 4) return;
        let textContent = $(this).text().toLowerCase();
        $(this).toggle(textContent.includes(filter));
      });
    });
  }

  // --- F. GENERATE BARCODE ---
  $("#btnRandom").click(function () {
    const timestamp = new Date().getTime().toString().substr(-6);
    const randomNum = Math.floor(100 + Math.random() * 900);
    $("#barcode_value")
      .val("HRW" + timestamp + randomNum)
      .trigger("input");
  });

  $("#barcode_value").on("input", function () {
    const val = $(this).val().trim();
    if (val.length > 2) {
      $("#btnPrint").removeAttr("disabled");
      if (typeof JsBarcode !== "undefined") {
        JsBarcode("#barcode-canvas", val, {
          format: "CODE128",
          width: 2,
          height: 60,
          displayValue: true,
          fontSize: 14,
          lineColor: "#0f172a",
        });
      }
      $("#label-info").text("Status: Siap Registrasi / Cetak");
    } else {
      $("#btnPrint").attr("disabled", "disabled");
      const svg = document.getElementById("barcode-canvas");
      if (svg) {
        while (svg.lastChild) svg.removeChild(svg.lastChild);
      }
      $("#label-info").text("");
    }
  });

  if ($("#barcode_value").val() && $("#barcode_value").val() !== "") {
    $("#barcode_value").trigger("input");
    $("#label-info").html(
      "<span class='text-success'><i class='bi bi-check-circle'></i> Terdaftar di Database</span>",
    );
  }

  // --- G. RENDER BANYAK BARCODE (UNTUK LEMBAR CETAK STIKER) ---
  if ($(".barcode-element").length > 0 && typeof JsBarcode !== "undefined") {
    $(".barcode-element").each(function () {
      const valueCode = $(this).attr("data-value");

      if (valueCode && valueCode !== "") {
        JsBarcode(this, valueCode, {
          format: "CODE128",
          width: 1.5,
          height: 40,
          displayValue: true,
          fontSize: 12,
          margin: 4,
          lineColor: "#0f172a",
        });
      }
    });

    // Buka kunci tombol Simpan ke Stok jika preview barcode ada
    $("#btnSimpanStokBatch").prop("disabled", false);
  }

  // EVENT LISTENER TOMBOL SIMPAN KE STOK BARANG (BATCH)
  $("#btnSimpanStokBatch").on("click", function () {
    let barcodesToSave = [];

    // Mengambil nilai barcode dari elemen-elemen preview lembar cetak
    $(".barcode-element").each(function () {
      let code = $(this).attr("data-value") || $(this).text().trim();
      if (code) {
        barcodesToSave.push(code);
      }
    });

    if (barcodesToSave.length === 0) {
      showAlert("Tidak ada barcode di lembar preview untuk disimpan!", "warning");
      return;
    }

    // Popup Konfirmasi Keamanan
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: "Input ke Stok Barang?",
        text: `Apakah Anda yakin ingin mendaftarkan ${barcodesToSave.length} item barcode ini secara otomatis ke stok barang?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, Simpan Stok!",
        cancelButtonText: "Batal"
      }).then((result) => {
        if (result.isConfirmed) {
          eksekusiSimpanBatchStok(barcodesToSave);
        }
      });
    } else {
      if (confirm(`Apakah Anda yakin ingin memasukkan ${barcodesToSave.length} item barcode ini ke Stok Barang?`)) {
        eksekusiSimpanBatchStok(barcodesToSave);
      }
    }
  });

  // --- H. STOK BARANG SCANNER ---
  var lastScannedBarcode = "";

  $("#modalTambahBarang").on("shown.bs.modal", function () {
    resetFormDigitParse();
    lastScannedBarcode = "";
    $("#scanBarcodeInput").focus();
  });

  function eksekusiScanBarcode(barcodeVal) {
    if (barcodeVal === lastScannedBarcode) return;

    if (barcodeVal.length === 9 && /^\d+$/.test(barcodeVal)) {
      lastScannedBarcode = barcodeVal;

      var genderCode = barcodeVal.substring(0, 1);
      var tipeCode = barcodeVal.substring(1, 3);
      var sizeCode = barcodeVal.substring(3, 5);

      var parsedGender = APP_CONFIG.GENDER[genderCode] || null;
      var parsedTipe = APP_CONFIG.TYPE[tipeCode] || null;
      var parsedSize = APP_CONFIG.SIZE[sizeCode] || null;

      $("#inputGender").val(parsedGender);
      $("#inputTipe").val(parsedTipe);
      $("#inputSize").val(parsedSize);

      if (parsedGender && parsedTipe && parsedSize) {
        $.ajax({
          url: "controllers/cek_barcode.php",
          type: "GET",
          data: { barcode: barcodeVal },
          dataType: "json",
          beforeSend: function () {
            setParsingAlert(
              "loading",
              "Menganalisis status pendaftaran kode...",
            );
          },
          success: function (response) {
            $("#btnSimpanStok").prop("disabled", false);

            if (response.exists === true || response.success === true) {
              setParsingAlert(
                "warning",
                `<strong>Item Sudah Terdaftar!</strong> Ganti dengan barcode lain !`,
              );
            } else {
              setParsingAlert(
                "success",
                `<strong>Barcode Baru!</strong> Mendaftarkan item <strong>${parsedTipe} ${parsedGender} (${parsedSize})</strong>.`,
              );
            }
            $("#btnSimpanStok").focus();
          },
          error: function () {
            $("#btnSimpanStok").prop("disabled", false);
            setParsingAlert(
              "secondary",
              "Validasi terhambat. Data lokal siap disimpan.",
            );
          },
        });
      } else {
        invalidDigitFallback(
          barcodeVal,
          "Kode komponen tidak dikenali sistem.",
        );
      }
    } else if (barcodeVal.length > 9) {
      invalidDigitFallback(
        barcodeVal,
        "Barcode harus berjumlah tepat 9 digit angka penuh.",
      );
    }
  }

  $("#scanBarcodeInput").on("input", function () {
    var barcodeVal = $(this).val().trim();
    if (barcodeVal.length === 9) {
      eksekusiScanBarcode(barcodeVal);
    } else {
      lastScannedBarcode = "";
    }
  });

  $("#scanBarcodeInput").on("keypress", function (e) {
    if (e.which === 13) {
      e.preventDefault();
      var barcodeVal = $(this).val().trim();
      eksekusiScanBarcode(barcodeVal);
    }
  });

  // --- I. RETURN MODULE AUTO-LOAD ---
  if (window.IS_AUTO_RETURN) {
    loadTransactionItems(window.AUTO_BARCODES || []);
  }

  if ($("#id_request").val() !== "") {
    setTimeout(function() {
      $("#id_sales").focus();
    }, 300);
  }

  // Tampilkan tombol saat halaman di-scroll lebih dari 150px
  $(window).scroll(function() {
    if ($(this).scrollTop() > 150) {
      $('#scrollToTopBtn').fadeIn();
    } else {
      $('#scrollToTopBtn').fadeOut();
    }
  });

  // Efek smooth scroll saat tombol diklik
  $('#scrollToTopBtn').click(function(e) {
    e.preventDefault();
    $('html, body').animate({ scrollTop: 0 }, 300);
  });

}); // END DOM READY

// =========================================================================
// 3. GLOBAL HELPER & MODULE FUNCTIONS
// =========================================================================

// --- Parser Barcode 9 Digit ---
function parseBarcode(rawCode) {
  const clean = String(rawCode).replace(/\*/g, "").trim();
  if (clean.length !== 9 || isNaN(clean)) {
    return {
      raw: clean,
      label: `(Barcode: ${clean})`,
      text: `(Barcode: ${clean})`,
      isValid: false,
    };
  }

  const gender = APP_CONFIG.GENDER[clean.substring(0, 1)] || "Unknown";
  const type = APP_CONFIG.TYPE[clean.substring(1, 3)] || "Item";
  const size = APP_CONFIG.SIZE[clean.substring(3, 5)] || "Unknown";
  const num = clean.substring(5, 9);

  return {
    raw: clean,
    gender: gender,
    type: type,
    size: size,
    number: num,
    label: `${type.toUpperCase()} ${gender.toUpperCase()} (SIZE ${size}) - #${num}`,
    text: `(${type} ${gender} - Ukuran ${size} - #${num})`,
    isValid: true,
  };
}

// --- Transaksi Module Functions ---
function processAutoScan(barcodeVal) {
  const parsed = parseBarcode(barcodeVal);

  if (!parsed.isValid) {
    showAlert(
      "Format Barcode tidak valid! Harus berisi 9 digit angka.",
      "danger",
    );
    return;
  }

  // --- PENGECEKAN MAX SCAN (Sesuai Request Ticket) ---
  if (window.transactionData && window.transactionData.isAuto) {
    const maxQty = parseInt(window.transactionData.totalQty) || 0;
    if (maxQty > 0 && scannedBarcodes.size >= maxQty) {
      showAlert(
        `<strong>Batas Maksimum Scan!</strong> Permintaan tiket ini hanya membutuhkan <b>${maxQty} Pcs</b> item.`,
        "warning"
      );
      return;
    }
  }

  if (scannedBarcodes.has(parsed.raw)) {
    showAlert(
      `Barcode <strong>${parsed.raw}</strong> sudah masuk ke dalam daftar transaksi!`,
      "warning",
    );
    return;
  }

  const emptyInput = Array.from(
    document.querySelectorAll(".barcode-item-input"),
  ).find((input) => !input.value.trim());

  if (emptyInput) {
    const cardId = emptyInput.dataset.id;
    fillCardData(cardId, parsed);
  } else {
    addItemCard(parsed);
  }

  scannedBarcodes.add(parsed.raw);
  showAlert(`Berhasil memindai <strong>${parsed.label}</strong>`, "success");

  // Reset status validasi setiap kali ada barang baru di-scan
  invalidateForm();
}

function addItemCard(prefilledData = null) {
  itemCount++;
  const id = itemCount;
  const container = document.getElementById("dynamic-item-container");
  if (!container) return;

  const cardHtml = `
        <div class="col-md-4 item-row" id="item-card-${id}">
            <div class="bg-white border rounded-3 p-3 h-100" style="box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold item-number" style="color: #556ee6; font-size: 14px;">
                        <i class="bi bi-box-seam me-2"></i>Item #${id}
                    </span>
                    <button type="button" class="btn btn-sm text-danger btn-remove-item fw-bold" 
                            style="${
                              id === 1 && !prefilledData ? "display: none;" : ""
                            } background-color: #fee2e2; border-radius: 4px; padding: 2px 8px;" 
                            onclick="removeItemCard(${id})">
                        <i class="bi bi-trash3 me-1"></i>Hapus
                    </button>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-bold text-secondary mb-2" style="font-size: 13px;">Barcode Item</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" 
                               class="form-control barcode-item-input ${
                                 prefilledData ? "bg-light" : ""
                               }" 
                               id="barcode-input-${id}"
                               data-id="${id}"
                               name="barcode_item[]" 
                               value="${prefilledData ? prefilledData.raw : ""}"
                               placeholder="Scan Barcode"
                               ${prefilledData ? "readonly" : ""}>
                    </div>
                    <div class="barcode-detail-text text-uppercase fw-bold text-primary mt-2 ps-2" id="detail-text-${id}" style="font-size: 12px; letter-spacing: 0.5px; min-height: 18px;">
                        ${prefilledData ? prefilledData.label : ""}
                    </div>
                    <input type="hidden" name="detail_item[]" id="detail-hidden-${id}" class="barcode-detail-hidden" value="${
                      prefilledData ? prefilledData.label : ""
                    }">
                </div>
            </div>
        </div>
    `;

  container.insertAdjacentHTML("beforeend", cardHtml);

  if (prefilledData) {
    scannedBarcodes.add(prefilledData.raw);
  }
  updateRemoveButtons();
  invalidateForm(); // Reset status validasi
}

function fillCardData(id, parsedData) {
  const input = document.getElementById(`barcode-input-${id}`);
  const text = document.getElementById(`detail-text-${id}`);
  const hidden = document.getElementById(`detail-hidden-${id}`);

  if (input) {
    input.value = parsedData.raw;
    input.readOnly = true;
    input.classList.add("bg-light");
  }
  if (text) text.innerText = parsedData.label;
  if (hidden) hidden.value = parsedData.label;

  scannedBarcodes.add(parsedData.raw);
  updateRemoveButtons();
  invalidateForm(); // Reset status validasi
}

function removeItemCard(id) {
  const input = document.getElementById(`barcode-input-${id}`);
  const barcodeVal = input ? input.value.trim() : "";

  if (barcodeVal) {
    scannedBarcodes.delete(barcodeVal);
  }

  const cards = document.querySelectorAll(".item-row");

  if (cards.length === 1) {
    if (input) {
      input.value = "";
      input.readOnly = false;
      input.classList.remove("bg-light");
    }
    const textElement = document.getElementById(`detail-text-${id}`);
    const hiddenElement = document.getElementById(`detail-hidden-${id}`);

    if (textElement) textElement.innerText = "";
    if (hiddenElement) hiddenElement.value = "";

    showAlert("Item #1 berhasil dikosongkan.", "info");
  } else {
    const card = document.getElementById(`item-card-${id}`);
    if (card) card.remove();
    showAlert("Item berhasil dihapus.", "info");
  }

  updateRemoveButtons();
  invalidateForm(); // Reset status validasi setelah hapus item
}

function updateRemoveButtons() {
  const cards = document.querySelectorAll(".item-row");
  cards.forEach((card) => {
    const btn = card.querySelector(".btn-remove-item");
    const input = card.querySelector(".barcode-item-input");
    const hasValue = input && input.value.trim() !== "";

    if (btn) {
      btn.style.display = cards.length > 1 || hasValue ? "block" : "none";
    }
  });
}

// --- FUNGSI VALIDASI GABUNGAN (DATABASE + KECOCOKAN TIKET) ---
async function validateAllItems() {
  invalidateForm(); // Kunci tombol proses di awal pemeriksaan
  const inputs = document.querySelectorAll(".barcode-item-input");
  let validCount = 0;
  let errors = [];
  let scannedItems = [];

  // 1. Loop setiap input & cek keberadaannya di database MySQL via Controller
  for (const input of inputs) {
    const val = input.value.trim();

    if (val) {
      try {
        const response = await fetch("controllers/validate_barcode.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ barcode: val }),
        });

        const result = await response.json();

        if (result.success) {
          const parsed = parseBarcode(val);
          const id = input.dataset.id;

          fillCardData(id, parsed);

          const text = document.getElementById(`detail-text-${id}`);
          const hidden = document.getElementById(`detail-hidden-${id}`);

          if (text) text.innerText = result.message;
          if (hidden) hidden.value = result.message;

          validCount++;
          scannedItems.push(parsed);
        } else {
          errors.push(`Barcode <b>${val}</b>: ${result.message}`);
        }
      } catch (err) {
        errors.push(`Gagal terhubung ke database untuk barcode <b>${val}</b>`);
      }
    }
  }

  // Jika tidak ada item yang diisi
  if (validCount === 0 && errors.length === 0) {
    showAlert("Belum ada barcode yang diinputkan untuk divalidasi!", "warning");
    return;
  }

  // 1. Jika ada barcode yang gagal ditemukan di database
  if (errors.length > 0) {
    showAlert(
      `<strong>Gagal Validasi Database:</strong><br>• ${errors.join("<br>• ")}`,
      "danger",
    );
    return;
  }

  // 2. Jika seluruh item terdaftar di database, lakukan pengecekan kesesuaian JUMLAH pesanan tiket
  if (window.transactionData && window.transactionData.isAuto) {
    const t = window.transactionData;
    let countTop = 0;
    let countBottom = 0;
    let ticketErrors = [];

    // Hitung berapa banyak Baju dan Celana yang telah di-scan
    scannedItems.forEach((item) => {
      if (item.type === "Baju") {
        countTop++;
      } else if (item.type === "Celana") {
        countBottom++;
      }
    });

    // Pengecekan Kuantitas Baju
    const targetQtyTop = parseInt(t.qtyTop) || 0;
    if (targetQtyTop > 0 && countTop !== targetQtyTop) {
      ticketErrors.push(
        `Jumlah Baju yang di-scan (<b>${countTop} Pcs</b>) tidak sesuai pesanan tiket (<b>${targetQtyTop} Pcs</b>)`
      );
    }

    // Pengecekan Kuantitas Celana
    const targetQtyBottoms = parseInt(t.qtyBottoms) || 0;
    if (targetQtyBottoms > 0 && countBottom !== targetQtyBottoms) {
      ticketErrors.push(
        `Jumlah Celana yang di-scan (<b>${countBottom} Pcs</b>) tidak sesuai pesanan tiket (<b>${targetQtyBottoms} Pcs</b>)`
      );
    }

    // Jika jumlah item tidak sesuai
    if (ticketErrors.length > 0) {
      showAlert(
        `<strong>Jumlah Item Tidak Sesuai Tiket:</strong><br>• ${ticketErrors.join(
          "<br>• "
        )}`,
        "danger"
      );
      return; // Tombol proses tetap terkunci
    } else {
      showAlert(
        `<strong>Validasi Sempurna!</strong> Seluruh (${validCount}) item terdaftar & jumlah pcs cocok dengan tiket.`,
        "success"
      );
    }
  } else {
    showAlert(
      `<strong>Validasi Berhasil!</strong> ${validCount} item terkonfirmasi terdaftar di database.`,
      "success"
    );
  }

  // BUKA KUNCI TOMBOL PROSES TRANSAKSI JIKA LOLOS
  isValidated = true;
  const btnProses = document.getElementById("btnProses") || document.querySelector("#formTransaksi button[type='submit']");
  if (btnProses) {
    btnProses.disabled = false;
  }
}

function resetForm() {
  scannedBarcodes.clear();
  const container = document.getElementById("dynamic-item-container");
  if (container) container.innerHTML = "";
  itemCount = 0;
  addItemCard();
}

function calculateGrandTotal() {
  let total = 0;
  $(".calc-row").each(function () {
    let price = parseInt($(this).find(".price").val()) || 0;
    let qty = parseInt($(this).find(".qty").val()) || 0;
    total += price * qty;
  });
  $("#grandTotal").text("Rp " + total.toLocaleString("id-ID"));
}

// 1. Fungsi showAlert (Dinamis JS) - Menggunakan Animasi Bootstrap Native
function showAlert(msg, type) {
  const alertContainer = document.getElementById("alertContainer") || document.querySelector(".floating-alert-container");
  
  if (alertContainer) {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type} alert-dismissible fade show mb-3`;
    alertDiv.setAttribute("role", "alert");
    alertDiv.innerHTML = `
      <div>${msg}</div>
    `;

    alertContainer.appendChild(alertDiv);

    setTimeout(function () {
      alertDiv.classList.remove("show");
      setTimeout(function () {
        alertDiv.remove();
      }, 300);
    }, 4000);
  }
}

// 2. Auto-Dismiss Alert PHP Session saat Halaman Dimuat
$(document).ready(function () {
  const existingAlerts = document.querySelectorAll(".floating-alert-container .alert, #alertContainer .alert");
  
  existingAlerts.forEach(function (alertEl) {
    setTimeout(function () {
      alertEl.classList.remove("show");
      setTimeout(function () {
        alertEl.remove();
      }, 300);
    }, 4000);
  });
});

// --- Return Module Functions ---
function openProcessPage(trxId, salesId, saName, detailInfo, barcodesArray) {
  const viewList = document.getElementById("view-return-list");
  const viewProcess = document.getElementById("view-return-process");
  if (viewList) viewList.style.display = "none";
  if (viewProcess) viewProcess.style.display = "block";

  const inputNo = document.getElementById("input_no_return");
  const inputSales = document.getElementById("input_id_sales");
  const inputNama = document.getElementById("input_nama");

  if (inputNo) inputNo.value = trxId;
  if (inputSales) inputSales.value = salesId;
  if (inputNama) inputNama.value = saName;

  const listRincian = document.getElementById("rincian-item-list");
  if (listRincian) {
    listRincian.innerHTML = `
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td class="fw-bold py-3 ps-0 text-secondary" width="15%">Info Order</td>
                <td class="py-3 text-dark">: ${detailInfo}</td>
            </tr>
        `;
  }

  // BERSHIHAN ALERT LAMA
  const alertContainer = document.getElementById("alertContainer") || document.querySelector(".floating-alert-container");
  if (alertContainer) alertContainer.innerHTML = "";

  loadTransactionItems(barcodesArray);
}

function loadTransactionItems(barcodesArray) {
  const container = document.getElementById("dynamic-item-container");
  if (!container) return;
  container.innerHTML = "";
  itemCount = 0;

  if (!barcodesArray || barcodesArray.length === 0) {
    container.innerHTML =
      '<div class="alert alert-warning">Tidak ada barcode terdeteksi pada transaksi ini.</div>';
    return;
  }

  barcodesArray.forEach((barcode) => {
    addItemRow(barcode);
  });
}

function addItemRow(barcodeVal) {
  itemCount++;
  const container = document.getElementById("dynamic-item-container");
  if (!container) return;

  const id = itemCount;
  const parsed = parseBarcode(barcodeVal);

  const rowHtml = `
        <div class="item-row bg-white border rounded-3 p-3 mb-3 shadow-sm" id="item-row-${id}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold item-number" style="color: #b91c1c; font-size: 14px;">
                    <i class="bi bi-box-seam me-2"></i>Barang #${id}
                    <small class="text-dark fw-normal ms-2">${parsed.text}</small>
                </span>
                <button type="button" class="btn btn-sm text-danger fw-bold" 
                        style="background-color: #fee2e2; border-radius: 4px; padding: 2px 8px;" 
                        onclick="removeItemRow(${id})">
                    <i class="bi bi-trash3 me-1"></i>Hapus
                </button>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Barcode Barang</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-danger"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" class="form-control bg-light fw-bold" name="barcode_return[]" value="${parsed.raw}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Kondisi / Alasan Return</label>
                    <select class="form-select" name="kondisi_return[]" required>
                        <option value="Kebesaran">Tukar: Ukuran Kebesaran</option>
                        <option value="Kekecilan">Tukar: Ukuran Kekecilan</option>
                        <option value="Cacat Produksi">Rusak: Cacat Produksi / Baju Rusak</option>
                    </select>
                </div>
            </div>
        </div>
    `;

  container.insertAdjacentHTML("beforeend", rowHtml);
}

function removeItemRow(id) {
  const row = document.getElementById(`item-row-${id}`);
  if (row) row.remove();
}

function cancelProcess() {
  if (window.IS_AUTO_RETURN) {
    window.location.href = "return.php";
  } else {
    const procView = document.getElementById("view-return-process");
    const listView = document.getElementById("view-return-list");
    if (procView) procView.style.display = "none";
    if (listView) listView.style.display = "block";
  }
}

function filterTable() {
  const searchInput = document.getElementById("searchTrx");
  if (!searchInput) return;
  const query = searchInput.value.toUpperCase();
  const rows = document.querySelectorAll("#tableTrx tbody tr");
  rows.forEach((row) => {
    row.style.display = row.innerText.toUpperCase().includes(query)
      ? ""
      : "none";
  });
}

// --- Helpers Stok Barang ---
function setParsingAlert(status, message) {
  let classes = "",
    icon = "";
  if (status === "loading") {
    classes = "p-3 border rounded bg-light text-center small text-secondary";
    icon =
      '<div class="spinner-border text-primary spinner-border-sm mb-1"></div><br>';
  } else if (status === "success") {
    classes =
      "p-3 border rounded bg-success-subtle border-success text-success text-start";
    icon = '<i class="bi bi-check-circle-fill me-1"></i>';
  } else if (status === "warning") {
    classes =
      "p-3 border rounded bg-warning-subtle border-warning text-warning-emphasis text-start";
    icon = '<i class="bi bi-stars me-1"></i>';
  } else {
    classes =
      "p-3 border rounded bg-secondary-subtle border-secondary text-secondary text-start";
    icon = '<i class="bi bi-exclamation-triangle-fill me-1"></i>';
  }
  $("#parsingAlertBox")
    .removeClass()
    .addClass(classes)
    .html(icon + " " + message);
}

function invalidDigitFallback(barcodeText, reason) {
  $("#btnSimpanStok").prop("disabled", true);
  $("#parsingAlertBox")
    .removeClass()
    .addClass(
      "p-3 border rounded bg-danger-subtle border-danger text-danger text-center",
    )
    .html(
      `<i class="bi bi-x-circle-fill d-block mb-1 fs-5"></i> <strong>Gagal Mengurai Kode!</strong><br><span class="small">${reason} (Input: <code>${barcodeText}</code>)</span>`,
    );
  $("#inputGender, #inputTipe, #inputSize").val("");
  $("#scanBarcodeInput").val("").focus();
}

function resetFormDigitParse() {
  $("#scanBarcodeInput").val("").focus();
  $("#inputGender, #inputTipe, #inputSize").val("");
  $("#btnSimpanStok").prop("disabled", true);
  $("#parsingAlertBox")
    .removeClass()
    .addClass("p-3 border rounded bg-light text-center small text-secondary")
    .html(
      '<i class="bi bi-arrow-left-right d-block mb-1 text-muted fs-5"></i><span>Silakan scan barcode untuk ekstraksi digit otomatis.</span>',
    );
}

// Helper AJAX untuk Mengirim Batch Barcode ke Backend
function eksekusiSimpanBatchStok(barcodes) {
  $.ajax({
    url: "controllers/proses_generate.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({ 
      action: "simpan_stok_batch", 
      barcodes: barcodes 
    }),
    beforeSend: function () {
      $("#btnSimpanStokBatch")
        .prop("disabled", true)
        .html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
    },
    success: function (response) {
      if (response.success) {
        showAlert(`<strong>Berhasil!</strong> ${response.message}`, "success");
        $("#btnSimpanStokBatch")
          .prop("disabled", true)
          .html('<i class="bi bi-check-circle-fill me-1"></i> Sudah Disimpan');
      } else {
        showAlert(`<strong>Gagal:</strong> ${response.message}`, "danger");
        $("#btnSimpanStokBatch")
          .prop("disabled", false)
          .html('<i class="bi bi-box-arrow-in-down me-1"></i> Simpan ke Stok Barang');
      }
    },
    error: function () {
      showAlert("Terjadi kesalahan sistem / jaringan!", "danger");
      $("#btnSimpanStokBatch")
        .prop("disabled", false)
        .html('<i class="bi bi-box-arrow-in-down me-1"></i> Simpan ke Stok Barang');
    }
  });
}

// =========================================================================
// REAL-TIME AUTO UPDATE PENDING REQUEST (ANTI-CACHE & FAST POLLING)
// =========================================================================
$(document).ready(function () {
    console.log("✅ scripts.js berhasil dimuat!");

    // Pengecekan keberadaan elemen wrapper
    if ($("#pending-tab-wrapper").length > 0) {
        console.log("✅ Elemen #pending-tab-wrapper ditemukan! Memulai polling...");

        let lastPendingCount = parseInt($("#badge-pending-count").text()) || 0;

        setInterval(function () {
            $.ajax({
                url: "controllers/pending_update.php",
                type: "GET",
                cache: false,
                data: { _t: new Date().getTime() },
                dataType: "json",
                success: function (response) {
                    console.log("🔄 [Polling Status]: Check server... Total:", response.total_pending);
                    
                    if (response && response.status === "success") {
                        let serverCount = parseInt(response.total_pending) || 0;

                        if (serverCount !== lastPendingCount) {
                            console.log("🚀 Data baru terdeteksi! Mengupdate tabel... Old:", lastPendingCount, "New:", serverCount);
                            lastPendingCount = serverCount;

                            // Update Badge
                            $("#badge-pending-count").text(serverCount);

                            // Update Partial Load
                            let cleanUrl = window.location.href.split('#')[0];
                            let refreshUrl = cleanUrl + (cleanUrl.indexOf('?') >= 0 ? '&' : '?') + '_ts=' + new Date().getTime();

                            $("#pending-tab-wrapper").load(refreshUrl + " #pending-tab-wrapper > *", function () {
                                console.log("🎉 Tabel pending berhasil diperbarui!");
                            });
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.error("❌ Polling Error:", status, error, xhr.responseText);
                }
            });
        }, 3000); // 3 detik
    } else {
        console.warn("⚠️ Elemen #pending-tab-wrapper TIDAK ditemukan di halaman ini!");
    }
});