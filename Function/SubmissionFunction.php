<?php
// ../../../Function/SubmissionFunction.php

include '../Config/ConnectDB.php';
session_start();

// Fungsi untuk menentukan path redirect berdasarkan level user
function get_redirect_path($user_level, $params = '') {
    $base_path = '../App/View/';
    $page = 'Index.php?page=oprec';
    if ($user_level == 1) {
        return $base_path . 'SuperAdmin/' . $page . $params;
    } elseif ($user_level == 2) {
        return $base_path . 'Admin/' . $page . $params;
    } else {
        // Default fallback atau halaman error
        return '../App/View/SuperAdmin/Login.php';
    }
}

$user_level = $_SESSION['user_level'] ?? 0;

// Tangani penghapusan submission
if (isset($_GET['action']) && $_GET['action'] === 'delete_user_submissions') {
    // Ambil user_id dan form_info_id dari URL
    $user_id = (int)($_GET['user_id'] ?? 0);
    $form_info_id = (int)($_GET['form_info_id'] ?? 0);

    // Validasi input
    if ($user_id <= 0 || $form_info_id <= 0) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&view_submissions=$form_info_id&error=invalid_data");
        exit;
    }

    // Hapus semua submission untuk user_id dan form_info_id tertentu
    // Kita perlu menghapus dari tabel submit berdasarkan form_id yang terkait dengan form_info_id
    $delete_query = "
        DELETE s FROM submit s
        INNER JOIN form f ON s.form_id = f.id
        WHERE s.user_id = ? AND f.form_info_id = ?
    ";
    $stmt_delete = $koneksi->prepare($delete_query);
    if ($stmt_delete) {
        $stmt_delete->bind_param("ii", $user_id, $form_info_id);
        $stmt_delete->execute();
        $affected_rows = $stmt_delete->affected_rows;
        $stmt_delete->close();

        // Redirect kembali ke halaman submissions dengan pesan sukses
        header("Location: " . get_redirect_path($user_level, "&view_submissions=$form_info_id&deleted_submission=$user_id&affected_rows=$affected_rows"));
        exit;
    } else {
        error_log("Error deleting submissions: " . $koneksi->error);
        header("Location: " . get_redirect_path($user_level, "&view_submissions=$form_info_id&error=query_gagal"));
        exit;
    }
}

// --- TAMBAHAN: Fungsi untuk menyetel status submission ---
if (isset($_GET['action']) && ($_GET['action'] === 'approve_user_submissions' || $_GET['action'] === 'reject_user_submissions')) {
    // Ambil user_id dan form_info_id dari URL
    $user_id = (int)($_GET['user_id'] ?? 0);
    $form_info_id = (int)($_GET['form_info_id'] ?? 0);

    // Tentukan status berdasarkan aksi
    $status = ($_GET['action'] === 'approve_user_submissions') ? 'approved' : 'rejected';

    // Validasi input
    if ($user_id <= 0 || $form_info_id <= 0) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&view_submissions=$form_info_id&error=invalid_data");
        exit;
    }

    // Update status untuk *semua* submission dari user_id untuk form_info_id tertentu
    $update_query = "
        UPDATE submit s
        INNER JOIN form f ON s.form_id = f.id
        SET s.status = ?
        WHERE s.user_id = ? AND f.form_info_id = ?
    ";
    $stmt_update = $koneksi->prepare($update_query);
    if ($stmt_update) {
        $stmt_update->bind_param("sii", $status, $user_id, $form_info_id);
        $stmt_update->execute();
        $affected_rows = $stmt_update->affected_rows;
        $stmt_update->close();

        // Redirect kembali ke halaman submissions dengan pesan sukses
        $status_msg = $status === 'approved' ? 'approved' : 'rejected';
        header("Location: " . get_redirect_path($user_level, "&view_submissions=$form_info_id&$status_msg=$user_id&affected_rows=$affected_rows"));
        exit;
    } else {
        error_log("Error updating submission status: " . $koneksi->error);
        header("Location: " . get_redirect_path($user_level, "&view_submissions=$form_info_id&error=query_gagal"));
        exit;
    }
}
// --- SAMPAI SINI ---

// Jika aksi tidak dikenali
header("Location: " . get_redirect_path($user_level, "&error=unknown_action"));
exit;

?>