<?php include('Header.php'); ?>
<?php include('../../../Config/ConnectDB.php'); ?>

<?php
function getKegiatanData($koneksi) {
    $sql = "SELECT id, nama_kegiatan, agenda, tanggal, jam_mulai, jam_selesai, lokasi 
            FROM kegiatan 
            ORDER BY tanggal DESC, jam_mulai DESC";
    $result = mysqli_query($koneksi, $sql);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_free_result($result);
    }
    return $data;
}
$kegiatan_list = getKegiatanData($koneksi);
?>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Kegiatan</h1>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahKegiatanModal" onclick="resetFormKegiatan()">
                <i class="fas fa-plus fa-sm"></i> Tambah Kegiatan
            </button>
            <!-- Opsional: Export Excel (bisa dikembangkan nanti) -->
            <!-- <button type="button" class="btn btn-success btn-sm" id="exportExcel">
                <i class="fas fa-file-excel fa-sm"></i> Export Excel
            </button> -->
        </div>
    </div>

    <!-- Alert dari Session -->
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

    <!-- Data Kegiatan Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Kegiatan</h6>
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
                            <?php foreach ($kegiatan_list as $k): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($k['nama_kegiatan']); ?></td>
                                    <td><?= htmlspecialchars($k['agenda']); ?></td>
                                    <td><?= htmlspecialchars(date('d-m-Y', strtotime($k['tanggal']))); ?></td>
                                    <td><?= htmlspecialchars($k['jam_mulai']); ?></td>
                                    <td><?= htmlspecialchars($k['jam_selesai']); ?></td>
                                    <td><?= htmlspecialchars($k['lokasi']); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-circle btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#tambahKegiatanModal"
                                            onclick='editKegiatan(
                                                <?= (int)$k['id'] ?>,
                                                <?= json_encode(htmlspecialchars($k['nama_kegiatan'])) ?>,
                                                <?= json_encode(htmlspecialchars($k['agenda'])) ?>,
                                                <?= json_encode($k['tanggal']) ?>,
                                                <?= json_encode($k['jam_mulai']) ?>,
                                                <?= json_encode($k['jam_selesai']) ?>,
                                                <?= json_encode(htmlspecialchars($k['lokasi'])) ?>
                                            )'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="../../../Function/KegiatanFunction.php?action=delete&id=<?= $k['id']; ?>"
                                           class="btn btn-danger btn-circle btn-sm"
                                           onclick="return confirm('Yakin hapus kegiatan ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data kegiatan.</td>
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