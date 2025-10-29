<?php
include '../../../Config/ConnectDB.php'; // Sesuaikan path ke ConnectDB.php
include '../SuperAdmin/Header.php'; // Header untuk pengguna biasa (sesuaikan path)

// Ambil form_info_id dari URL
$form_info_id = isset($_GET['form_info_id']) ? (int)$_GET['form_info_id'] : 0;

if ($form_info_id <= 0) {
    die("ID Formulir Tidak Valid.");
}

// Ambil detail formulir
$form_detail_query = "SELECT id, judul, deskripsi, gambar FROM form_info WHERE id = ?";
$stmt = $koneksi->prepare($form_detail_query);
$stmt->bind_param("i", $form_info_id);
$stmt->execute();
$form_detail = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$form_detail) {
    die("Formulir Tidak Ditemukan.");
}

// Ambil field-field formulir
$fields_query = "SELECT id, nama, tipe, label, opsi FROM form WHERE form_info_id = ? ORDER BY id ASC";
$stmt = $koneksi->prepare($fields_query);
$stmt->bind_param("i", $form_info_id);
$stmt->execute();
$result_fields = $stmt->get_result();
$form_fields = [];
while ($row = $result_fields->fetch_assoc()) {
    $form_fields[] = $row;
}
$stmt->close();

// Proses pengiriman formulir (hanya jika metode adalah POST)
$submission_success = false;
$submission_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // GANTI INI DENGAN LOGIKA PENGECEKAN SESI PENGGUNA YANG SEBENARNYA
    // Contoh sederhana (asumsi user_id 999 untuk pengguna biasa):
    $current_user_id = 999; // Gantilah dengan cara Anda mendapatkan user_id yang login

    if ($current_user_id <= 0) {
        $submission_error = "Anda harus login untuk mengisi formulir ini.";
    } else {
        $koneksi->autocommit(FALSE); // Mulai transaksi
        $error_occurred = false;
        $stmt_insert = $koneksi->prepare("INSERT INTO submit (form_id, user_id, field_name, field_value, created_at, submitted_at) VALUES (?, ?, ?, ?, NOW(), NOW())");

        if (!$stmt_insert) {
            $submission_error = "Kesalahan internal: " . $koneksi->error;
            $error_occurred = true;
        } else {
            foreach ($form_fields as $field) {
                $field_name = $field['nama'];
                $field_type = $field['tipe'];
                $field_id = $field['id'];

                // Ambil nilai dari POST
                $field_value = '';
                if ($field_type === 'checkbox' || $field_type === 'radio' || $field_type === 'select') {
                    // Untuk radio/checkbox/select, nilai biasanya string tunggal
                    $field_value = $_POST[$field_name] ?? '';
                    // Validasi opsi (opsional tapi dianjurkan)
                    if ($field_value !== '') {
                        $options = json_decode($field['opsi'], true);
                        if (is_array($options) && !in_array($field_value, $options)) {
                             $submission_error = "Nilai untuk field '" . htmlspecialchars($field['label']) . "' tidak valid.";
                             $error_occurred = true;
                             break; // Hentikan loop jika error
                        }
                    }
                } else {
                    // Untuk text, email, number, textarea, file
                    $field_value = trim($_POST[$field_name] ?? '');

                    // Validasi file upload jika tipe field adalah 'file'
                    if ($field_type === 'file') {
                        if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
                            $upload_dir = "../../../uploads/submissions/"; // Buat folder ini dan pastikan dapat ditulis
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true); // Buat folder jika belum ada
                            }
                            $file_name = uniqid() . '_' . basename($_FILES[$field_name]['name']);
                            $target_file = $upload_dir . $file_name;

                            // Validasi tipe file dan ukuran (contoh sederhana)
                            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']; // Tambahkan sesuai kebutuhan
                            $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                            if (in_array($file_ext, $allowed_types) && $_FILES[$field_name]['size'] <= 5000000) { // Max 5MB
                                if (move_uploaded_file($_FILES[$field_name]['tmp_name'], $target_file)) {
                                    $field_value = $file_name; // Simpan nama file ke database
                                } else {
                                    $submission_error = "Gagal mengupload file untuk '" . htmlspecialchars($field['label']) . "'.";
                                    $error_occurred = true;
                                    break; // Hentikan loop jika error upload
                                }
                            } else {
                                $submission_error = "File untuk '" . htmlspecialchars($field['label']) . "' tidak valid (tipe/ukuran).";
                                $error_occurred = true;
                                break; // Hentikan loop jika error validasi
                            }
                        } else {
                             // Jika field file kosong atau error upload
                             if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] !== UPLOAD_ERR_NO_FILE) {
                                 $submission_error = "Error upload file untuk '" . htmlspecialchars($field['label']) . "'.";
                                 $error_occurred = true;
                                 break;
                             }
                             // Jika file tidak wajib, biarkan $field_value kosong
                        }
                    }
                }

                // Validasi wajib diisi (hanya contoh dasar, validasi sebenarnya bisa lebih kompleks)
                if (empty($field_value) && $field_type !== 'file') { // Asumsikan file tidak wajib untuk sekarang
                     // Tambahkan logika validasi wajib diisi jika diperlukan
                     // Misalnya, cek dari metadata tambahan di tabel `form` jika ada
                     // Untuk sekarang, kita lanjutkan tanpa validasi wajib
                 }


                if (!$error_occurred) {
                    $stmt_insert->bind_param("iiss", $field_id, $current_user_id, $field_name, $field_value);
                    if (!$stmt_insert->execute()) {
                        $submission_error = "Gagal menyimpan jawaban untuk '" . htmlspecialchars($field['label']) . "'.";
                        $error_occurred = true;
                        break; // Hentikan loop jika error insert
                    }
                }
            }
            $stmt_insert->close();
        }

        if (!$error_occurred) {
            $koneksi->commit();
            $submission_success = true;
        } else {
            $koneksi->rollback();
            // Tidak perlu set $submission_error lagi karena sudah diisi di loop
        }
    }
}

