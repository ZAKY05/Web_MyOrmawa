<?php
include '../../../Config/ConnectDB.php';

$active_form_id = isset($_GET['form_id']) ? (int)$_GET['form_id'] : 0;
$show_create_form = isset($_GET['create']) && $_GET['create'] == '1';

$forms_result = mysqli_query($koneksi, "SELECT id, judul, deskripsi, gambar FROM form_info ORDER BY created_at DESC");
$all_forms = [];
while ($row = mysqli_fetch_assoc($forms_result)) {
    $all_forms[] = $row;
}

$form_detail = null;
$form_fields = [];
if ($active_form_id > 0) {
    $form_detail_query = "SELECT id, judul, deskripsi, gambar FROM form_info WHERE id = ?";
    $stmt = $koneksi->prepare($form_detail_query);
    $stmt->bind_param("i", $active_form_id);
    $stmt->execute();
    $form_detail = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($form_detail) {
        $fields_query = "SELECT id, nama, tipe, label, opsi FROM form WHERE form_info_id = ? ORDER BY id ASC";
        $stmt = $koneksi->prepare($fields_query);
        $stmt->bind_param("i", $active_form_id);
        $stmt->execute();
        $result_fields = $stmt->get_result();
        while ($row = $result_fields->fetch_assoc()) {
            $form_fields[] = $row;
        }
        $stmt->close();
    } else {
        $active_form_id = 0;
    }
}

$submissionCount = 0;
if ($active_form_id > 0) {
    $countQuery = "SELECT COUNT(DISTINCT user_id) as total FROM submit WHERE form_id IN (SELECT id FROM form WHERE form_info_id = ?)";
    $stmt = $koneksi->prepare($countQuery);
    $stmt->bind_param("i", $active_form_id);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $submissionCountRow = $countResult->fetch_assoc();
    $submissionCount = $submissionCountRow['total'] ?? 0;
    $stmt->close();
} else {
    $countQuery = "SELECT COUNT(DISTINCT user_id) as total FROM submit";
    $countResult = mysqli_query($koneksi, $countQuery);
    $submissionCountRow = mysqli_fetch_assoc($countResult);
    $submissionCount = $submissionCountRow['total'] ?? 0;
}

