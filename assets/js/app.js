let daftarMenu = [];
let keranjang = [];
let metodeTerpilih = "Tunai";

const formatRupiah = (angka) => {
  return "Rp " + Math.round(Number(angka)).toLocaleString("id-ID");
};

// Ikon otomatis berdasarkan kata kunci nama menu
function getMenuIcon(nama) {
  const n = nama.toLowerCase();
  if (n.includes("kopi") || n.includes("latte")) return "☕";
  if (n.includes("matcha") || n.includes("teh")) return "🍵";
  if (n.includes("nasi") || n.includes("ayam")) return "🍛";
  if (n.includes("mie")) return "🍜";
  if (n.includes("kentang")) return "🍟";
  if (n.includes("air")) return "💧";
  return "🍽️";
}

// 1. Ambil Data Menu dari API MySQL
async function muatMenu() {
  try {
    const response = await fetch("api/get_menu.php");
    const hasil = await response.json();

    if (hasil.status === "success") {
      daftarMenu = hasil.data;
      renderMenu(daftarMenu);
    } else {
      document.getElementById("menu-grid").innerHTML = `<p class="col-span-full text-center text-red-600 py-6">${hasil.pesan}</p>`;
    }
  } catch (error) {
    document.getElementById("menu-grid").innerHTML = `<p class="col-span-full text-center text-red-600 py-6">Gagal terhubung ke server database.</p>`;
  }
}

// 2. Render Katalog Menu (Dengan Kontrol Qty Langsung di Kartu Menu)
function renderMenu(data) {
  const grid = document.getElementById("menu-grid");

  if (data.length === 0) {
    grid.innerHTML = `<div class="col-span-full text-center py-8 text-stone-400 text-sm">Menu tidak ditemukan.</div>`;
    return;
  }

  grid.innerHTML = data.map((menu) => {
    const isHabis = menu.stok <= 0;
    const itemDiKeranjang = keranjang.find((k) => k.id_menu === menu.id_menu);
    const qtySaatIni = itemDiKeranjang ? itemDiKeranjang.qty : 0;

    return `
      <div class="rounded-2xl border ${isHabis ? "border-red-200 bg-red-50/40 opacity-75" : "border-stone-200/90 bg-cream/50 hover:bg-white hover:border-terracotta/50 hover:shadow-md"} p-4 transition-all flex flex-col justify-between">
        <div>
          <div class="flex items-start justify-between gap-2">
            <div class="w-11 h-11 rounded-2xl ${isHabis ? "bg-red-100" : "bg-latte"} flex items-center justify-center text-2xl shadow-inner">
              ${getMenuIcon(menu.nama_menu)}
            </div>
            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full ${
              isHabis ? "bg-red-100 text-red-700" : "bg-sageLight text-sage"
            }">
              ${isHabis ? "Stok Habis" : `Stok: ${menu.stok}`}
            </span>
          </div>
          <h4 class="font-bold text-sm text-espresso mt-3 leading-snug">${menu.nama_menu}</h4>
        </div>

        <div class="mt-4 pt-3 border-t border-stone-200/70 flex items-center justify-between gap-2">
          <span class="font-extrabold text-sm text-espresso">${formatRupiah(menu.harga)}</span>

          ${
            isHabis
              ? `<button disabled class="px-3 py-1.5 rounded-xl text-xs font-bold bg-stone-200 text-stone-400 cursor-not-allowed">Habis</button>`
              : qtySaatIni > 0
              ? `
                <div class="flex items-center gap-1.5 bg-latte p-1 rounded-xl border border-stone-200">
                  <button type="button" onclick="ubahQtyByMenuId(${menu.id_menu}, -1)" class="w-7 h-7 rounded-lg bg-white text-espresso hover:bg-stone-100 font-extrabold text-xs shadow-sm">-</button>
                  <span class="w-6 text-center font-extrabold text-xs text-espresso">${qtySaatIni}</span>
                  <button type="button" onclick="ubahQtyByMenuId(${menu.id_menu}, 1)" class="w-7 h-7 rounded-lg bg-espresso text-white hover:bg-terracotta font-extrabold text-xs shadow-sm">+</button>
                </div>
              `
              : `
                <button type="button" onclick="tambahKeKeranjang(${menu.id_menu})" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-espresso hover:bg-terracotta text-white shadow-sm transition active:scale-95 flex items-center gap-1.5">
                  <i class="fa-solid fa-plus"></i>
                  <span>Tambah</span>
                </button>
              `
          }
        </div>
      </div>
    `;
  }).join("");
}

// 3. Pencarian Nama Menu Real-Time
document.getElementById("menu-search").addEventListener("input", (e) => {
  const keyword = e.target.value.toLowerCase().trim();
  const filtered = daftarMenu.filter((m) => m.nama_menu.toLowerCase().includes(keyword));
  renderMenu(filtered);
});

