<?php
include '../Config/ConnectDB.php';

// Pastikan sesi dimulai untuk mengakses $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper function for redirection
function get_redirect_url($page, $params = '') {
    $base_url = '../App/View/SuperAdmin/Index.php'; // Default for SuperAdmin
    if (isset($_SESSION['user_level']) && $_SESSION['user_level'] == 2) {
        $base_url = '../App/View/Admin/Index.php'; // For Admin
    }
    return $base_url . '?page=' . $page . ($params ? '&' . $params : '');
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create_form_info':
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . get_redirect_url('oprec', 'error=user_not_logged_in'));
            exit;
        }
        $user_id = (int)$_SESSION['user_id'];
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $jenis_form = trim($_POST['jenis_form'] ?? 'anggota');
        if (!in_array($jenis_form, ['anggota', 'event'])) {
            $jenis_form = 'anggota';
        }
        $redirect_page = ($jenis_form === 'event') ? 'oprec-event' : 'oprec';

        if (!$judul) {
            header("Location: " . get_redirect_url($redirect_page, 'error=judul_kosong'));
            exit;
        }

        $gambar_nama = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $target_dir = "../uploads/form/";
            $gambar_nama_asli = basename($_FILES["gambar"]["name"]);
            $imageFileType = strtolower(pathinfo($target_dir . $gambar_nama_asli, PATHINFO_EXTENSION));
            $check = getimagesize($_FILES["gambar"]["tmp_name"]);

            if ($check !== false && $_FILES["gambar"]["size"] <= 5000000 && in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
                $gambar_nama = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $gambar_nama_asli);
                if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_dir . $gambar_nama)) {
                    $gambar_nama = '';
                }
            }
        }

        $stmt = $koneksi->prepare("INSERT INTO form_info (judul, deskripsi, gambar, user_id, jenis_form) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssis", $judul, $deskripsi, $gambar_nama, $user_id, $jenis_form);
            $stmt->execute();
            $new_form_info_id = $stmt->insert_id;
            $stmt->close();
            header("Location: " . get_redirect_url($redirect_page, "form_id=$new_form_info_id&success=form"));
        } else {
            header("Location: " . get_redirect_url($redirect_page, 'error=query_gagal'));
        }
        break;

    case 'update_form_info':
        $form_info_id = (int)($_POST['form_info_id'] ?? 0);
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if (!isset($_SESSION['user_id'])) {
            header("Location: " . get_redirect_url('oprec', "form_id=$form_info_id&error=user_not_logged_in"));
            exit;
        }
        $current_user_id = (int)$_SESSION['user_id'];
        $current_user_level = (int)$_SESSION['user_level'];

        $stmt_check = $koneksi->prepare("SELECT user_id, jenis_form, gambar FROM form_info WHERE id = ?");
        $stmt_check->bind_param("i", $form_info_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row_check = $result_check->fetch_assoc();
        $stmt_check->close();

        $jenis_form = $row_check ? $row_check['jenis_form'] : 'anggota';
        $redirect_page = ($jenis_form === 'event') ? 'oprec-event' : 'oprec';

        if (!$form_info_id || !$judul) {
            header("Location: " . get_redirect_url($redirect_page, "form_id=$form_info_id&error=invalid_data"));
            exit;
        }

        if (!$row_check || ($current_user_level != 1 && $row_check['user_id'] != $current_user_id)) {
            header("Location: " . get_redirect_url($redirect_page, "form_id=$form_info_id&error=unauthorized_access"));
            exit;
        }

        $gambar_lama = $row_check['gambar'] ?? '';
        $gambar_nama = $gambar_lama;
        
        // Proses upload gambar baru jika ada file yang diupload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0 && $_FILES['gambar']['size'] > 0) {
            $target_dir = "../uploads/form/";
            $gambar_nama_asli = basename($_FILES["gambar"]["name"]);
            $imageFileType = strtolower(pathinfo($target_dir . $gambar_nama_asli, PATHINFO_EXTENSION));
            $check = getimagesize($_FILES["gambar"]["tmp_name"]);

            if ($check !== false && $_FILES["gambar"]["size"] <= 5000000 && in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
                // Hapus gambar lama jika ada
                if ($gambar_lama && file_exists($target_dir . $gambar_lama)) {
                    unlink($target_dir . $gambar_lama);
                }
                
                // Buat nama file baru untuk mencegah konflik
                $gambar_nama = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $gambar_nama_asli);
                
                // Pindahkan file yang diupload
                if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_dir . $gambar_nama)) {
                    // Jika upload gagal, kembalikan ke gambar lama
                    $gambar_nama = $gambar_lama;
                }
            } else {
                // Jika file tidak valid, tetap gunakan gambar lama
                $gambar_nama = $gambar_lama;
            }
        }

        // Ambil status dari POST
        $status = trim($_POST['status'] ?? 'private');
        // Validasi status
        if (!in_array($status, ['published', 'private'])) {
            $status = 'private';
        }

        $stmt = $koneksi->prepare("UPDATE form_info SET judul = ?, deskripsi = ?, gambar = ?, status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssssi", $judul, $deskripsi, $gambar_nama, $status, $form_info_id);
            $stmt->execute();
            $stmt->close();
            header("Location: " . get_redirect_url($redirect_page, "form_id=$form_info_id&success=form"));
        } else {
            header("Location: " . get_redirect_url($redirect_page, "form_id=$form_info_id&error=query_gagal"));
        }
        break;

    case 'delete_form':
        $form_info_id = (int)($_GET['id'] ?? 0);

        if ($form_info_id > 0) {
            if (!isset($_SESSION['user_id'])) {
                header("Location: " . get_redirect_url('oprec', 'error=user_not_logged_in'));
                exit;
            }
            $current_user_id = (int)$_SESSION['user_id'];
            $current_user_level = (int)$_SESSION['user_level'];

            $stmt_check = $koneksi->prepare("SELECT user_id, jenis_form, gambar FROM form_info WHERE id = ?");
            $stmt_check->bind_param("i", $form_info_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $row_check = $result_check->fetch_assoc();
            $stmt_check->close();

            $jenis_form = $row_check ? $row_check['jenis_form'] : 'anggota';
            $redirect_page = ($jenis_form === 'event') ? 'oprec-event' : 'oprec';

            if (!$row_check || ($current_user_level != 1 && $row_check['user_id'] != $current_user_id)) {
                header("Location: " . get_redirect_url($redirect_page, 'error=unauthorized_access'));
                exit;
            }

            $stmt = $koneksi->prepare("DELETE FROM form_info WHERE id = ?");
            $stmt->bind_param("i", $form_info_id);
            $stmt->execute();
            $stmt->close();

            $gambar_nama = $row_check['gambar'] ?? '';
            if ($gambar_nama && file_exists("../uploads/form/" . $gambar_nama)) {
                unlink("../uploads/form/" . $gambar_nama);
            }

            header("Location: " . get_redirect_url($redirect_page, 'deleted=form'));
        } else {
            header("Location: " . get_redirect_url('oprec', 'error=invalid_id'));
        }
        break;

    case 'add_field':
        $form_info_id = (int)($_POST['form_info_id'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        $type = $_POST['type'] ?? '';

        $stmt_jenis = $koneksi->prepare("SELECT jenis_form FROM form_info WHERE id = ?");
        $stmt_jenis->bind_param("i", $form_info_id);
        $stmt_jenis->execute();
        $result_jenis = $stmt_jenis->get_result();
        $row_jenis = $result_jenis->fetch_assoc();
        $stmt_jenis->close();
        $jenis_form = $row_jenis ? $row_jenis['jenis_form'] : 'anggota';
        $redirect_page = ($jenis_form === 'event') ? 'oprec-event' : 'oprec';

        if (!$form_info_id || !$label || !$type) {
            header("Location: " . get_redirect_url($redirect_page, "form_id=$form_info_id&error=invalid_data"));
            exit;
        }
        
        $name = preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace(' ', '_', $label)));
        $opsi = '';
        if ($type === 'radio' || $type === 'select') {
            $options = $_POST['options'] ?? [];
            $options = array_filter(array_map('trim', $options));
            $opsi = json_encode(array_values($options));
        }

        $stmt = $koneksi->prepare("INSERT INTO form (form_info_id, nama, tipe, label, opsi, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("issss", $form_info_id, $name, $type, $label, $opsi);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: " . get_redirect_url($redirect_page, "form_id=$form_info_id&success=field"));
        break;

    case 'delete_field':
        $id = (int)($_POST['delete_id'] ?? 0);
        $form_info_id = (int)($_POST['form_info_id'] ?? 0);

        $stmt_jenis = $koneksi->prepare("SELECT jenis_form FROM form_info WHERE id = ?");
        $stmt_jenis->bind_param("i", $form_info_id);
        $stmt_jenis->execute();
        $result_jenis = $stmt_jenis->get_result();
        $row_jenis = $result_jenis->fetch_assoc();
        $stmt_jenis->close();
        $jenis_form = $row_jenis ? $row_jenis['jenis_form'] : 'anggota';
        $redirect_page = ($jenis_form === 'event') ? 'oprec-event' : 'oprec';

        if ($id > 0) {
            $stmt = $koneksi->prepare("DELETE FROM form WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: " . get_redirect_url($redirect_page, "form_id=$form_info_id&deleted=field"));
        break;

    default:
        header("Location: " . get_redirect_url('oprec', 'error=unknown_action'));
        break;
}

exit;
?>