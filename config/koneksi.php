<?php
// Mengatur zona waktu sesuai lokasi (WIB)
date_default_timezone_set('Asia/Jakarta');

$host     = "localhost";
$username = "root";      // Default user XAMPP
$password = "";          // Default password XAMPP (kosong)
$database = "kasir";

// Membuat koneksi menggunakan MySQLi
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$conn) {
    // Mengembalikan response JSON jika gagal konek
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "pesan"  => "Koneksi database gagal: " . mysqli_connect_error()
    ]);
    exit;
}

// Mengatur karakter encoding ke utf8mb4
mysqli_set_charset($conn, "utf8mb4");
?>