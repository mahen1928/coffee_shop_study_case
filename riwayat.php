<?php
require_once 'config/koneksi.php';

// Ambil ringkasan total penjualan
$summaryQuery = "SELECT COUNT(id_transaksi) AS total_trx, COALESCE(SUM(total_bayar), 0) AS total_omzet FROM transaksi";
$summaryRes   = mysqli_query($conn, $summaryQuery);
$summary      = mysqli_fetch_assoc($summaryRes);

// Ambil daftar transaksi beserta rincian item yang dibeli
$query = "
    SELECT 
        t.id_transaksi,
        t.no_struk,
        t.tanggal,
        t.total_bayar,
        t.metode_pembayaran,
        t.uang_diterima,
        t.kembalian,
        GROUP_CONCAT(CONCAT(m.nama_menu, ' (', dt.qty, 'x)') SEPARATOR ', ') AS rincian_menu
    FROM transaksi t
    LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    LEFT JOIN menu m ON dt.id_menu = m.id_menu
    GROUP BY t.id_transaksi
    ORDER BY t.tanggal DESC
";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Riwayat Transaksi — Aplikasi Kasir TPS</title>
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
          <i class="fa-solid fa-receipt text-lg"></i>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="font-extrabold text-lg tracking-tight leading-none">Aplikasi Kasir TPS</h1>
            <span class="text-[10px] uppercase tracking-wider bg-sage text-white px-2.5 py-0.5 rounded-full font-semibold">● Online</span>
          </div>
          <p class="text-xs text-stone-300 mt-0.5">Log & Riwayat Penyimpanan Data Transaksi</p>
        </div>
      </div>

      <!-- Menu Navigasi 3 Halaman -->
      <nav class="flex items-center bg-mocha/70 p-1 rounded-2xl border border-white/10">
        <a href="index.php" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-stone-200 hover:text-white transition">
          <i class="fa-solid fa-mug-hot"></i>
          <span>Kasir & Order</span>
        </a>
        <a href="kelola_menu.php" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-stone-200 hover:text-white transition">
          <i class="fa-solid fa-boxes-stacked"></i>
          <span>Data Barang</span>
        </a>
        <a href="riwayat.php" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-terracotta text-white shadow">
          <i class="fa-solid fa-receipt"></i>
          <span>Riwayat Transaksi</span>
        </a>
      </nav>
    </div>
  </header>

  <!-- KONTEN UTAMA -->
  <main class="flex-1 max-w-[1500px] w-full mx-auto p-4 sm:p-6 space-y-6">

    <!-- KARTU RINGKASAN -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="bg-white rounded-3xl p-5 border border-stone-200/80 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Total Pendapatan Tercatat</span>
          <div class="text-2xl font-extrabold text-sage mt-1">Rp <?= number_format($summary['total_omzet'], 0, ',', '.') ?></div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-sageLight text-sage flex items-center justify-center text-xl">
          <i class="fa-solid fa-coins"></i>
        </div>
      </div>

      <div class="bg-white rounded-3xl p-5 border border-stone-200/80 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Total Transaksi Berhasil</span>
          <div class="text-2xl font-extrabold text-espresso mt-1"><?= (int)$summary['total_trx'] ?> Transaksi</div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-orange-50 text-terracotta flex items-center justify-center text-xl">
          <i class="fa-solid fa-check-double"></i>
        </div>
      </div>
    </div>

    <!-- TABEL RIWAYAT TRANSAKSI -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-stone-200/80">
      <div class="flex items-center justify-between pb-4 mb-4 border-b border-stone-100">
        <div>
          <h2 class="font-extrabold text-lg text-espresso">Daftar Transaksi Masuk</h2>
          <p class="text-xs text-stone-500">Seluruh transaksi tunai & non-tunai tersimpan otomatis ke database MySQL</p>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-stone-200 text-[11px] uppercase tracking-wider text-stone-500">
              <th class="py-3 px-3">No. Struk</th>
              <th class="py-3 px-3">Waktu Transaksi</th>
              <th class="py-3 px-3">Rincian Pesanan Customer</th>
              <th class="py-3 px-3">Metode</th>
              <th class="py-3 px-3">Total Bayar</th>
              <th class="py-3 px-3">Tunai / Kembali</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-100 text-sm">
            <?php if (mysqli_num_rows($result) === 0) : ?>
              <tr>
                <td colspan="6" class="text-center py-8 text-stone-400">Belum ada riwayat transaksi.</td>
              </tr>
            <?php else : ?>
              <?php while ($trx = mysqli_fetch_assoc($result)) : ?>
                <tr class="hover:bg-cream/60 transition">
                  <td class="py-3.5 px-3 font-mono font-bold text-xs text-espresso"><?= htmlspecialchars($trx['no_struk']) ?></td>
                  <td class="py-3.5 px-3 text-xs text-stone-600"><?= date('d/m/Y H:i', strtotime($trx['tanggal'])) ?> WIB</td>
                  <td class="py-3.5 px-3 text-xs font-medium text-espresso"><?= htmlspecialchars($trx['rincian_menu'] ?? '-') ?></td>
                  <td class="py-3.5 px-3">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= $trx['metode_pembayaran'] === 'Tunai' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' ?>">
                      <?= htmlspecialchars($trx['metode_pembayaran']) ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-3 font-extrabold text-sage">Rp <?= number_format($trx['total_bayar'], 0, ',', '.') ?></td>
                  <td class="py-3.5 px-3 text-xs text-stone-500">
                    Rp <?= number_format($trx['uang_diterima'], 0, ',', '.') ?> / 
                    <span class="font-semibold text-espresso">Rp <?= number_format($trx['kembalian'], 0, ',', '.') ?></span>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</body>
</html>