// 4. Kelola Keranjang & Qty
function tambahKeKeranjang(idMenu) {
  const menu = daftarMenu.find((m) => m.id_menu === idMenu);
  if (!menu) return;

  if (menu.stok <= 0) {
    tampilkanPopUpGagal(`Stok "${menu.nama_menu}" sedang habis! Silakan pilih menu lain.`);
    return;
  }

  const itemAda = keranjang.find((item) => item.id_menu === idMenu);
  if (itemAda) {
    if (itemAda.qty + 1 > menu.stok) {
      tampilkanPopUpGagal(`Stok "${menu.nama_menu}" hanya tersisa ${menu.stok} porsi.`);
      return;
    }
    itemAda.qty += 1;
  } else {
    keranjang.push({
      id_menu: menu.id_menu,
      nama_menu: menu.nama_menu,
      harga: menu.harga,
      stok_maks: menu.stok,
      qty: 1,
    });
  }

  sinkronkanTampilan();
}

window.ubahQtyByMenuId = (idMenu, delta) => {
  const index = keranjang.findIndex((item) => item.id_menu === idMenu);
  if (index === -1) return;

  const item = keranjang[index];
  const qtyBaru = item.qty + delta;

  if (qtyBaru <= 0) {
    keranjang.splice(index, 1);
  } else if (qtyBaru > item.stok_maks) {
    tampilkanPopUpGagal(`Maksimal stok "${item.nama_menu}" hanya ${item.stok_maks}.`);
    return;
  } else {
    item.qty = qtyBaru;
  }

  sinkronkanTampilan();
};

function sinkronkanTampilan() {
  const keyword = document.getElementById("menu-search").value.toLowerCase().trim();
  const filtered = daftarMenu.filter((m) => m.nama_menu.toLowerCase().includes(keyword));
  renderMenu(filtered);
  renderKeranjang();
}

// 5. Render Keranjang Kanan
function renderKeranjang() {
  const container = document.getElementById("cart-items-container");

  if (keranjang.length === 0) {
    container.innerHTML = `
      <div class="text-center py-8 px-4 border-2 border-dashed border-stone-200 rounded-2xl text-stone-400">
        <i class="fa-solid fa-mug-saucer text-2xl mb-2"></i>
        <p class="text-xs font-medium">Belum ada pesanan dipilih.<br/>Klik <b>Tambah</b> pada menu di sebelah kiri.</p>
      </div>
    `;
    document.getElementById("label-total").textContent = "Rp 0";
    hitungKembalian();
    return;
  }

  let total = 0;
  container.innerHTML = keranjang.map((item) => {
    const subtotal = item.harga * item.qty;
    total += subtotal;

    return `
      <div class="flex items-center justify-between gap-2 p-3 rounded-2xl bg-cream border border-stone-200/80">
        <div class="min-w-0 flex-1">
          <div class="font-bold text-xs text-espresso truncate">${item.nama_menu}</div>
          <div class="text-[11px] text-stone-500 mt-0.5">${formatRupiah(item.harga)} / item</div>
        </div>

        <div class="flex items-center gap-1.5">
          <button type="button" onclick="ubahQtyByMenuId(${item.id_menu}, -1)" class="w-7 h-7 rounded-lg bg-white border border-stone-200 text-espresso hover:bg-stone-100 font-bold text-xs">-</button>
          <span class="w-5 text-center font-extrabold text-xs text-espresso">${item.qty}</span>
          <button type="button" onclick="ubahQtyByMenuId(${item.id_menu}, 1)" class="w-7 h-7 rounded-lg bg-espresso text-white hover:bg-terracotta font-bold text-xs">+</button>
        </div>

        <div class="text-right min-w-[75px]">
          <div class="font-extrabold text-xs text-espresso">${formatRupiah(subtotal)}</div>
        </div>
      </div>
    `;
  }).join("");

  document.getElementById("label-total").textContent = formatRupiah(total);
  hitungKembalian();
}

document.getElementById("btn-batal").addEventListener("click", () => {
  keranjang = [];
  document.getElementById("input-uang").value = "";
  sinkronkanTampilan();
});

// 6. Metode Pembayaran & Kalkulasi Kembalian
document.querySelectorAll(".pay-method-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    metodeTerpilih = btn.dataset.method;

    document.querySelectorAll(".pay-method-btn").forEach((b) => {
      const aktif = b.dataset.method === metodeTerpilih;
      b.className = aktif
        ? "pay-method-btn flex items-center justify-center gap-2 p-3 rounded-xl border-2 border-terracotta bg-orange-50/70 text-espresso font-bold text-xs transition"
        : "pay-method-btn flex items-center justify-center gap-2 p-3 rounded-xl border border-stone-200 bg-white hover:bg-stone-50 text-stone-600 font-semibold text-xs transition";
    });

    document.getElementById("box-tunai").classList.toggle("hidden", metodeTerpilih !== "Tunai");
    document.getElementById("box-nontunai").classList.toggle("hidden", metodeTerpilih !== "Non-Tunai");
  });
});

const inputUang = document.getElementById("input-uang");
inputUang.addEventListener("input", hitungKembalian);

function hitungTotalTagihan() {
  return keranjang.reduce((sum, item) => sum + item.harga * item.qty, 0);
}

