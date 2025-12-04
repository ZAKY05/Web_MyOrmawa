<?php
include('../SuperAdmin/Header.php');
include('../../../Config/ConnectDB.php');
include('../../../Function/KompetisiFunction.php');

// Handle operations
handleKompetisiOperations($koneksi);

// Get data
$admin_ormawa_info = getAdminOrmawaInfoKompetisi($koneksi);
$all_ormawa_list = getOrmawaListKompetisi($koneksi);
$kompetisi_list = getKompetisiData($koneksi);
?>

<style>
    /* Mengatasi konflik default DataTables */
    #dataTableKompetisi_wrapper .row:first-child {
        display: none; /* Sembunyikan search bar default DataTables */
    }
    #dataTableKompetisi_wrapper .row:last-child {
        margin-top: 15px;
    }

    /* CSS untuk Search Box yang Rapi */
    .search-box .input-group-text {
        border-right: none; 
    }

    .search-box input {
        border-left: none; 
    }

    /* Pastikan semua elemen filter di tengah baris */
    .row.align-items-center > div {
        margin-bottom: 0.5rem; 
    }
    @media (min-width: 768px) {
        .row.align-items-center > div {
            margin-bottom: 0; 
        }
    }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-trophy"></i> Manajemen Kompetisi
        </h1>
        <button type="button" class="btn btn-success btn-icon-split" data-toggle="modal" data-target="#tambahKompetisiModal" onclick="resetForm()">
            <span class="icon text-white-50">
                <i class="fas fa-plus"></i>
            </span>
            <span class="text">Tambah Kompetisi</span>
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
                    window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>?page=kompetisi';
                }, 1500);
            </script>
        <?php
            unset($_SESSION['redirect']);
        endif;
        
        unset($_SESSION['message']);
        unset($_SESSION['msg_type']);
        ?>
    <?php endif; ?>

    <div class="row mb-3 align-items-center">
        <div class="col-md-9">
            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control" 
                            placeholder="Cari: Nama Kompetisi, Penyelenggara, Ormawa..." 
                            onkeyup="filterAll()">
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <button class="btn btn-secondary w-100" onclick="resetFilters()">
                <i class="fas fa-redo"></i> Reset Filter
            </button>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Daftar Kompetisi
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableKompetisi" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">No</th>
                            <th width="8%">Poster</th>
                            <th width="20%">Nama Kompetisi</th>
                            <th width="15%">Penyelenggara</th>
                            <th width="12%">Ormawa</th>
                            <th width="15%">Tanggal</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php
                        if (count($kompetisi_list) > 0) {
                            $no = 1;
                            foreach ($kompetisi_list as $kompetisi):
                                $gambar_path = '../../../Uploads/kompetisi/' . htmlspecialchars($kompetisi['gambar'] ?? '');
                                $image_src = (file_exists($gambar_path) && !empty($kompetisi['gambar'])) ? $gambar_path : '../../../Asset/img/default-event.jpg';
                                
                                $searchText = strtolower(
                                    $kompetisi['nama_kompetisi'] . ' ' .
                                    $kompetisi['penyelenggara'] . ' ' .
                                    $kompetisi['nama_ormawa']
                                );
                        ?>
                            <tr data-search="<?php echo htmlspecialchars($searchText, ENT_QUOTES); ?>">
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td class="text-center">
                                    <img src="<?php echo $image_src; ?>" alt="Poster" class="img-thumbnail" style="max-width: 80px; max-height: 107px; object-fit: cover;">
                                </td>
                                <td><?php echo htmlspecialchars($kompetisi['nama_kompetisi'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($kompetisi['penyelenggara'] ?? ''); ?></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($kompetisi['nama_ormawa'] ?? ''); ?></span></td>
                                <td>
                                    <small>
                                        <?php echo date('d M Y', strtotime($kompetisi['tgl_mulai'])); ?> - 
                                        <?php echo date('d M Y', strtotime($kompetisi['tgl_selesai'])); ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-circle btn-sm mb-1" 
                                        data-toggle="modal" 
                                        data-target="#modalDetailKompetisi" 
                                        onclick="viewKompetisiDetail(<?php echo $kompetisi['id']; ?>)" 
                                        title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <button class="btn btn-warning btn-circle btn-sm mb-1 edit-kompetisi-btn" 
                                        data-toggle="modal" 
                                        data-target="#editKompetisiModal"
                                        data-id="<?php echo $kompetisi['id']; ?>" 
                                        data-nama="<?php echo htmlspecialchars($kompetisi['nama_kompetisi'] ?? '', ENT_QUOTES); ?>" 
                                        data-penyelenggara="<?php echo htmlspecialchars($kompetisi['penyelenggara'] ?? '', ENT_QUOTES); ?>"
                                        data-deskripsi="<?php echo htmlspecialchars($kompetisi['deskripsi'] ?? '', ENT_QUOTES); ?>" 
                                        data-tgl_mulai="<?php echo $kompetisi['tgl_mulai'] ?? ''; ?>" 
                                        data-tgl_selesai="<?php echo $kompetisi['tgl_selesai'] ?? ''; ?>" 
                                        data-ormawa_id="<?php echo $kompetisi['id_ormawa'] ?? ''; ?>" 
                                        data-gambar="<?php echo htmlspecialchars($kompetisi['gambar'] ?? '', ENT_QUOTES); ?>"
                                        data-file_panduan="<?php echo htmlspecialchars($kompetisi['file_panduan'] ?? '', ENT_QUOTES); ?>"
                                        onclick="editKompetisiFromButton(this)"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button class="btn btn-danger btn-circle btn-sm mb-1 delete-kompetisi-btn" 
                                        data-id="<?php echo $kompetisi['id']; ?>"
                                        data-nama="<?php echo htmlspecialchars($kompetisi['nama_kompetisi'] ?? '', ENT_QUOTES); ?>"
                                        onclick="deleteKompetisi(this)"
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php 
                            endforeach;
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>Tidak ada data kompetisi.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div id="noResults" class="text-center text-muted mt-3 d-none">
                    <i class="fas fa-magnifying-glass fa-2x mb-2"></i>
                    <p>Tidak ada data yang sesuai dengan pencarian Anda.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Kompetisi -->
<div class="modal fade" id="modalDetailKompetisi" tabindex="-1" role="dialog" aria-labelledby="modalDetailKompetisiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalDetailKompetisiLabel">
                    <i class="fas fa-info-circle"></i> Detail Kompetisi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailKompetisiContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php include('../FormData/TambahKompetisi.php'); ?>

<!-- Modal Edit Kompetisi -->
<div class="modal fade" id="editKompetisiModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Kompetisi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" id="formEditKompetisi">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="kompetisi_id" id="edit_kompetisi_id">
                    
                    <?php if (isset($_SESSION['user_level']) && $_SESSION['user_level'] === 2 && $admin_ormawa_info): ?>
                        <input type="hidden" name="id_ormawa" id="edit_ormawa_id_hidden" value="<?php echo $admin_ormawa_info['id']; ?>">
                        <div class="form-group">
                            <label>Ormawa Penyelenggara</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin_ormawa_info['nama_ormawa']); ?>" readonly>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="edit_ormawa_id">Ormawa Penyelenggara <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_ormawa_id" name="id_ormawa" required>
                                <option value="">Pilih Ormawa</option>
                                <?php foreach ($all_ormawa_list as $ormawa): ?>
                                    <option value="<?php echo $ormawa['id']; ?>"><?php echo htmlspecialchars($ormawa['nama_ormawa']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="edit_nama_kompetisi">Nama Kompetisi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nama_kompetisi" name="nama_kompetisi" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_penyelenggara">Penyelenggara <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_penyelenggara" name="penyelenggara" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_deskripsi">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tgl_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_tgl_mulai" name="tgl_mulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tgl_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_tgl_selesai" name="tgl_selesai" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Gambar Poster Saat Ini</label><br>
                        <div id="current_image_display"></div>
                        <input type="hidden" name="gambar_lama" id="edit_gambar_lama">
                    </div>

                    <div class="form-group">
                        <label for="edit_gambar">Ganti Gambar (Opsional)</label>
                        <input type="file" class="form-control-file" id="edit_gambar" name="gambar" accept="image/*">
                        <small class="form-text text-muted">Format: JPG/PNG. Maksimal: 2MB. Rekomendasi: 600x800px</small>
                    </div>

                    <div class="form-group">
                        <label>File Panduan Saat Ini</label><br>
                        <div id="current_file_display"></div>
                        <input type="hidden" name="file_panduan_lama" id="edit_file_panduan_lama">
                    </div>

                    <div class="form-group">
                        <label for="edit_file_panduan">Ganti File Panduan (Opsional)</label>
                        <input type="file" class="form-control-file" id="edit_file_panduan" name="file_panduan" accept=".pdf,.doc,.docx">
                        <small class="form-text text-muted">Format: PDF, DOC, DOCX. Maksimal: 10MB.</small>
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
// Data Kompetisi yang sudah di-encode dari PHP
const kompetisiData = <?php echo json_encode($kompetisi_list); ?>;
const tableBody = document.getElementById('tableBody');
const noResults = document.getElementById('noResults');

$(document).ready(function() {
    // Inisialisasi DataTables
    const dataTable = $('#dataTableKompetisi').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[0, "asc"]]
    });

    dataTable.settings()[0].oFeatures.bFilter = false; 
    filterAll(); 
});

