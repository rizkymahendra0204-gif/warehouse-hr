/**
 * WAREHOUSE-HR - Main Application JavaScript
 * File ini berisi seluruh logika interaktif sistem (100% Pure JS & Multi-Language)
 */

// =========================================================================
// 0. HELPER TRANSLATION (I18N)
// =========================================================================
function t(key, defaultText, params = {}) {
  if (typeof defaultText === "object") {
    params = defaultText;
    defaultText = key;
  }
  let text =
    window.I18N && window.I18N[key] ? window.I18N[key] : defaultText || key;
  for (let paramKey in params) {
    text = text.replace(new RegExp(`{${paramKey}}`, "g"), params[paramKey]);
  }
  return text;
}

// =========================================================================
// 1. GLOBAL CONFIGURATION & MAPPINGS (SAFE DECLARATION)
// =========================================================================
var APP_CONFIG = window.APP_CONFIG || {
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
let isValidated = false;

function invalidateForm() {
  isValidated = false;
  const btnProses =
    document.getElementById("btnProses") ||
    document.querySelector("#formTransaksi button[type='submit']");
  if (btnProses) {
    btnProses.disabled = true;
  }
}

// =========================================================================
// 2. HELPER FUNCTIONS & GLOBAL ACTION HANDLERS
// =========================================================================

// Action Handler untuk Tombol Return Item
function openProcessPage(id, sales, nama, detail, barcodes) {
  if (!id) return;
  
  let url = "return.php?req=" + encodeURIComponent(id);
  if (sales) url += "&sales=" + encodeURIComponent(sales);
  if (nama) url += "&nama=" + encodeURIComponent(nama);
  if (detail) url += "&detail=" + encodeURIComponent(detail);
  
  if (Array.isArray(barcodes) && barcodes.length > 0) {
    url += "&item=" + encodeURIComponent(barcodes[0]);
  }
  
  window.location.href = url;
}

// Action Handler untuk Tombol Batal pada Form Return
function cancelProcess() {
  window.location.href = "return.php";
}

// Helper Mapping untuk Warehouse Management Modal
function setAuditData(barcode, detail, statusTx, statusBrg) {
  const elBarcode = document.getElementById("modal_barcode");
  const elDisplay = document.getElementById("modal_barcode_display");
  const elDetail = document.getElementById("modal_detail_display");
  const elStatusTx = document.getElementById("modal_status_tx");
  const elStatusBrg = document.getElementById("modal_status_brg");

  if (elBarcode) elBarcode.value = barcode;
  if (elDisplay) elDisplay.innerText = "#" + barcode;
  if (elDetail) elDetail.innerText = detail;
  if (elStatusTx) elStatusTx.value = statusTx;
  if (elStatusBrg) elStatusBrg.value = statusBrg;
}

// =========================================================================
// FUNGSI VALIDASI ADVANCED (DATABASE + KONSISTENSI GENDER + PENYESUAIAN TIKET)
// =========================================================================
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
        errors.push(
          t(
            "err_db_connect_barcode",
            "Gagal terhubung ke database untuk barcode <b>{barcode}</b>",
            { barcode: val },
          ),
        );
      }
    }
  }

  // Jika tidak ada item yang diisi
  if (validCount === 0 && errors.length === 0) {
    showAlert(
      t("alert_no_barcode_input", "Belum ada barcode yang diinputkan"),
      "warning",
    );
    return false;
  }

  // Jika ada barcode yang gagal ditemukan di database
  if (errors.length > 0) {
    showAlert(
      t(
        "alert_failed_db_val",
        "<strong>Gagal Validasi Database:</strong><br>• {errors}",
        { errors: errors.join("<br>• ") },
      ),
      "danger",
    );
    return false;
  }

  let ticketErrors = [];

  // =========================================================================
  // 🔍 LAPIS 1: CEK KONSISTENSI GENDER ANTAR ITEM (DILARANG CAMPUR PRIA & WANITA)
  // =========================================================================
  let uniqueGenders = new Set();
  scannedItems.forEach((item) => {
    let g = (item.gender || "").toString().toLowerCase().trim();
    if (g === "1" || g === "pria" || g === "male") uniqueGenders.add("Pria");
    if (g === "2" || g === "wanita" || g === "female")
      uniqueGenders.add("Wanita");
  });

  if (uniqueGenders.size > 1) {
    ticketErrors.push(
      t(
        "err_mixed_gender",
        "Kamu memasukkan pakaian <b>Pria</b> dan <b>Wanita</b> sekaligus dalam satu transaksi.",
      ),
    );
  }

  // =========================================================================
  // 🔍 LAPIS 2: PENGECEKAN KESESUAIAN DENGAN TIKET REQUEST
  // =========================================================================
  if (window.transactionData && window.transactionData.isAuto) {
    const tData = window.transactionData;
    let countTop = 0;
    let countBottom = 0;

    // Normalisasi Gender Tiket (male / female / pria / wanita)
    const rawTargetGender = (
      tData.gender ||
      tData.genderSA ||
      tData.gender_sa ||
      ""
    )
      .toString()
      .toLowerCase()
      .trim();
    let normalizedTargetGender = "";
    let targetGenderLabel = "";

    if (
      rawTargetGender === "male" ||
      rawTargetGender === "pria" ||
      rawTargetGender === "1"
    ) {
      normalizedTargetGender = "male";
      targetGenderLabel = "Pria (Male)";
    } else if (
      rawTargetGender === "female" ||
      rawTargetGender === "wanita" ||
      rawTargetGender === "2"
    ) {
      normalizedTargetGender = "female";
      targetGenderLabel = "Wanita (Female)";
    }

    // Loop pengecekan Gender dan Kuantitas Item
    scannedItems.forEach((item, index) => {
      const rawScannedGender = (item.gender || "")
        .toString()
        .toLowerCase()
        .trim();
      let normalizedScannedGender = "";

      if (
        rawScannedGender === "male" ||
        rawScannedGender === "pria" ||
        rawScannedGender === "1"
      ) {
        normalizedScannedGender = "male";
      } else if (
        rawScannedGender === "female" ||
        rawScannedGender === "wanita" ||
        rawScannedGender === "2"
      ) {
        normalizedScannedGender = "female";
      }

      // Cocokkan Gender Barang dengan Tiket
      if (normalizedTargetGender && normalizedScannedGender) {
        if (normalizedTargetGender !== normalizedScannedGender) {
          ticketErrors.push(
            t(
              "err_gender_mismatch",
              "Item #{num} (<b>{type}</b>): Gender barang (<b>{gender}</b>) tidak sesuai pesanan tiket (<b>{target}</b>)",
              {
                num: index + 1,
                type: item.type,
                gender: item.gender,
                target: targetGenderLabel,
              },
            ),
          );
        }
      }

      if (item.type === "Baju") countTop++;
      if (item.type === "Celana") countBottom++;
    });

    // Pengecekan Kuantitas Baju
    const targetQtyTop = parseInt(tData.qtyTop) || 0;
    if (targetQtyTop > 0 && countTop !== targetQtyTop) {
      ticketErrors.push(
        t(
          "err_qty_top_mismatch",
          "Jumlah Baju yang di-scan (<b>{scanned} Pcs</b>) tidak sesuai pesanan tiket (<b>{target} Pcs</b>)",
          { scanned: countTop, target: targetQtyTop },
        ),
      );
    }

    // Pengecekan Kuantitas Celana
    const targetQtyBottoms = parseInt(tData.qtyBottoms) || 0;
    if (targetQtyBottoms > 0 && countBottom !== targetQtyBottoms) {
      ticketErrors.push(
        t(
          "err_qty_bottom_mismatch",
          "Jumlah Celana yang di-scan (<b>{scanned} Pcs</b>) tidak sesuai pesanan tiket (<b>{target} Pcs</b>)",
          { scanned: countBottom, target: targetQtyBottoms },
        ),
      );
    }
  }

  // Jika terdapat kesalahan (Gender Campur / Beda dengan Tiket / Qty Salah)
  if (ticketErrors.length > 0) {
    showAlert(
      t(
        "alert_failed_trx_val",
        "<strong>Gagal Validasi Transaksi:</strong><br>• {errors}",
        { errors: ticketErrors.join("<br>• ") },
      ),
      "danger",
    );
    return false; // Tombol "Proses Transaksi" TETAP TERKUNCI
  }

  showAlert(
    t(
      "alert_val_success",
      "<strong>Validasi Berhasil!</strong> Seluruh ({count}) item terdaftar & cocok dengan tiket.",
      { count: validCount },
    ),
    "success",
  );

  // BUKA KUNCI TOMBOL PROSES TRANSAKSI JIKA LOLOS
  isValidated = true;
  const btnProses =
    document.getElementById("btnProses") ||
    document.querySelector("#formTransaksi button[type='submit']");
  if (btnProses) {
    btnProses.disabled = false;
  }
  return true;
}

