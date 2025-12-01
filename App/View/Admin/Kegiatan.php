<?php
include('Header.php');
include('../../../Config/ConnectDB.php');

$ormawa_id = $_SESSION['ormawa_id'] ?? 0;
if ($ormawa_id <= 0) {
    die("Error: Ormawa ID tidak valid.");
}

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
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $data;
}

$kegiatan_list = getKegiatanData($koneksi, $ormawa_id);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-alt me-2"></i>Data Kegiatan 
            <?= htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ormawa'); ?>
        </h1>
        <button class="btn btn-success btn-icon-split" 
                data-bs-toggle="modal" 
                data-bs-target="#modalForm" 
                onclick="resetForm()">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Tambah Kegiatan</span>
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Daftar Kegiatan
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Kegiatan</th>
                            <th>Agenda</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Lokasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($kegiatan_list)): ?>
                            <?php $no = 1; foreach ($kegiatan_list as $keg): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($keg['nama_kegiatan']); ?></td>
                                    <td class="small"><?= htmlspecialchars(substr($keg['agenda'], 0, 50)) . (strlen($keg['agenda']) > 50 ? '...' : ''); ?></td>
                                    <td><?= date('d M Y', strtotime($keg['tanggal'])); ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= substr($keg['jam_mulai'], 0, 5); ?></span> – 
                                        <span class="badge bg-secondary"><?= substr($keg['jam_selesai'], 0, 5); ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($keg['lokasi']); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-circle btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalForm"
                                            onclick='editKegiatan(
                                                <?= (int)$keg['id'] ?>,
                                                <?= json_encode(htmlspecialchars($keg['nama_kegiatan'], ENT_QUOTES)) ?>,
                                                <?= json_encode(htmlspecialchars($keg['agenda'], ENT_QUOTES)) ?>,
                                                <?= json_encode($keg['tanggal']) ?>,
                                                <?= json_encode(substr($keg['jam_mulai'], 0, 5)) ?>,
                                                <?= json_encode(substr($keg['jam_selesai'], 0, 5)) ?>,
                                                <?= json_encode(htmlspecialchars($keg['lokasi'], ENT_QUOTES)) ?>
                                            )'
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <!-- ✅ Perbaikan: Gunakan tombol dengan class delete-btn -->
                                        <button class="btn btn-danger btn-circle btn-sm delete-btn" 
                                                data-id="<?= (int)$keg['id']; ?>" 
                                                data-nama="<?= htmlspecialchars($keg['nama_kegiatan'], ENT_QUOTES); ?>"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                    <p class="mb-0">Belum ada kegiatan terjadwal.</p>
                                </td>
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Tampilkan notifikasi dari SESSION (setelah redirect)
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['success'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= addslashes($_SESSION['success']); ?>',
            timer: 2000,
            showConfirmButton: false
        });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?= addslashes($_SESSION['error']); ?>',
            timer: 3000,
            showConfirmButton: false
        });
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    // === Hapus Kegiatan dengan SweetAlert2 ===
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama') || 'kegiatan ini';

            Swal.fire({
                title: 'Yakin hapus?',
                text: `Kegiatan "${nama}" akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // ✅ Perbaikan: URL ke KegiatanFunction.php (bukan AnggotaFunction.php)
                    fetch(`../../../Function/KegiatanFunction.php?action=delete&id=${id}`)
                    .then(response => {
                        if (response.redirected) {
                            window.location.href = response.url; // akan trigger notif session
                        } else {
                            return response.json().catch(() => ({}));
                        }
                    })
                    .then(data => {
                        if (data?.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Dihapus!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else if (!response?.redirected) {
                            Swal.fire('Gagal!', 'Tidak dapat menghapus kegiatan.', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error!', 'Terjadi kesalahan jaringan.', 'error');
                    });
                }
            });
        });
    });
});
</script>

<?php mysqli_close($koneksi); ?>