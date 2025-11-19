<?php
include('Header.php');
include('../../../Config/ConnectDB.php');


$stmt = mysqli_prepare($koneksi, "SELECT * FROM beasiswa ORDER BY created_at DESC");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$beasiswa_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Beasiswa</h1>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahBeasiswaModal">
            <i class="fas fa-plus fa-sm"></i> Tambah Beasiswa
        </button>
    </div>

    <!-- Data Kompetisi Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Beasiswa</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTableBeasiswa" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Beasiswa</th>
                            <th>Penyelenggara</th>
                            <th>Periode</th>
                            <th>Deadline</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($beasiswa_list)): ?>
                            <?php $no = 1; foreach ($beasiswa_list as $b): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($b['nama_beasiswa']); ?></td>
                                    <td><?= htmlspecialchars($b['penyelenggara']); ?></td>
                                    <td><?= htmlspecialchars($b['periode'] ?? '–'); ?></td>
                                    <td><?= $b['deadline'] ? date('d M Y', strtotime($b['deadline'])) : '–'; ?></td>
                                    <td>
                                        <!-- Detail -->
                                        <button class="btn btn-info btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#detailModal"
                                                data-id="<?= $b['id']; ?>"
                                                data-nama="<?= htmlspecialchars($b['nama_beasiswa'], ENT_QUOTES); ?>"
                                                data-penyelenggara="<?= htmlspecialchars($b['penyelenggara'], ENT_QUOTES); ?>"
                                                data-periode="<?= htmlspecialchars($b['periode'] ?? '', ENT_QUOTES); ?>"
                                                data-deadline="<?= htmlspecialchars($b['deadline'] ?? '', ENT_QUOTES); ?>"
                                                data-deskripsi="<?= htmlspecialchars($b['deskripsi'], ENT_QUOTES); ?>"
                                                data-gambar="<?= htmlspecialchars($b['gambar'], ENT_QUOTES); ?>"
                                                data-file-panduan="<?= htmlspecialchars($b['file_panduan'], ENT_QUOTES); ?>"
                                                title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <!-- Edit -->
                                        <button class="btn btn-warning btn-sm edit-btn" 
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="<?= $b['id']; ?>"
                                                data-nama="<?= htmlspecialchars($b['nama_beasiswa'], ENT_QUOTES); ?>"
                                                data-penyelenggara="<?= htmlspecialchars($b['penyelenggara'], ENT_QUOTES); ?>"
                                                data-periode="<?= htmlspecialchars($b['periode'] ?? '', ENT_QUOTES); ?>"
                                                data-deadline="<?= htmlspecialchars($b['deadline'] ?? '', ENT_QUOTES); ?>"
                                                data-deskripsi="<?= htmlspecialchars($b['deskripsi'], ENT_QUOTES); ?>"
                                                data-gambar="<?= htmlspecialchars($b['gambar'], ENT_QUOTES); ?>"
                                                data-file-panduan="<?= htmlspecialchars($b['file_panduan'], ENT_QUOTES); ?>"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Hapus -->
                                        <a href="../../../Function/BeasiswaFunction.php?action=delete&id=<?= $b['id']; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Yakin hapus beasiswa ini?')"
                                           title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada beasiswa.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include('../FormData/TambahBeasiswa.php'); ?>

