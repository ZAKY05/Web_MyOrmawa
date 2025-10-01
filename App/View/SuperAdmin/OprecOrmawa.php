<?php
include '../../../Config/ConnectDB.php'; // Pastikan file ini berisi koneksi MySQLi seperti yang kamu berikan

// Handle delete submission
if (isset($_GET['delete_user']) && !empty($_GET['delete_user'])) {
    $username = mysqli_real_escape_string($koneksi, $_GET['delete_user']);
    $query = "DELETE FROM submissions WHERE username = '$username'";
    mysqli_query($koneksi, $query);
    header("Location: index.php?deleted_submission=1");
    exit;
}

// Ambil data form fields
$result = mysqli_query($koneksi, "SELECT * FROM form ORDER BY id ASC");
$fields = [];
while ($row = mysqli_fetch_assoc($result)) {
    $fields[] = $row;
}

// Ambil jumlah submission unik
$countQuery = "SELECT COUNT(DISTINCT CONCAT(user_id, '-', form_id)) as total FROM submit WHERE user_id IS NOT NULL";
$countResult = mysqli_query($koneksi, $countQuery);
$submissionCountRow = mysqli_fetch_assoc($countResult);
$submissionCount = $submissionCountRow['total'] ?? 0;

include('../SuperAdmin/Header.php');
?>

