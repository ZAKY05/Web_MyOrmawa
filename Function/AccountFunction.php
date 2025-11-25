<?php
// Pastikan session start untuk ambil ID Ormawa yang sedang login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../Config/ConnectDB.php';

// Ambil ID Ormawa yang sedang login (PENTING)
$current_ormawa_id = $_SESSION['user']['id'] ?? 0;

// Keamanan: Jika tidak ada ID login, tolak akses
if ($current_ormawa_id == 0) {
    header("Location: ../SuperAdmin/Login.php"); // Atau halaman error
    exit;
}

// --- FUNGSI: Menambah Anggota ---
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama     = trim($_POST['full_name'] ?? '');
    $nim      = trim($_POST['nim'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nama) || empty($nim) || empty($email) || empty($password)) {
        header("Location: ../App/View/SuperAdmin/Account.php?error=form_kosong");
        exit;
    }

    // Cek Email Duplikat (Opsional tapi disarankan)
    // ... code validasi email ...

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // INSERT DATA: Perhatikan kolom id_ormawa diisi $current_ormawa_id
    // Level di set 3 (Anggota)
    $stmt = $koneksi->prepare("INSERT INTO user (full_name, nim, email, password, level, id_ormawa) VALUES (?, ?, ?, ?, 3, ?)");
    
    if ($stmt) {
        $stmt->bind_param("ssssi", $nama, $nim, $email, $password_hash, $current_ormawa_id);
        $stmt->execute();
        $stmt->close();

        header("Location: ../App/View/Admin/Account.php?success=anggota_ditambah");
        exit;
    } else {
        header("Location: ../App/View/SuperAdmin/Account.php?error=query_gagal");
        exit;
    }
}

// --- FUNGSI: Edit Anggota ---
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id       = (int)($_POST['id'] ?? 0);
    $nama     = trim($_POST['full_name'] ?? '');
    $nim      = trim($_POST['nim'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi Keamanan: Pastikan yang diedit adalah anggota milik Ormawa ini
    // Cek apakah user ID tersebut memiliki id_ormawa = login ID
    $check = $koneksi->prepare("SELECT id FROM user WHERE id = ? AND id_ormawa = ?");
    $check->bind_param("ii", $id, $current_ormawa_id);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
         header("Location: ../App/View/SuperAdmin/Account.php?error=akses_ditolak");
         exit;
    }
    $check->close();

    // Logic update sama seperti sebelumnya...
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("UPDATE user SET full_name = ?, nim = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nama, $nim, $email, $password_hash, $id);
    } else {
        $stmt = $koneksi->prepare("UPDATE user SET full_name = ?, nim = ?, email = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nama, $nim, $email, $id);
    }
    
    if ($stmt) {
        $stmt->execute();
        header("Location: ../App/View/SuperAdmin/Account.php?success=anggota_diperbarui");
    } else {
        header("Location: ../App/View/SuperAdmin/Account.php?error=gagal_update");
    }
    exit;
}

// --- FUNGSI: Hapus Anggota ---
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);

    // Keamanan: Hanya hapus jika id_ormawa cocok dengan yang login
    $stmt = $koneksi->prepare("DELETE FROM user WHERE id = ? AND id_ormawa = ?");
    $stmt->bind_param("ii", $id, $current_ormawa_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: ../App/View/SuperAdmin/Account.php?deleted=berhasil");
    } else {
        // Jika tidak ada yg terhapus, mungkin ID salah atau mencoba hapus punya ormawa lain
        header("Location: ../App/View/SuperAdmin/Account.php?error=gagal_hapus");
    }
    exit;
}
?>