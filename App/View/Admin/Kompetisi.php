<?php
// Admin/Ormawa/Kompetisi.php

// 1. Inisialisasi dan Keamanan
include('../../../Config/ConnectDB.php');
include('../../../Function/KompetisiFunction.php');

// Batasi akses hanya untuk level 2 (Admin Ormawa)
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 2) {
    header("Location: ../SuperAdmin/Login.php");
    exit();
}

// Tangani operasi CRUD (Tambah/Edit/Hapus)
handleKompetisiOperations($koneksi);
include('Header.php');

// 2. Pengambilan Data
$kompetisi_list = getKompetisiData($koneksi); 
$admin_ormawa_info = getAdminOrmawaInfoKompetisi($koneksi);
?>

<style>
    /* Styling untuk menyembunyikan fitur default DataTables jika digunakan */
    #dataTableKompetisi_wrapper .row:first-child {
        display: none; 
    }
    #dataTableKompetisi_wrapper .row:last-child {
        margin-top: 15px;
    }

    /* Styling untuk Search Box */
    .search-box .input-group-text {
        border-right: none; 
        background-color: #e9ecef;
    }
    .search-box input {
        border-left: none; 
    }
    
    .filter-section .row > div {
        margin-bottom: 0.5rem; 
    }
    @media (min-width: 768px) {
        .filter-section .row > div {
            margin-bottom: 0; 
        }
    }
</style>

