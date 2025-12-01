<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../Config/ConnectDB.php';

$current_ormawa_id = $_SESSION['user']['id'] ?? 0;

if ($current_ormawa_id == 0) {
    $_SESSION['error'] = "Session kadaluarsa. Silakan login ulang.";
    header("Location: ../SuperAdmin/Login.php");
    exit;
}

// Helper: Redirect ke halaman Account
function redirectAccount() {
    header("Location: ../App/View/Admin/Account.php");
    exit;
}

// --- TAMBAH ---
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama     = trim($_POST['full_name'] ?? '');
    $nim      = trim($_POST['nim'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nama) || empty($nim) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Semua field wajib diisi!";
        redirectAccount();
    }

    // Cek duplikat email (opsional tapi penting)
    $checkEmail = $koneksi->prepare("SELECT id FROM user WHERE email = ? AND id_ormawa = ?");
    $checkEmail->bind_param("si", $email, $current_ormawa_id);
    $checkEmail->execute();
    if ($checkEmail->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Email sudah terdaftar di ormawa ini.";
        redirectAccount();
    }
    $checkEmail->close();

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $koneksi->prepare("INSERT INTO user (full_name, nim, email, password, level, id_ormawa) VALUES (?, ?, ?, ?, 3, ?)");
    
    if ($stmt) {
        $stmt->bind_param("ssssi", $nama, $nim, $email, $password_hash, $current_ormawa_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Akun anggota berhasil ditambahkan!";
        } else {
            $_SESSION['error'] = "Gagal menyimpan data: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Gagal menyiapkan query.";
    }

    redirectAccount();
}

// --- EDIT ---
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id       = (int)($_POST['id'] ?? 0);
    $nama     = trim($_POST['full_name'] ?? '');
    $nim      = trim($_POST['nim'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if ($id <= 0 || empty($nama) || empty($nim) || empty($email)) {
        $_SESSION['error'] = "Data tidak valid.";
        redirectAccount();
    }

    // Validasi kepemilikan
    $check = $koneksi->prepare("SELECT id FROM user WHERE id = ? AND id_ormawa = ?");
    $check->bind_param("ii", $id, $current_ormawa_id);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        $_SESSION['error'] = "Anda tidak berhak mengedit akun ini.";
        redirectAccount();
    }
    $check->close();

    $password = $_POST['password'] ?? '';
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("UPDATE user SET full_name = ?, nim = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nama, $nim, $email, $password_hash, $id);
    } else {
        $stmt = $koneksi->prepare("UPDATE user SET full_name = ?, nim = ?, email = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nama, $nim, $email, $id);
    }

    if ($stmt && $stmt->execute()) {
        $_SESSION['success'] = "Data akun berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Gagal memperbarui data.";
    }
    redirectAccount();
}

// --- HAPUS ---
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id <= 0) {
        $_SESSION['error'] = "ID tidak valid.";
        redirectAccount();
    }

    $stmt = $koneksi->prepare("DELETE FROM user WHERE id = ? AND id_ormawa = ?");
    $stmt->bind_param("ii", $id, $current_ormawa_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Akun anggota berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus. Pastikan akun milik Anda.";
    }
    redirectAccount();
}

// Fallback
redirectAccount();
?>