?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><?= htmlspecialchars($form_detail['judul']) ?></h4>
        </div>
        <div class="card-body">
            <?php if ($form_detail['gambar']): ?>
                <div class="text-center mb-3">
                    <img src="../../../uploads/form/<?= htmlspecialchars($form_detail['gambar']) ?>" alt="Cover" class="img-fluid rounded" style="max-height: 200px;">
                </div>
            <?php endif; ?>
            <p><?= htmlspecialchars($form_detail['deskripsi']) ?></p>
            <hr>

            <?php if ($submission_success): ?>
                <div class="alert alert-success">
                    <strong>Sukses!</strong> Formulir berhasil dikirim.
                </div>
            <?php elseif ($submission_error): ?>
                <div class="alert alert-danger">
                    <strong>Error!</strong> <?= $submission_error ?>
                </div>
            <?php endif; ?>

            <?php if (!$submission_success): ?>
                <form method="POST" enctype="multipart/form-data">
                    <?php foreach ($form_fields as $field): ?>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold"><?= htmlspecialchars($field['label']) ?>: </label>
                            <?php if ($field['tipe'] === 'text'): ?>
                                <input type="text" name="<?= htmlspecialchars($field['nama']) ?>" class="form-control" required>
                            <?php elseif ($field['tipe'] === 'email'): ?>
                                <input type="email" name="<?= htmlspecialchars($field['nama']) ?>" class="form-control" required>
                            <?php elseif ($field['tipe'] === 'number'): ?>
                                <input type="number" name="<?= htmlspecialchars($field['nama']) ?>" class="form-control" required>
                            <?php elseif ($field['tipe'] === 'textarea'): ?>
                                <textarea name="<?= htmlspecialchars($field['nama']) ?>" class="form-control" rows="3" required></textarea>
                            <?php elseif ($field['tipe'] === 'file'): ?>
                                <input type="file" name="<?= htmlspecialchars($field['nama']) ?>" class="form-control-file" accept="image/*,.pdf,.doc,.docx"> <!-- Sesuaikan accept type -->
                            <?php elseif ($field['tipe'] === 'radio'): ?>
                                <?php $options = json_decode($field['opsi'], true); ?>
                                <?php if (is_array($options)): ?>
                                    <?php foreach ($options as $opt): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="<?= htmlspecialchars($field['nama']) ?>" id="opt_<?= $field['id'] ?>_<?= urlencode($opt) ?>" value="<?= htmlspecialchars($opt) ?>" required>
                                            <label class="form-check-label" for="opt_<?= $field['id'] ?>_<?= urlencode($opt) ?>"><?= htmlspecialchars($opt) ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php elseif ($field['tipe'] === 'select'): ?>
                                <select name="<?= htmlspecialchars($field['nama']) ?>" class="form-control" required>
                                    <option value="">-- Pilih --</option>
                                    <?php $options = json_decode($field['opsi'], true); ?>
                                    <?php if (is_array($options)): ?>
                                        <?php foreach ($options as $opt): ?>
                                            <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-primary btn-block">Kirim Jawaban</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../SuperAdmin/Footer.php';  ?>