// =========================================================================
// 3. DOM READY LISTENERS
// =========================================================================
$(document).ready(function () {
  console.log("✅ scripts.js berhasil dimuat!");

  if (localStorage.getItem("sidebar_collapsed") === "true") {
    $(".sidebar").addClass("collapsed");
  }

  $("#sidebarToggle").on("click", function () {
    $(".sidebar").toggleClass("collapsed");
    const isCollapsed = $(".sidebar").hasClass("collapsed");
    localStorage.setItem("sidebar_collapsed", isCollapsed);
  });

  if ($(".floating-alert-container .alert, #alertContainer .alert").length > 0) {
    setTimeout(function () {
      $(".floating-alert-container .alert, #alertContainer .alert").fadeOut("slow", function () {
        $(this).remove();
      });
    }, 4000);
  }

  $(".btn-submit").on("click", function (e) {
    e.preventDefault();
    const successModal = document.getElementById("successModal");
    if (successModal) {
      var myModal = new bootstrap.Modal(successModal);
      myModal.show();
    }
  });

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
    if (typeof calculateGrandTotal === "function") {
      calculateGrandTotal();
    }
  });

  if ($("#dynamic-item-container").length > 0) {
    invalidateForm();

    // Jika pada halaman transaksi, buat minimal 1 item card
    if ($("#dynamic-item-container .item-row").length === 0 && $("#formTransaksi").length > 0) {
      if (window.transactionData && window.transactionData.isAuto && window.transactionData.totalQty > 0) {
        for (let i = 0; i < window.transactionData.totalQty; i++) {
          addItemCard();
        }
      } else {
        addItemCard();
      }
    }

    // Jika pada VIEW 2 return.php (Auto Barcodes)
    if (window.IS_AUTO_RETURN && Array.isArray(window.AUTO_BARCODES) && window.AUTO_BARCODES.length > 0) {
      const container = $("#dynamic-item-container");
      container.empty();
      itemCount = 0;
      
      window.AUTO_BARCODES.forEach(function (code) {
        addItemRow(code);
      });
    }

    $("#btn-validate, #btnValidate")
      .off("click")
      .on("click", function () {
        validateAllItems();
      });

    $("#formTransaksi").on("submit", function (e) {
      if (!isValidated) {
        e.preventDefault();
        showAlert(
          t(
            "alert_failed_submit",
            "<strong>Gagal Submit:</strong> Harap lakukan <b>Validate Items</b> terlebih dahulu!"
          ),
          "danger"
        );
      }
    });

    $("#dynamic-item-container").on("input", ".barcode-item-input", function () {
      invalidateForm();
    });
  }

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

  if ($("#searchInput, #searchTrx").length > 0) {
    $("#searchInput, #searchTrx").on("keyup", function () {
      let filter = $(this).val().toLowerCase();
      $(".table-responsive tbody tr").each(function () {
        if ($(this).find("td").length < 4) return;
        let textContent = $(this).text().toLowerCase();
        $(this).toggle(textContent.includes(filter));
      });
    });
  }

  $(document).on(
    "change",
    "#select_gender, #gender, select[name='gender'], #select_tipe, #tipe, select[name='tipe']",
    function () {
      var $genderEl = $("#select_gender, #gender, select[name='gender']");
      var $tipeEl = $("#select_tipe, #tipe, select[name='tipe']");
      var $ukuran = $("#select_ukuran, #ukuran, select[name='ukuran']");

      var gender = $genderEl.val();
      var tipe = $tipeEl.val();

      if ($(this).is("#select_gender, #gender, select[name='gender']")) {
        $tipeEl.val('');
        tipe = '';
      }

      if (!$ukuran.length) return;

      $ukuran.empty().append('<option value="">-- Select Size --</option>');

      var isPria =
        gender === "1" ||
        String(gender).toLowerCase() === "pria" ||
        String(gender).toLowerCase() === "male";
      var isCelana = tipe === "02" || String(tipe).toLowerCase() === "celana";

      if (isPria && isCelana) {
        var sizesCelanaPria = [
          { value: "30", label: "30" },
          { value: "32", label: "32" },
          { value: "34", label: "34" },
          { value: "36", label: "36" },
        ];
        $.each(sizesCelanaPria, function (i, item) {
          $ukuran.append(new Option(item.label, item.value));
        });
      } else if (tipe !== "" && tipe !== null) {
        var sizesAlfabet = [
          { value: "01", label: "S" },
          { value: "02", label: "M" },
          { value: "03", label: "L" },
          { value: "04", label: "XL" },
        ];
        $.each(sizesAlfabet, function (i, item) {
          $ukuran.append(new Option(item.label, item.value));
        });
      }
    }
  );

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
  }

  if ($("#btnSimpanStokBatch").length > 0) {
    $("#btnSimpanStokBatch").prop("disabled", false);
  }

  $(document).on("click", "#btnSimpanStokBatch", function (e) {
    e.preventDefault();

    var barcodes = [];
    $(".barcode-element").each(function () {
      var code = $(this).attr("data-value") || $(this).data("value");
      if (code) {
        barcodes.push(String(code).trim());
      }
    });

    if (barcodes.length === 0) {
      $("[data-barcode]").each(function () {
        var code = $(this).data("barcode");
        if (code) barcodes.push(String(code).trim());
      });
    }

    if (barcodes.length === 0) {
      alert("Tidak ada data barcode yang ditemukan pada preview!");
      return;
    }

    if (!confirm("Apakah Anda yakin ingin menyimpan " + barcodes.length + " barcode ini ke Stok Barang?")) {
      return;
    }

    var $btn = $(this);
    var originalText = $btn.html();
    $btn.prop("disabled", true).html('<i class="bi bi-hourglass-split me-1"></i> Menyimpan...');

    $.ajax({
      url: window.location.href,
      type: "POST",
      contentType: "application/json; charset=utf-8",
      data: JSON.stringify({
        action: "simpan_stok_batch",
        barcodes: barcodes
      }),
      dataType: "json",
      success: function (res) {
        if (res.success) {
          alert(res.message);
          window.location.reload();
        } else {
          alert("Gagal menyimpan: " + res.message);
          $btn.prop("disabled", false).html(originalText);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", xhr.responseText);
        alert("Terjadi kesalahan server saat menyimpan stok.");
        $btn.prop("disabled", false).html(originalText);
      }
    });
  });

  $(".content-area").scroll(function () {
    if ($(this).scrollTop() > 150) {
      $("#scrollToTopBtn").fadeIn();
    } else {
      $("#scrollToTopBtn").fadeOut();
    }
  });

  $("#scrollToTopBtn").click(function (e) {
    e.preventDefault();
    $(".content-area").animate({ scrollTop: 0 }, 300);
  });

  // =========================================================================
  // REAL-TIME AUTO UPDATE PENDING REQUEST (ANTI-CACHE & FAST POLLING)
  // =========================================================================
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
          if (response && response.status === "success") {
            let serverCount = parseInt(response.total_pending) || 0;

            if (serverCount !== lastPendingCount) {
              console.log(
                "🚀 Data baru terdeteksi! Mengupdate tabel... Old:",
                lastPendingCount,
                "New:",
                serverCount
              );
              lastPendingCount = serverCount;

              $("#badge-pending-count").text(serverCount);

              let cleanUrl = window.location.href.split("#")[0];
              let refreshUrl =
                cleanUrl +
                (cleanUrl.indexOf("?") >= 0 ? "&" : "?") +
                "_ts=" +
                new Date().getTime();

              $("#pending-tab-wrapper").load(
                refreshUrl + " #pending-tab-wrapper > *",
                function () {
                  console.log("🎉 Tabel pending berhasil diperbarui!");
                }
              );
            }
          }
        },
        error: function (xhr, status, error) {
          console.error("❌ Polling Error:", status, error, xhr.responseText);
        },
      });
    }, 3000);
  }

  // =========================================================================
  // PERINGATAN UNSAVED CHANGES
  // =========================================================================
  window.onbeforeunload = function () {
    var $inputSales = $("#input_id_sales");
    if ($inputSales.length && $inputSales.val().trim() !== "") {
      return t(
        "alert_unsaved_changes",
        "Perubahan belum disimpan, yakin ingin meninggalkan halaman?"
      );
    }
  };

  $("form").on("submit", function () {
    window.onbeforeunload = null;
  });

}); // END DOM READY

