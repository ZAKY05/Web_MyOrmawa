<?php
include('Header.php');
include('../../../Config/ConnectDB.php');

$user_level = $_SESSION['user_level'] ?? 0;
$ormawa_id = $_SESSION['ormawa_id'] ?? 0;

// Query: ambil data dokumen
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

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-alt me-2"></i>
            Arsip Dokumen <?php echo htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ormawa'); ?>
        </h1>
        <button type="button" class="btn btn-success btn-icon-split" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Tambah Dokumen</span>
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
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-folder-open me-2"></i>Daftar Dokumen
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <?php if ($user_level === 1) : ?><th>Ormawa</th><?php endif; ?>
                            <th>Jenis</th>
                            <th>Nama Dokumen</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) : ?>
                                <tr data-jenis="<?= htmlspecialchars($row['jenis_dokumen']) ?>" data-nama="<?= htmlspecialchars(strtolower($row['nama_dokumen'])) ?>">
                                    <td><?= $no++; ?></td>
                                    <?php if ($user_level === 1) : ?>
                                        <td><?= htmlspecialchars($row['nama_ormawa'] ?? '–') ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <span class="badge bg-<?= match ($row['jenis_dokumen']) {
                                                                    'Proposal' => 'primary',
                                                                    'SPJ' => 'success',
                                                                    'LPJ' => 'info',
                                                                    default => 'secondary'
                                                                } ?>">
                                            <?= htmlspecialchars($row['jenis_dokumen']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($row['nama_dokumen']) ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal_upload'])) ?></td>
                                    <td>
                                        <!-- Preview -->
                                        <a href="../../../uploads/dokumen/<?= urlencode($row['file_path']) ?>" 
                                           target="_blank" 
                                           class="btn btn-info btn-circle btn-sm" 
                                           title="Preview dokumen"
                                           onclick="return openDocument(event, '<?= addslashes($row['file_path']) ?>')">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Unduh -->
                                        <a href="../../../uploads/dokumen/<?= urlencode($row['file_path']) ?>" 
                                           class="btn btn-success btn-circle btn-sm" 
                                           title="Unduh">
                                            <i class="fas fa-download"></i>
                                        </a>

                                        <!-- Edit -->
                                        <button class="btn btn-warning btn-circle btn-sm edit-btn"
                                            data-id="<?= (int)$row['id'] ?>"
                                            data-nama="<?= htmlspecialchars($row['nama_dokumen'], ENT_QUOTES) ?>"
                                            data-jenis="<?= htmlspecialchars($row['jenis_dokumen'], ENT_QUOTES) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Hapus -->
                                        <button class="btn btn-danger btn-circle btn-sm delete-btn" data-id="<?= (int)$row['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// === 1. Notifikasi dari SESSION (setelah redirect) ===
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

    // === 2. Filter & Search Real-time ===
    const searchInput = document.getElementById('searchInput');
    const filterJenis = document.getElementById('filterJenis');
    const rows = document.querySelectorAll('#dataTable tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const jenisFilter = filterJenis.value;

        rows.forEach(row => {
            const nama = row.dataset.nama || '';
            const jenis = row.dataset.jenis || '';

            const matchesSearch = searchTerm === '' || nama.includes(searchTerm);
            const matchesJenis = jenisFilter === '' || jenis === jenisFilter;

            row.style.display = (matchesSearch && matchesJenis) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterTable);
    filterJenis.addEventListener('change', filterTable);

    // === 3. Hapus dengan SweetAlert ===
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');

            Swal.fire({
                title: 'Yakin hapus dokumen?',
                text: "File akan dihapus permanen dari server!",
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
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch(`../../../Function/DocumentFunction.php?action=delete&id=${id}`)
                    .then(response => {
                        if (response.redirected) {
                            window.location.href = response.url;
                        } else {
                            Swal.fire('Gagal!', 'Tidak dapat menghapus dokumen.', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error!', 'Terjadi kesalahan jaringan.', 'error');
                    });
                }
            });
        });
    });

    // === 4. Edit: isi modal ===
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            const jenis = this.getAttribute('data-jenis');

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_jenis').value = jenis;

            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        });
    });
});

// === Fungsi Preview File ===
function openDocument(e, filePath) {
    const ext = filePath.split('.').pop().toLowerCase();
    const supported = ['pdf', 'jpg', 'jpeg', 'png', 'txt'];

    if (!supported.includes(ext)) {
        e.preventDefault();
        Swal.fire({
            icon: 'info',
            title: 'Preview Tidak Didukung',
            html: `File <strong>${ext.toUpperCase()}</strong> tidak bisa dipreview langsung.<br>
                   Ingin mengunduh saja?`,
            showCancelButton: true,
            confirmButtonText: 'Ya, Unduh',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const link = document.createElement('a');
                link.href = '../../../uploads/dokumen/' + encodeURIComponent(filePath);
                link.download = filePath;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
        return false;
    }
    return true; // Biarkan buka di tab baru
}
</script>

<?php include('Footer.php'); ?>
<?php mysqli_close($koneksi); ?>