<div class="container-fluid">
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-trophy me-2"></i>Manajemen Kompetisi 
            <?= htmlspecialchars($_SESSION['ormawa_nama'] ?? ''); ?>
        </h1>
        <button type="button" class="btn btn-success btn-icon-split" 
                data-bs-toggle="modal" data-bs-target="#tambahKompetisiModal">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Tambah Kompetisi</span>
        </button>
    </div>

    <div class="filter-section mb-4">
        <div class="row align-items-center">
            
            <div class="col-md-9">
                <div class="search-box">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control" 
                                placeholder="Cari: Nama Kompetisi, Penyelenggara..." 
                                onkeyup="filterAll()">
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <button class="btn btn-secondary w-100" onclick="resetFilters()">
                    <i class="fas fa-redo me-1"></i> Reset Filter
                </button>
            </div>
            
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Daftar Kompetisi
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableKompetisi" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Gambar</th>
                            <th>Nama Kompetisi</th>
                            <th>Penyelenggara</th>
                            <th>Ormawa</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBodyKompetisi">
                        <?php if (!empty($kompetisi_list)): ?>
                            <?php foreach ($kompetisi_list as $index => $kompetisi): ?>
                                <?php
                                $gambar_path = '../../../Uploads/kompetisi/' . $kompetisi['gambar'];
                                $image_src = (file_exists($gambar_path) && !empty($kompetisi['gambar'])) 
                                    ? $gambar_path 
                                    : 'https://via.placeholder.com/50?text=No+Image';
                                
                                $searchText = strtolower(
                                    $kompetisi['nama_kompetisi'] . ' ' .
                                    $kompetisi['penyelenggara'] . ' ' .
                                    $kompetisi['nama_ormawa']
                                );
                                ?>
                                <tr data-search="<?= htmlspecialchars($searchText, ENT_QUOTES); ?>">
                                    <td><?= $index + 1; ?></td>
                                    <td>
                                        <img src="<?= $image_src; ?>" width="50" height="50" class="img-thumbnail rounded">
                                    </td>
                                    <td><?= htmlspecialchars($kompetisi['nama_kompetisi']); ?></td>
                                    <td><?= htmlspecialchars($kompetisi['penyelenggara']); ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($kompetisi['nama_ormawa']); ?></span></td>
                                    <td>
                                        <small>
                                            <?= date('d M Y', strtotime($kompetisi['tgl_mulai'])); ?><br>
                                            <i class="fas fa-arrow-right"></i>
                                            <?= date('d M Y', strtotime($kompetisi['tgl_selesai'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-circle btn-sm view-btn"
                                            data-id="<?= (int)$kompetisi['id']; ?>"
                                            title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button class="btn btn-warning btn-circle btn-sm edit-btn"
                                            data-id="<?= (int)$kompetisi['id']; ?>"
                                            data-nama="<?= htmlspecialchars($kompetisi['nama_kompetisi'], ENT_QUOTES); ?>"
                                            data-penyelenggara="<?= htmlspecialchars($kompetisi['penyelenggara'], ENT_QUOTES); ?>"
                                            data-deskripsi="<?= htmlspecialchars($kompetisi['deskripsi'], ENT_QUOTES); ?>"
                                            data-tgl_mulai="<?= $kompetisi['tgl_mulai']; ?>"
                                            data-tgl_selesai="<?= $kompetisi['tgl_selesai']; ?>"
                                            data-ormawa_id="<?= (int)$kompetisi['id_ormawa']; ?>"
                                            data-gambar="<?= htmlspecialchars($kompetisi['gambar']); ?>"
                                            data-file_panduan="<?= htmlspecialchars($kompetisi['file_panduan']); ?>"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button class="btn btn-danger btn-circle btn-sm delete-btn" 
                                                data-id="<?= (int)$kompetisi['id']; ?>"
                                                data-nama="<?= htmlspecialchars($kompetisi['nama_kompetisi'], ENT_QUOTES); ?>"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="default-no-data">
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-trophy fa-2x mb-2"></i>
                                    <p class="mb-0">Belum ada kompetisi terdaftar.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr id="noResults" class="text-center text-muted d-none">
                            <td colspan="7" class="py-4">
                                <i class="fas fa-magnifying-glass fa-2x mb-2"></i>
                                <p>Tidak ada data yang sesuai dengan pencarian Anda.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal Detail Kompetisi -->
<div class="modal fade" id="detailKompetisiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Detail Kompetisi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailKompetisiContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php include('../FormData/TambahKompetisi.php'); ?>

<!-- Modal Edit Kompetisi -->
<div class="modal fade" id="editKompetisiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Kompetisi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editKompetisiForm" method="POST" enctype="multipart/form-data" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="kompetisi_id" id="edit_kompetisi_id">
                
                <div class="modal-body">
                    <input type="hidden" name="id_ormawa" value="<?= (int)$_SESSION['ormawa_id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Ormawa</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['ormawa_nama']); ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Kompetisi</label>
                        <input type="text" class="form-control" name="nama_kompetisi" id="edit_nama_kompetisi" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Penyelenggara</label>
                        <input type="text" class="form-control" name="penyelenggara" id="edit_penyelenggara" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="tgl_mulai" id="edit_tgl_mulai" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" name="tgl_selesai" id="edit_tgl_selesai" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Gambar Saat Ini</label><br>
                        <img id="current_image_display" src="" width="120" class="img-thumbnail rounded">
                        <input type="hidden" name="gambar_lama" id="edit_gambar_lama">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ganti Gambar (Opsional)</label>
                        <input type="file" class="form-control" name="gambar" id="edit_gambar" accept="image/*">
                        <div class="form-text text-muted">Format: JPG, PNG (maks 2MB)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">File Panduan Saat Ini</label><br>
                        <div id="current_file_display"></div>
                        <input type="hidden" name="file_panduan_lama" id="edit_file_panduan_lama">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ganti File Panduan (Opsional)</label>
                        <input type="file" class="form-control" name="file_panduan" id="edit_file_panduan" accept=".pdf,.doc,.docx">
                        <div class="form-text text-muted">Format: PDF, DOC, DOCX (maks 10MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('Footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Data Kompetisi untuk detail view
const kompetisiData = <?php echo json_encode($kompetisi_list); ?>;

/**
 * Logika Filter dan Pencarian Client-Side
 */
function filterAll() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const tableBody = document.getElementById('tableBodyKompetisi');
    const rows = tableBody.getElementsByTagName('tr');
    let visibleRowCount = 0;

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        
        // Lewati baris yang tidak berisi data
        if (row.id === 'noResults' || row.classList.contains('default-no-data')) continue; 

        const searchTextData = row.getAttribute('data-search') || '';
        const searchMatch = searchTextData.includes(searchInput);

        if (searchMatch) {
            row.style.display = '';
            visibleRowCount++;
        } else {
            row.style.display = 'none';
        }
    }

    // Tampilkan/Sembunyikan pesan "Tidak ada hasil"
    const noResultsRow = document.getElementById('noResults');
    const defaultNoDataRow = document.querySelector('.default-no-data');

    if (visibleRowCount === 0) {
        if (defaultNoDataRow) {
            noResultsRow.classList.add('d-none');
        } else {
            noResultsRow.classList.remove('d-none');
        }
    } else {
        noResultsRow.classList.add('d-none');
    }
}

/**
 * Mereset input dan filter tabel
 */
function resetFilters() {
    document.getElementById('searchInput').value = '';
    filterAll(); 
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Notifikasi SweetAlert dari SESSION
    <?php if (isset($_SESSION['message'])): ?>
        Swal.fire({
            icon: '<?= $_SESSION['msg_type'] === 'success' ? 'success' : 'error'; ?>',
            title: '<?= $_SESSION['msg_type'] === 'success' ? 'Berhasil!' : 'Gagal!'; ?>',
            text: '<?= addslashes($_SESSION['message']); ?>',
            timer: <?= $_SESSION['msg_type'] === 'success' ? 2000 : 3000; ?>,
            showConfirmButton: false
        });
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['msg_type']);
        ?>
    <?php endif; ?>

    // 2. Logic View Detail Kompetisi
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = parseInt(this.getAttribute('data-id'));
            const kompetisi = kompetisiData.find(item => item.id == id);
            
            if (kompetisi) {
                let html = '<div class="row">';
                
                html += '<div class="col-md-4 text-center mb-3">';
                const imagePath = '../../../Uploads/kompetisi/' + kompetisi.gambar;
                const imageSrc = kompetisi.gambar ? imagePath : 'https://via.placeholder.com/300?text=No+Image';
                
                html += '<img src="' + imageSrc + '" alt="Poster" class="img-thumbnail" style="max-width: 100%; max-height: 300px; object-fit: cover;">';
                html += '</div>';
                
                html += '<div class="col-md-8">';
                html += '<h4>' + kompetisi.nama_kompetisi + '</h4>';
                html += '<p><span class="badge bg-info">' + kompetisi.nama_ormawa + '</span></p>';
                html += '<hr>';
                
                html += '<p><strong><i class="fas fa-building me-2"></i>Penyelenggara:</strong><br>' + kompetisi.penyelenggara + '</p>';
                html += '<p><strong><i class="fas fa-align-left me-2"></i>Deskripsi:</strong><br>' + (kompetisi.deskripsi || '-') + '</p>';
                html += '<p><strong><i class="fas fa-calendar me-2"></i>Tanggal:</strong><br>' + kompetisi.tgl_mulai + ' s/d ' + kompetisi.tgl_selesai + '</p>';
                
                if (kompetisi.file_panduan) {
                    html += '<p><strong><i class="fas fa-file me-2"></i>File Panduan:</strong> <a href="../../../Uploads/kompetisi_panduan/' + kompetisi.file_panduan + '" target="_blank" class="btn btn-sm btn-danger"><i class="fas fa-download"></i> Download</a></p>';
                }
                
                html += '</div>';
                html += '</div>';
                
                document.getElementById('detailKompetisiContent').innerHTML = html;
                
                const detailModal = new bootstrap.Modal(document.getElementById('detailKompetisiModal'));
                detailModal.show();
            }
        });
    });

    // 3. Logic Edit Kompetisi
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            const penyelenggara = this.getAttribute('data-penyelenggara');
            const deskripsi = this.getAttribute('data-deskripsi');
            const tglMulai = this.getAttribute('data-tgl_mulai');
            const tglSelesai = this.getAttribute('data-tgl_selesai');
            const gambar = this.getAttribute('data-gambar');
            const filePanduan = this.getAttribute('data-file_panduan');

            document.getElementById('edit_kompetisi_id').value = id;
            document.getElementById('edit_nama_kompetisi').value = nama;
            document.getElementById('edit_penyelenggara').value = penyelenggara;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('edit_tgl_mulai').value = tglMulai;
            document.getElementById('edit_tgl_selesai').value = tglSelesai;
            document.getElementById('edit_gambar_lama').value = gambar;
            document.getElementById('edit_file_panduan_lama').value = filePanduan;

            // Tampilkan preview gambar
            const imgDisplay = document.getElementById('current_image_display');
            if (gambar) {
                imgDisplay.src = '../../../Uploads/kompetisi/' + encodeURIComponent(gambar);
                imgDisplay.style.display = 'inline-block';
            } else {
                imgDisplay.src = 'https://via.placeholder.com/120?text=No+Image'; 
                imgDisplay.style.display = 'inline-block';
            }
            
            // Tampilkan file panduan saat ini
            const fileDisplay = document.getElementById('current_file_display');
            if (filePanduan) {
                fileDisplay.innerHTML = '<a href="../../../Uploads/kompetisi_panduan/' + encodeURIComponent(filePanduan) + '" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-file"></i> ' + filePanduan + '</a>';
            } else {
                fileDisplay.innerHTML = '<span class="text-muted">Tidak ada file</span>';
            }

            const editModal = new bootstrap.Modal(document.getElementById('editKompetisiModal'));
            editModal.show();
        });
    });

    // 4. Preview gambar baru saat dipilih
    document.getElementById('edit_gambar')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2000000) {
                Swal.fire('Error', 'Ukuran file terlalu besar! Maksimal 2MB.', 'error');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('current_image_display').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // 5. Preview file panduan baru saat dipilih
    document.getElementById('edit_file_panduan')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 10000000) {
                Swal.fire('Error', 'Ukuran file terlalu besar! Maksimal 10MB.', 'error');
                this.value = '';
                return;
            }
            
            document.getElementById('current_file_display').innerHTML = 
                '<span class="badge bg-success"><i class="fas fa-file"></i> ' + file.name + ' (baru)</span>';
        }
    });

    // 6. Logic Hapus Kompetisi dengan SweetAlert2
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama') || 'kompetisi ini';

            Swal.fire({
                title: 'Yakin hapus kompetisi?',
                html: `Anda akan menghapus kompetisi <strong>"${nama}"</strong> secara permanen.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Buat form POST secara dinamis
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = ''; // Kirim ke halaman saat ini

                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'hapus';
                    form.appendChild(actionInput);

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'kompetisi_id';
                    idInput.value = id;
                    form.appendChild(idInput);

                    document.body.appendChild(form);
                    form.submit(); // Submit form

                    // SweetAlert akan hilang setelah halaman reload
                }
            });
        });
    });
    
    // Inisialisasi filter saat halaman dimuat
    filterAll();
});
</script>