include('../SuperAdmin/Header.php');
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-cogs"></i> Form Builder</h1>
        <a href="?page=oprec&create=1" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Form Baru
        </a>
    </div>

    <!-- Alerts -->
    <?php if (isset($_GET['deleted_submission'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-check-circle"></i> Berhasil!</strong> Data submission berhasil dihapus.
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-triangle"></i> Error!</strong>
            <?php
            $error_msg = $_GET['error'];
            if ($error_msg == 'judul_kosong') echo 'Judul formulir tidak boleh kosong.';
            elseif ($error_msg == 'query_gagal') echo 'Terjadi kesalahan saat menyimpan ke database.';
            elseif ($error_msg == 'invalid_data') echo 'Data formulir tidak valid.';
            elseif ($error_msg == 'invalid_type') echo 'Tipe field tidak valid.';
            elseif ($error_msg == 'invalid_id') echo 'ID formulir tidak valid.';
            elseif ($error_msg == 'unknown_action') echo 'Aksi tidak dikenali.';
            else echo htmlspecialchars($error_msg);
            ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-check-circle"></i> Sukses!</strong>
            <?php
            $success_msg = $_GET['success'];
            if ($success_msg == 'field') echo 'Field berhasil ditambahkan!';
            elseif ($success_msg == 'form') echo 'Formulir berhasil disimpan!';
            else echo 'Operasi berhasil!';
            ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-trash"></i> Terhapus!</strong>
            <?php
            $deleted_msg = $_GET['deleted'];
            if ($deleted_msg == 'field') echo 'Field berhasil dihapus!';
            elseif ($deleted_msg == 'form') echo 'Formulir berhasil dihapus!';
            else echo 'Data berhasil dihapus!';
            ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Formulir</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($all_forms) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Form Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $form_detail ? htmlspecialchars(substr($form_detail['judul'], 0, 20)) . (strlen($form_detail['judul']) > 20 ? '...' : '') : 'Tidak ada' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Submissions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $submissionCount ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Kolom Kiri: Daftar Formulir -->
        <div class="col-xl-4 col-lg-5">
            <!-- Daftar Semua Formulir -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Formulir</h6>
                    <a href="?page=oprec&create=1" class="btn btn-primary btn-sm btn-circle" title="Buat Form Baru">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($all_forms)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p class="mb-2">Belum ada formulir</p>
                            <a href="?page=oprec&create=1" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Buat Formulir Pertama
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($all_forms as $form): ?>
                                <div class="list-group-item px-0 <?= $active_form_id == $form['id'] ? 'bg-light' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <a href="?page=oprec&form_id=<?= $form['id'] ?>" class="font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($form['judul']) ?>
                                            </a>
                                            <?php if ($active_form_id == $form['id']): ?>
                                                <span class="badge badge-success badge-sm ml-1">Aktif</span>
                                            <?php endif; ?>
                                            <p class="text-muted small mb-0 mt-1">
                                                <?= htmlspecialchars(substr($form['deskripsi'], 0, 60)) . (strlen($form['deskripsi']) > 60 ? '...' : '') ?>
                                            </p>
                                        </div>
                                        <div class="ml-2">
                                            <a href="?page=oprec&form_id=<?= $form['id'] ?>" class="btn btn-info btn-sm btn-circle" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-danger btn-sm btn-circle" title="Hapus" 
                                                    data-toggle="modal" 
                                                    data-target="#deleteFormModal<?= $form['id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Konfirmasi Hapus Form -->
                                <div class="modal fade" id="deleteFormModal<?= $form['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="deleteFormModalLabel<?= $form['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteFormModalLabel<?= $form['id'] ?>">Konfirmasi Hapus</h5>
                                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus formulir <strong>"<?= htmlspecialchars($form['judul']) ?>"</strong>? 
                                                <br><br>
                                                <span class="text-danger">Semua field dan data yang terkait akan ikut terhapus!</span>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                                                <a class="btn btn-danger" href="../../../Function/FormFunction.php?action=delete_form&id=<?= $form['id'] ?>">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Info - Hanya tampil jika create=1 atau ada form_id -->
            <?php if ($show_create_form || ($active_form_id > 0 && $form_detail)): ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <?= $active_form_id > 0 && $form_detail ? 'Edit Formulir' : 'Buat Formulir Baru' ?>
                        </h6>
                        <a href="?page=oprec" class="btn btn-sm btn-secondary btn-circle" title="Tutup">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="../../../Function/FormFunction.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?= $active_form_id > 0 ? 'update_form_info' : 'create_form_info' ?>">
                            <?php if ($active_form_id > 0): ?>
                                <input type="hidden" name="form_info_id" value="<?= $active_form_id ?>">
                            <?php endif; ?>

                            <div class="form-group">
                                <label class="small font-weight-bold">Judul Formulir <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control" value="<?= $form_detail ? htmlspecialchars($form_detail['judul']) : '' ?>" placeholder="Masukkan judul formulir" required>
                            </div>

                            <div class="form-group">
                                <label class="small font-weight-bold">Deskripsi Formulir</label>
                                <textarea name="deskripsi" class="form-control" rows="3" placeholder="Masukkan deskripsi formulir"><?= $form_detail ? htmlspecialchars($form_detail['deskripsi']) : '' ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="small font-weight-bold">Gambar Cover</label>
                                <?php if ($form_detail && $form_detail['gambar']): ?>
                                    <div class="mb-2">
                                        <img src="../../../uploads/<?= htmlspecialchars($form_detail['gambar']) ?>" alt="Cover" class="img-thumbnail" style="max-height: 120px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="gambar" class="form-control-file" accept="image/*">
                                <?php if ($form_detail && $form_detail['gambar']): ?>
                                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah</small>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> <?= $active_form_id > 0 ? 'Perbarui' : 'Simpan' ?> Formulir
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kolom Kanan: Tambah Field & Preview -->
        <div class="col-xl-8 col-lg-7">
            <?php if ($active_form_id > 0 && $form_detail): ?>
                <div class="row">
                    <!-- Tambah Field -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-success">Tambah Field Baru</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="../../../Function/FormFunction.php">
                                    <input type="hidden" name="action" value="add_field">
                                    <input type="hidden" name="form_info_id" value="<?= $active_form_id ?>">

                                    <div class="form-group">
                                        <label class="small font-weight-bold">Label Field <span class="text-danger">*</span></label>
                                        <input type="text" name="label" class="form-control" placeholder="Contoh: Nama Lengkap" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="small font-weight-bold">Tipe Field <span class="text-danger">*</span></label>
                                        <select name="type" id="type" class="form-control" onchange="toggleOptions()">
                                            <option value="text">Input Text</option>
                                            <option value="email">Email</option>
                                            <option value="number">Number</option>
                                            <option value="textarea">Text Area</option>
                                            <option value="file">File Upload</option>
                                            <option value="radio">Radio Button</option>
                                            <option value="select">Dropdown Select</option>
                                        </select>
                                    </div>

                                    <div id="options-container" style="display:none;">
                                        <div class="card bg-light mb-3">
                                            <div class="card-body p-3">
                                                <label class="small font-weight-bold mb-2">Opsi Pilihan</label>
                                                <div id="options-list">
                                                    <div class="input-group input-group-sm mb-2">
                                                        <input type="text" name="options[]" class="form-control" placeholder="Opsi 1">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-danger" onclick="removeOption(this)" style="display:none;">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-secondary btn-sm btn-block" onclick="addOption()">
                                                    <i class="fas fa-plus"></i> Tambah Opsi
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-plus"></i> Tambah Field
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Form -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-dark">Preview Form</h6>
                            </div>
                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                <?php if ($form_detail['gambar']): ?>
                                    <div class="text-center mb-3">
                                        <img src="../../../uploads/<?= htmlspecialchars($form_detail['gambar']) ?>" alt="Cover" class="img-fluid rounded" style="max-height: 150px;">
                                    </div>
                                <?php endif; ?>
                                
                                <h5 class="font-weight-bold text-gray-900"><?= htmlspecialchars($form_detail['judul']) ?></h5>
                                <p class="text-muted small mb-3"><?= htmlspecialchars($form_detail['deskripsi']) ?></p>
                                <hr>

                                <?php if (empty($form_fields)): ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="small mb-0">Belum ada field</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($form_fields as $field): ?>
                                        <div class="form-group">
                                            <label class="small font-weight-bold"><?= htmlspecialchars($field['label']) ?></label>
                                            <?php if ($field['tipe'] === 'text'): ?>
                                                <input type="text" class="form-control form-control-sm" placeholder="<?= htmlspecialchars($field['label']) ?>" disabled>
                                            <?php elseif ($field['tipe'] === 'email'): ?>
                                                <input type="email" class="form-control form-control-sm" placeholder="nama@email.com" disabled>
                                            <?php elseif ($field['tipe'] === 'number'): ?>
                                                <input type="number" class="form-control form-control-sm" placeholder="0" disabled>
                                            <?php elseif ($field['tipe'] === 'textarea'): ?>
                                                <textarea class="form-control form-control-sm" rows="2" placeholder="<?= htmlspecialchars($field['label']) ?>" disabled></textarea>
                                            <?php elseif ($field['tipe'] === 'file'): ?>
                                                <input type="file" class="form-control-file" disabled>
                                            <?php elseif ($field['tipe'] === 'radio'): ?>
                                                <?php $options = json_decode($field['opsi'], true); ?>
                                                <?php if (is_array($options)): ?>
                                                    <?php foreach ($options as $opt): ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" disabled>
                                                            <label class="form-check-label small"><?= htmlspecialchars($opt) ?></label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            <?php elseif ($field['tipe'] === 'select'): ?>
                                                <select class="form-control form-control-sm" disabled>
                                                    <option>-- Pilih --</option>
                                                    <?php $options = json_decode($field['opsi'], true); ?>
                                                    <?php if (is_array($options)): ?>
                                                        <?php foreach ($options as $opt): ?>
                                                            <option><?= htmlspecialchars($opt) ?></option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Field -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Daftar Field (<?= count($form_fields) ?>)</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($form_fields)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Belum ada field dalam formulir ini</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="15%">Tipe</th>
                                            <th width="30%">Label</th>
                                            <th width="40%">Opsi</th>
                                            <th width="15%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($form_fields as $field): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge badge-primary"><?= strtoupper($field['tipe']) ?></span>
                                                </td>
                                                <td class="font-weight-bold"><?= htmlspecialchars($field['label']) ?></td>
                                                <td>
                                                    <?php if (in_array($field['tipe'], ['radio', 'select'])): ?>
                                                        <small class="text-muted">
                                                            <?php
                                                            $options = json_decode($field['opsi'], true);
                                                            if (is_array($options)) {
                                                                echo implode(', ', array_slice($options, 0, 3));
                                                                if (count($options) > 3) echo '...';
                                                            }
                                                            ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">-</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm btn-circle" 
                                                            data-toggle="modal" 
                                                            data-target="#deleteFieldModal<?= $field['id'] ?>"
                                                            title="Hapus Field">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal Konfirmasi Hapus Field -->
                                            <div class="modal fade" id="deleteFieldModal<?= $field['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="deleteFieldModalLabel<?= $field['id'] ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteFieldModalLabel<?= $field['id'] ?>">Konfirmasi Hapus Field</h5>
                                                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">×</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Apakah Anda yakin ingin menghapus field <strong>"<?= htmlspecialchars($field['label']) ?>"</strong>?
                                                            <br><br>
                                                            <span class="text-danger">Field ini akan dihapus secara permanen!</span>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                                                            <form method="POST" action="../../../Function/FormFunction.php" class="d-inline">
                                                                <input type="hidden" name="action" value="delete_field">
                                                                <input type="hidden" name="delete_id" value="<?= $field['id'] ?>">
                                                                <input type="hidden" name="form_info_id" value="<?= $active_form_id ?>">
                                                                <button type="submit" class="btn btn-danger">
                                                                    <i class="fas fa-trash"></i> Hapus
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($show_create_form): ?>
                <!-- Pesan saat mode create tapi belum save -->
                <div class="card shadow mb-4">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-arrow-left fa-4x text-gray-300 mb-4"></i>
                        <h4 class="text-gray-800">Isi Form di Sebelah Kiri</h4>
                        <p class="text-muted mb-0">Lengkapi judul, deskripsi, dan gambar formulir terlebih dahulu, lalu klik "Simpan Formulir" untuk melanjutkan menambahkan field.</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="card shadow mb-4">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-clipboard-list fa-4x text-gray-300 mb-4"></i>
                        <h4 class="text-gray-800">Pilih atau Buat Formulir</h4>
                        <p class="text-muted mb-4">Silakan pilih formulir dari daftar di sebelah kiri atau klik tombol <strong>"Buat Form Baru"</strong> untuk memulai.</p>
                        <a href="?page=oprec&create=1" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Buat Formulir Baru
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function toggleOptions() {
        const type = document.getElementById('type').value;
        const container = document.getElementById('options-container');
        container.style.display = (type === 'radio' || type === 'select') ? 'block' : 'none';
    }

    let optionIndex = 1;

    function addOption() {
        const container = document.getElementById('options-list');
        const newDiv = document.createElement('div');
        newDiv.className = 'input-group input-group-sm mb-2';
        newDiv.innerHTML = `
            <input type="text" name="options[]" class="form-control" placeholder="Opsi ${++optionIndex}">
            <div class="input-group-append">
                <button type="button" class="btn btn-danger" onclick="removeOption(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        container.appendChild(newDiv);
        updateRemoveButtons();
    }

    function removeOption(btn) {
        btn.closest('.input-group').remove();
        updateRemoveButtons();
    }

    function updateRemoveButtons() {
        const items = document.querySelectorAll('#options-list .input-group');
        items.forEach(item => {
            const btn = item.querySelector('.btn-danger');
            if (btn) {
                btn.style.display = items.length > 1 ? 'inline-block' : 'none';
            }
        });
    }
</script>

<?php include('../SuperAdmin/Footer.php'); ?>