window.setUangPas = () => {
  const total = hitungTotalTagihan();
  if (total > 0) {
    inputUang.value = total;
    hitungKembalian();
  }
};

window.setNominalCepat = (nominal) => {
  inputUang.value = nominal;
  hitungKembalian();
};

function hitungKembalian() {
  const total = hitungTotalTagihan();
  const uang = Number(inputUang.value) || 0;
  const kembali = uang - total;
  const label = document.getElementById("label-kembalian");

  if (uang === 0) {
    label.textContent = "Rp 0";
    label.className = "font-extrabold text-base text-stone-500";
  } else if (kembali < 0) {
    label.textContent = "Kurang " + formatRupiah(Math.abs(kembali));
    label.className = "font-extrabold text-base text-red-600";
  } else {
    label.textContent = formatRupiah(kembali);
    label.className = "font-extrabold text-base text-sage";
  }
}

// 7. Proses Pembayaran ke API PHP & Tampilkan Pop-Up Struk
document.getElementById("btn-bayar").addEventListener("click", async () => {
  if (keranjang.length === 0) {
    tampilkanPopUpGagal("Keranjang pesanan masih kosong! Silakan pilih menu terlebih dahulu.");
    return;
  }

  const total = hitungTotalTagihan();
  const uangDiterima = metodeTerpilih === "Tunai" ? Number(inputUang.value) || 0 : total;

  if (metodeTerpilih === "Tunai" && uangDiterima < total) {
    tampilkanPopUpGagal(`Uang tunai kurang! Total tagihan adalah ${formatRupiah(total)}.`);
    return;
  }

  try {
    const response = await fetch("api/proses_transaksi.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        items: keranjang,
        metode_pembayaran: metodeTerpilih,
        uang_diterima: uangDiterima,
      }),
    });

    const hasil = await response.json();

    if (hasil.status === "success") {
      tampilkanPopUpBerhasil(hasil.struk);
      keranjang = [];
      inputUang.value = "";
      renderKeranjang();
      muatMenu();
    } else {
      tampilkanPopUpGagal(hasil.pesan);
    }
  } catch (error) {
    tampilkanPopUpGagal("Gagal terhubung ke server saat memproses transaksi.");
  }
});

// Pop-Up Struk: BERHASIL
function tampilkanPopUpBerhasil(struk) {
  const banner = document.getElementById("status-banner");
  banner.className = "bg-sage text-white px-5 py-4 flex items-center justify-between no-print";
  document.getElementById("status-icon").className = "fa-solid fa-circle-check text-amber-300";
  document.getElementById("status-title").textContent = "Transaksi Berhasil!";
  document.getElementById("status-desc").textContent = "Data tersimpan & stok menu diperbarui.";

  document.getElementById("receipt-wrapper").classList.remove("hidden");
  document.getElementById("btn-cetak").classList.remove("hidden");

  document.getElementById("struk-no").textContent = struk.no_struk;
  document.getElementById("struk-tgl").textContent = struk.tanggal;

  document.getElementById("struk-list-item").innerHTML = struk.items.map((item) => `
    <div class="flex justify-between">
      <div>
        <div class="font-bold text-stone-900">${item.nama_menu}</div>
        <div class="text-[10px] text-stone-500">${item.qty} x ${formatRupiah(item.harga_satuan)}</div>
      </div>
      <div class="font-bold">${formatRupiah(item.subtotal)}</div>
    </div>
  `).join("");

  document.getElementById("struk-total").textContent = formatRupiah(struk.total_bayar);
  document.getElementById("struk-metode").textContent = struk.metode_pembayaran;
  document.getElementById("struk-bayar").textContent = formatRupiah(struk.uang_diterima);
  document.getElementById("struk-kembali").textContent = formatRupiah(struk.kembalian);

  document.getElementById("modal-struk").classList.remove("hidden");
}

// Pop-Up Struk: GAGAL
function tampilkanPopUpGagal(pesanError) {
  const banner = document.getElementById("status-banner");
  banner.className = "bg-red-600 text-white px-5 py-4 flex items-center justify-between no-print";
  document.getElementById("status-icon").className = "fa-solid fa-triangle-exclamation text-amber-200";
  document.getElementById("status-title").textContent = "Transaksi Gagal!";
  document.getElementById("status-desc").textContent = pesanError;

  document.getElementById("receipt-wrapper").classList.add("hidden");
  document.getElementById("btn-cetak").classList.add("hidden");

  document.getElementById("modal-struk").classList.remove("hidden");
}

// Tutup Modal
const tutupModal = () => document.getElementById("modal-struk").classList.add("hidden");
document.getElementById("btn-tutup-modal").addEventListener("click", tutupModal);
document.getElementById("btn-close-x").addEventListener("click", tutupModal);

// Jam Real-Time di Header
function updateClock() {
  const now = new Date();
  document.getElementById("live-clock").textContent =
    now.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" }) + " WIB";
}

// Inisialisasi Awal
updateClock();
setInterval(updateClock, 15000);
muatMenu();
renderKeranjang();