// Fungsi Search
function filterAll() {
    const searchInput = document.getElementById('searchInput');
    const searchText = searchInput.value.toLowerCase().trim();
    
    const rows = tableBody.querySelectorAll('tr');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowSearchText = row.getAttribute('data-search') || '';
        const isSearchMatch = searchText === '' || rowSearchText.includes(searchText);

        if (isSearchMatch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    if (visibleCount === 0) {
        noResults.classList.remove('d-none');
    } else {
        noResults.classList.add('d-none');
    }
}

// Fungsi Reset Filter
function resetFilters() {
    document.getElementById('searchInput').value = '';
    filterAll();
}

function viewKompetisiDetail(id) {
    const kompetisi = kompetisiData.find(item => item.id == id);
    
    if (kompetisi) {
        let html = '<div class="row">';
        
        html += '<div class="col-md-4 text-center mb-3">';
        const imagePath = '../../../Uploads/kompetisi/' + kompetisi.gambar;
        const imageSrc = kompetisi.gambar ? imagePath : '../../../Asset/img/default-event.jpg';
        
        html += '<img src="' + imageSrc + '" alt="Poster" class="img-thumbnail" style="max-width: 100%; max-height: 300px; object-fit: cover;">';
        html += '</div>';
        
        html += '<div class="col-md-8">';
        html += '<h4>' + kompetisi.nama_kompetisi + '</h4>';
        html += '<p><span class="badge badge-info">' + kompetisi.nama_ormawa + '</span></p>';
        html += '<hr>';
        
        html += '<p><strong><i class="fas fa-building"></i> Penyelenggara:</strong><br>' + kompetisi.penyelenggara + '</p>';
        html += '<p><strong><i class="fas fa-align-left"></i> Deskripsi:</strong><br>' + (kompetisi.deskripsi || '-') + '</p>';
        html += '<p><strong><i class="fas fa-calendar"></i> Tanggal:</strong><br>' + kompetisi.tgl_mulai + ' s/d ' + kompetisi.tgl_selesai + '</p>';
        
        if (kompetisi.file_panduan) {
            html += '<p><strong><i class="fas fa-file"></i> File Panduan:</strong> <a href="../../../Uploads/kompetisi_panduan/' + kompetisi.file_panduan + '" target="_blank" class="btn btn-sm btn-danger"><i class="fas fa-download"></i> Download</a></p>';
        }
        
        html += '</div>';
        html += '</div>';
        
        document.getElementById('detailKompetisiContent').innerHTML = html;
    }
}

function editKompetisiFromButton(button) {
    const id = button.getAttribute('data-id');
    const nama = button.getAttribute('data-nama');
    const penyelenggara = button.getAttribute('data-penyelenggara');
    const deskripsi = button.getAttribute('data-deskripsi');
    const tgl_mulai = button.getAttribute('data-tgl_mulai');
    const tgl_selesai = button.getAttribute('data-tgl_selesai');
    const ormawa_id = button.getAttribute('data-ormawa_id');
    const gambar = button.getAttribute('data-gambar');
    const file_panduan = button.getAttribute('data-file_panduan');
    
    document.getElementById('edit_kompetisi_id').value = id || '';
    document.getElementById('edit_nama_kompetisi').value = nama || '';
    document.getElementById('edit_penyelenggara').value = penyelenggara || '';
    document.getElementById('edit_deskripsi').value = deskripsi || '';
    document.getElementById('edit_tgl_mulai').value = tgl_mulai || '';
    document.getElementById('edit_tgl_selesai').value = tgl_selesai || '';
    document.getElementById('edit_gambar_lama').value = gambar || '';
    document.getElementById('edit_file_panduan_lama').value = file_panduan || '';
    
    const ormawaSelect = document.getElementById('edit_ormawa_id');
    if (ormawaSelect) {
        ormawaSelect.value = ormawa_id || '';
    }
    
    const imagePreview = document.getElementById('current_image_display');
    if (gambar) {
        imagePreview.innerHTML = 
            '<img src="../../../Uploads/kompetisi/' + gambar + '" alt="Current Poster" style="max-width: 150px; max-height: 200px;" class="border rounded">' +
            '<p class="text-muted small mt-1">Poster saat ini (upload file baru untuk mengganti)</p>';
    } else {
        imagePreview.innerHTML = 
            '<p class="text-muted small">Tidak ada poster. Upload file untuk menambahkan poster.</p>';
    }
    
    const filePreview = document.getElementById('current_file_display');
    if (file_panduan) {
        filePreview.innerHTML = 
            '<span class="badge badge-danger">' + file_panduan + '</span>' +
            '<p class="text-muted small mt-1">File panduan saat ini (upload file baru untuk mengganti)</p>';
    } else {
        filePreview.innerHTML = 
            '<span class="badge badge-secondary">Tidak ada</span>' +
            '<p class="text-muted small mt-1">Upload file untuk menambahkan panduan</p>';
    }
}

function resetForm() {
    const modalTambah = document.getElementById('tambahKompetisiModal');
    if (modalTambah) {
        const form = modalTambah.querySelector('form');
        if (form) form.reset();
    }
}

function deleteKompetisi(button) {
    const id = button.getAttribute('data-id');
    const nama = button.getAttribute('data-nama');
    
    if (confirm('Yakin ingin menghapus kompetisi "' + nama + '"?\n\nData yang dihapus tidak dapat dikembalikan!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'hapus';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'kompetisi_id';
        idInput.value = id;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Event Listeners untuk preview file
document.getElementById('edit_gambar')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 2000000) {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
            this.value = '';
            return;
        }
        
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung! Gunakan JPG, JPEG, atau PNG.');
            this.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('current_image_display').innerHTML = 
                '<img src="' + e.target.result + '" alt="Preview" style="max-width: 150px; max-height: 200px;" class="border rounded">' +
                '<p class="text-muted small mt-1">Preview gambar baru</p>';
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('edit_file_panduan')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 10000000) {
            alert('Ukuran file terlalu besar! Maksimal 10MB.');
            this.value = '';
            return;
        }
        
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung! Gunakan PDF, DOC, atau DOCX.');
            this.value = '';
            return;
        }
        
        document.getElementById('current_file_display').innerHTML = 
            '<span class="badge badge-danger">' + file.name + '</span>' +
            '<p class="text-muted small mt-1">File baru dipilih</p>';
    }
});

// Loading state saat submit
$('form').on('submit', function() {
    $(this).find('button[type="submit"]').prop('disabled', true);
    $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
});
</script>

<?php include('../SuperAdmin/Footer.php'); ?>