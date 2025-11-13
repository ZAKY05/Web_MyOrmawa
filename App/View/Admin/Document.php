<?php
// Session sudah aktif (dari Index.php), jadi tidak perlu session_start()

include('Header.php');
include('../../../Config/ConnectDB.php');

$user_level = $_SESSION['user_level'] ?? 0;
$ormawa_id = $_SESSION['ormawa_id'] ?? 0;

// Query dengan filter berdasarkan level user
if ($user_level === 1) {
    // SuperAdmin: lihat semua dokumen
    $sql = "
        SELECT 
            d.id,
            d.nama_dokumen,
            d.jenis_dokumen,
            d.tanggal_upload,
            d.file_path,
            u.nama AS nama_user,
            o.nama_ormawa
        FROM dokumen d
        LEFT JOIN user u ON d.id_user = u.id
        LEFT JOIN ormawa o ON d.id_ormawa = o.id
        ORDER BY d.tanggal_upload DESC
    ";
} elseif ($user_level === 2) {
    // Admin Ormawa: hanya lihat dokumen milik ormawanya
    $sql = "
        SELECT 
            d.id,
            d.nama_dokumen,
            d.jenis_dokumen,
            d.tanggal_upload,
            d.file_path,
            u.nama AS nama_user,
            o.nama_ormawa
        FROM dokumen d
        LEFT JOIN user u ON d.id_user = u.id
        LEFT JOIN ormawa o ON d.id_ormawa = o.id
        WHERE d.id_ormawa = ?
        ORDER BY d.tanggal_upload DESC
    ";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $ormawa_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
} else {
    $result = false;
}

// Jika SuperAdmin, jalankan query biasa
if ($user_level === 1) {
    $result = mysqli_query($koneksi, $sql);
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-file-alt me-2"></i>
            Arsip Dokumen <?php echo htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ormawa'); ?>
        </h2>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-plus me-1"></i> Tambah Dokumen
        </button>
    </div>

    <!-- Filter & Search -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="searchInput" placeholder="Cari dokumen...">
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterJenis">
                <option value="">Semua Jenis</option>
                <option value="Proposal">Proposal</option>
                <option value="SPJ">SPJ</option>
                <option value="LPJ">LPJ</option>
            </select>
        </div>
    </div>

    <!-- Tabel Dokumen -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="documentTable">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">User</th>
                            <?php if ($user_level === 1): ?><th scope="col">Ormawa</th><?php endif; ?>
                            <th scope="col">Jenis</th>
                            <th scope="col">Nama Dokumen</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <th scope="row"><?= $no++ ?></th>
                                    <td><?= htmlspecialchars($row['nama_user'] ?? '–') ?></td>
                                    <?php if ($user_level === 1): ?>
                                        <td><?= htmlspecialchars($row['nama_ormawa'] ?? '–') ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <span class="badge bg-<?= match ($row['jenis_dokumen']) {
                                            'Proposal' => 'primary',
                                            'SPJ' => 'success',
                                            'LPJ' => 'info',
                                            default => 'secondary'
                                        } ?>"><?= htmlspecialchars($row['jenis_dokumen']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($row['nama_dokumen']) ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal_upload'])) ?></td>
                                    <td>
                                        <!-- Unduh -->
                                        <a href="../../../uploads/dokumen/<?= urlencode($row['file_path']) ?>" 
                                           class="btn btn-sm btn-success" title="Unduh">
                                            <i class="fas fa-download"></i>
                                        </a>

                                        <!-- Edit -->
                                        <button class="btn btn-sm btn-warning edit-btn" 
                                            data-id="<?= $row['id'] ?>"
                                            data-nama="<?= htmlspecialchars($row['nama_dokumen'], ENT_QUOTES); ?>"
                                            data-jenis="<?= htmlspecialchars($row['jenis_dokumen'], ENT_QUOTES); ?>"
                                            data-bs-toggle="modal" data-bs-target="#editModal"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Hapus -->
                                        <a href="../../../Function/DocumentFunction.php?action=delete&id=<?= $row['id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin hapus dokumen ini?')"
                                           title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $user_level === 1 ? 7 : 6 ?>" class="text-center text-muted">
                                    Tidak ada dokumen untuk <?php echo htmlspecialchars($_SESSION['ormawa_nama'] ?? 'ormawa ini'); ?>.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('../FormData/TambahDocument.php'); ?>

<!-- Modal Edit Dokumen -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../../../Function/DocumentFunction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">

                <!-- Gunakan session yang benar -->
                <input type="hidden" name="id_ormawa" value="<?= $_SESSION['ormawa_id'] ?>">
                <input type="hidden" name="id_user" value="<?= $_SESSION['user_id'] ?>">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Dokumen</label>
                        <input type="text" class="form-control" name="nama_dokumen" id="edit_nama" required>
                    </div>
                    <div class="mb-3">
                        <label>Jenis Dokumen</label>
                        <select class="form-select" name="jenis_dokumen" id="edit_jenis" required>
                            <option value="Proposal">Proposal</option>
                            <option value="SPJ">SPJ</option>
                            <option value="LPJ">LPJ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Ganti File (Opsional)</label>
                        <input type="file" class="form-control" name="file_dokumen">
                        <div class="form-text">Biarkan kosong jika tidak ingin ganti file.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const nama = button.getAttribute('data-nama');
        const jenis = button.getAttribute('data-jenis');

        editModal.querySelector('#edit_id').value = id;
        editModal.querySelector('#edit_nama').value = nama;
        editModal.querySelector('#edit_jenis').value = jenis;
    });
});
</script>

<?php include('Footer.php'); ?>
<?php mysqli_close($koneksi); ?>