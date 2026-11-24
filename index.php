<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>POS Kasir TPS — Sistem Pemesanan & Pembayaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
          },
          colors: {
            cream: '#FAF7F2',
            latte: '#F2ECE1',
            espresso: '#2C1E16',
            mocha: '#4A3528',
            terracotta: '#C85A32',
            terracottaDark: '#A84622',
            sage: '#4A6B5D',
            sageLight: '#E6EFEA',
          }
        }
      }
    }
  </script>
  <style>
    body {
      background-color: #FAF7F2;
      color: #2C1E16;
    }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #F2ECE1; border-radius: 999px; }
    ::-webkit-scrollbar-thumb { background: #C9B9A6; border-radius: 999px; }
    ::-webkit-scrollbar-thumb:hover { background: #A6927C; }

    @keyframes slideIn {
      from { opacity: 0; transform: translateY(-8px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-slide-in { animation: slideIn 0.2s ease-out forwards; }

    /* Pengaturan Cetak Struk Thermal (58mm / 80mm) */
    @media print {
      body * { visibility: hidden; }
      #printable-receipt, #printable-receipt * { visibility: visible; }
      #printable-receipt {
        position: fixed;
        left: 0;
        top: 0;
        width: 80mm;
        padding: 10px;
        background: white !important;
        color: black !important;
        box-shadow: none !important;
        border: none !important;
      }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-terracotta selection:text-white">

  <!-- TOP NAVIGATION BAR -->
  <header class="bg-espresso text-cream sticky top-0 z-30 shadow-md no-print">
    <div class="max-w-[1500px] mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
      <!-- Brand Logo -->
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-terracotta flex items-center justify-center text-white shadow-inner">
          <i class="fa-solid fa-cash-register text-lg"></i>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="font-extrabold text-lg tracking-tight leading-none">Aplikasi Kasir TPS</h1>
            <span class="text-[10px] uppercase tracking-wider bg-sage text-white px-2.5 py-0.5 rounded-full font-semibold">● Online</span>
          </div>
          <p class="text-xs text-stone-300 mt-0.5">Transaction Processing System • Stok & Pembayaran Real-Time</p>
        </div>
      </div>

     <!-- Navigation Links -->
      <nav class="flex items-center bg-mocha/70 p-1 rounded-2xl border border-white/10">
        <a href="index.php" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-terracotta text-white shadow">
          <i class="fa-solid fa-mug-hot"></i>
          <span>Kasir & Order</span>
        </a>
        <a href="kelola_menu.php" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-stone-200 hover:text-white transition">
          <i class="fa-solid fa-boxes-stacked"></i>
          <span>Data Barang</span>
        </a>
        <a href="riwayat.php" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-stone-200 hover:text-white transition">
          <i class="fa-solid fa-receipt"></i>
          <span>Riwayat Transaksi</span>
        </a>
      </nav>

      <!-- Live Clock -->
      <div class="hidden md:flex items-center gap-3 text-xs text-stone-300">
        <div class="text-right">
          <div id="live-clock" class="font-bold text-cream text-sm">--:-- WIB</div>
          <div>Input Cepat • Auto-Stok</div>
        </div>
        <div class="w-9 h-9 rounded-full bg-sage flex items-center justify-center text-white font-bold border border-white/20">
          KS
        </div>
      </div>
    </div>
  </header>

  <!-- MAIN WORKSPACE -->
  <main class="flex-1 max-w-[1500px] w-full mx-auto p-3 sm:p-5 no-print">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

      <!-- =================================================================== -->
      <!-- KOLOM KIRI (7/8 cols): KATALOG MENU & PILIH QTY LANGSUNG            -->
      <!-- =================================================================== -->
      <div class="lg:col-span-7 xl:col-span-8 bg-white rounded-3xl p-4 sm:p-6 shadow-sm border border-stone-200/80">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-stone-100">
          <div>
            <h2 class="font-extrabold text-lg sm:text-xl text-espresso flex items-center gap-2">
              <i class="fa-solid fa-book-open text-terracotta"></i>
              <span>Daftar Menu Tersedia</span>
            </h2>
            <p class="text-xs text-stone-500 mt-0.5">Klik <b>Tambah</b> atau atur jumlah <b>(- / +)</b> langsung pada menu pilihan pelanggan</p>
          </div>

          <!-- Pencarian Nama Menu (Tanpa Scan Barcode) -->
          <div class="relative w-full sm:w-64">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-xs"></i>
            <input type="text" id="menu-search" placeholder="Cari nama menu..." class="w-full pl-9 pr-4 py-2.5 bg-cream border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-terracotta/40 focus:border-terracotta transition" />
          </div>
        </div>

        <!-- Grid Kartu Menu (Diisi otomatis oleh JavaScript) -->
        <div id="menu-grid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
          <div class="col-span-full text-center py-10 text-stone-400 text-sm">Memuat daftar menu dari database...</div>
        </div>
      </div>

      <!-- =================================================================== -->
      <!-- KOLOM KANAN (5/4 cols): KERANJANG PESANAN & PEMBAYARAN              -->
      <!-- =================================================================== -->
      <div class="lg:col-span-5 xl:col-span-4 bg-white rounded-3xl shadow-md border border-stone-200/90 overflow-hidden sticky top-20">
        
        <!-- Header Panel Keranjang -->
        <div class="bg-espresso text-cream p-4 sm:p-5 flex items-center justify-between">
          <div>
            <span class="text-[11px] uppercase tracking-wider text-stone-300 font-semibold">Transaksi Aktif</span>
            <h3 class="text-xl font-extrabold mt-0.5">Pesanan Customer</h3>
          </div>
          <button type="button" id="btn-batal" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-red-500/80 text-xs font-semibold text-cream transition">
            <i class="fa-solid fa-trash-can mr-1"></i>Kosongkan
          </button>
        </div>

        <div class="p-4 sm:p-5 space-y-4">
          
          <!-- Daftar Item di Keranjang -->
          <div id="cart-items-container" class="max-h-[270px] overflow-y-auto pr-1 space-y-2.5">
            <!-- Diisi otomatis oleh JavaScript -->
          </div>

          <!-- Ringkasan Total Pembayaran -->
          <div class="bg-cream rounded-2xl p-4 border border-stone-200/70">
            <div class="flex justify-between items-center">
              <span class="font-bold text-sm text-espresso">Total Pembayaran</span>
              <span id="label-total" class="font-extrabold text-2xl text-terracotta">Rp 0</span>
            </div>
          </div>

          <!-- Pilihan Metode Pembayaran (Tunai / Non-Tunai) -->
          <div class="space-y-3">
            <label class="block text-xs font-bold uppercase tracking-wider text-stone-500">Metode Pembayaran</label>
            <div class="grid grid-cols-2 gap-2.5">
              <button type="button" data-method="Tunai" class="pay-method-btn flex items-center justify-center gap-2 p-3 rounded-xl border-2 border-terracotta bg-orange-50/70 text-espresso font-bold text-xs transition">
                <i class="fa-solid fa-money-bill-wave text-base text-terracotta"></i>
                <span>Tunai (Cash)</span>
              </button>
              <button type="button" data-method="Non-Tunai" class="pay-method-btn flex items-center justify-center gap-2 p-3 rounded-xl border border-stone-200 bg-white hover:bg-stone-50 text-stone-600 font-semibold text-xs transition">
                <i class="fa-solid fa-qrcode text-base"></i>
                <span>Non-Tunai (QR)</span>
              </button>
            </div>

            <!-- Box Input Tunai -->
            <div id="box-tunai" class="bg-latte/60 rounded-2xl p-3.5 border border-stone-200/80 space-y-2.5">
              <div class="flex items-center justify-between gap-2">
                <label for="input-uang" class="text-xs font-bold text-espresso">Uang Diterima:</label>
                <input type="number" id="input-uang" placeholder="0" min="0" class="w-40 text-right font-extrabold text-sm px-3 py-2 rounded-xl bg-white border border-stone-300 focus:outline-none focus:border-terracotta" />
              </div>
              
              <!-- Tombol Nominal Cepat -->
              <div class="flex flex-wrap gap-1.5">
                <button type="button" onclick="setUangPas()" class="text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-white border border-stone-200 hover:border-terracotta text-espresso">Uang Pas</button>
                <button type="button" onclick="setNominalCepat(50000)" class="text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-white border border-stone-200 hover:border-terracotta text-espresso">Rp 50.000</button>
                <button type="button" onclick="setNominalCepat(100000)" class="text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-white border border-stone-200 hover:border-terracotta text-espresso">Rp 100.000</button>
              </div>

              <div class="flex justify-between items-center pt-2 border-t border-stone-200/80 text-xs">
                <span class="text-stone-600 font-semibold">Kembalian:</span>
                <span id="label-kembalian" class="font-extrabold text-base text-sage">Rp 0</span>
              </div>
            </div>

            <!-- Box Non-Tunai (Memanggil assets/img/qr_code.jpg) -->
            <div id="box-nontunai" class="hidden bg-latte/60 rounded-2xl p-3.5 border border-stone-200/80">
              <div class="flex items-center gap-3.5">
                <div class="bg-white p-2 rounded-xl border border-stone-200 shrink-0">
                  <img 
                    src="assets/img/qr_code.jpg" 
                    alt="QR Code Pembayaran" 
                    class="w-24 h-24 object-contain rounded-lg"
                    onerror="this.onerror=null; this.src='qr_code.jpg';"
                  />
                </div>
                <div class="text-xs space-y-1">
                  <div class="font-bold text-espresso flex items-center gap-1.5">
                    <span>Pembayaran QR / Digital</span>
                    <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[10px]">Siap Scan</span>
                  </div>
                  <p class="text-stone-500 leading-relaxed">Arahkan customer memindai kode QR di layar, lalu klik tombol proses di bawah.</p>
                </div>
              </div>
            </div>

            <!-- Tombol Proses Pembayaran -->
            <button type="button" id="btn-bayar" class="w-full py-3.5 px-5 rounded-2xl bg-terracotta hover:bg-terracottaDark text-white font-extrabold text-sm shadow-lg shadow-terracotta/25 flex items-center justify-center gap-2 transition active:scale-[0.99]">
              <i class="fa-solid fa-circle-check"></i>
              <span>Proses Pembayaran & Lihat Struk</span>
            </button>
          </div>

        </div>
      </div>
    </div>
  </main>

  <!-- ===================================================================== -->
  <!-- MODAL POP-UP STRUK (MEMBERITAHU BERHASIL / GAGAL + OPSIONAL CETAK)    -->
  <!-- ===================================================================== -->
  <div id="modal-struk" class="fixed inset-0 z-50 hidden bg-espresso/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl animate-slide-in">
      
      <!-- Banner Status Dinamis (Berhasil = Hijau Sage | Gagal = Merah) -->
      <div id="status-banner" class="bg-sage text-white px-5 py-4 flex items-center justify-between no-print">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-lg">
            <i id="status-icon" class="fa-solid fa-circle-check text-amber-300"></i>
          </div>
          <div>
            <h3 id="status-title" class="font-extrabold text-base">Transaksi Berhasil!</h3>
            <p id="status-desc" class="text-xs text-emerald-100">Pesanan tercatat & stok menu diperbarui</p>
          </div>
        </div>
        <button type="button" id="btn-close-x" class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center text-white">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <!-- Area Struk (Tampil saat berhasil & siap cetak thermal jika dibutuhkan) -->
      <div id="receipt-wrapper" class="p-5 max-h-[62vh] overflow-y-auto bg-stone-100/80">
        <div id="printable-receipt" class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 font-mono text-xs text-stone-800 space-y-3">
          <div class="text-center border-b border-dashed border-stone-300 pb-3">
            <div class="font-sans font-extrabold text-base text-espresso tracking-tight">KASIR TPS CAFE</div>
            <div class="text-[11px] text-stone-500">Bukti Transaksi Pembayaran</div>
          </div>

          <div class="flex justify-between text-[11px] text-stone-600">
            <div>No: <span id="struk-no" class="font-bold text-stone-900">-</span></div>
            <div id="struk-tgl">-</div>
          </div>

          <div id="struk-list-item" class="border-t border-b border-dashed border-stone-300 py-2.5 space-y-1.5">
            <!-- Item rincian diisi JS -->
          </div>

          <div class="space-y-1 text-[11px]">
            <div class="flex justify-between font-bold text-sm text-espresso">
              <span>TOTAL</span>
              <span id="struk-total">Rp 0</span>
            </div>
            <div class="flex justify-between pt-1 text-stone-600">
              <span>Metode</span>
              <span id="struk-metode" class="font-bold">-</span>
            </div>
            <div class="flex justify-between text-stone-600">
              <span>Bayar</span>
              <span id="struk-bayar">Rp 0</span>
            </div>
            <div class="flex justify-between text-stone-600">
              <span>Kembali</span>
              <span id="struk-kembali">Rp 0</span>
            </div>
          </div>

          <div class="text-center pt-3 border-t border-dashed border-stone-300 text-[11px] text-stone-500">
            <div class="font-sans font-bold text-espresso">Terima kasih atas pesanan Anda!</div>
          </div>
        </div>
      </div>

      <!-- Tombol Aksi Pop-Up -->
      <div class="p-4 bg-white border-t border-stone-200 flex flex-col sm:flex-row gap-2.5 no-print">
        <button type="button" id="btn-cetak" onclick="window.print()" class="flex-1 py-3 px-4 rounded-xl bg-espresso hover:bg-mocha text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition">
          <i class="fa-solid fa-print"></i>
          <span>Cetak Struk (Thermal)</span>
        </button>
        <button type="button" id="btn-tutup-modal" class="flex-1 py-3 px-4 rounded-xl bg-terracotta hover:bg-terracottaDark text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition">
          <i class="fa-solid fa-check"></i>
          <span>Selesai / Tutup</span>
        </button>
      </div>
    </div>
  </div>

  <script src="assets/js/app.js"></script>
</body>
</html>