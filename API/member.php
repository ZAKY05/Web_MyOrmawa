<?php
header("Content-Type: application/json");
require_once "../Config/ConnectDB.php";

// Ambil id_ormawa dari query parameter
$id_ormawa = $_GET['id_ormawa'] ?? null;

if (!$id_ormawa) {
    echo json_encode([
        "success" => false,
        "message" => "Ormawa tidak tersedia"
    ]);
    exit();
}

// Fungsi format member
function formatMember($row) {
    return [
        "id"         => $row["id"],
        "name"       => $row["nama"],
        "department" => $row["departemen"],
        "position"   => $row["jabatan"],
        "phone"      => $row["no_telpon"],
        "prodi"      => $row["prodi"]
    ];
}

// Query data anggota
$q = "SELECT id, nama, departemen, jabatan, no_telpon, prodi 
      FROM anggota 
      WHERE id_ormawa='$id_ormawa' 
      ORDER BY nama ASC";
$res = mysqli_query($koneksi, $q);

$members = [];
while ($row = mysqli_fetch_assoc($res)) {
    $members[] = formatMember($row);
}

// Kirim response JSON
echo json_encode([
    "success" => true,
    "message" => "Data anggota berhasil diambil",
    "data"    => $members
]);
exit();
?>