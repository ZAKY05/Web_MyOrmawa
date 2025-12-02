<?php
// JANGAN panggil session_start() di sini — pastikan sudah dijalankan di halaman utama

// Ambil dari session — fallback ke Guest jika belum login atau nilai session null
// Menggunakan isset untuk mengecek keberadaan, lalu ?? untuk menghindari null ke htmlspecialchars
$user_nama = isset($_SESSION['user_nama']) ? htmlspecialchars($_SESSION['user_nama'] ?? 'Guest', ENT_QUOTES, 'UTF-8') : 'Guest';
$ormawa_nama = isset($_SESSION['ormawa_nama']) ? htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Kemahasiswaan', ENT_QUOTES, 'UTF-8') : 'Kemahasiswaan';
$user_id = $_SESSION['user_id'] ?? null; // Ambil user_id dari session
$user_level = $_SESSION['user_level'] ?? null; // Ambil user_level dari session jika ada

// Ganti path ke file koneksi Anda -> ../../../Config/ConnectDB.php -> Menggunakan include
include '../../../Config/ConnectDB.php';

$full_name = 'Guest';
$nim = '';
$email = '';
$program_studi = '';
$angkatan = '';
$level_nama = '';
// Perbaiki path logo default dan logo ormawa
$logo_ormawa = '../../../Asset/Img/gw.jpg'; // Default image
$nama_ormawa_detail = '';

