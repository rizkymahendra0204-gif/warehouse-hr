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
// 2. HELPER MAPPING UNTUK WAREHOUSE MANAGEMENT MODAL
// =========================================================================
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
// 3. DOM READY LISTENERS
// =========================================================================
$(document).ready(function () {
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
    calculateGrandTotal();
  });

  if ($("#dynamic-item-container").length > 0 && $("#formTransaksi").length > 0) {
    invalidateForm();

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

  $(document).on(
    "change",
    "#select_gender, #gender, select[name='gender'], #select_tipe, #tipe, select[name='tipe']",
    function () {
      var $genderEl = $("#select_gender, #gender, select[name='gender']");
      var $tipeEl = $("#select_tipe, #tipe, select[name='tipe']");
      var $ukuran = $("#select_ukuran, #ukuran, select[name='ukuran']");

      var gender = $genderEl.val();
      var tipe = $tipeEl.val();

      // Jika yang diubah adalah dropdown Gender, reset nilai Tipe Pakaian
      if ($(this).is("#select_gender, #gender, select[name='gender']")) {
        $tipeEl.val('');
        tipe = ''; // Kosongkan variabel tipe agar size juga ter-reset
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
    $("#btnSimpanStokBatch").prop("disabled", false);
  }

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
});

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
                        style="${id === 1 && !prefilledData ? "display: none;" : ""} background-color: #fee2e2; border-radius: 4px; padding: 2px 8px;" 
                        onclick="removeItemCard(${id})">
                    <i class="bi bi-trash3 me-1"></i>${t("btn_cancel", "Hapus")}
                </button>
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

  if (prefilledData) {
    scannedBarcodes.add(prefilledData.raw);
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