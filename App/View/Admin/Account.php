<?php
include('Header.php');
include('../../../Config/ConnectDB.php');

// Ambil data dari session
$ormawa_id = $_SESSION['ormawa_id'] ?? 0;
$user_level = $_SESSION['user_level'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;

if ($ormawa_id <= 0) {
    die("<div class='container mt-5 text-center'><h3 class='text-danger'>Error: ORMAWA tidak valid.</h3><p>Silakan login sebagai Admin ORMAWA.</p><a href='../SuperAdmin/Login.php' class='btn btn-primary'>Kembali ke Login</a></div>");
}
if ($user_level != 2) {
    die("<div class='container mt-5 text-center'><h3 class='text-warning'>Akses Ditolak</h3><p>Hanya Admin ORMAWA yang dapat mengelola akun anggota.</p><a href='javascript:history.back()' class='btn btn-secondary'>Kembali</a></div>");
}

function getAccountMember($koneksi, $id_ormawa)
{
    $sql = "SELECT id, full_name, nim, email, level, created_at 
            FROM user 
            WHERE id_ormawa = ? AND level IN (3,4) 
            ORDER BY full_name ASC";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("i", $id_ormawa);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$accounts = getAccountMember($koneksi, $ormawa_id);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users"></i> Daftar Akun Anggota
        </h1>
        <button class="btn btn-success btn-icon-split" data-bs-toggle="modal" data-bs-target="#tambahAccountModal"
            onclick="resetAccountForm()">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Tambah Anggota</span>
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Data Akun Anggota
                <?= htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Anda'); ?>
                <span class="badge bg-primary ms-2">
                    <?= count($accounts); ?> Anggota
                </span>
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($accounts)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-user-plus fa-3x mb-3"></i>
                    <p class="mb-0">Belum ada akun anggota terdaftar.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Nama Lengkap</th>
                                <th width="15%">NIM</th>
                                <th width="25%">Email</th>
                                <th width="10%">Role</th>
                                <th width="10%">Terdaftar</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            foreach ($accounts as $acc): ?>
                                <tr>
                                    <td>
                                        <?= $no++; ?>
                                    </td>
                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($acc['full_name']); ?>
                                        </strong>
                                        <br><small class="text-muted">ID:
                                            <?= $acc['id']; ?>
                                        </small>
                                    </td>
                                    <td><code><?= htmlspecialchars($acc['nim']); ?></code></td>
                                    <td>
                                        <?= htmlspecialchars($acc['email']); ?>
                                    </td>
                                    <td>
                                        <?php if ($acc['level'] == 3): ?>
                                            <span class="badge bg-warning text-dark">Pengurus</span>
                                        <?php elseif ($acc['level'] == 4): ?>
                                            <span class="badge bg-info">Mahasiswa</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <?= $acc['level']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="far fa-calendar"></i>
                                            <?= date('d M Y', strtotime($acc['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <button class="btn btn-warning btn-circle btn-sm edit-btn"
                                            data-id="<?= (int) $acc['id']; ?>"
                                            data-nama="<?= htmlspecialchars($acc['full_name']); ?>"
                                            data-nim="<?= htmlspecialchars($acc['nim']); ?>"
                                            data-email="<?= htmlspecialchars($acc['email']); ?>"
                                            data-level="<?= (int) $acc['level']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-circle btn-sm delete-btn"
                                            data-id="<?= (int) $acc['id']; ?>"
                                            data-nama="<?= htmlspecialchars($acc['full_name']); ?>" title="Hapus Anggota">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
    document.addEventListener('DOMContentLoaded', function () {
        // Notifikasi
        <?php if (!empty($_SESSION['success'])): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: '<?= addslashes($_SESSION['success']); ?>', timer: 2000, showConfirmButton: false });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            Swal.fire({ icon: 'error', title: 'Gagal!', text: '<?= addslashes($_SESSION['error']); ?>', timer: 3500, showConfirmButton: true });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        // ✅ Edit: kirim level — versi aman
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nama = this.getAttribute('data-nama');
                const nim = this.getAttribute('data-nim');
                const email = this.getAttribute('data-email');
                const level = this.getAttribute('data-level') || '3';

                // Reset & isi form
                const formAction = document.getElementById('formAction');
                const editId = document.getElementById('editId');
                const namaInput = document.getElementById('nama');
                const nimInput = document.getElementById('nim');
                const emailInput = document.getElementById('email');
                const levelSelect = document.getElementById('level');
                const passwordInput = document.getElementById('password');
                const formTitle = document.getElementById('formTitle');
                const passwordHelp = document.getElementById('passwordHelp');

                if (!formAction || !editId || !namaInput || !nimInput || !emailInput) {
                    console.error("Form element tidak ditemukan!");
                    return;
                }

                formAction.value = 'edit';
                editId.value = id;
                namaInput.value = nama;
                nimInput.value = nim;
                emailInput.value = email;

                // ✅ Isi level hanya jika ada
                if (levelSelect) {
                    levelSelect.value = level;
                } else {
                    console.warn("Select #level tidak ditemukan. Pastikan id='level' ada di form.");
                }

                if (passwordInput) {
                    passwordInput.value = '';
                    passwordInput.removeAttribute('required');
                }
                if (formTitle) formTitle.textContent = 'Edit Anggota';
                if (passwordHelp) passwordHelp.textContent = 'Kosongkan jika tidak ingin ganti password.';

                // Buka modal
                const modalEl = document.getElementById('tambahAccountModal');
                if (modalEl) {
                    new bootstrap.Modal(modalEl).show();
                } else {
                    console.error("Modal #tambahAccountModal tidak ditemukan!");
                }
            });
        });

        // Hapus
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const nama = this.getAttribute('data-nama');
                Swal.fire({
                    title: 'Yakin hapus?',
                    html: `<p>Akun <strong>${nama}</strong> akan dihapus permanen.</p>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`../../../Function/AccountFunction.php?action=delete&id=${id}`)
                            .then(() => window.location.reload());
                    }
                });
            });
        });
    });
</script>

<?php mysqli_close($koneksi); ?>