// =========================================================================
// 4. BARCODE & TRANSACTION DYNAMIC FUNCTIONS
// =========================================================================
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

function processAutoScan(barcodeVal) {
  const parsed = parseBarcode(barcodeVal);

  if (!parsed.isValid) {
    showAlert(
      t("alert_invalid_barcode", "Format Barcode tidak valid! Harus berisi 9 digit angka."),
      "danger"
    );
    return;
  }

  // PENGECEKAN MAX SCAN (Sesuai Tiket Transaksi)
  if (window.transactionData && window.transactionData.isAuto) {
    const maxQty = parseInt(window.transactionData.totalQty) || 0;
    if (maxQty > 0 && scannedBarcodes.size >= maxQty) {
      showAlert(
        t(
          "alert_max_scan",
          "<strong>Batas Maksimum Scan!</strong> Permintaan tiket ini hanya membutuhkan <b>{max} Pcs</b> item.",
          { max: maxQty }
        ),
        "warning"
      );
      return;
    }
  }

  if (scannedBarcodes.has(parsed.raw)) {
    showAlert(
      t(
        "alert_already_scanned",
        "Barcode <strong>{barcode}</strong> sudah masuk ke dalam daftar transaksi!",
        { barcode: parsed.raw }
      ),
      "warning"
    );
    return;
  }

  const emptyInput = Array.from(
    document.querySelectorAll(".barcode-item-input")
  ).find((input) => !input.value.trim());

  if (emptyInput) {
    const cardId = emptyInput.dataset.id;
    fillCardData(cardId, parsed);
  } else {
    addItemCard(parsed);
  }

  scannedBarcodes.add(parsed.raw);
  showAlert(
    t("alert_scan_success", "Berhasil memindai <strong>{label}</strong>", {
      label: parsed.label,
    }),
    "success"
  );

  invalidateForm();
}

