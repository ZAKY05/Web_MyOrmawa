<?php
date_default_timezone_set('Asia/Jakarta');
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'ormawa';

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    throw new Exception("Gagal koneksi: " . mysqli_connect_error());
}
mysqli_query($koneksi, "SET time_zone = '+07:00'");

return $koneksi;
?>