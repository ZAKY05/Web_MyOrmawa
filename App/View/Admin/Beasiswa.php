<?php
// Admin/SuperAdmin/Beasiswa.php

// 1. Inisialisasi dan Keamanan
include('../../../Config/ConnectDB.php');

// Batasi akses hanya untuk level 1 (SuperAdmin) atau level 2 (Admin Ormawa)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_level'], [1, 2])) {
    header("Location: ../SuperAdmin/Login.php");
    exit();
}

$user_level = (int)$_SESSION['user_level'];

// 2. Pengambilan Data
if ($user_level === 1) {
    // SuperAdmin: Ambil SEMUA beasiswa
    $stmt = mysqli_prepare($koneksi, "
        SELECT 
            b.id, b.nama_beasiswa, b.penyelenggara, b.deadline, 
            b.deskripsi, b.gambar, b.file_panduan, b.created_at,
            o.id AS ormawa_id, o.nama_ormawa
        FROM beasiswa b
        INNER JOIN ormawa o ON b.id_ormawa = o.id
        ORDER BY b.created_at DESC
    ");
} else {
    // Admin Ormawa: Hanya beasiswa milik ormawa-nya
    $ormawa_id = (int)($_SESSION['ormawa_id'] ?? 0);
    $stmt = mysqli_prepare($koneksi, "
        SELECT 
            b.id, b.nama_beasiswa, b.penyelenggara, b.deadline, 
            b.deskripsi, b.gambar, b.file_panduan, b.created_at,
            o.id AS ormawa_id, o.nama_ormawa
        FROM beasiswa b
        INNER JOIN ormawa o ON b.id_ormawa = o.id
        WHERE b.id_ormawa = ?
        ORDER BY b.created_at DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $ormawa_id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$beasiswa_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Ambil semua ormawa untuk modal edit (SuperAdmin)
$all_ormawa_list = [];
if ($user_level === 1) {
    $ormawa_query = mysqli_query($koneksi, "SELECT id, nama_ormawa FROM ormawa ORDER BY nama_ormawa");
    while ($o = mysqli_fetch_assoc($ormawa_query)) {
        $all_ormawa_list[] = $o;
    }
}

include('Header.php');
?>

<style>
    #dataTableBeasiswa_wrapper .row:first-child {
        display: none;
    }
    #dataTableBeasiswa_wrapper .row:last-child {
        margin-top: 15px;
    }

    .search-box .input-group-text {
        border-right: none;
        background-color: #e9ecef;
    }
    .search-box input {
        border-left: none;
    }
</style>

<div class="container-fluid">
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-graduation-cap me-2"></i>Manajemen Beasiswa
            <?php if ($user_level === 2): ?>
                <?= htmlspecialchars($_SESSION['ormawa_nama'] ?? ''); ?>
            <?php endif; ?>
        </h1>
        <button type="button" class="btn btn-success btn-icon-split" 
                data-bs-toggle="modal" data-bs-target="#tambahBeasiswaModal">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Tambah Beasiswa</span>
        </button>
    </div>

    <!-- Search Box -->
    <div class="mb-3">
        <div class="search-box">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" id="searchInput" class="form-control" 
                        placeholder="Cari: Nama Beasiswa, Penyelenggara, Ormawa..." 
                        onkeyup="filterBeasiswa()">
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Daftar Beasiswa
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableBeasiswa" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Gambar</th>
                            <th>Nama Beasiswa</th>
                            <th>Ormawa</th>
                            <th>Penyelenggara</th>
                            <th>Deadline</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBodyBeasiswa">
                        <?php if (!empty($beasiswa_list)): ?>
                            <?php foreach ($beasiswa_list as $index => $b): ?>
                                <?php
                                $gambar_path = '../../../Uploads/beasiswa/' . $b['gambar'];
                                $image_src = (file_exists($gambar_path) && !empty($b['gambar'])) 
                                    ? $gambar_path 
                                    : 'https://via.placeholder.com/50?text=No+Image';
                                
                                $searchText = strtolower(
                                    $b['nama_beasiswa'] . ' ' .
                                    $b['penyelenggara'] . ' ' .
                                    $b['nama_ormawa']
                                );
                                
                                $isExpired = $b['deadline'] && strtotime($b['deadline']) < time();
                                ?>
                                <tr data-search="<?= htmlspecialchars($searchText, ENT_QUOTES); ?>">
                                    <td><?= $index + 1; ?></td>
                                    <td>
                                        <img src="<?= $image_src; ?>" width="50" height="50" class="img-thumbnail rounded">
                                    </td>
                                    <td><?= htmlspecialchars($b['nama_beasiswa']); ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($b['nama_ormawa']); ?></span></td>
                                    <td><?= htmlspecialchars($b['penyelenggara']); ?></td>
                                    <td>
                                        <?= $b['deadline'] ? date('d M Y', strtotime($b['deadline'])) : '–'; ?>
                                        <?php if ($isExpired): ?>
                                            <span class="badge bg-danger ms-1">Tutup</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-circle btn-sm detail-btn"
                                            data-id="<?= (int)$b['id']; ?>"
                                            data-nama="<?= htmlspecialchars($b['nama_beasiswa'], ENT_QUOTES); ?>"
                                            data-penyelenggara="<?= htmlspecialchars($b['penyelenggara'], ENT_QUOTES); ?>"
                                            data-deadline="<?= htmlspecialchars($b['deadline'] ?? '', ENT_QUOTES); ?>"
                                            data-deskripsi="<?= htmlspecialchars($b['deskripsi'], ENT_QUOTES); ?>"
                                            data-gambar="<?= htmlspecialchars($b['gambar'] ?? '', ENT_QUOTES); ?>"
                                            data-file="<?= htmlspecialchars($b['file_panduan'] ?? '', ENT_QUOTES); ?>"
                                            data-ormawa="<?= htmlspecialchars($b['nama_ormawa'], ENT_QUOTES); ?>"
                                            title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button class="btn btn-warning btn-circle btn-sm edit-btn"
                                            data-id="<?= (int)$b['id']; ?>"
                                            data-nama="<?= htmlspecialchars($b['nama_beasiswa'], ENT_QUOTES); ?>"
                                            data-penyelenggara="<?= htmlspecialchars($b['penyelenggara'], ENT_QUOTES); ?>"
                                            data-deadline="<?= htmlspecialchars($b['deadline'] ?? '', ENT_QUOTES); ?>"
                                            data-deskripsi="<?= htmlspecialchars($b['deskripsi'], ENT_QUOTES); ?>"
                                            data-gambar="<?= htmlspecialchars($b['gambar'] ?? '', ENT_QUOTES); ?>"
                                            data-file="<?= htmlspecialchars($b['file_panduan'] ?? '', ENT_QUOTES); ?>"
                                            data-ormawa-id="<?= (int)$b['ormawa_id']; ?>"
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
                            <tr class="default-no-data">
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                                    <p class="mb-0">Belum ada beasiswa terdaftar.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr id="noResults" class="text-center text-muted d-none">
                            <td colspan="7" class="py-4">
                                <i class="fas fa-magnifying-glass fa-2x mb-2"></i>
                                <p>Tidak ada data yang sesuai dengan pencarian Anda.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailBeasiswaModal" tabindex="-1">
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
                        <img id="detailGambar" src="" alt="Gambar" 
                             class="img-fluid rounded mb-3" style="max-height: 250px; object-fit: cover;">
                        <a id="detailFilePanduan" href="#" target="_blank" class="btn btn-sm btn-outline-primary d-none">
                            <i class="fas fa-download me-1"></i> Download Panduan
                        </a>
                    </div>
                    <div class="col-md-8">
                        <h4 id="detailNama" class="mb-3"></h4>
                        <p><strong>Penyelenggara:</strong> <span id="detailPenyelenggara"></span></p>
                        <p><strong>Ormawa:</strong> <span id="detailOrmawa" class="badge bg-primary"></span></p>
                        <p><strong>Deadline:</strong> <span id="detailDeadline">–</span></p>
                        <h5 class="mt-3"><i class="fas fa-align-left me-2"></i>Deskripsi</h5>
                        <p id="detailDeskripsi" class="text-muted" style="white-space: pre-line;"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editBeasiswaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Beasiswa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBeasiswaForm" method="POST" enctype="multipart/form-data" action="../../../Function/BeasiswaFunction.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-body">
                    <?php if ($user_level === 1): ?>
                        <!-- SuperAdmin: Bisa ganti ormawa -->
                        <div class="mb-3">
                            <label class="form-label">Ormawa Penyelenggara</label>
                            <select class="form-select" name="id_ormawa" id="edit_ormawa_id" required>
                                <?php foreach ($all_ormawa_list as $o): ?>
                                    <option value="<?= (int)$o['id']; ?>"><?= htmlspecialchars($o['nama_ormawa']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <!-- Admin Ormawa: Tetap ormawa-nya -->
                        <input type="hidden" name="id_ormawa" value="<?= (int)$_SESSION['ormawa_id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Ormawa Penyelenggara</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['ormawa_nama']); ?>" readonly>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nama Beasiswa</label>
                        <input type="text" class="form-control" name="nama_beasiswa" id="edit_nama" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Penyelenggara</label>
                        <input type="text" class="form-control" name="penyelenggara" id="edit_penyelenggara" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deadline</label>
                        <input type="date" class="form-control" name="deadline" id="edit_deadline" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gambar Saat Ini</label><br>
                        <img id="edit_current_image" src="" width="120" class="img-thumbnail rounded">
                        <input type="hidden" name="gambar_lama" id="edit_gambar_lama">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ganti Gambar (Opsional)</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                        <div class="form-text text-muted">Format: JPG, PNG (maks 2MB)</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File Panduan Saat Ini</label><br>
                        <a id="edit_current_file" href="#" target="_blank" class="text-decoration-none d-none">
                            <i class="fas fa-file-pdf me-1"></i> Lihat Panduan
                        </a>
                        <input type="hidden" name="file_lama" id="edit_file_lama">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ganti File Panduan (Opsional)</label>
                        <input type="file" class="form-control" name="file_panduan" accept=".pdf,.doc,.docx">
                        <div class="form-text text-muted">Format: PDF, DOC (maks 10MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../FormData/TambahBeasiswa.php'); ?>

<?php include('Footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const UPLOAD_PATH = '../../../Uploads/beasiswa/';

/**
 * Logika Filter dan Pencarian Client-Side
 */
function filterBeasiswa() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const tableBody = document.getElementById('tableBodyBeasiswa');
    const rows = tableBody.getElementsByTagName('tr');
    let visibleRowCount = 0;

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        
        if (row.id === 'noResults' || row.classList.contains('default-no-data')) continue;

        const searchTextData = row.getAttribute('data-search') || '';
        const searchMatch = searchTextData.includes(searchInput);

        if (searchMatch) {
            row.style.display = '';
            visibleRowCount++;
        } else {
            row.style.display = 'none';
        }
    }

    const noResultsRow = document.getElementById('noResults');
    const defaultNoDataRow = document.querySelector('.default-no-data');

    if (visibleRowCount === 0) {
        if (defaultNoDataRow) {
            noResultsRow.classList.add('d-none');
        } else {
            noResultsRow.classList.remove('d-none');
        }
    } else {
        noResultsRow.classList.add('d-none');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Notifikasi SweetAlert dari SESSION
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

    // 2. Logic Detail Beasiswa
    document.querySelectorAll('.detail-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const nama = this.getAttribute('data-nama');
            const penyelenggara = this.getAttribute('data-penyelenggara');
            const deadline = this.getAttribute('data-deadline');
            const deskripsi = this.getAttribute('data-deskripsi');
            const gambar = this.getAttribute('data-gambar');
            const file = this.getAttribute('data-file');
            const ormawa = this.getAttribute('data-ormawa');

            document.getElementById('detailNama').textContent = nama;
            document.getElementById('detailPenyelenggara').textContent = penyelenggara;
            document.getElementById('detailOrmawa').textContent = ormawa;
            document.getElementById('detailDeadline').textContent = 
                deadline ? new Date(deadline).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '–';
            document.getElementById('detailDeskripsi').textContent = deskripsi;

            const imgDisplay = document.getElementById('detailGambar');
            imgDisplay.src = gambar ? UPLOAD_PATH + encodeURIComponent(gambar) : 'https://via.placeholder.com/300x200?text=No+Image';

            const fileLink = document.getElementById('detailFilePanduan');
            if (file) {
                fileLink.href = UPLOAD_PATH + encodeURIComponent(file);
                fileLink.classList.remove('d-none');
            } else {
                fileLink.classList.add('d-none');
            }

            const detailModal = new bootstrap.Modal(document.getElementById('detailBeasiswaModal'));
            detailModal.show();
        });
    });

    // 3. Logic Edit Beasiswa
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            const penyelenggara = this.getAttribute('data-penyelenggara');
            const deadline = this.getAttribute('data-deadline');
            const deskripsi = this.getAttribute('data-deskripsi');
            const gambar = this.getAttribute('data-gambar');
            const file = this.getAttribute('data-file');
            const ormawaId = this.getAttribute('data-ormawa-id');

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_penyelenggara').value = penyelenggara;
            document.getElementById('edit_deadline').value = deadline;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('edit_gambar_lama').value = gambar;
            document.getElementById('edit_file_lama').value = file;

            <?php if ($user_level === 1): ?>
                document.getElementById('edit_ormawa_id').value = ormawaId;
            <?php endif; ?>

            // Tampilkan preview gambar
            const imgDisplay = document.getElementById('edit_current_image');
            imgDisplay.src = gambar ? UPLOAD_PATH + encodeURIComponent(gambar) : 'https://via.placeholder.com/120?text=No+Image';

            // Tampilkan link file panduan
            const fileLink = document.getElementById('edit_current_file');
            if (file) {
                fileLink.href = UPLOAD_PATH + encodeURIComponent(file);
                fileLink.textContent = '📄 ' + file;
                fileLink.classList.remove('d-none');
            } else {
                fileLink.classList.add('d-none');
            }

            const editModal = new bootstrap.Modal(document.getElementById('editBeasiswaModal'));
            editModal.show();
        });
    });

    // 4. Logic Hapus Beasiswa dengan SweetAlert2
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama') || 'beasiswa ini';

            Swal.fire({
                title: 'Yakin hapus beasiswa?',
                html: `Anda akan menghapus beasiswa <strong>"${nama}"</strong> secara permanen.`,
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

                    window.location.href = `../../../Function/BeasiswaFunction.php?action=delete&id=${id}`;
                }
            });
        });
    });
    
    // Inisialisasi filter saat halaman dimuat
    filterBeasiswa();
});
</script>

<?php mysqli_close($koneksi); ?>