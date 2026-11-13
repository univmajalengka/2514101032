
<?php

// 1. Pengaturan koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_diskon";

// 2. Membuat koneksi
$conn = new mysqli(
    $servername,
    $username,
    $password,
    $dbname
);

// 3. Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

?>
