<?php
include('Header.php');
include('../../../Config/ConnectDB.php');

// Ambil dokumen lengkap dengan nama user dan nama ormawa
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
ORDER BY d.tanggal_upload DESC;
";
$result = $koneksi->query($sql);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-alt me-2"></i>Arsip Dokumen Anggota Ormawa</h2>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-plus me-1"></i> Tambah Dokumen
        </button>
    </div>

    <!-- Filter & Search -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="searchInput" placeholder="Cari nama dokumen, user, atau ormawa...">
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterJenis">
                <option value="">Semua Jenis Dokumen</option>
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
                            <th scope="col">Ormawa</th>
                            <th scope="col">Jenis</th>
                            <th scope="col">Nama Dokumen</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <th scope="row"><?= $no++ ?></th>
                                    <td><?= htmlspecialchars($row['nama_user'] ?? '–') ?></td>
                                    <td><?= htmlspecialchars($row['nama_ormawa'] ?? '–') ?></td>
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
                                           class="btn btn-sm btn-success" title="Unduh dokumen">
                                            <i class="fas fa-download"></i>
                                        </a>

                                        <!-- Edit -->
                                        <button class="btn btn-sm btn-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal"
                                            data-id="<?= $row['id'] ?>"
                                            data-nama="<?= htmlspecialchars($row['nama_dokumen']) ?>"
                                            data-jenis="<?= htmlspecialchars($row['jenis_dokumen']) ?>"
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
                                <td colspan="7" class="text-center text-muted">Tidak ada dokumen.</td>
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
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../../../Function/DocumentFunction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">

                <!-- Ambil dari session atau hardcode sementara -->
                <input type="hidden" name="id_ormawa" value="<?= $_SESSION['id_ormawa'] ?? 1 ?>">
                <input type="hidden" name="id_user" value="<?= $_SESSION['id_user'] ?? $_SESSION['id'] ?? 1 ?>">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <button type="submit" class="btn btn-warning">Simpan</button>
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