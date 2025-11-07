<?php
// JANGAN panggil session_start() di sini — sudah dipanggil di Index.php

include('Header.php');
include('../../../Config/ConnectDB.php');

// Ambil ormawa_id dari session yang sudah aktif
$ormawa_id = $_SESSION['ormawa_id'] ?? 0;

if ($ormawa_id <= 0) {
    die("Error: Ormawa ID tidak valid.");
}

// Fungsi ambil data anggota hanya milik ormawa ini
function getAnggotaData($koneksi, $ormawa_id) {
    $sql = "SELECT id, nama, departemen, jabatan, no_telpon, prodi FROM anggota WHERE id_ormawa = ? ORDER BY nama ASC";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $ormawa_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_free_result($result);
    }
    mysqli_stmt_close($stmt);
    return $data;
}

$anggota_list = getAnggotaData($koneksi, $ormawa_id);
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Data Anggota <?php echo htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ormawa'); ?></h1>
        <button class="btn btn-success btn-icon-split" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="resetForm()">
            <span class="icon text-white-50">
                <i class="fas fa-plus"></i>
            </span>
            <span class="text">Tambah Anggota</span>
        </button>
    </div>

    <!-- Alert Messages dari Session -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- DataTabels Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Anggota <?php echo htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ini'); ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Departemen</th>
                            <th>Jabatan</th>
                            <th>No. Telepon</th>
                            <th>Prodi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($anggota_list)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($anggota_list as $anggota): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($anggota['nama']); ?></td>
                                    <td><?= htmlspecialchars($anggota['departemen']); ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($anggota['jabatan']); ?></span></td>
                                    <td><?= htmlspecialchars($anggota['no_telpon']); ?></td>
                                    <td><?= htmlspecialchars($anggota['prodi']); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-circle btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalForm"
                                            onclick='editAnggota(
                                                <?= (int)$anggota['id'] ?>,
                                                <?= json_encode(htmlspecialchars($anggota['nama'])) ?>,
                                                <?= json_encode(htmlspecialchars($anggota['departemen'])) ?>,
                                                <?= json_encode(htmlspecialchars($anggota['jabatan'])) ?>,
                                                <?= json_encode(htmlspecialchars($anggota['no_telpon'])) ?>,
                                                <?= json_encode(htmlspecialchars($anggota['prodi'])) ?>
                                            )'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="../../../Function/AnggotaFunction.php?action=delete&id=<?= $anggota['id']; ?>"
                                           class="btn btn-danger btn-circle btn-sm"
                                           onclick="return confirm('Yakin hapus anggota ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data anggota untuk <?php echo htmlspecialchars($_SESSION['ormawa_nama'] ?? 'ormawa ini'); ?>.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include('../FormData/TambahAnggota.php'); ?>
<?php include('Footer.php'); ?>
<?php mysqli_close($koneksi); ?>