if ($user_id) {
    $query = "
        SELECT
            u.full_name,
            u.nim,
            u.email,
            u.program_studi,
            u.angkatan,
            u.level,
            o.nama_ormawa,
            o.logo AS logo_ormawa
        FROM user u
        LEFT JOIN ormawa o ON u.id_ormawa = o.id
        WHERE u.id = ?
    ";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($result);

    if ($user_data) {
        $full_name = htmlspecialchars($user_data['full_name'], ENT_QUOTES, 'UTF-8');
        $nim = htmlspecialchars($user_data['nim'], ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($user_data['email'], ENT_QUOTES, 'UTF-8');
        $level = $user_data['level'];
        $nama_ormawa_detail = $user_data['nama_ormawa'] ? htmlspecialchars($user_data['nama_ormawa'], ENT_QUOTES, 'UTF-8') : 'Tidak Terdaftar di Ormawa';
        // Perbaiki path logo ormawa -> uploads/logos/
        $logo_ormawa = $user_data['logo_ormawa'] ? '../../../uploads/logos/' . htmlspecialchars($user_data['logo_ormawa'], ENT_QUOTES, 'UTF-8') : '../../../Asset/Img/gw.jpg';

        // Mapping level
        $level_names = [
            '1' => 'Super Admin',
            '2' => 'Admin Ormawa',
            '3' => 'User Level 3',
            '4' => 'User Level 4'
        ];
        $level_nama = $level_names[$level] ?? 'Level Tidak Dikenal';
    }
}

// Tangani pesan dari UpdateProfile.php
$message = $_SESSION['profile_message'] ?? '';
$message_type = $_SESSION['profile_message_type'] ?? '';
$field_error = $_SESSION['field_error'] ?? ''; // Ambil field error dari session
// Hapus session setelah dibaca (penting agar tidak muncul lagi di halaman lain)
if (isset($_SESSION['profile_message'])) {
    unset($_SESSION['profile_message']);
}
if (isset($_SESSION['profile_message_type'])) {
    unset($_SESSION['profile_message_type']);
}
// Jangan hapus field_error dulu, kita butuh di JavaScript nanti, hapus setelah SweetAlert muncul
?>

<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Search -->
    <!-- ... (jika ada) ... -->

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">


        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="text-right mr-2 d-none d-lg-block">
                    <span class="d-block text-gray-600 small">
                        <?php echo $user_nama; ?>
                    </span>
                    <?php if (!empty($ormawa_nama)): ?>
                    <span class="d-block text-gray-500 small" style="font-size: 0.75rem;">
                        <b><?php echo $ormawa_nama; ?></b>
                    </span>
                    <?php endif; ?>
                </div>
                <img class="img-profile rounded-circle" src="../../../Asset/Img/gw.jpg" alt="Profile">
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <!-- Trigger Modal -->
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#profileModal">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>

</nav>

<!-- Modal Profil dan Ganti Password -->
<div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> <!-- Gunakan modal-lg untuk ukuran lebih besar -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileModalLabel">Profil Saya</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs" id="profileTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true">Informasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="password-tab" data-toggle="tab" href="#password" role="tab" aria-controls="password" aria-selected="false">Ganti Password</a>
                    </li>
                </ul>
                <!-- Tab Content -->
                <div class="tab-content mt-3" id="profileTabContent">
                    <!-- Tab Informasi -->
                    <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img src="<?php echo $logo_ormawa; ?>" class="img-profile rounded-circle mb-2" alt="Profil/Logo Ormawa" style="width: 150px; height: 150px; object-fit: cover;">
                                <h5 class="font-weight-bold"><?php echo $full_name; ?></h5>
                                <p class="text-muted"><?php echo $nama_ormawa_detail; ?></p>
                            </div>
                            <div class="col-md-8">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Nama Lengkap:</th>
                                        <td><?php echo $full_name; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td><?php echo $email; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Level:</th>
                                        <td><?php echo $level_nama; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Tab Ganti Password -->
                    <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                        <!-- Form submit ke UpdateProfile.php secara langsung -->
                        <form id="changePasswordForm" method="post" action="../../../Function/UpdateProfile.php">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-group">
                                <label for="current_password">Password Lama</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="toggleCurrentPassword" style="cursor: pointer;">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                <div id="current_password_error" class="invalid-feedback"></div> <!-- Tempat pesan error field spesifik (opsional) -->
                            </div>
                            <div class="form-group">
                                <label for="new_password">Password Baru</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="toggleNewPassword" style="cursor: pointer;">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                <div id="new_password_error" class="invalid-feedback"></div> <!-- Tempat pesan error field spesifik (opsional) -->
                            </div>
                            <div class="form-group">
                                <label for="confirm_new_password">Konfirmasi Password Baru</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="toggleConfirmNewPassword" style="cursor: pointer;">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                <div id="confirm_new_password_error" class="invalid-feedback"></div> <!-- Tempat pesan error field spesifik (opsional) -->
                            </div>
                            <button type="submit" class="btn btn-primary">Ubah Password</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Logout -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin logout dan meninggalkan halaman ini?
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                <a class="btn btn-primary" href="../../../Function/LoginFunction.php?logout=true"> <!-- Perbaiki path logout -->
                    <i class="fas fa-sign-out-alt fa-sm"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Style CSS untuk field error (jika belum ada di halaman utama) -->
<style>
.form-control.is-invalid {
    border-color: #dc3545; /* Warna border merah bootstrap */
    padding-right: calc(1.5em + 0.75rem); /* Sesuaikan padding jika ikon toggle memengaruhi */
    background-image: url("image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e"); /* Ikon error bawaan bootstrap */
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}
</style>

<!-- Sertakan SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- Sertakan SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Script untuk mengosongkan form, toggle visibility password, menandai field error, dan SweetAlert -->
<script>
// Fungsi untuk mengosongkan form dan menghapus kelas error
function clearPasswordFormAndErrors() {
    document.getElementById('changePasswordForm').reset();
    // Hapus kelas error dari semua input
    document.querySelectorAll('#changePasswordForm .form-control').forEach(input => {
        input.classList.remove('is-invalid');
    });
    // Hapus pesan error field spesifik
    document.querySelectorAll('#changePasswordForm .invalid-feedback').forEach(feedback => {
        feedback.innerHTML = '';
    });
}

// Event listener saat modal ditutup
document.getElementById('profileModal').addEventListener('hidden.bs.modal', function () {
    // Kosongkan form dan hapus error saat modal ditutup (jika sukses sebelumnya atau jika ingin selalu kosong)
    clearPasswordFormAndErrors();
});

// Jika modal dibuka kembali, pastikan form tetap kosong jika tidak ada pesan error
document.getElementById('profileModal').addEventListener('shown.bs.modal', function () {
    // Hapus error class dan pesan saat modal dibuka kembali
    clearPasswordFormAndErrors();
});

// Fungsi toggle visibility password
function setupPasswordToggle(inputId, toggleId) {
    const passwordInput = document.getElementById(inputId);
    const toggleElement = document.getElementById(toggleId);

    toggleElement.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        // Ganti ikon mata
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
}

// Setup untuk semua input password
document.addEventListener('DOMContentLoaded', function() {
    setupPasswordToggle('current_password', 'toggleCurrentPassword');
    setupPasswordToggle('new_password', 'toggleNewPassword');
    setupPasswordToggle('confirm_new_password', 'toggleConfirmNewPassword');

    // Ambil pesan dari PHP
    const message = "<?php echo addslashes($message); ?>"; // Gunakan addslashes untuk menghindari masalah jika pesan mengandung kutip
    const messageType = "<?php echo $message_type; ?>";
    const fieldError = "<?php echo $field_error; ?>";

    // Jika ada pesan, tampilkan SweetAlert
    if (message) {
        let icon = 'info'; // Default
        if (messageType === 'success') {
            icon = 'success';
        } else if (messageType === 'error') {
            icon = 'error';
        }

        Swal.fire({
            title: messageType === 'success' ? 'Berhasil!' : 'Gagal!',
            text: message,
            icon: icon,
            confirmButtonText: 'OK'
        }).then(() => {
            // Hapus session field_error setelah SweetAlert ditutup jika pesan error ditampilkan
            if (messageType === 'error' && fieldError && fieldError !== 'general') {
                // Tandai field error di sini
                const errorInput = document.getElementById(fieldError);
                if (errorInput) {
                    errorInput.classList.add('is-invalid');
                    // Bisa tambahkan pesan spesifik jika diinginkan
                    // const errorFeedback = document.getElementById(fieldError + '_error');
                    // errorFeedback.innerHTML = message; // Gunakan pesan umum sebagai contoh
                }
            }
            // Jika pesan sukses, kosongkan form
            if (messageType === 'success') {
                clearPasswordFormAndErrors();
            }
        });
    } else {
        // Jika tidak ada pesan umum, tetapi ada field_error (misalnya dari validasi JS sebelum dikirim), tandai fieldnya
        if (fieldError && fieldError !== 'general') {
            const errorInput = document.getElementById(fieldError);
            if (errorInput) {
                errorInput.classList.add('is-invalid');
            }
        }
    }
});
</script>