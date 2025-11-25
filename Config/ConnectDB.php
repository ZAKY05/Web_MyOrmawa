<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'myormawa_db';

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    throw new Exception("Gagal koneksi: " . mysqli_connect_error());
}

return $koneksi;
?>