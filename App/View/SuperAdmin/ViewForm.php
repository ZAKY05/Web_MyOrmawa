<?php
session_start();
include '../../../Config/ConnectDB.php';
include '../SuperAdmin/Header.php';

$form_info_id = isset($_GET['form_info_id']) ? (int)$_GET['form_info_id'] : 0;

if ($form_info_id <= 0) {
    die("ID Formulir Tidak Valid.");
}

$form_detail_query = "SELECT id, judul, deskripsi, gambar FROM form_info WHERE id = ?";
$stmt = $koneksi->prepare($form_detail_query);
$stmt->bind_param("i", $form_info_id);
$stmt->execute();
$form_detail = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$form_detail) {
    die("Formulir Tidak Ditemukan.");
}

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

$submission_success = false;
$submission_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

    if ($current_user_id <= 0) {
        $submission_error = "Anda harus login untuk mengisi formulir ini.";
    } else {
        if ($current_user_id > 0) {
            $koneksi->autocommit(FALSE);
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

                    $field_value = '';
                    if ($field_type === 'checkbox' || $field_type === 'radio' || $field_type === 'select') {
                        $field_value = $_POST[$field_name] ?? '';
                        if ($field_value !== '') {
                            $options = json_decode($field['opsi'], true);
                            if (is_array($options) && !in_array($field_value, $options)) {
                                 $submission_error = "Nilai untuk field '" . htmlspecialchars($field['label']) . "' tidak valid.";
                                 $error_occurred = true;
                                 break;
                            }
                        }
                    } else {
                        $field_value = trim($_POST[$field_name] ?? '');

                        if ($field_type === 'file') {
                            if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
                                $upload_dir = "../../../uploads/submissions/";
                                if (!is_dir($upload_dir)) {
                                    mkdir($upload_dir, 0755, true);
                                }
                                $file_name = uniqid() . '_' . basename($_FILES[$field_name]['name']);
                                $target_file = $upload_dir . $file_name;

                                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
                                $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                                if (in_array($file_ext, $allowed_types) && $_FILES[$field_name]['size'] <= 5000000) {
                                    if (move_uploaded_file($_FILES[$field_name]['tmp_name'], $target_file)) {
                                        $field_value = $file_name;
                                    } else {
                                        $submission_error = "Gagal mengupload file untuk '" . htmlspecialchars($field['label']) . "'.";
                                        $error_occurred = true;
                                        break;
                                    }
                                } else {
                                    $submission_error = "File untuk '" . htmlspecialchars($field['label']) . "' tidak valid (tipe/ukuran).";
                                    $error_occurred = true;
                                    break;
                                }
                            } else {
                                 if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] !== UPLOAD_ERR_NO_FILE) {
                                     $submission_error = "Error upload file untuk '" . htmlspecialchars($field['label']) . "'.";
                                     $error_occurred = true;
                                     break;
                                 }
                            }
                        }
                    }

                    if (!$error_occurred) {
                        $stmt_insert->bind_param("iiss", $field_id, $current_user_id, $field_name, $field_value);
                        if (!$stmt_insert->execute()) {
                            $submission_error = "Gagal menyimpan jawaban untuk '" . htmlspecialchars($field['label']) . "'.";
                            $error_occurred = true;
                            break;
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
            }
        }
    }
}
?>

