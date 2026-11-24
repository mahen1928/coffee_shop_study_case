<?php
require_once 'config/koneksi.php';

// Ambil semua data menu dari database
$query = "SELECT * FROM menu ORDER BY id_menu DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Barang & Stok — Aplikasi Kasir TPS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
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
</head>
<body class="bg-cream text-espresso min-h-screen flex flex-col antialiased">

  <!-- TOP NAVIGATION BAR -->
  <header class="bg-espresso text-cream sticky top-0 z-30 shadow-md">
    <div class="max-w-[1500px] mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-terracotta flex items-center justify-center text-white shadow-inner">
          <i class="fa-solid fa-boxes-stacked text-lg"></i>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="font-extrabold text-lg tracking-tight leading-none">Aplikasi Kasir TPS</h1>
            <span class="text-[10px] uppercase tracking-wider bg-sage text-white px-2.5 py-0.5 rounded-full font-semibold">● Online</span>
          </div>
          <p class="text-xs text-stone-300 mt-0.5">Manajemen Data Barang & Stok Menu</p>
        </div>
      </div>

      <!-- Menu Navigasi 3 Halaman -->
      <nav class="flex items-center bg-mocha/70 p-1 rounded-2xl border border-white/10">
        <a href="index.php" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-stone-200 hover:text-white transition">
          <i class="fa-solid fa-mug-hot"></i>
          <span>Kasir & Order</span>
        </a>
        <a href="kelola_menu.php" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-terracotta text-white shadow">
          <i class="fa-solid fa-boxes-stacked"></i>
          <span>Data Barang</span>
        </a>
        <a href="riwayat.php" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-stone-200 hover:text-white transition">
          <i class="fa-solid fa-receipt"></i>
          <span>Riwayat Transaksi</span>
        </a>
      </nav>
    </div>
  </header>

  <!-- KONTEN UTAMA -->
  <main class="flex-1 max-w-[1500px] w-full mx-auto p-4 sm:p-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

      <!-- FORM TAMBAH DATA BARANG (4 Cols) -->
      <div class="lg:col-span-4 bg-white rounded-3xl p-6 shadow-sm border border-stone-200/80 sticky top-20">
        <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-stone-100">
          <div class="w-9 h-9 rounded-xl bg-orange-50 text-terracotta flex items-center justify-center">
            <i class="fa-solid fa-plus"></i>
          </div>
          <div>
            <h2 class="font-extrabold text-lg text-espresso">Tambah Barang Baru</h2>
            <p class="text-xs text-stone-500">Item baru otomatis muncul di layar kasir</p>
          </div>
        </div>

        <form id="form-tambah-barang" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-espresso mb-1.5">Nama Menu / Barang *</label>
            <input type="text" id="nama_menu" required placeholder="Contoh: Croissant Cokelat" class="w-full px-3.5 py-2.5 bg-cream border border-stone-200 rounded-xl text-sm focus:outline-none focus:border-terracotta" />
          </div>

          <div>
            <label class="block text-xs font-bold text-espresso mb-1.5">Harga Jual (Rp) *</label>
            <input type="number" id="harga" required min="500" step="500" placeholder="Contoh: 25000" class="w-full px-3.5 py-2.5 bg-cream border border-stone-200 rounded-xl text-sm focus:outline-none focus:border-terracotta" />
          </div>

          <div>
            <label class="block text-xs font-bold text-espresso mb-1.5">Stok Awal *</label>
            <input type="number" id="stok" required min="0" placeholder="Contoh: 20" class="w-full px-3.5 py-2.5 bg-cream border border-stone-200 rounded-xl text-sm focus:outline-none focus:border-terracotta" />
          </div>

          <div>
            <label class="block text-xs font-bold text-stone-500 mb-1.5">Kode Barang (Opsional - Otomatis jika kosong)</label>
            <input type="text" id="kode_barcode" placeholder="Contoh: MN009" class="w-full px-3.5 py-2.5 bg-cream border border-stone-200 rounded-xl text-sm focus:outline-none focus:border-terracotta" />
          </div>

          <button type="submit" class="w-full py-3.5 px-5 rounded-2xl bg-terracotta hover:bg-terracottaDark text-white font-extrabold text-sm shadow-lg shadow-terracotta/25 flex items-center justify-center gap-2 transition active:scale-[0.99]">
            <i class="fa-solid fa-floppy-disk"></i>
            <span>Simpan Data Barang</span>
          </button>
        </form>
      </div>

      <!-- TABEL DAFTAR BARANG & UPDATE STOK (8 Cols) -->
      <div class="lg:col-span-8 bg-white rounded-3xl p-6 shadow-sm border border-stone-200/80">
        <div class="flex flex-wrap items-center justify-between gap-2 pb-4 mb-5 border-b border-stone-100">
          <div>
            <h2 class="font-extrabold text-lg text-espresso">Daftar Inventori Menu</h2>
            <p class="text-xs text-stone-500">Ubah angka stok dan klik <b>Simpan</b> untuk mengisi ulang stok habis</p>
          </div>
          <span class="px-3 py-1 rounded-full bg-cream border border-stone-200 text-xs font-bold text-espresso">
            Total: <?= mysqli_num_rows($result) ?> Item
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-stone-200 text-[11px] uppercase tracking-wider text-stone-500">
                <th class="py-3 px-3">Kode</th>
                <th class="py-3 px-3">Nama Barang</th>
                <th class="py-3 px-3">Harga</th>
                <th class="py-3 px-3">Status</th>
                <th class="py-3 px-3">Atur Stok</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 text-sm">
              <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr class="hover:bg-cream/60 transition">
                  <td class="py-3.5 px-3 font-mono text-xs text-stone-500"><?= htmlspecialchars($row['kode_barcode']) ?></td>
                  <td class="py-3.5 px-3 font-bold text-espresso"><?= htmlspecialchars($row['nama_menu']) ?></td>
                  <td class="py-3.5 px-3 font-semibold text-terracotta">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                  <td class="py-3.5 px-3">
                    <?php if ((int)$row['stok'] <= 0) : ?>
                      <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-[11px] font-bold">Stok Habis</span>
                    <?php else : ?>
                      <span class="px-2.5 py-1 rounded-full bg-sageLight text-sage text-[11px] font-bold">Tersedia</span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3.5 px-3">
                    <div class="flex items-center gap-2">
                      <input type="number" id="stok-input-<?= $row['id_menu'] ?>" value="<?= (int)$row['stok'] ?>" min="0" class="w-20 px-2.5 py-1.5 rounded-xl border border-stone-300 text-center font-bold text-xs focus:outline-none focus:border-terracotta" />
                      <button type="button" onclick="updateStok(<?= $row['id_menu'] ?>)" class="px-3 py-1.5 rounded-xl bg-espresso hover:bg-sage text-white text-xs font-bold transition">
                        Simpan
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>

  <script>
    // Tambah Barang Baru
    document.getElementById('form-tambah-barang').addEventListener('submit', async (e) => {
      e.preventDefault();

      const payload = {
        aksi: 'tambah',
        nama_menu: document.getElementById('nama_menu').value,
        harga: document.getElementById('harga').value,
        stok: document.getElementById('stok').value,
        kode_barcode: document.getElementById('kode_barcode').value
      };

      const res = await fetch('api/simpan_menu.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const hasil = await res.json();

      alert(hasil.pesan);
      if (hasil.status === 'success') {
        window.location.reload();
      }
    });

    // Update Stok Barang
    async function updateStok(idMenu) {
      const stokBaru = document.getElementById(`stok-input-${idMenu}`).value;

      const res = await fetch('api/simpan_menu.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          aksi: 'update_stok',
          id_menu: idMenu,
          stok: stokBaru
        })
      });
      const hasil = await res.json();

      alert(hasil.pesan);
      if (hasil.status === 'success') {
        window.location.reload();
      }
    }
  </script>
</body>
</html>