// Fungsi Generasi Kartu Item Transaksi Utama
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
            </div>
            <div class="mb-2">
                <label class="form-label fw-bold text-secondary mb-2" style="font-size: 13px;">Barcode Item</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary"><i class="bi bi-upc-scan"></i></span>
                    <input type="text" 
                           class="form-control barcode-item-input ${prefilledData ? "bg-light" : ""}" 
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
                <input type="hidden" name="detail_item[]" id="detail-hidden-${id}" class="barcode-detail-hidden" value="${prefilledData ? prefilledData.label : ""}">
            </div>
        </div>
    </div>
  `;

  container.insertAdjacentHTML("beforeend", cardHtml);

  if (prefilledData && prefilledData.raw) {
    scannedBarcodes.add(prefilledData.raw);
  }
  updateRemoveButtons();
  invalidateForm();
}

// Fungsi Khusus Generasi Baris Item Return
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

  if (parsed && parsed.isValid) {
    scannedBarcodes.add(parsed.raw);
  }
  updateRemoveButtons();
  invalidateForm();
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
  invalidateForm();
}

function reindexItemCards() {
  const cards = document.querySelectorAll("#dynamic-item-container .item-row");

  cards.forEach((card, index) => {
    const newId = index + 1;
    card.id = `item-card-${newId}`;

    const numberEl = card.querySelector(".item-number");
    if (numberEl) {
      numberEl.innerHTML = `<i class="bi bi-box-seam me-2"></i>Item #${newId}`;
    }

    const removeBtn = card.querySelector(".btn-remove-item");
    if (removeBtn) {
      removeBtn.setAttribute("onclick", `removeItemCard(${newId})`);
    }

    const inputEl = card.querySelector(".barcode-item-input");
    if (inputEl) {
      inputEl.id = `barcode-input-${newId}`;
      inputEl.dataset.id = newId;
    }

    const detailTextEl = card.querySelector(".barcode-detail-text");
    if (detailTextEl) {
      detailTextEl.id = `detail-text-${newId}`;
    }

    const detailHiddenEl = card.querySelector(".barcode-detail-hidden");
    if (detailHiddenEl) {
      detailHiddenEl.id = `detail-hidden-${newId}`;
    }
  });

  itemCount = cards.length;
}

