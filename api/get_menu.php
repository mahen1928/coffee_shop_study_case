<?php
header('Content-Type: application/json');
require_once '../config/koneksi.php';

$query = "SELECT id_menu, kode_barcode, nama_menu, harga, stok FROM menu ORDER BY nama_menu ASC";
$result = mysqli_query($conn, $query);

$data_menu = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Konversi tipe data angka agar presisi di JavaScript
        $row['id_menu'] = (int) $row['id_menu'];
        $row['harga']   = (float) $row['harga'];
        $row['stok']    = (int) $row['stok'];
        $data_menu[]    = $row;
    }

    echo json_encode([
        "status" => "success",
        "data"   => $data_menu
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "pesan"  => "Gagal mengambil data menu: " . mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>