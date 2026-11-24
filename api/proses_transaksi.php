<?php
header('Content-Type: application/json');
require_once '../config/koneksi.php';

// Hanya menerima metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "pesan" => "Metode request tidak diizinkan."]);
    exit;
}

// Membaca data JSON yang dikirim dari JavaScript (Fetch API)
$input = json_decode(file_get_contents('php://input'), true);

// Validasi kelengkapan data dasar
if (!$input || empty($input['items']) || !isset($input['metode_pembayaran']) || !isset($input['uang_diterima'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "pesan" => "Data pesanan tidak lengkap atau keranjang kosong."]);
    exit;
}

$items             = $input['items'];
$metode_pembayaran = $input['metode_pembayaran']; // 'Tunai' atau 'Non-Tunai'
$uang_diterima     = (float) $input['uang_diterima'];

// Validasi nilai enum metode pembayaran
if (!in_array($metode_pembayaran, ['Tunai', 'Non-Tunai'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "pesan" => "Metode pembayaran tidak valid."]);
    exit;
}

// Memulai Database Transaction (Menjamin keamanan data: semua sukses atau semua dibatalkan)
mysqli_begin_transaction($conn);

try {
    $total_bayar    = 0;
    $detail_valid   = [];

    // 1. Cek ketersediaan stok & hitung ulang total harga dari database (mencegah manipulasi harga dari frontend)
    $stmt_cek = mysqli_prepare($conn, "SELECT nama_menu, harga, stok FROM menu WHERE id_menu = ? FOR UPDATE");

    foreach ($items as $item) {
        $id_menu = (int) $item['id_menu'];
        $qty     = (int) $item['qty'];

        if ($qty <= 0) {
            throw new Exception("Jumlah pesanan tidak valid.");
        }

        mysqli_stmt_bind_param($stmt_cek, "i", $id_menu);
        mysqli_stmt_execute($stmt_cek);
        $res_menu = mysqli_stmt_get_result($stmt_cek);
        $menu_db  = mysqli_fetch_assoc($res_menu);

        if (!$menu_db) {
            throw new Exception("Menu dengan ID $id_menu tidak ditemukan.");
        }

        // Validasi stok habis / kurang
        if ($menu_db['stok'] < $qty) {
            throw new Exception("Stok menu '{$menu_db['nama_menu']}' tidak mencukupi (Sisa stok: {$menu_db['stok']}).");
        }

        $harga_satuan = (float) $menu_db['harga'];
        $subtotal     = $harga_satuan * $qty;
        $total_bayar += $subtotal;

        $detail_valid[] = [
            'id_menu'      => $id_menu,
            'nama_menu'    => $menu_db['nama_menu'],
            'qty'          => $qty,
            'harga_satuan' => $harga_satuan,
            'subtotal'     => $subtotal
        ];
    }
    mysqli_stmt_close($stmt_cek);

    // 2. Validasi kecukupan pembayaran
    if ($metode_pembayaran === 'Non-Tunai') {
        // Untuk Non-Tunai (QR/Digital), nominal dianggap pas sesuai total tagihan
        $uang_diterima = $total_bayar;
        $kembalian     = 0;
    } else {
        if ($uang_diterima < $total_bayar) {
            throw new Exception("Uang tunai kurang! Total tagihan: Rp " . number_format($total_bayar, 0, ',', '.'));
        }
        $kembalian = $uang_diterima - $total_bayar;
    }

    // 3. Membuat Nomor Struk Unik (Format: TRX-YYYYMMDD-HIS-RAND)
    $no_struk = "TRX-" . date("Ymd-His") . "-" . rand(10, 99);
    $tanggal  = date("Y-m-d H:i:s");

    // 4. Simpan ke tabel transaksi
    $stmt_trx = mysqli_prepare($conn, "INSERT INTO transaksi (no_struk, tanggal, total_bayar, metode_pembayaran, uang_diterima, kembalian) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_trx, "ssdsdd", $no_struk, $tanggal, $total_bayar, $metode_pembayaran, $uang_diterima, $kembalian);
    
    if (!mysqli_stmt_execute($stmt_trx)) {
        throw new Exception("Gagal menyimpan data transaksi utama.");
    }
    
    $id_transaksi = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt_trx);

    // 5. Simpan ke detail_transaksi & kurangi stok pada tabel menu
    $stmt_detail = mysqli_prepare($conn, "INSERT INTO detail_transaksi (id_transaksi, id_menu, qty, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
    $stmt_stok   = mysqli_prepare($conn, "UPDATE menu SET stok = stok - ? WHERE id_menu = ?");

    foreach ($detail_valid as $d) {
        // Insert detail
        mysqli_stmt_bind_param($stmt_detail, "iiidd", $id_transaksi, $d['id_menu'], $d['qty'], $d['harga_satuan'], $d['subtotal']);
        if (!mysqli_stmt_execute($stmt_detail)) {
            throw new Exception("Gagal menyimpan rincian pesanan.");
        }

        // Update kurangi stok
        mysqli_stmt_bind_param($stmt_stok, "ii", $d['qty'], $d['id_menu']);
        if (!mysqli_stmt_execute($stmt_stok)) {
            throw new Exception("Gagal memperbarui stok menu.");
        }
    }

    mysqli_stmt_close($stmt_detail);
    mysqli_stmt_close($stmt_stok);

    // 6. Commit transaksi (simpan permanen semua perubahan)
    mysqli_commit($conn);

    // Kirim response sukses beserta data struk untuk ditampilkan di Pop-Up
    echo json_encode([
        "status" => "success",
        "pesan"  => "Transaksi berhasil diproses!",
        "struk"  => [
            "no_struk"          => $no_struk,
            "tanggal"           => date("d/m/Y H:i:s", strtotime($tanggal)),
            "items"             => $detail_valid,
            "total_bayar"       => $total_bayar,
            "metode_pembayaran" => $metode_pembayaran,
            "uang_diterima"     => $uang_diterima,
            "kembalian"         => $kembalian
        ]
    ]);

} catch (Exception $e) {
    // Jika terjadi error apa pun, batalkan seluruh perubahan database
    mysqli_rollback($conn);
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "pesan"  => $e->getMessage()
    ]);
}

mysqli_close($conn);
?>