<?php
include('Header.php');
include('../../../Config/ConnectDB.php');

// ✅ Pastikan SuperAdmin
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] != 1) {
    $_SESSION['error'] = "Akses ditolak.";
    header("Location: Login.php");
    exit();
}

// Ambil SEMUA kompetisi + nama ormawa
$stmt = mysqli_prepare($koneksi, "
    SELECT 
        k.id,
        k.nama_kompetisi,
        k.penyelenggara,
        k.periode,
        k.deadline,
        k.deskripsi,
        k.gambar,
        k.file_panduan,
        k.created_at,
        o.id AS ormawa_id,
        o.nama_ormawa
    FROM kompetisi k
    INNER JOIN ormawa o ON k.id_ormawa = o.id
    ORDER BY k.created_at DESC
");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$kompetisi_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-trophy me-2"></i>Manajemen Kompetisi Semua Ormawa
        </h1>
        <button class="btn btn-success btn-icon-split" 
                data-bs-toggle="modal" 
                data-bs-target="#tambahKompetisiModal"
                onclick="resetFormKompetisi()">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Tambah Kompetisi</span>
        </button>
    </div>

    <!-- 🔍 Search Box -->
    <div class="mb-3">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="searchInput" class="form-control" 
                   placeholder="Cari nama kompetisi, penyelenggara, atau ormawa...">
        </div>
        <small class="text-muted">Pencarian berdasarkan nama, penyelenggara, atau ormawa.</small>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Daftar Kompetisi
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTableKompetisi" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Kompetisi</th>
                            <th>Penyelenggara</th>
                            <th>Ormawa</th>
                            <th>Deadline</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="kompetisiTableBody">
                        <?php if (!empty($kompetisi_list)): ?>
                            <?php $no = 1; foreach ($kompetisi_list as $k): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="search-col"><?= htmlspecialchars($k['nama_kompetisi']); ?></td>
                                    <td class="search-col"><?= htmlspecialchars($k['penyelenggara']); ?></td>
                                    <td class="search-col"><?= htmlspecialchars($k['nama_ormawa']); ?></td>
                                    <td>
                                        <?= $k['deadline'] ? date('d M Y', strtotime($k['deadline'])) : '–'; ?>
                                        <?php if ($k['deadline'] && strtotime($k['deadline']) < time()): ?>
                                            <span class="badge bg-danger ms-1">Tutup</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Detail -->
                                        <button class="btn btn-info btn-circle btn-sm detail-btn" 
                                                data-bs-toggle="modal" data-bs-target="#detailModal"
                                                data-id="<?= (int)$k['id']; ?>"
                                                data-nama="<?= htmlspecialchars($k['nama_kompetisi'], ENT_QUOTES); ?>"
                                                data-penyelenggara="<?= htmlspecialchars($k['penyelenggara'], ENT_QUOTES); ?>"
                                                data-periode="<?= htmlspecialchars($k['periode'] ?? '', ENT_QUOTES); ?>"
                                                data-deadline="<?= htmlspecialchars($k['deadline'] ?? '', ENT_QUOTES); ?>"
                                                data-deskripsi="<?= htmlspecialchars($k['deskripsi'], ENT_QUOTES); ?>"
                                                data-gambar="<?= htmlspecialchars($k['gambar'] ?? '', ENT_QUOTES); ?>"
                                                data-file-panduan="<?= htmlspecialchars($k['file_panduan'] ?? '', ENT_QUOTES); ?>"
                                                data-ormawa="<?= htmlspecialchars($k['nama_ormawa'], ENT_QUOTES); ?>"
                                                title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <!-- Edit -->
                                        <button class="btn btn-warning btn-circle btn-sm edit-btn" 
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="<?= (int)$k['id']; ?>"
                                                data-nama="<?= htmlspecialchars($k['nama_kompetisi'], ENT_QUOTES); ?>"
                                                data-penyelenggara="<?= htmlspecialchars($k['penyelenggara'], ENT_QUOTES); ?>"
                                                data-periode="<?= htmlspecialchars($k['periode'] ?? '', ENT_QUOTES); ?>"
                                                data-deadline="<?= htmlspecialchars($k['deadline'] ?? '', ENT_QUOTES); ?>"
                                                data-deskripsi="<?= htmlspecialchars($k['deskripsi'], ENT_QUOTES); ?>"
                                                data-gambar="<?= htmlspecialchars($k['gambar'] ?? '', ENT_QUOTES); ?>"
                                                data-file-panduan="<?= htmlspecialchars($k['file_panduan'] ?? '', ENT_QUOTES); ?>"
                                                data-ormawa-id="<?= (int)$k['ormawa_id']; ?>"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Hapus -->
                                        <button class="btn btn-danger btn-circle btn-sm delete-btn" 
                                                data-id="<?= (int)$k['id']; ?>"
                                                data-nama="<?= htmlspecialchars($k['nama_kompetisi'], ENT_QUOTES); ?>"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-trophy fa-2x mb-2"></i>
                                    <p class="mb-0">Belum ada kompetisi terdaftar.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div id="noResultsMessage" class="text-center text-muted d-none py-3">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p>Tidak ada kompetisi yang sesuai.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail (sama seperti sebelumnya) -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Detail Kompetisi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="detailGambar" src="https://via.placeholder.com/300x200?text=No+Image" 
                             alt="Gambar Kompetisi" class="img-fluid rounded mb-3" 
                             style="max-height: 250px; object-fit: cover;">
                        <a id="downloadPanduanBtn" href="#" target="_blank" 
                           class="btn btn-sm btn-outline-primary d-none">
                            <i class="fas fa-download me-1"></i>Download Panduan
                        </a>
                    </div>
                    <div class="col-md-8">
                        <h4 id="detailNama" class="mb-3"></h4>
                        <p><strong>Penyelenggara:</strong> <span id="detailPenyelenggara" class="text-primary"></span></p>
                        <p><strong>Ormawa:</strong> <span id="detailOrmawa" class="badge bg-secondary"></span></p>
                        <p><strong>Periode:</strong> <span id="detailPeriode">–</span></p>
                        <p><strong>Deadline:</strong> <span id="detailDeadline">–</span></p>
                        <div class="mt-3">
                            <h5><i class="fas fa-align-left me-2"></i>Deskripsi</h5>
                            <p id="detailDeskripsi" class="text-muted" style="white-space: pre-line;"></p>
                        </div>
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

<!-- Modal Edit (DIPERBAHARUI: tambah pilih Ormawa) -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Kompetisi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" action="../../../Function/KompetisiFunction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">

                <div class="modal-body">
                    <!-- 🔑 Pilih Ormawa (untuk SuperAdmin) -->
                    <div class="mb-3">
                        <label class="form-label">Ormawa Penyelenggara <span class="text-danger">*</span></label>
                        <select class="form-control" name="id_ormawa" id="edit_ormawa_id" required>
                            <?php
                            $ormawas = mysqli_query($koneksi, "SELECT id, nama_ormawa FROM ormawa ORDER BY nama_ormawa");
                            while ($o = mysqli_fetch_assoc($ormawas)):
                            ?>
                                <option value="<?= (int)$o['id']; ?>"><?= htmlspecialchars($o['nama_ormawa']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Kompetisi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_kompetisi" id="edit_nama_kompetisi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="penyelenggara" id="edit_penyelenggara" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Periode (Opsional)</label>
                            <input type="text" class="form-control" name="periode" id="edit_periode">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Deadline (Opsional)</label>
                            <input type="date" class="form-control" name="deadline" id="edit_deadline">
                        </div>
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

<!-- Modal Tambah (Ditambahkan di bawah — atau include terpisah) -->
<?php include('../FormData/TambahKompetisi.php'); ?>

<?php include('Footer.php'); ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const UPLOAD_BASE_PATH = '../../../uploads/kompetisi/';

    // 🔍 Search
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('kompetisiTableBody');
    const noResultsMsg = document.getElementById('noResultsMessage');
    const rows = Array.from(tableBody.querySelectorAll('tr:not(:has(td[colspan]))'));

    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        let count = 0;
        rows.forEach(row => {
            const text = Array.from(row.querySelectorAll('.search-col'))
                .map(el => el.textContent.toLowerCase())
                .join(' ');
            if (query === '' || text.includes(query)) {
                row.style.display = '';
                count++;
            } else {
                row.style.display = 'none';
            }
        });
        noResultsMsg.classList.toggle('d-none', count > 0 || query === '');
    });

    // Detail
    document.querySelectorAll('.detail-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('detailNama').textContent = this.getAttribute('data-nama');
            document.getElementById('detailPenyelenggara').textContent = this.getAttribute('data-penyelenggara');
            document.getElementById('detailOrmawa').textContent = this.getAttribute('data-ormawa');
            document.getElementById('detailPeriode').textContent = this.getAttribute('data-periode') || '–';
            document.getElementById('detailDeadline').textContent = 
                this.getAttribute('data-deadline') ? 
                new Date(this.getAttribute('data-deadline')).toLocaleDateString('id-ID') : '–';
            document.getElementById('detailDeskripsi').textContent = this.getAttribute('data-deskripsi');

            const gambar = this.getAttribute('data-gambar');
            document.getElementById('detailGambar').src = 
                gambar ? UPLOAD_BASE_PATH + encodeURIComponent(gambar) : 
                'https://via.placeholder.com/300x200?text=No+Image';

            const panduan = this.getAttribute('data-file-panduan');
            const link = document.getElementById('downloadPanduanBtn');
            if (panduan) {
                link.href = UPLOAD_BASE_PATH + encodeURIComponent(panduan);
                link.classList.remove('d-none');
            } else {
                link.classList.add('d-none');
            }
        });
    });

    // Edit
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const ormawaId = this.getAttribute('data-ormawa-id');

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama_kompetisi').value = this.getAttribute('data-nama');
            document.getElementById('edit_penyelenggara').value = this.getAttribute('data-penyelenggara');
            document.getElementById('edit_periode').value = this.getAttribute('data-periode');
            document.getElementById('edit_deadline').value = this.getAttribute('data-deadline');
            document.getElementById('edit_deskripsi').value = this.getAttribute('data-deskripsi');
            document.getElementById('edit_ormawa_id').value = ormawaId;

            const gambar = this.getAttribute('data-gambar');
            document.getElementById('edit_current_image').src = 
                gambar ? UPLOAD_BASE_PATH + encodeURIComponent(gambar) : 
                'https://via.placeholder.com/120?text=No+Image';

            const panduan = this.getAttribute('data-file-panduan');
            const link = document.getElementById('edit_current_file');
            if (panduan) {
                link.href = UPLOAD_BASE_PATH + encodeURIComponent(panduan);
                link.textContent = `📄 ${panduan}`;
                link.classList.remove('d-none');
            } else {
                link.classList.add('d-none');
            }
        });
    });

    // Hapus
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            Swal.fire({
                title: 'Hapus Kompetisi?',
                text: `“${nama}” akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.href = `../../../Function/KompetisiFunction.php?action=delete&id=${id}&level=1`;
                }
            });
        });
    });

    // Notifikasi
    <?php if (isset($_SESSION['success'])): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: '<?= addslashes($_SESSION['success']); ?>', timer: 2000, showConfirmButton: false });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        Swal.fire({ icon: 'error', title: 'Gagal!', text: '<?= addslashes($_SESSION['error']); ?>', timer: 3000, showConfirmButton: false });
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
});
</script>

<?php mysqli_close($koneksi); ?>