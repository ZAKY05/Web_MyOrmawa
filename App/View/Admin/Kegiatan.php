<?php
// Session sudah dimulai di Index.php — jangan panggil session_start() lagi

include('Header.php');
include('../../../Config/ConnectDB.php');

// Ambil ormawa_id dari session yang sudah aktif
$ormawa_id = $_SESSION['ormawa_id'] ?? 0;

if ($ormawa_id <= 0) {
    die("Error: Ormawa ID tidak valid.");
}

// Fungsi ambil data kegiatan hanya milik ormawa ini
function getKegiatanData($koneksi, $ormawa_id) {
    $sql = "SELECT id, nama_kegiatan, agenda, tanggal, jam_mulai, jam_selesai, lokasi 
            FROM kegiatan 
            WHERE id_ormawa = ? 
            ORDER BY tanggal DESC, jam_mulai ASC";
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

$kegiatan_list = getKegiatanData($koneksi, $ormawa_id);
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Data Kegiatan <?php echo htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ormawa'); ?></h1>
        <button class="btn btn-success btn-icon-split" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="resetForm()">
            <span class="icon text-white-50">
                <i class="fas fa-plus"></i>
            </span>
            <span class="text">Tambah Kegiatan</span>
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
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kegiatan <?php echo htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ini'); ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kegiatan</th>
                            <th>Agenda</th>
                            <th>Tanggal</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Lokasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($kegiatan_list)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($kegiatan_list as $keg): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($keg['nama_kegiatan']); ?></td>
                                    <td><?= htmlspecialchars($keg['agenda']); ?></td>
                                    <td><?= htmlspecialchars(date('d-m-Y', strtotime($keg['tanggal']))); ?></td>
                                    <td><?= htmlspecialchars(substr($keg['jam_mulai'], 0, 5)); ?></td>
                                    <td><?= htmlspecialchars(substr($keg['jam_selesai'], 0, 5)); ?></td>
                                    <td><?= htmlspecialchars($keg['lokasi']); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-circle btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalForm"
                                            onclick='editKegiatan(
                                                <?= (int)$keg['id'] ?>,
                                                <?= json_encode(htmlspecialchars($keg['nama_kegiatan'])) ?>,
                                                <?= json_encode(htmlspecialchars($keg['agenda'])) ?>,
                                                <?= json_encode($keg['tanggal']) ?>,
                                                <?= json_encode(substr($keg['jam_mulai'], 0, 5)) ?>,
                                                <?= json_encode(substr($keg['jam_selesai'], 0, 5)) ?>,
                                                <?= json_encode(htmlspecialchars($keg['lokasi'])) ?>
                                            )'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="../../../Function/KegiatanFunction.php?action=delete&id=<?= $keg['id']; ?>"
                                           class="btn btn-danger btn-circle btn-sm"
                                           onclick="return confirm('Yakin hapus kegiatan \"<?= addslashes(htmlspecialchars($keg['nama_kegiatan'])) ?>\" ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data kegiatan untuk <?php echo htmlspecialchars($_SESSION['ormawa_nama'] ?? 'ormawa ini'); ?>.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include('../FormData/TambahKegiatan.php'); ?>
<?php include('Footer.php'); ?>
<?php mysqli_close($koneksi); ?>