<!-- Alert untuk submission deleted -->
<?php if (isset($_GET['deleted_submission'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> Data submission berhasil dihapus!
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cogs me-2"></i> Form Builder Pro
        </h1>
        <p class="text-muted">Buat dan kelola form dinamis dengan mudah</p>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Fields</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($fields) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6 mb-4">
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

    <!-- Alerts -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> Field berhasil ditambahkan!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-trash"></i> Field berhasil dihapus!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Kolom Kiri: Tambah & Daftar Field -->
        <div class="col-xl-6 col-lg-6">
            <!-- Tambah Field Baru -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-plus"></i> Tambah Field Baru
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="../../../Function/FormFunction.php">
                        <input type="hidden" name="action" value="add_field">
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Label Field</label>
                            <input type="text" name="label" class="form-control" placeholder="Masukkan label field" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-list"></i> Tipe Field</label>
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
                            <label><i class="fas fa-list-ul"></i> Opsi Pilihan</label>
                            <div id="options-list" class="mt-2">
                                <div class="d-flex mb-2">
                                    <input type="text" name="options[]" class="form-control mr-2" placeholder="Opsi 1">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)" style="display:none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addOption()">
                                <i class="fas fa-plus"></i> Tambah Opsi
                            </button>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block mt-3">
                            <i class="fas fa-plus"></i> Tambah Field
                        </button>
                    </form>
                </div>
            </div>

            <!-- Daftar Field -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="fas fa-list"></i> Daftar Field (<?= count($fields) ?>)
                    </h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($fields)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada field yang dibuat</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($fields as $field): ?>
                            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                                <div>
                                    <span class="badge badge-primary"><?= strtoupper($field['tipe']) ?></span>
                                    <strong><?= htmlspecialchars($field['label']) ?></strong>
                                    <?php if (in_array($field['tipe'], ['radio', 'select'])): ?>
                                        <div class="text-muted small mt-1">
                                            <?php
                                            $options = json_decode($field['options'], true);
                                            if (is_array($options)) {
                                                echo '<i class="fas fa-tags"></i> ' . implode(', ', array_slice($options, 0, 3));
                                                if (count($options) > 3) echo '...';
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <form method="POST" action="../../../Function/FormFunction.php" class="ml-2" onsubmit="return confirm('Hapus field ini?')">
                                    <input type="hidden" name="action" value="delete_field">
                                    <input type="hidden" name="delete_id" value="<?= $field['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Preview Form -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-eye"></i> Preview Form
                    </h6>
                </div>
                <div class="card-body bg-light">
                    <div class="p-4 bg-white rounded">
                        <h6 class="font-weight-bold text-gray-800 mb-3">
                            <i class="fas fa-user"></i> Informasi Pengguna
                        </h6>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" placeholder="Masukkan username">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" placeholder="nama@email.com">
                        </div>

                        <hr>

                        <h6 class="font-weight-bold text-gray-800 mb-3">
                            <i class="fas fa-edit"></i> Form Fields
                        </h6>

                        <?php if (empty($fields)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>Belum ada field untuk ditampilkan</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($fields as $field): ?>
                                <div class="form-group">
                                    <label><?= htmlspecialchars($field['label']) ?></label>
                                    <?php if ($field['tipe'] === 'text'): ?>
                                        <input type="text" class="form-control" placeholder="Masukkan <?= strtolower($field['label']) ?>">
                                    <?php elseif ($field['tipe'] === 'email'): ?>
                                        <input type="email" class="form-control" placeholder="nama@email.com">
                                    <?php elseif ($field['type'] === 'number'): ?>
                                        <input type="number" class="form-control" placeholder="Masukkan angka">
                                    <?php elseif ($field['tipe'] === 'textarea'): ?>
                                        <textarea class="form-control" rows="3" placeholder="Masukkan <?= strtolower($field['label']) ?>"></textarea>
                                    <?php elseif ($field['tipe'] === 'file'): ?>
                                        <input type="file" class="form-control">
                                    <?php elseif ($field['tipe'] === 'radio'): ?>
                                        <div class="mt-2">
                                            <?php $options = json_decode($field['options'], true); ?>
                                            <?php if (is_array($options)): ?>
                                                <?php foreach ($options as $opt): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="preview_<?= $field['id'] ?>">
                                                        <label class="form-check-label"><?= htmlspecialchars($opt) ?></label>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif ($field['type'] === 'select'): ?>
                                        <select class="form-control">
                                            <option value="">-- Pilih <?= $field['label'] ?> --</option>
                                            <?php $options = json_decode($field['options'], true); ?>
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
    </div>

    <!-- Action Buttons -->
    <div class="text-center mt-4">
        <a href="view_forms.php" class="btn btn-primary mr-2">
            <i class="fas fa-eye"></i> Preview Form Full
        </a>
        <button id="toggleSubmissions" class="btn btn-secondary">
            <i class="fas fa-database"></i> Lihat Submissions
        </button>
    </div>

    <!-- Area Tabel Submissions (Sembunyi Awal) -->
    <div id="submissionsTableContainer" class="mt-5" style="display: none;">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-table"></i> Data Submissions
                </h6>
            </div>
            <div class="card-body">
                <?php
                // Ambil data submissions per user
                $stmt = "SELECT 
                            username, 
                            email,
                            MIN(created_at) as first_submit,
                            MAX(created_at) as last_submit,
                            COUNT(*) as field_count
                         FROM submissions 
                         WHERE username IS NOT NULL 
                         GROUP BY username, email 
                         ORDER BY last_submit DESC";
                $result = mysqli_query($koneksi, $stmt);
                $userSubmissions = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $userSubmissions[] = $row;
                }
                ?>

                <?php if (empty($userSubmissions)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>Belum ada data submission.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>User Info</th>
                                    <th>Fields</th>
                                    <th>Submit Info</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userSubmissions as $sub): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($sub['username']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($sub['email']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary"><?= $sub['field_count'] ?> fields</span>
                                    </td>
                                    <td>
                                        <small>
                                            First: <?= date('d/m/Y H:i', strtotime($sub['first_submit'])) ?><br>
                                            Last: <?= date('d/m/Y H:i', strtotime($sub['last_submit'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-sm mr-2" 
                                                onclick="viewUserDetails('<?= addslashes($sub['username']) ?>', '<?= addslashes($sub['email']) ?>')">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                        <a href="?delete_user=<?= urlencode($sub['username']) ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Hapus semua data dari user <?= addslashes($sub['username']) ?>?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Submissions -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="fas fa-user-circle"></i> Detail Submission: <span id="modalUsername"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="mt-2">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
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
        newDiv.className = 'd-flex mb-2';
        newDiv.innerHTML = `
            <input type="text" name="options[]" class="form-control mr-2" placeholder="Opsi ${++optionIndex}">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(newDiv);
        updateRemoveButtons();
    }

    function removeOption(btn) {
        btn.closest('.d-flex').remove();
        updateRemoveButtons();
    }

    function updateRemoveButtons() {
        const items = document.querySelectorAll('#options-list .d-flex');
        items.forEach(item => {
            const btn = item.querySelector('.btn-danger');
            btn.style.display = items.length > 1 ? 'inline-block' : 'none';
        });
    }

    document.getElementById('toggleSubmissions').addEventListener('click', function() {
        const container = document.getElementById('submissionsTableContainer');
        const isHidden = container.style.display === 'none' || container.style.display === '';
        container.style.display = isHidden ? 'block' : 'none';
        this.innerHTML = isHidden 
            ? '<i class="fas fa-eye-slash"></i> Sembunyikan Submissions' 
            : '<i class="fas fa-database"></i> Lihat Submissions';
    });

    function viewUserDetails(username, email) {
        document.getElementById('modalUsername').textContent = username + ' (' + email + ')';
        $('#detailModal').modal('show');
        
        fetch('get_user_details.php?username=' + encodeURIComponent(username))
            .then(response => response.text())
            .then(html => {
                document.getElementById('modalBody').innerHTML = html;
            })
            .catch(() => {
                document.getElementById('modalBody').innerHTML = '<div class="alert alert-danger">Gagal memuat data.</div>';
            });
    }
</script>

<?php include('../SuperAdmin/Footer.php'); ?>