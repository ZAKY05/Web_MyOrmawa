<?php
header("Content-Type: application/json");
require_once "../Config/ConnectDB.php";

$id_ormawa = $_GET['id_ormawa'] ?? null;

if (!$id_ormawa) {
    echo json_encode(["success" => false, "message" => "Ormawa tidak tersedia"]);
    exit();
}

function formatKegiatan($row) {
    return [
        "id"          => $row["id"],
        "nama"        => $row["nama_kegiatan"],
        "agenda"      => $row["agenda"],
        "tanggal"     => $row["tanggal"],
        "jam_mulai"   => $row["jam_mulai"],
        "jam_selesai" => $row["jam_selesai"],
        "lokasi"      => $row["lokasi"],
        "created_at"  => $row["created_at"],
        "updated_at"  => $row["updated_at"]
    ];
}

$q = "SELECT * FROM kegiatan WHERE id_ormawa='$id_ormawa' ORDER BY tanggal DESC";
$res = mysqli_query($koneksi, $q);

$kegiatan = [];
while ($row = mysqli_fetch_assoc($res)) {
    $kegiatan[] = formatKegiatan($row);
}

echo json_encode(["success" => true, "message" => "Data kegiatan berhasil diambil", "data" => $kegiatan]);