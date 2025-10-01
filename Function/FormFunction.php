<?php
include '../Config/ConnectDB.php';

// Tambah field
if (isset($_POST['action']) && $_POST['action'] === 'add_field') {
    $label = trim($_POST['label'] ?? '');
    $type = $_POST['type'] ?? '';

    if (!$label || !$type) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec");
        exit;
    }

    // Validasi tipe
    $allowedTypes = ['text', 'email', 'number', 'textarea', 'file', 'radio', 'select'];
    if (!in_array($type, $allowedTypes)) {
        header("Location: ../App/View/SuperAdmin/Index.php?page=oprec");
        exit;
    }

    // Normalisasi nama field
    $name = preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace(' ', '_', $label)));

    // Handle opsi
    $opsi = '';
    if ($type === 'radio' || $type === 'select') {
        $options = $_POST['options'] ?? [];
        // Filter opsi kosong
        $options = array_filter(array_map('trim', $options));
        $opsi = json_encode(array_values($options));
    }

    // Simpan ke tabel `form`
    $stmt = $koneksi->prepare("INSERT INTO form (nama, tipe, label, opsi, created_at) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("ssss", $name, $type, $label, $opsi);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&success=1");
    exit;
}

// Hapus field
if (isset($_POST['action']) && $_POST['action'] === 'delete_field') {
    $id = $_POST['delete_id'] ?? 0;
    if (is_numeric($id) && $id > 0) {
        $stmt = $koneksi->prepare("DELETE FROM form WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: ../App/View/SuperAdmin/Index.php?page=oprec&deleted=1");
    exit;
}