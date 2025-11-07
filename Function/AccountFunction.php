<?php
include '../Config/ConnectDB.php';

// --- FUNGSI: Menambah Akun Ormawa ---
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama     = trim($_POST['nama'] ?? '');
    $nim      = trim($_POST['nim'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi wajib
    if (empty($nama) || empty($nim) || empty($username) || empty($email) || empty($password)) {
        header("Location: ../App/View/SuperAdmin/Account.php?error=form_kosong");
        exit;
    }

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../App/View/SuperAdmin/Account.php?error=email_invalid");
        exit;
    }

    // Cek apakah username sudah ada
    $stmt = $koneksi->prepare("SELECT id FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        header("Location: ../App/View/SuperAdmin/Account.php?error=username_duplikat");
        exit;
    }
    $stmt->close();

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Simpan ke database dengan level = 2 (Ormawa)
    $stmt = $koneksi->prepare("INSERT INTO user (nama, nim, username, email, password, level) VALUES (?, ?, ?, ?, ?, 2)");
    if ($stmt) {
        $stmt->bind_param("sssss", $nama, $nim, $username, $email, $password_hash);
        $stmt->execute();
        $stmt->close();

        header("Location: ../App/View/SuperAdmin/Index.php?page=account&success=akun_ditambah");
        exit;
    } else {
        error_log("Error inserting user: " . $koneksi->error);
        header("Location: ../App/View/SuperAdmin/Account.php?error=query_gagal");
        exit;
    }
}

// --- FUNGSI: Memperbarui Akun Ormawa ---
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id       = (int)($_POST['id'] ?? 0);
    $nama     = trim($_POST['nama'] ?? '');
    $nim      = trim($_POST['nim'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$id || empty($nama) || empty($nim) || empty($username) || empty($email)) {
        header("Location: ../App/View/SuperAdmin/Account.php?error=data_invalid");
        exit;
    }

    // Ambil data lama untuk validasi perubahan username
    $stmt = $koneksi->prepare("SELECT username FROM user WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_lama = $result->fetch_assoc();
    $stmt->close();

    if (!$user_lama) {
        header("Location: ../App/View/SuperAdmin/Account.php?error=user_tidak_ada");
        exit;
    }

    // Cek duplikasi username (kecuali milik diri sendiri)
    if ($username !== $user_lama['username']) {
        $stmt = $koneksi->prepare("SELECT id FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            header("Location: ../App/View/SuperAdmin/Account.php?error=username_duplikat");
            exit;
        }
        $stmt->close();
    }

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../App/View/SuperAdmin/Account.php?error=email_invalid");
        exit;
    }

    // Update password hanya jika diisi
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("UPDATE user SET nama = ?, nim = ?, username = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $nama, $nim, $username, $email, $password_hash, $id);
    } else {
        $stmt = $koneksi->prepare("UPDATE user SET nama = ?, nim = ?, username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nama, $nim, $username, $email, $id);
    }

    if ($stmt) {
        $stmt->execute();
        $stmt->close();

        header("Location: ../App/View/SuperAdmin/Account.php?success=akun_diperbarui");
        exit;
    } else {
        error_log("Error updating user: " . $koneksi->error);
        header("Location: ../App/View/SuperAdmin/Account.php?error=query_gagal");
        exit;
    }
}

// --- FUNGSI: Menghapus Akun Ormawa ---
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        // Pastikan hanya akun level 2 yang dihapus
        $stmt = $koneksi->prepare("DELETE FROM user WHERE id = ? AND level = 2");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: ../App/View/SuperAdmin/Account.php?deleted=akun");
        exit;
    } else {
        header("Location: ../App/View/SuperAdmin/Account.php?error=id_invalid");
        exit;
    }
}

// Jika tidak ada aksi valid
header("Location: ../App/View/SuperAdmin/Account.php?error=aksi_tidak_dikenal");
exit;
?>