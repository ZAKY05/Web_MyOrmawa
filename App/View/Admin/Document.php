<?php
// Session sudah aktif (dari Index.php)

include('Header.php');
include('../../../Config/ConnectDB.php');

$user_level = $_SESSION['user_level'] ?? 0;
$ormawa_id = $_SESSION['ormawa_id'] ?? 0;

// Query: ambil detail lengkap termasuk ukuran_file
if ($user_level === 1) {
    $sql = "
        SELECT 
            d.id,
            d.nama_dokumen,
            d.jenis_dokumen,
            d.tanggal_upload,
            d.file_path,
            d.ukuran_file,
            o.nama_ormawa
        FROM dokumen d
        LEFT JOIN ormawa o ON d.id_ormawa = o.id
        ORDER BY d.tanggal_upload DESC
    ";
    $result = mysqli_query($koneksi, $sql);
} elseif ($user_level === 2) {
    $sql = "
        SELECT 
            d.id,
            d.nama_dokumen,
            d.jenis_dokumen,
            d.tanggal_upload,
            d.file_path,
            d.ukuran_file,
            o.nama_ormawa
        FROM dokumen d
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
                            <?php if ($user_level === 1) : ?><th scope="col">Ormawa</th><?php endif; ?>
                            <th scope="col">Jenis</th>
                            <th scope="col">Nama Dokumen</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                            <?php $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) : ?>
                                <tr>
                                    <th scope="row"><?= $no++; ?></th>
                                    <?php if ($user_level === 1) : ?>
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
                                        <!-- Detail -->
                                        <!-- Preview (Buka dokumen langsung) -->
                                        <a href="../../../uploads/dokumen/<?= urlencode($row['file_path']) ?>" target="_blank" class="btn btn-info btn-sm" title="Preview dokumen" onclick="return openDocument(event, '<?= addslashes($row['file_path']) ?>')">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Unduh -->
                                        <a href="../../../uploads/dokumen/<?= urlencode($row['file_path']) ?>" class="btn btn-sm btn-success" title="Unduh">
                                            <i class="fas fa-download"></i>
                                        </a>

                                        <!-- Edit -->
                                        <button class="btn btn-sm btn-warning edit-btn" data-id="<?= $row['id'] ?>" data-nama="<?= htmlspecialchars($row['nama_dokumen'], ENT_QUOTES); ?>" data-jenis="<?= htmlspecialchars($row['jenis_dokumen'], ENT_QUOTES); ?>" data-bs-toggle="modal" data-bs-target="#editModal" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Hapus -->
                                        <a href="../../../Function/DocumentFunction.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus dokumen ini?')" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="<?= $user_level === 1 ? 6 : 5 ?>" class="text-center text-muted">
                                    Tidak ada dokumen.
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

<!-- Modal Detail Dokumen -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Detail Dokumen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">Nama Dokumen</th>
                        <td id="detail_nama"></td>
                    </tr>
                    <tr>
                        <th>Jenis</th>
                        <td>
                            <span class="badge bg-primary" id="detail_jenis"></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Tanggal Upload</th>
                        <td id="detail_tanggal"></td>
                    </tr>
                    <?php if ($user_level === 1) : ?>
                        <tr>
                            <th>Ormawa</th>
                            <td id="detail_ormawa"></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Ukuran File</th>
                        <td id="detail_ukuran"> – KB</td>
                    </tr>
                    <tr>
                        <th>File</th>
                        <td>
                            <a id="detail_file_link" target="_blank" class="text-decoration-none">
                                <i class="fas fa-file me-1"></i>
                                <span id="detail_file_name"></span>
                            </a>
                        </td>
                    </tr>
                </table>
                <div class="alert alert-info small">
                    <i class="fas fa-lightbulb me-1"></i>
                    Anda bisa mengunduh dokumen dengan klik ikon <i class="fas fa-download text-success"></i> di tabel.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit (sama seperti sebelumnya, tanpa perubahan) -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../../../Function/DocumentFunction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
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
        // Modal Detail
        const detailModal = document.getElementById('detailModal');
        detailModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('detail_nama').textContent = button.getAttribute('data-nama');
            document.getElementById('detail_jenis').textContent = button.getAttribute('data-jenis');
            document.getElementById('detail_tanggal').textContent = button.getAttribute('data-tanggal');
            document.getElementById('detail_ormawa').textContent = button.getAttribute('data-ormawa');
            document.getElementById('detail_ukuran').textContent = button.getAttribute('data-ukuran') + ' KB';

            const fileName = button.getAttribute('data-file');
            document.getElementById('detail_file_name').textContent = fileName;
            document.getElementById('detail_file_link').href = '../../../uploads/dokumen/' + encodeURIComponent(fileName);
        });

        // Modal Edit
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('edit_id').value = button.getAttribute('data-id');
            document.getElementById('edit_nama').value = button.getAttribute('data-nama');
            document.getElementById('edit_jenis').value = button.getAttribute('data-jenis');
        });
    });
    function openDocument(e, filePath) {
    const ext = filePath.split('.').pop().toLowerCase();
    const supportedPreview = ['pdf', 'jpg', 'jpeg', 'png', 'txt'];
    
    if (supportedPreview.includes(ext)) {
        // Buka langsung di tab baru (browser akan preview otomatis)
        return true;
    } else {
        // Untuk DOC/XLS, tawarkan unduh saja
        if (confirm('File ' + ext.toUpperCase() + ' tidak bisa dipreview langsung.\nIngin mengunduh saja?')) {
            // Biarkan link mengarah ke unduh (sama seperti tombol download)
            return true;
        }
        return false;
    }
}
</script>
</scripfunction>

<?php include('Footer.php'); ?>
<?php mysqli_close($koneksi); ?>