<style>
    * {
        box-sizing: border-box;
    }
    
    .form-wrapper {
        max-width: 780px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .form-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(33, 150, 243, 0.08);
        overflow: hidden;
        border: 1px solid rgba(33, 150, 243, 0.12);
        transition: box-shadow 0.3s ease;
    }
    
    .form-card:hover {
        box-shadow: 0 4px 20px rgba(33, 150, 243, 0.15);
    }
    
    .form-header {
        background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
        padding: 32px 28px;
        position: relative;
        overflow: hidden;
    }
    
    .form-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }
    
    .form-header h1 {
        color: #ffffff;
        font-size: 28px;
        font-weight: 600;
        margin: 0;
        position: relative;
        z-index: 1;
        letter-spacing: -0.5px;
    }
    
    .form-body {
        padding: 36px 28px;
    }
    
    .cover-image-wrapper {
        margin: -8px 0 28px;
        text-align: center;
        position: relative;
    }
    
    .cover-image {
        max-width: 100%;
        max-height: 280px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease;
    }
    
    .cover-image:hover {
        transform: scale(1.02);
    }
    
    .form-description {
        color: #546e7a;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 2px solid #e3f2fd;
    }
    
    .alert-message {
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 28px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideDown 0.4s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .alert-success {
        background: #e8f5e9;
        border-left: 4px solid #4caf50;
        color: #2e7d32;
    }
    
    .alert-error {
        background: #ffebee;
        border-left: 4px solid #f44336;
        color: #c62828;
    }
    
    .alert-message strong {
        font-weight: 600;
    }
    
    .form-field {
        margin-bottom: 28px;
    }
    
    .field-label {
        display: block;
        color: #37474f;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 8px;
        letter-spacing: 0.2px;
    }
    
    .field-input,
    .field-textarea,
    .field-select {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 15px;
        color: #37474f;
        background: #fafafa;
        transition: all 0.3s ease;
        font-family: inherit;
    }
    
    .field-input:focus,
    .field-textarea:focus,
    .field-select:focus {
        outline: none;
        border-color: #2196f3;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(33, 150, 243, 0.08);
    }
    
    .field-textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .field-file {
        width: 100%;
        padding: 12px;
        border: 2px dashed #e0e0e0;
        border-radius: 8px;
        background: #fafafa;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
    }
    
    .field-file:hover {
        border-color: #2196f3;
        background: #e3f2fd;
    }
    
    .radio-group,
    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .radio-option,
    .checkbox-option {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        background: #fafafa;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .radio-option:hover,
    .checkbox-option:hover {
        background: #e3f2fd;
        border-color: #2196f3;
    }
    
    .radio-option input,
    .checkbox-option input {
        margin-right: 12px;
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #2196f3;
    }
    
    .radio-option label,
    .checkbox-option label {
        cursor: pointer;
        color: #37474f;
        font-size: 14px;
        flex: 1;
        margin: 0;
    }
    
    .submit-button {
        width: 100%;
        padding: 14px 24px;
        background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 12px;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
    }
    
    .submit-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(33, 150, 243, 0.4);
    }
    
    .submit-button:active {
        transform: translateY(0);
    }
    
    @media (max-width: 768px) {
        .form-wrapper {
            margin: 20px auto;
            padding: 0 16px;
        }
        
        .form-header {
            padding: 24px 20px;
        }
        
        .form-header h1 {
            font-size: 22px;
        }
        
        .form-body {
            padding: 24px 20px;
        }
    }
</style>

<div class="form-wrapper">
    <div class="form-card">
        <div class="form-header">
            <h1><?= htmlspecialchars($form_detail['judul']) ?></h1>
        </div>
        <div class="form-body">
            <?php if ($form_detail['gambar']): ?>
                <div class="cover-image-wrapper">
                    <img src="../../../uploads/form/<?= htmlspecialchars($form_detail['gambar']) ?>" alt="Cover" class="cover-image">
                </div>
            <?php endif; ?>
            
            <div class="form-description">
                <?= htmlspecialchars($form_detail['deskripsi']) ?>
            </div>

            <?php if ($submission_success): ?>
                <div class="alert-message alert-success">
                    <strong>✓ Berhasil!</strong> Formulir Anda telah berhasil dikirim.
                </div>
            <?php elseif ($submission_error): ?>
                <div class="alert-message alert-error">
                    <strong>✗ Error!</strong> <?= $submission_error ?>
                </div>
            <?php endif; ?>

            <?php if (!$submission_success): ?>
                <form method="POST" enctype="multipart/form-data" id="mainForm">
                    <?php foreach ($form_fields as $field): ?>
                        <div class="form-field">
                            <label class="field-label"><?= htmlspecialchars($field['label']) ?></label>
                            
                            <?php if ($field['tipe'] === 'text'): ?>
                                <input type="text" name="<?= htmlspecialchars($field['nama']) ?>" class="field-input" required>
                                
                            <?php elseif ($field['tipe'] === 'email'): ?>
                                <input type="email" name="<?= htmlspecialchars($field['nama']) ?>" class="field-input" required>
                                
                            <?php elseif ($field['tipe'] === 'number'): ?>
                                <input type="number" name="<?= htmlspecialchars($field['nama']) ?>" class="field-input" required>
                                
                            <?php elseif ($field['tipe'] === 'textarea'): ?>
                                <textarea name="<?= htmlspecialchars($field['nama']) ?>" class="field-textarea" required></textarea>
                                
                            <?php elseif ($field['tipe'] === 'file'): ?>
                                <input type="file" name="<?= htmlspecialchars($field['nama']) ?>" class="field-file" accept="image/*,.pdf,.doc,.docx">
                                
                            <?php elseif ($field['tipe'] === 'radio'): ?>
                                <div class="radio-group">
                                    <?php $options = json_decode($field['opsi'], true); ?>
                                    <?php if (is_array($options)): ?>
                                        <?php foreach ($options as $opt): ?>
                                            <div class="radio-option">
                                                <input type="radio" name="<?= htmlspecialchars($field['nama']) ?>" id="opt_<?= $field['id'] ?>_<?= urlencode($opt) ?>" value="<?= htmlspecialchars($opt) ?>" required>
                                                <label for="opt_<?= $field['id'] ?>_<?= urlencode($opt) ?>"><?= htmlspecialchars($opt) ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                            <?php elseif ($field['tipe'] === 'select'): ?>
                                <select name="<?= htmlspecialchars($field['nama']) ?>" class="field-select" required>
                                    <option value="">-- Pilih Salah Satu --</option>
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
                    
                    <button type="submit" class="submit-button">Kirim Formulir</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('mainForm');
        
        if (form) {
            // Smooth scroll ke error message jika ada
            const alertError = document.querySelector('.alert-error');
            if (alertError) {
                alertError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            // Animasi untuk input fields
            const inputs = form.querySelectorAll('.field-input, .field-textarea, .field-select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateX(4px)';
                    this.parentElement.style.transition = 'transform 0.3s ease';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateX(0)';
                });
            });
            
            // Enhanced radio/checkbox interaction
            const radioOptions = form.querySelectorAll('.radio-option, .checkbox-option');
            radioOptions.forEach(option => {
                const input = option.querySelector('input');
                
                option.addEventListener('click', function() {
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        input.checked = !input.checked;
                        
                        // Trigger change event
                        const event = new Event('change', { bubbles: true });
                        input.dispatchEvent(event);
                    }
                });
                
                input.addEventListener('change', function() {
                    if (this.type === 'radio') {
                        // Remove checked style from all radio options in the same group
                        const group = this.closest('.radio-group');
                        if (group) {
                            group.querySelectorAll('.radio-option').forEach(opt => {
                                opt.style.background = '#fafafa';
                                opt.style.borderColor = '#e0e0e0';
                            });
                        }
                    }
                    
                    if (this.checked) {
                        option.style.background = '#e3f2fd';
                        option.style.borderColor = '#2196f3';
                    } else {
                        option.style.background = '#fafafa';
                        option.style.borderColor = '#e0e0e0';
                    }
                });
            });
            
            // File input enhancement
            const fileInputs = form.querySelectorAll('.field-file');
            fileInputs.forEach(fileInput => {
                fileInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        const fileName = this.files[0].name;
                        const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2);
                        
                        // Create or update file info display
                        let fileInfo = this.parentElement.querySelector('.file-info');
                        if (!fileInfo) {
                            fileInfo = document.createElement('div');
                            fileInfo.className = 'file-info';
                            fileInfo.style.marginTop = '8px';
                            fileInfo.style.fontSize = '13px';
                            fileInfo.style.color = '#2196f3';
                            this.parentElement.appendChild(fileInfo);
                        }
                        fileInfo.textContent = `📎 ${fileName} (${fileSize} MB)`;
                    }
                });
            });
            
            // Form submission animation
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('.submit-button');
                submitBtn.textContent = 'Mengirim...';
                submitBtn.style.opacity = '0.7';
                submitBtn.disabled = true;
            });
        }
        
        // Success message auto-scroll
        const alertSuccess = document.querySelector('.alert-success');
        if (alertSuccess) {
            alertSuccess.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
</script>

<?php include '../SuperAdmin/Footer.php'; ?>