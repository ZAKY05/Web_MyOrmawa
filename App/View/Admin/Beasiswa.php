<?php
include('Header.php');
include('../../../Config/ConnectDB.php');

$ormawa_id = $_SESSION['ormawa_id'] ?? 0;
if ($ormawa_id <= 0) {
    die("Error: Anda tidak terdaftar di ORMawa manapun.");
}

$stmt = mysqli_prepare($koneksi, "
    SELECT id, nama_beasiswa, penyelenggara, periode, deadline, deskripsi, gambar, file_panduan
    FROM beasiswa 
    WHERE id_ormawa = ? 
    ORDER BY created_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $ormawa_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$beasiswa_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-graduation-cap me-2"></i>Manajemen Beasiswa 
            <?= htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ormawa'); ?>
        </h1>
        <button class="btn btn-success btn-icon-split" 
                data-bs-toggle="modal" 
                data-bs-target="#tambahBeasiswaModal"
                onclick="resetFormBeasiswa()">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Tambah Beasiswa</span>
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Daftar Beasiswa
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTableBeasiswa" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Beasiswa</th>
                            <th>Penyelenggara</th>
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
                                    <td><span class="badge bg-info"><?= htmlspecialchars($b['penyelenggara']); ?></span></td>
                                    <td>
                                        <?= $b['deadline'] ? date('d M Y', strtotime($b['deadline'])) : '–'; ?>
                                        <?php if ($b['deadline'] && strtotime($b['deadline']) < time()): ?>
                                            <span class="badge bg-danger ms-1">Tutup</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-circle btn-sm detail-btn"
                                                data-bs-toggle="modal" data-bs-target="#detailModal"
                                                data-id="<?= (int)$b['id']; ?>"
                                                data-nama="<?= htmlspecialchars($b['nama_beasiswa'], ENT_QUOTES); ?>"
                                                data-penyelenggara="<?= htmlspecialchars($b['penyelenggara'], ENT_QUOTES); ?>"
                                                data-periode="<?= htmlspecialchars($b['periode'] ?? '', ENT_QUOTES); ?>"
                                                data-deadline="<?= htmlspecialchars($b['deadline'] ?? '', ENT_QUOTES); ?>"
                                                data-deskripsi="<?= htmlspecialchars($b['deskripsi'], ENT_QUOTES); ?>"
                                                data-gambar="<?= htmlspecialchars($b['gambar'] ?? '', ENT_QUOTES); ?>"
                                                data-file-panduan="<?= htmlspecialchars($b['file_panduan'] ?? '', ENT_QUOTES); ?>"
                                                title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <button class="btn btn-warning btn-circle btn-sm edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="<?= (int)$b['id']; ?>"
                                                data-nama="<?= htmlspecialchars($b['nama_beasiswa'], ENT_QUOTES); ?>"
                                                data-penyelenggara="<?= htmlspecialchars($b['penyelenggara'], ENT_QUOTES); ?>"
                                                data-periode="<?= htmlspecialchars($b['periode'] ?? '', ENT_QUOTES); ?>"
                                                data-deadline="<?= htmlspecialchars($b['deadline'] ?? '', ENT_QUOTES); ?>"
                                                data-deskripsi="<?= htmlspecialchars($b['deskripsi'], ENT_QUOTES); ?>"
                                                data-gambar="<?= htmlspecialchars($b['gambar'] ?? '', ENT_QUOTES); ?>"
                                                data-file-panduan="<?= htmlspecialchars($b['file_panduan'] ?? '', ENT_QUOTES); ?>"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button class="btn btn-danger btn-circle btn-sm delete-btn"
                                                data-id="<?= (int)$b['id']; ?>"
                                                data-nama="<?= htmlspecialchars($b['nama_beasiswa'], ENT_QUOTES); ?>"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                                    <p class="mb-0">Belum ada beasiswa terdaftar.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Detail Beasiswa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="detailGambar" src="https://via.placeholder.com/300x200?text=No+Image" 
                             alt="Gambar" class="img-fluid rounded mb-3" style="max-height: 250px; object-fit: cover;">
                        <a id="downloadPanduanBtn" href="#" target="_blank" 
                           class="btn btn-sm btn-outline-primary d-none">
                            <i class="fas fa-download me-1"></i> Download Panduan
                        </a>
                    </div>
                    <div class="col-md-8">
                        <h4 id="detailNama" class="mb-3"></h4>
                        <p><strong>Penyelenggara:</strong> <span id="detailPenyelenggara"></span></p>
                        <p><strong>Periode:</strong> <span id="detailPeriode">–</span></p>
                        <p><strong>Deadline:</strong> <span id="detailDeadline">–</span></p>
                        <h5 class="mt-3"><i class="fas fa-align-left me-2"></i> Deskripsi</h5>
                        <p id="detailDeskripsi" class="text-muted" style="white-space: pre-line;"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i> Edit Beasiswa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../../Function/BeasiswaFunction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Beasiswa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_beasiswa" id="edit_nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="penyelenggara" id="edit_penyelenggara" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Periode (Opsional)</label>
                        <input type="text" class="form-control" name="periode" id="edit_periode">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deadline (Opsional)</label>
                        <input type="date" class="form-control" name="deadline" id="edit_deadline">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Saat Ini</label><br>
                        <img id="edit_current_image" src="https://via.placeholder.com/120?text=No+Image" 
                             alt="Gambar" width="120" class="img-thumbnail rounded mb-2">
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                        <small class="form-text text-muted">Format: JPG, PNG (≤2MB)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Buku Panduan Saat Ini</label><br>
                        <a id="edit_current_file" href="#" target="_blank" class="text-decoration-none d-none">
                            <i class="fas fa-file-pdf me-1"></i> Lihat Panduan
                        </a>
                        <input type="file" class="form-control" name="file_panduan" accept=".pdf,.doc,.docx">
                        <small class="form-text text-muted">Format: PDF, DOC (≤10MB)</small>
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

<?php include('../FormData/TambahBeasiswa.php'); ?>
<?php include('Footer.php'); ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const UPLOAD_BASE_PATH = '../uploads/beasiswa/';
    // ✅ Notifikasi dari SESSION (pastikan KompetisiFunction.php pakai ini)
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

    // Hapus
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama') || 'beasiswa ini';

            Swal.fire({
                title: 'Yakin hapus?',
                text: `Beasiswa "${nama}" akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `../../../Function/BeasiswaFunction.php?action=delete&id=${id}`;
                }
            });
        });
    });

  // Detail
  document.querySelectorAll('.detail-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            const penyelenggara = this.getAttribute('data-penyelenggara');
            const periode = this.getAttribute('data-periode');
            const deadline = this.getAttribute('data-deadline');
            const deskripsi = this.getAttribute('data-deskripsi');
            const gambar = this.getAttribute('data-gambar');
            const filePanduan = this.getAttribute('data-file-panduan');

            document.getElementById('detailNama').textContent = nama;
            document.getElementById('detailPenyelenggara').textContent = penyelenggara;
            document.getElementById('detailPeriode').textContent = periode || '–';
            document.getElementById('detailDeadline').textContent = 
                deadline ? new Date(deadline).toLocaleDateString('id-ID') : '–';
            document.getElementById('detailDeskripsi').textContent = deskripsi;

            const imgEl = document.getElementById('detailGambar');
            if (gambar) {
                // ✅ PERBAIKAN: Mengganti '../uploads/beasiswa/' menjadi Jalur Absolut
                const imagePath = UPLOAD_BASE_PATH + encodeURIComponent(gambar);
                imgEl.src = imagePath;
                console.log('Detail Gambar Path:', imagePath); // Debugging
            } else {
                imgEl.src = 'https://via.placeholder.com/300x200?text=No+Image';
            }

            const fileLink = document.getElementById('downloadPanduanBtn');
            if (filePanduan) {
                // ✅ PERBAIKAN: Mengganti '../uploads/beasiswa/' menjadi Jalur Absolut
                fileLink.href = UPLOAD_BASE_PATH + encodeURIComponent(filePanduan);
                fileLink.classList.remove('d-none');
                console.log('Detail File Path:', fileLink.href); // Debugging
            } else {
                fileLink.classList.add('d-none');
            }
        });
    });

    // Edit (Penting: Perbaikan yang sama diterapkan di sini juga)
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            const penyelenggara = this.getAttribute('data-penyelenggara');
            const periode = this.getAttribute('data-periode');
            const deadline = this.getAttribute('data-deadline');
            const deskripsi = this.getAttribute('data-deskripsi');
            const gambar = this.getAttribute('data-gambar');
            const filePanduan = this.getAttribute('data-file-panduan');

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_penyelenggara').value = penyelenggara;
            document.getElementById('edit_periode').value = periode;
            document.getElementById('edit_deadline').value = deadline;
            document.getElementById('edit_deskripsi').value = deskripsi;

            const imgEl = document.getElementById('edit_current_image');
            if (gambar) {
                // ✅ PERBAIKAN: Mengganti '../uploads/beasiswa/' menjadi Jalur Absolut
                const imagePath = UPLOAD_BASE_PATH + encodeURIComponent(gambar);
                imgEl.src = imagePath;
                console.log('Edit Gambar Path:', imagePath); // Debugging
            } else {
                imgEl.src = 'https://via.placeholder.com/120?text=No+Image';
            }

            const fileLink = document.getElementById('edit_current_file');
            if (filePanduan) {
                // ✅ PERBAIKAN: Mengganti '../uploads/beasiswa/' menjadi Jalur Absolut
                fileLink.href = UPLOAD_BASE_PATH + encodeURIComponent(filePanduan);
                fileLink.textContent = `📄 ${filePanduan}`;
                fileLink.classList.remove('d-none');
            } else {
                fileLink.classList.add('d-none');
            }
        });
    });
});

function resetFormBeasiswa() {
    const form = document.getElementById('formBeasiswa');
    if (form) form.reset();
    const img = document.getElementById('current_image_display');
    const file = document.getElementById('current_file_display');
    if (img) img.src = 'https://via.placeholder.com/120?text=No+Image';
    if (file) file.classList.add('d-none');
}
</script>

<?php mysqli_close($koneksi); ?>