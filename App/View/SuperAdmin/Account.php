<?php
include('Header.php');
include('../../../Config/ConnectDB.php');

// ✅ Pastikan SuperAdmin
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] != 1) {
    $_SESSION['error'] = "Akses ditolak.";
    header("Location: Login.php");
    exit();
}

// Helper: konversi level ke label
function getLevelLabel($level) {
    return match ((int)$level) {
        2 => '<span class="badge bg-danger">Admin</span>',
        3 => '<span class="badge bg-warning text-dark">Pengurus</span>',
        4 => '<span class="badge bg-info">Mahasiswa</span>',
        default => '<span class="badge bg-secondary">Lainnya</span>'
    };
}

// Ambil filter level dari URL
$filter_level = $_GET['level'] ?? '';

// Bangun WHERE clause
$where = "u.level IN (2,3,4) AND u.id_ormawa IS NOT NULL AND u.id_ormawa > 0";
if ($filter_level === '2') $where .= " AND u.level = 2";
if ($filter_level === '3') $where .= " AND u.level = 3";
if ($filter_level === '4') $where .= " AND u.level = 4";

$sql = "
    SELECT 
        u.id,
        u.full_name,
        u.nim,
        u.email,
        u.level,
        u.id_ormawa,
        u.created_at,
        o.nama_ormawa
    FROM user u
    LEFT JOIN ormawa o ON u.id_ormawa = o.id
    WHERE $where
    ORDER BY u.level ASC, u.full_name ASC
";

$result = mysqli_query($koneksi, $sql);
$accounts = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users"></i> Manajemen Akun Semua Ormawa
        </h1>
        <button class="btn btn-success btn-icon-split" 
                data-bs-toggle="modal" 
                data-bs-target="#tambahAccountModal"
                onclick="resetAccountForm()">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Tambah Akun</span>
        </button>
    </div>

    <!-- 🔧 Filter Level (Dropdown SEPERTI YANG ANDA MINTA) -->
    <div class="row mb-3">
        <div class="col-md-3">
            <select class="form-select" id="filterJenis">
                <option value="">Semua</option>
                <option value="2" <?= $filter_level === '2' ? 'selected' : ''; ?>>Admin</option>
                <option value="3" <?= $filter_level === '3' ? 'selected' : ''; ?>>Pengurus</option>
                <option value="4" <?= $filter_level === '4' ? 'selected' : ''; ?>>Mahasiswa</option>
            </select>
        </div>
    </div>

    <!-- 🔍 Search Box -->
    <div class="mb-3">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="searchInput" class="form-control" 
                   placeholder="Cari nama, NIM, email, atau ormawa...">
        </div>
        <small class="text-muted">Pencarian berdasarkan nama, NIM, email, atau ormawa.</small>
    </div>

    <!-- DataTables -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Daftar Akun
                <span class="badge bg-primary ms-2"><?= count($accounts); ?> Akun</span>
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($accounts)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-user-plus fa-3x mb-3"></i>
                    <p class="mb-0">Tidak ada akun yang sesuai filter.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nama Lengkap</th>
                                <th>NIM</th>
                                <th>Level</th>
                                <th>Ormawa</th>
                                <th>Email</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="accountsTableBody">
                            <?php $no = 1; foreach ($accounts as $acc): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="search-col"><?= htmlspecialchars($acc['full_name']); ?></td>
                                    <td class="search-col"><?= !empty($acc['nim']) ? '<code>' . htmlspecialchars($acc['nim']) . '</code>' : '–'; ?></td>
                                    <td><?= getLevelLabel($acc['level']); ?></td>
                                    <td class="search-col"><?= htmlspecialchars($acc['nama_ormawa'] ?? '–'); ?></td>
                                    <td class="search-col"><?= htmlspecialchars($acc['email']); ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="far fa-calendar"></i> <?= date('d M Y', strtotime($acc['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if (in_array($acc['level'], [2, 3, 4])): ?>
                                            <button class="btn btn-warning btn-circle btn-sm edit-btn"
                                                data-id="<?= (int)$acc['id']; ?>"
                                                data-nama="<?= htmlspecialchars($acc['full_name']); ?>"
                                                data-nim="<?= htmlspecialchars($acc['nim'] ?? ''); ?>"
                                                data-email="<?= htmlspecialchars($acc['email']); ?>"
                                                data-level="<?= (int)$acc['level']; ?>"
                                                data-ormawa-id="<?= (int)$acc['id_ormawa']; ?>"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-circle btn-sm delete-btn" 
                                                    data-id="<?= (int)$acc['id']; ?>"
                                                    data-nama="<?= htmlspecialchars($acc['full_name']); ?>"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">–</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div id="noResultsMessage" class="text-center text-muted d-none py-3">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p>Tidak ada akun yang sesuai.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../FormData/TambahAccount.php'); ?>
<?php include('Footer.php'); ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 🔧 Filter: ganti URL saat pilih dropdown
    document.getElementById('filterJenis').addEventListener('change', function() {
        const level = this.value;
        const url = new URL(window.location);
        if (level) {
            url.searchParams.set('level', level);
        } else {
            url.searchParams.delete('level');
        }
        window.location.href = url.toString();
    });

    // 🔍 Search real-time
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('accountsTableBody');
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

    // Notifikasi
    <?php if (!empty($_SESSION['success'])): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: '<?= addslashes($_SESSION['success']); ?>', timer: 2000, showConfirmButton: false });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        Swal.fire({ icon: 'error', title: 'Gagal!', text: '<?= addslashes($_SESSION['error']); ?>', timer: 3500, showConfirmButton: true });
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    // Hapus
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            Swal.fire({
                title: 'Hapus Akun?',
                html: `<p>Akun <strong>${nama}</strong> akan dihapus permanen.</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.href = `../../../Function/AccountFunction.php?action=delete&id=${id}`;
                }
            });
        });
    });

    // Edit
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('formAction').value = 'edit';
            document.getElementById('editId').value = this.getAttribute('data-id');
            document.getElementById('nama').value = this.getAttribute('data-nama');
            document.getElementById('nim').value = this.getAttribute('data-nim');
            document.getElementById('email').value = this.getAttribute('data-email');
            
            const level = this.getAttribute('data-level');
            const ormawaId = this.getAttribute('data-ormawa-id');
            
            // Atur level & ormawa jika ada select
            const levelSelect = document.getElementById('level');
            const ormawaSelect = document.getElementById('ormawa_id');
            if (levelSelect) levelSelect.value = level;
            if (ormawaSelect) ormawaSelect.value = ormawaId;

            document.getElementById('password').value = '';
            document.getElementById('password').removeAttribute('required');
            document.getElementById('formTitle').textContent = 'Edit Akun';
            const help = document.getElementById('passwordHelp');
            if (help) help.textContent = 'Kosongkan jika tidak ingin mengganti password.';

            new bootstrap.Modal(document.getElementById('tambahAccountModal')).show();
        });
    });
});
</script>

<?php mysqli_close($koneksi); ?>