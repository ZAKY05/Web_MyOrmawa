<?php
// config/database.php

$host = 'localhost';
$dbname = 'myormawa_db';
$username = 'root';
$password = '';

$koneksi = mysqli_connect($host, $username, $password, $dbname);
if(!$koneksi){
    die("Koneksi gagal");
}
?>