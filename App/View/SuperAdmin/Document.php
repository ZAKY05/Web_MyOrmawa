<?php
// SUPERADMIN - Document Management
include('Header.php');
include('../../../Config/ConnectDB.php');
include('../../../Function/DocumentFunction.php');

// Handle operations
handleDocumentOperations($koneksi);

// Get user info
$user_level = $_SESSION['user_level'] ?? 0;
$ormawa_id_session = $_SESSION['ormawa_id'] ?? 0;

// Get ormawa list for dropdown
$all_ormawa_list = getOrmawaList($koneksi);

// Get documents based on user level
$dokumen_list = getDocumentData($koneksi);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-alt"></i> Manajemen Dokumen
        </h1>
        <button type="button" class="btn btn-success btn-icon-split" data-toggle="modal" data-target="#tambahDokumenModal" onclick="resetForm()">
            <span class="icon text-white-50">
                <i class="fas fa-plus"></i>
            </span>
            <span class="text">Tambah Dokumen</span>
        </button>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $_SESSION['msg_type'] == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        
        <?php
        if (isset($_SESSION['redirect']) && $_SESSION['redirect'] === true):
        ?>
            <script>
                setTimeout(function() {
                    window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>?page=doc';
                }, 1500);
            </script>
        <?php
            unset($_SESSION['redirect']);
        endif;
        
        unset($_SESSION['message']);
        unset($_SESSION['msg_type']);
        ?>
    <?php endif; ?>

    <!-- Filter & Search -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="searchInput" placeholder="Cari nama dokumen...">
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-control" id="filterJenis">
                <option value="">Semua Jenis</option>
                <option value="Proposal">Proposal</option>
                <option value="SPJ">SPJ</option>
                <option value="LPJ">LPJ</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-control" id="filterOrmawa">
                <option value="">Semua Ormawa</option>
                <?php foreach ($all_ormawa_list as $ormawa): ?>
                    <option value="<?php echo htmlspecialchars($ormawa['nama_ormawa']); ?>">
                        <?php echo htmlspecialchars($ormawa['nama_ormawa']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-secondary btn-block" onclick="resetFilters()">
                <i class="fas fa-redo"></i> Reset
            </button>
        </div>
    </div>

    <!-- Document Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Daftar Dokumen
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableDokumen" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">No</th>
                            <th width="25%">Nama Dokumen</th>
                            <th width="12%">Jenis</th>
                            <th width="15%">Ormawa</th>
                            <th width="12%">Tanggal Upload</th>
                            <th width="8%">Ukuran</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($dokumen_list) > 0) {
                            $no = 1;
                            foreach ($dokumen_list as $doc):
                        ?>
                            <tr data-jenis="<?php echo htmlspecialchars($doc['jenis_dokumen']); ?>" 
                                data-nama="<?php echo htmlspecialchars(strtolower($doc['nama_dokumen'])); ?>"
                                data-ormawa="<?php echo htmlspecialchars($doc['nama_ormawa']); ?>">
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td>
                                    <i class="fas fa-file-alt text-info mr-2"></i>
                                    <?php echo htmlspecialchars($doc['nama_dokumen'] ?? ''); ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo ($doc['jenis_dokumen'] == 'Proposal') ? 'primary' : 
                                             (($doc['jenis_dokumen'] == 'SPJ') ? 'success' : 'warning'); 
                                    ?>">
                                        <?php echo htmlspecialchars($doc['jenis_dokumen'] ?? ''); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($doc['nama_ormawa'] ?? ''); ?></td>
                                <td>
                                    <small><?php echo date('d M Y, H:i', strtotime($doc['tanggal_upload'])); ?></small>
                                </td>
                                <td>
                                    <small><?php echo number_format($doc['ukuran_file'], 2); ?> KB</small>
                                </td>
                                <td class="text-center">
                                    <a href="../../../Uploads/dokumen/<?php echo urlencode($doc['file_path']); ?>" 
                                       class="btn btn-info btn-circle btn-sm mb-1" 
                                       target="_blank"
                                       title="Lihat/Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    
                                    <button class="btn btn-warning btn-circle btn-sm mb-1 edit-doc-btn" 
                                        data-toggle="modal" 
                                        data-target="#editDokumenModal"
                                        data-id="<?php echo $doc['id']; ?>" 
                                        data-nama="<?php echo htmlspecialchars($doc['nama_dokumen'] ?? '', ENT_QUOTES); ?>" 
                                        data-jenis="<?php echo htmlspecialchars($doc['jenis_dokumen'] ?? '', ENT_QUOTES); ?>"
                                        data-ormawa_id="<?php echo $doc['id_ormawa'] ?? ''; ?>"
                                        data-file_path="<?php echo htmlspecialchars($doc['file_path'] ?? '', ENT_QUOTES); ?>"
                                        onclick="editDocumentFromButton(this)"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button class="btn btn-danger btn-circle btn-sm mb-1 delete-doc-btn" 
                                        data-id="<?php echo $doc['id']; ?>"
                                        data-nama="<?php echo htmlspecialchars($doc['nama_dokumen'] ?? '', ENT_QUOTES); ?>"
                                        onclick="deleteDocument(this)"
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php 
                            endforeach;
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>Tidak ada data dokumen.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Dokumen -->
<div class="modal fade" id="tambahDokumenModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Dokumen Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" id="formTambahDokumen">
                <div class="modal-body">
                    <input type="hidden" name="action" value="tambah">

                    <div class="form-group">
                        <label for="id_ormawa">Pilih Ormawa <span class="text-danger">*</span></label>
                        <select class="form-control" id="id_ormawa" name="id_ormawa" required>
                            <option value="">Pilih Ormawa</option>
                            <?php foreach ($all_ormawa_list as $ormawa): ?>
                                <option value="<?php echo (int)$ormawa['id']; ?>">
                                    <?php echo htmlspecialchars($ormawa['nama_ormawa']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Pilih organisasi pemilik dokumen ini.</small>
                    </div>

                    <div class="form-group">
                        <label for="nama_dokumen">Nama Dokumen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_dokumen" name="nama_dokumen" placeholder="Contoh: Proposal Kegiatan Seminar Nasional 2025" required maxlength="255">
                        <small class="form-text text-muted">Berikan nama yang jelas dan deskriptif.</small>
                    </div>

                    <div class="form-group">
                        <label for="jenis_dokumen">Jenis Dokumen <span class="text-danger">*</span></label>
                        <select class="form-control" id="jenis_dokumen" name="jenis_dokumen" required>
                            <option value="">Pilih Jenis Dokumen</option>
                            <option value="Proposal">Proposal</option>
                            <option value="SPJ">SPJ (Surat Pertanggungjawaban)</option>
                            <option value="LPJ">LPJ (Laporan Pertanggungjawaban)</option>
                        </select>
                        <small class="form-text text-muted">Tentukan kategori dokumen untuk memudahkan pencarian.</small>
                    </div>

                    <div class="form-group">
                        <label for="file_dokumen">Upload File Dokumen <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" id="file_dokumen" name="file_dokumen" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                        <small class="form-text text-muted">
                            <strong>Format yang didukung:</strong> PDF, DOC, DOCX, XLS, XLSX<br>
                            <strong>Ukuran maksimal:</strong> 10 MB
                        </small>
                    </div>

                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Catatan:</strong> Pastikan file yang Anda upload sudah benar karena dokumen ini akan tersimpan dalam sistem dan dapat diakses oleh admin terkait.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Dokumen -->
<div class="modal fade" id="editDokumenModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Dokumen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" id="formEditDokumen">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="dokumen_id" id="edit_dokumen_id">
                    <input type="hidden" name="file_path_lama" id="edit_file_path_lama">
                    
                    <div class="form-group">
                        <label for="edit_ormawa_id">Ormawa <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_ormawa_id" name="id_ormawa" required>
                            <option value="">Pilih Ormawa</option>
                            <?php foreach ($all_ormawa_list as $ormawa): ?>
                                <option value="<?php echo $ormawa['id']; ?>"><?php echo htmlspecialchars($ormawa['nama_ormawa']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_nama_dokumen">Nama Dokumen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nama_dokumen" name="nama_dokumen" required maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="edit_jenis_dokumen">Jenis Dokumen <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_jenis_dokumen" name="jenis_dokumen" required>
                            <option value="">Pilih Jenis</option>
                            <option value="Proposal">Proposal</option>
                            <option value="SPJ">SPJ (Surat Pertanggungjawaban)</option>
                            <option value="LPJ">LPJ (Laporan Pertanggungjawaban)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>File Dokumen Saat Ini</label><br>
                        <div id="current_file_display"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_file_dokumen">Ganti File (Opsional)</label>
                        <input type="file" class="form-control-file" id="edit_file_dokumen" name="file_dokumen" accept=".pdf,.doc,.docx,.xls,.xlsx">
                        <small class="form-text text-muted">Format: PDF, DOC, DOCX, XLS, XLSX. Maksimal: 10MB. Biarkan kosong jika tidak ingin mengganti.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#dataTableDokumen').DataTable({
        "order": [[4, "desc"]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    // Filter & Search Real-time
    const searchInput = document.getElementById('searchInput');
    const filterJenis = document.getElementById('filterJenis');
    const filterOrmawa = document.getElementById('filterOrmawa');
    const rows = document.querySelectorAll('#dataTableDokumen tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const jenisFilter = filterJenis.value;
        const ormawaFilter = filterOrmawa.value;

        rows.forEach(row => {
            const nama = row.dataset.nama || '';
            const jenis = row.dataset.jenis || '';
            const ormawa = row.dataset.ormawa || '';

            const matchesSearch = searchTerm === '' || nama.includes(searchTerm);
            const matchesJenis = jenisFilter === '' || jenis === jenisFilter;
            const matchesOrmawa = ormawaFilter === '' || ormawa === ormawaFilter;

            row.style.display = (matchesSearch && matchesJenis && matchesOrmawa) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterTable);
    filterJenis.addEventListener('change', filterTable);
    filterOrmawa.addEventListener('change', filterTable);
});

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterJenis').value = '';
    document.getElementById('filterOrmawa').value = '';
    
    // Show all rows
    const rows = document.querySelectorAll('#dataTableDokumen tbody tr');
    rows.forEach(row => {
        row.style.display = '';
    });
}

function editDocumentFromButton(button) {
    const id = button.getAttribute('data-id');
    const nama = button.getAttribute('data-nama');
    const jenis = button.getAttribute('data-jenis');
    const ormawa_id = button.getAttribute('data-ormawa_id');
    const file_path = button.getAttribute('data-file_path');
    
    document.getElementById('edit_dokumen_id').value = id || '';
    document.getElementById('edit_nama_dokumen').value = nama || '';
    document.getElementById('edit_jenis_dokumen').value = jenis || '';
    document.getElementById('edit_file_path_lama').value = file_path || '';
    document.getElementById('edit_ormawa_id').value = ormawa_id || '';
    
    const filePreview = document.getElementById('current_file_display');
    if (file_path) {
        const ext = file_path.split('.').pop().toLowerCase();
        let icon = 'fa-file';
        if (ext === 'pdf') icon = 'fa-file-pdf text-danger';
        else if (['doc', 'docx'].includes(ext)) icon = 'fa-file-word text-primary';
        else if (['xls', 'xlsx'].includes(ext)) icon = 'fa-file-excel text-success';
        
        filePreview.innerHTML = 
            '<i class="fas ' + icon + ' fa-2x mr-2"></i>' +
            '<span class="badge badge-secondary">' + file_path + '</span>' +
            '<p class="text-muted small mt-2">Upload file baru untuk mengganti dokumen ini</p>';
    } else {
        filePreview.innerHTML = '<p class="text-muted small">Tidak ada file</p>';
    }
}

function resetForm() {
    const form = document.getElementById('formTambahDokumen');
    if (form) form.reset();
}

function deleteDocument(button) {
    const id = button.getAttribute('data-id');
    const nama = button.getAttribute('data-nama');
    
    if (confirm('Yakin ingin menghapus dokumen "' + nama + '"?\n\nData yang dihapus tidak dapat dikembalikan!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'hapus';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'dokumen_id';
        idInput.value = id;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

document.getElementById('file_dokumen')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 10000000) {
            alert('Ukuran file terlalu besar! Maksimal 10MB.');
            this.value = '';
            return;
        }
        
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung! Gunakan PDF, DOC, DOCX, XLS, atau XLSX.');
            this.value = '';
            return;
        }
    }
});

document.getElementById('edit_file_dokumen')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 10000000) {
            alert('Ukuran file terlalu besar! Maksimal 10MB.');
            this.value = '';
            return;
        }
        
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung! Gunakan PDF, DOC, DOCX, XLS, atau XLSX.');
            this.value = '';
            return;
        }
        
        document.getElementById('current_file_display').innerHTML = 
            '<i class="fas fa-file fa-2x text-success mr-2"></i>' +
            '<span class="badge badge-success">' + file.name + '</span>' +
            '<p class="text-muted small mt-2">File baru dipilih (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)</p>';
    }
});

$('form').on('submit', function() {
    $(this).find('button[type="submit"]').prop('disabled', true);
    $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
});
</script>

<?php include('Footer.php'); ?>