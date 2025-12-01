<?php
include('Header.php');
include('../../../Config/ConnectDB.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_user_id = $_SESSION['user']['id'] ?? 0;
$current_level   = $_SESSION['user']['level'] ?? 0;

function getAccountMember($koneksi, $id_ormawa) {
    $sql = "SELECT id, full_name, nim, email, password, level 
            FROM user 
            WHERE id_ormawa = ? AND level = 3 
            ORDER BY full_name ASC";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("i", $id_ormawa);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    return $data;
}

if ($current_level == 2) {
    $accounts = getAccountMember($koneksi, $current_user_id);
} else {
    $accounts = [];
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Anggota Ormawa</h1>
        <button class="btn btn-success btn-icon-split" data-bs-toggle="modal" data-bs-target="#tambahAccountModal" onclick="resetAccountForm()">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Tambah Anggota</span>
        </button>
    </div>

    <!-- SweetAlert notif dari session (akan di-handle via JS di bawah) -->
    <!-- Hapus alert biasa, ganti oleh SweetAlert -->

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Anggota Anda</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIM</th>
                            <th>Email</th>
                            <th>Password (Encrypted)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($accounts)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($accounts as $acc): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($acc['full_name']); ?></td>
                                    <td><?= htmlspecialchars($acc['nim']); ?></td>
                                    <td><?= htmlspecialchars($acc['email']); ?></td>
                                    <td style="word-break: break-all; font-size: 0.8em;">
                                        <?= htmlspecialchars(substr($acc['password'], 0, 10)) . '...'; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-warning btn-circle btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#tambahAccountModal"
                                            onclick='editAccount(
                                                <?= (int)$acc['id'] ?>,
                                                <?= json_encode(htmlspecialchars($acc['full_name'])) ?>,
                                                <?= json_encode(htmlspecialchars($acc['nim'])) ?>,
                                                <?= json_encode(htmlspecialchars($acc['email'])) ?>
                                            )'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-circle btn-sm delete-btn" data-id="<?= (int)$acc['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data anggota untuk Ormawa ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('../FormData/TambahAccount.php'); ?>
<?php include('Footer.php'); ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Tampilkan notifikasi dari query string (karena AccountFunction.php pakai redirect dengan ?success=...)
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);

    // Notifikasi sukses
    if (urlParams.has('success')) {
        let msg = '';
        switch(urlParams.get('success')) {
            case 'anggota_ditambah':
                msg = 'Akun anggota berhasil ditambahkan!';
                break;
            case 'anggota_diperbarui':
                msg = 'Data akun berhasil diperbarui!';
                break;
            default:
                msg = 'Operasi berhasil!';
        }
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: msg,
            timer: 2000,
            showConfirmButton: false
        });
        // Bersihkan URL tanpa reload
        history.replaceState(null, '', window.location.pathname);
    }

    // Notifikasi error
    if (urlParams.has('error')) {
        let msg = '';
        switch(urlParams.get('error')) {
            case 'form_kosong':
                msg = 'Pastikan semua field wajib diisi!';
                break;
            case 'akses_ditolak':
                msg = 'Anda tidak berhak mengedit data ini.';
                break;
            case 'gagal_hapus':
                msg = 'Gagal menghapus data. Pastikan data milik Anda.';
                break;
            case 'gagal_update':
                msg = 'Gagal memperbarui data.';
                break;
            default:
                msg = 'Terjadi kesalahan. Silakan coba lagi.';
        }
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: msg,
            timer: 3000,
            showConfirmButton: false
        });
        history.replaceState(null, '', window.location.pathname);
    }

    // Notifikasi hapus
    if (urlParams.has('deleted') && urlParams.get('deleted') === 'berhasil') {
        Swal.fire({
            icon: 'success',
            title: 'Dihapus!',
            text: 'Akun anggota berhasil dihapus.',
            timer: 2000,
            showConfirmButton: false
        });
        history.replaceState(null, '', window.location.pathname);
    }

    // === Hapus dengan SweetAlert ===
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');

            Swal.fire({
                title: 'Yakin hapus akun ini?',
                text: "Data login dan akses akan ikut terhapus permanen!",
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

                    fetch(`../../../Function/AccountFunction.php?action=delete&id=${id}`)
                    .then(response => {
                        if (response.redirected) {
                            // Redirect ke URL baru (akan trigger notif via query string di atas)
                            window.location.href = response.url;
                        } else {
                            Swal.fire('Gagal!', 'Tidak dapat menghapus akun.', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error!', 'Terjadi kesalahan jaringan.', 'error');
                    });
                }
            });
        });
    });
});
</script>

<?php mysqli_close($koneksi); ?>