function removeItemCard(id) {
  const input = document.getElementById(`barcode-input-${id}`);
  const barcodeVal = input ? input.value.trim() : "";

  if (barcodeVal) {
    scannedBarcodes.delete(barcodeVal);
  }

  const cards = document.querySelectorAll("#dynamic-item-container .item-row");

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

    showAlert(t("alert_item1_cleared", "Item #1 berhasil dikosongkan."), "info");
  } else {
    const card = document.getElementById(`item-card-${id}`);
    if (card) {
      card.remove();
    }
    showAlert(t("alert_item_deleted", "Item berhasil dihapus."), "info");
  }

  reindexItemCards();
  updateRemoveButtons();
  invalidateForm();
}

function updateRemoveButtons() {
  const cards = document.querySelectorAll("#dynamic-item-container .item-row");
  cards.forEach((card) => {
    const btn = card.querySelector(".btn-remove-item");
    const input = card.querySelector(".barcode-item-input");
    const hasValue = input && input.value.trim() !== "";

    if (btn) {
      btn.style.display = cards.length > 1 || hasValue ? "block" : "none";
    }
  });
}

function showAlert(msg, type) {
  const alertContainer =
    document.getElementById("alertContainer") ||
    document.querySelector(".floating-alert-container");

  if (alertContainer) {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type} alert-dismissible fade show mb-3`;
    alertDiv.setAttribute("role", "alert");
    alertDiv.innerHTML = `<div>${msg}</div>`;

    alertContainer.appendChild(alertDiv);

    setTimeout(function () {
      alertDiv.classList.remove("show");
      setTimeout(function () {
        alertDiv.remove();
      }, 300);
    }, 4000);
  }
}