<?php
header('Content-Type: application/json');
require_once '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "pesan" => "Metode tidak diizinkan."]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$aksi  = $input['aksi'] ?? 'tambah';

if ($aksi === 'tambah') {
    $nama_menu    = trim($input['nama_menu'] ?? '');
    $harga        = (float) ($input['harga'] ?? 0);
    $stok         = (int) ($input['stok'] ?? 0);
    // Buat kode barang otomatis jika tidak diinput manual (karena tanpa scan barcode)
    $kode_barcode = !empty(trim($input['kode_barcode'] ?? '')) 
                    ? trim($input['kode_barcode']) 
                    : "MN-" . date("His") . rand(10, 99);

    if ($nama_menu === '' || $harga <= 0 || $stok < 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "pesan" => "Nama menu, harga (> 0), dan stok wajib diisi dengan benar."]);
        exit;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO menu (kode_barcode, nama_menu, harga, stok) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssdi", $kode_barcode, $nama_menu, $harga, $stok);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            "status" => "success",
            "pesan"  => "Menu '$nama_menu' berhasil ditambahkan!"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "pesan"  => "Gagal menyimpan data barang: " . mysqli_error($conn)
        ]);
    }
    mysqli_stmt_close($stmt);

} elseif ($aksi === 'update_stok') {
    $id_menu  = (int) ($input['id_menu'] ?? 0);
    $stok_baru = (int) ($input['stok'] ?? -1);

    if ($id_menu <= 0 || $stok_baru < 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "pesan" => "Data stok tidak valid."]);
        exit;
    }

    $stmt = mysqli_prepare($conn, "UPDATE menu SET stok = ? WHERE id_menu = ?");
    mysqli_stmt_bind_param($stmt, "ii", $stok_baru, $id_menu);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            "status" => "success",
            "pesan"  => "Stok barang berhasil diperbarui!"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "pesan"  => "Gagal memperbarui stok."
        ]);
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>