<!-- Modal Detail Kompetisi -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detail Beasiswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <img id="detailGambar" src="" alt="Gambar" class="img-fluid rounded" style="max-height: 200px; object-fit: cover;">
                        <div class="text-center mt-2">
                            <a id="downloadPanduanBtn" href="#" class="btn btn-sm btn-outline-primary d-none" target="_blank">
                                <i class="fas fa-download me-1"></i>Download Panduan
                            </a>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h4 id="detailNama" class="mb-3"></h4>
                        <p><strong>Penyelenggara:</strong> <span id="detailPenyelenggara"></span></p>
                        <p><strong>Periode:</strong> <span id="detailPeriode">–</span></p>
                        <p><strong>Deadline:</strong> <span id="detailDeadline">–</span></p>
                        <h5 class="mt-4">Deskripsi</h5>
                        <p id="detailDeskripsi" class="text-muted"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Kompetisi -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../../../Function/BeasiswaFunction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Beasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Beasiswa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_beasiswa" id="edit_nama" required>
                    </div>
                    <div class="mb-3">
                        <label>Penyelenggara <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="penyelenggara" id="edit_penyelenggara" required>
                    </div>
                    <div class="mb-3">
                        <label>Periode (Opsional)</label>
                        <input type="text" class="form-control" name="periode" id="edit_periode" placeholder="Contoh: 10-12 Januari 2026">
                    </div>
                    <div class="mb-3">
                        <label>Deadline (Opsional)</label>
                        <input type="date" class="form-control" name="deadline" id="edit_deadline">
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Gambar (Opsional)</label>
                        <br>
                        <img id="current_image_display" src="" alt="Gambar" width="100" height="100" class="img-thumbnail mb-2">
                        <input type="file" class="form-control-file" name="gambar" accept="image/*">
                        <small class="form-text text-muted">Format: JPG, JPEG, PNG</small>
                    </div>
                    <div class="mb-3">
                        <label>Buku Panduan (Opsional)</label>
                        <br>
                        <a id="current_file_display" href="#" target="_blank" class="text-decoration-none d-none">
                            <i class="fas fa-file-pdf me-1"></i> Lihat Panduan
                        </a>
                        <input type="file" class="form-control-file" name="file_panduan" accept=".pdf,.doc,.docx">
                        <small class="form-text text-muted">Format: PDF, DOC, DOCX</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('Footer.php'); ?>
<?php mysqli_close($koneksi); ?>

<style>
#detailGambar { background: #f8f9fa; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const BASE_URL = window.location.origin + '/MyOrmawa';

    // Modal Detail
    const detailModal = document.getElementById('detailModal');
    detailModal.addEventListener('show.bs.modal', function(event) {
        const b = event.relatedTarget;
        document.getElementById('detailNama').textContent = b.getAttribute('data-nama');
        document.getElementById('detailPenyelenggara').textContent = b.getAttribute('data-penyelenggara');
        document.getElementById('detailPeriode').textContent = b.getAttribute('data-periode') || '–';
        const dl = b.getAttribute('data-deadline');
        document.getElementById('detailDeadline').textContent = dl ? new Date(dl).toLocaleDateString() : '–';
        document.getElementById('detailDeskripsi').textContent = b.getAttribute('data-deskripsi');

        const gambar = b.getAttribute('data-gambar');
        const gambarPath = gambar ? BASE_URL + '/uploads/beasiswa/' + encodeURIComponent(gambar) : 'https://via.placeholder.com/200?text=No+Image';
        document.getElementById('detailGambar').src = gambarPath;

        const file = b.getAttribute('data-file-panduan');
        const fileBtn = document.getElementById('downloadPanduanBtn');
        if (file) {
            fileBtn.href = BASE_URL + '/uploads/beasiswa/' + encodeURIComponent(file);
            fileBtn.classList.remove('d-none');
        } else {
            fileBtn.classList.add('d-none');
        }
    });

    // Modal Edit
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        const b = event.relatedTarget;
        document.getElementById('edit_id').value = b.getAttribute('data-id');
        document.getElementById('edit_nama').value = b.getAttribute('data-nama');
        document.getElementById('edit_penyelenggara').value = b.getAttribute('data-penyelenggara');
        document.getElementById('edit_periode').value = b.getAttribute('data-periode') || '';
        document.getElementById('edit_deadline').value = b.getAttribute('data-deadline') || '';
        document.getElementById('edit_deskripsi').value = b.getAttribute('data-deskripsi');

        const gambar = b.getAttribute('data-gambar');
        const gambarPath = gambar ? BASE_URL + '/uploads/beasiswa/' + encodeURIComponent(gambar) : 'https://via.placeholder.com/100?text=No+Image';
        document.getElementById('current_image_display').src = gambarPath;

        const file = b.getAttribute('data-file-panduan');
        const fileLink = document.getElementById('current_file_display');
        if (file) {
            fileLink.href = BASE_URL + '/uploads/beasiswa/' + encodeURIComponent(file);
            fileLink.classList.remove('d-none');
        } else {
            fileLink.classList.add('d-none');
        }
    });
});
</script>