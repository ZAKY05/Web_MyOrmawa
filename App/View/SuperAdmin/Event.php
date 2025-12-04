<?php
include('../SuperAdmin/Header.php');
include('../../../Config/ConnectDB.php');
// Pastikan EventFunction.php memiliki fungsi getEventData() dan handleEventOperations()
include('../../../Function/EventFunction.php'); 

// Pastikan fungsi ini tersedia di EventFunction.php atau lokasi lain yang di-include
handleEventOperations($koneksi);

// Pastikan fungsi ini tersedia
$admin_ormawa_info = getAdminOrmawaInfo($koneksi); 
$all_ormawa_list = getOrmawaList($koneksi); 
$event_list = getEventData($koneksi);

// Daftar Kategori Event untuk Filter
$kategori_event_list = [
    'Semua', 'Perayaan', 'Workshop', 'Seminar', 'Kompetisi', 
    'Festival', 'Olahraga', 'Seni', 'Akademik', 'Lainnya'
];
?>

<style>
    /* Mengatasi konflik default DataTables */
    #dataTableEvent_wrapper .row:first-child {
        display: none; /* Sembunyikan search bar default DataTables */
    }
    #dataTableEvent_wrapper .row:last-child {
        margin-top: 15px;
    }

    /* CSS untuk Search Box yang Rapi dan Sesuai Permintaan */
    .search-box .input-group-text {
        /* Menggunakan style default Bootstrap untuk kotak abu-abu */
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
            <i class="fas fa-calendar-alt"></i> Manajemen Event
        </h1>
        <button type="button" class="btn btn-success btn-icon-split" data-toggle="modal" data-target="#tambahEventModal" onclick="resetForm()">
            <span class="icon text-white-50">
                <i class="fas fa-plus"></i>
            </span>
            <span class="text">Tambah Event</span>
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
                    window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>?page=event';
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
        
        <div class="col-md-5">
            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control" 
                            placeholder="Cari: Nama Event, Lokasi, Penyelenggara..." 
                            onkeyup="filterAll()">
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <select class="form-control" id="filterKategori" onchange="filterAll()">
                <option value="Semua">Semua Kategori</option>
                <?php foreach ($kategori_event_list as $kategori): ?>
                    <?php if ($kategori !== 'Semua'): ?>
                        <option value="<?php echo htmlspecialchars($kategori); ?>">
                            <?php echo htmlspecialchars($kategori); ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
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
                <i class="fas fa-table"></i> Daftar Event
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableEvent" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">No</th>
                            <th width="8%">Poster</th>
                            <th width="15%">Nama Event</th>
                            <th width="8%">Kategori</th>
                            <th width="12%">Penyelenggara</th>
                            <th width="12%">Tanggal</th>
                            <th width="10%">Waktu</th>
                            <th width="12%">Lokasi</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php
                        if (count($event_list) > 0) {
                            $no = 1;
                            foreach ($event_list as $event):
                                $gambar_path = '../../../Uploads/event/' . htmlspecialchars($event['gambar'] ?? '');
                                $image_src = (file_exists($gambar_path) && !empty($event['gambar'])) ? $gambar_path : '../../../Asset/img/default-event.jpg';
                                
                                // Format waktu
                                $waktu_mulai = !empty($event['waktu_mulai']) ? substr($event['waktu_mulai'], 0, 5) : '-';
                                $waktu_selesai = !empty($event['waktu_selesai']) ? substr($event['waktu_selesai'], 0, 5) : '-';
                                $waktu_display = $waktu_mulai . ' - ' . $waktu_selesai;

                                // Data untuk Search & Filter
                                $searchText = strtolower(
                                    $event['nama_event'] . ' ' .
                                    $event['kategori'] . ' ' .
                                    $event['nama_ormawa'] . ' ' .
                                    $event['lokasi']
                                );
                        ?>
                            <tr data-search="<?php echo htmlspecialchars($searchText, ENT_QUOTES); ?>" 
                                data-kategori="<?php echo htmlspecialchars($event['kategori'] ?? '', ENT_QUOTES); ?>">
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td class="text-center">
                                    <img src="<?php echo $image_src; ?>" alt="Poster" class="img-thumbnail" style="max-width: 80px; max-height: 107px; object-fit: cover;">
                                </td>
                                <td><?php echo htmlspecialchars($event['nama_event'] ?? ''); ?></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($event['kategori'] ?? ''); ?></span></td>
                                <td><?php echo htmlspecialchars($event['nama_ormawa'] ?? ''); ?></td>
                                <td>
                                    <small>
                                        <?php echo date('d M Y', strtotime($event['tgl_mulai'])); ?> - 
                                        <?php echo date('d M Y', strtotime($event['tgl_selesai'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <small><?php echo $waktu_display; ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($event['lokasi'] ?? ''); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-circle btn-sm mb-1" 
                                        data-toggle="modal" 
                                        data-target="#modalDetailEvent" 
                                        onclick="viewEventDetail(<?php echo $event['id']; ?>)" 
                                        title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <button class="btn btn-warning btn-circle btn-sm mb-1 edit-event-btn" 
                                        data-toggle="modal" 
                                        data-target="#editEventModal"
                                        data-id="<?php echo $event['id']; ?>" 
                                        data-nama="<?php echo htmlspecialchars($event['nama_event'] ?? '', ENT_QUOTES); ?>" 
                                        data-kategori="<?php echo htmlspecialchars($event['kategori'] ?? '', ENT_QUOTES); ?>"
                                        data-deskripsi="<?php echo htmlspecialchars($event['deskripsi'] ?? '', ENT_QUOTES); ?>" 
                                        data-tgl_mulai="<?php echo $event['tgl_mulai'] ?? ''; ?>" 
                                        data-tgl_selesai="<?php echo $event['tgl_selesai'] ?? ''; ?>" 
                                        data-waktu_mulai="<?php echo $event['waktu_mulai'] ?? ''; ?>"
                                        data-waktu_selesai="<?php echo $event['waktu_selesai'] ?? ''; ?>"
                                        data-lokasi="<?php echo htmlspecialchars($event['lokasi'] ?? '', ENT_QUOTES); ?>" 
                                        data-ormawa_id="<?php echo $event['ormawa_id'] ?? ''; ?>" 
                                        data-gambar="<?php echo htmlspecialchars($event['gambar'] ?? '', ENT_QUOTES); ?>"
                                        data-buku_panduan="<?php echo htmlspecialchars($event['buku_panduan'] ?? '', ENT_QUOTES); ?>"
                                        onclick="editEventFromButton(this)"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button class="btn btn-danger btn-circle btn-sm mb-1 delete-event-btn" 
                                        data-id="<?php echo $event['id']; ?>"
                                        data-nama="<?php echo htmlspecialchars($event['nama_event'] ?? '', ENT_QUOTES); ?>"
                                        onclick="deleteEvent(this)"
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php 
                            endforeach;
                        } else {
                            echo "<tr><td colspan='9' class='text-center'>Tidak ada data event.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div id="noResults" class="text-center text-muted mt-3 d-none">
                    <i class="fas fa-magnifying-glass fa-2x mb-2"></i>
                    <p>Tidak ada data yang sesuai dengan pencarian atau filter Anda.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="modalDetailEvent" tabindex="-1" role="dialog" aria-labelledby="modalDetailEventLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalDetailEventLabel">
                    <i class="fas fa-info-circle"></i> Detail Event
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailEventContent">
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

<?php include('../FormData/TambahEvent.php'); ?>

<div class="modal fade" id="editEventModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" id="formEditEvent">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="event_id" id="edit_event_id">
                    
                    <?php if (isset($_SESSION['user_level']) && $_SESSION['user_level'] === 2 && $admin_ormawa_info): ?>
                        <input type="hidden" name="ormawa_id" id="edit_ormawa_id_hidden" value="<?php echo $admin_ormawa_info['id']; ?>">
                        <div class="form-group">
                            <label>Ormawa Penyelenggara</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin_ormawa_info['nama_ormawa']); ?>" readonly>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="edit_ormawa_id">Ormawa Penyelenggara <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_ormawa_id" name="ormawa_id" required>
                                <option value="">Pilih Ormawa</option>
                                <?php foreach ($all_ormawa_list as $ormawa): ?>
                                    <option value="<?php echo $ormawa['id']; ?>"><?php echo htmlspecialchars($ormawa['nama_ormawa']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="edit_nama_event">Nama Event <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nama_event" name="nama_event" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_kategori">Kategori Event <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_kategori" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($kategori_event_list as $kategori_option): ?>
                                <?php if ($kategori_option !== 'Semua'): ?>
                                    <option value="<?php echo $kategori_option; ?>"><?php echo $kategori_option; ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="waktu_mulai" id="edit_waktu_mulai" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="waktu_selesai" id="edit_waktu_selesai" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_lokasi">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_lokasi" name="lokasi" required>
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
                        <label>Buku Panduan Saat Ini</label><br>
                        <div id="current_buku_display"></div>
                        <input type="hidden" name="buku_panduan_lama" id="edit_buku_panduan_lama">
                    </div>

                    <div class="form-group">
                        <label for="edit_buku_panduan">Ganti Buku Panduan (Opsional)</label>
                        <input type="file" class="form-control-file" id="edit_buku_panduan" name="buku_panduan" accept=".pdf">
                        <small class="form-text text-muted">Format: PDF. Maksimal: 5MB.</small>
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
// Data Event yang sudah di-encode dari PHP
const eventData = <?php echo json_encode($event_list); ?>;
const tableBody = document.getElementById('tableBody');
const noResults = document.getElementById('noResults');

$(document).ready(function() {
    // Inisialisasi DataTables
    const dataTable = $('#dataTableEvent').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true, // Tetap true agar fungsionalitas DataTables tetap ada
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "order": [[0, "asc"]]
    });

    // Sembunyikan search bar default DataTables saat DataTables selesai diinisialisasi
    // Kita akan menggunakan filterAll() kustom kita
    dataTable.settings()[0].oFeatures.bFilter = false; 

    // Panggil filterAll saat halaman dimuat untuk memastikan DataTables sudah siap
    filterAll(); 
});


// ✅ FUNGSI GABUNGAN SEARCH DAN FILTER KUSTOM
function filterAll() {
    const searchInput = document.getElementById('searchInput');
    const kategoriFilter = document.getElementById('filterKategori');
    
    const searchText = searchInput.value.toLowerCase().trim();
    const selectedKategori = kategoriFilter.value; // 'Semua', 'Perayaan', dll.
    
    const rows = tableBody.querySelectorAll('tr');
    let visibleCount = 0;

    rows.forEach(row => {
        // Ambil data dari atribut data-search dan data-kategori yang sudah kita tambahkan di PHP
        const rowSearchText = row.getAttribute('data-search') || '';
        const rowKategori = row.getAttribute('data-kategori') || '';

        // 1. Cek Pencarian
        const isSearchMatch = searchText === '' || rowSearchText.includes(searchText);

        // 2. Cek Filter Kategori
        const isKategoriMatch = selectedKategori === 'Semua' || rowKategori === selectedKategori;

        // Tampilkan baris jika cocok dengan pencarian DAN kategori
        if (isSearchMatch && isKategoriMatch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Tampilkan/menyembunyikan pesan "tidak ada hasil"
    if (visibleCount === 0) {
        noResults.classList.remove('d-none');
    } else {
        noResults.classList.add('d-none');
    }
}

// ✅ FUNGSI RESET FILTER
function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterKategori').value = 'Semua';
    filterAll(); // Terapkan semua filter/search yang kosong
}

// --- Fungsi JavaScript lainnya (viewEventDetail, editEventFromButton, resetForm, deleteEvent) tetap sama ---

function viewEventDetail(id) {
    const event = eventData.find(item => item.id == id);
    
    if (event) {
        let html = '<div class="row">';
        
        html += '<div class="col-md-4 text-center mb-3">';
        // Tentukan path gambar
        const imagePath = '../../../Uploads/event/' + event.gambar;
        // Cek apakah gambar benar-benar ada (sisi klien tidak bisa cek file, tapi kita asumsikan pathnya benar)
        const imageSrc = event.gambar ? imagePath : '../../../Asset/img/default-event.jpg';
        
        html += '<img src="' + imageSrc + '" alt="Poster" class="img-thumbnail" style="max-width: 100%; max-height: 300px; object-fit: cover;">';
        html += '</div>';
        
        html += '<div class="col-md-8">';
        html += '<h4>' + event.nama_event + '</h4>';
        html += '<p><span class="badge badge-info">' + event.kategori + '</span></p>';
        html += '<hr>';
        
        html += '<p><strong><i class="fas fa-users"></i> Penyelenggara:</strong><br>' + event.nama_ormawa + '</p>';
        html += '<p><strong><i class="fas fa-align-left"></i> Deskripsi:</strong><br>' + (event.deskripsi || '-') + '</p>';
        html += '<p><strong><i class="fas fa-calendar"></i> Tanggal:</strong><br>' + event.tgl_mulai + ' s/d ' + event.tgl_selesai + '</p>';
        
        // Tampilkan waktu
        const waktuMulai = event.waktu_mulai ? event.waktu_mulai.substring(0, 5) : '-';
        const waktuSelesai = event.waktu_selesai ? event.waktu_selesai.substring(0, 5) : '-';
        html += '<p><strong><i class="fas fa-clock"></i> Waktu:</strong> ' + waktuMulai + ' - ' + waktuSelesai + ' WIB</p>';
        
        html += '<p><strong><i class="fas fa-map-marker-alt"></i> Lokasi:</strong> ' + (event.lokasi || '-') + '</p>';
        
        if (event.buku_panduan) {
            html += '<p><strong><i class="fas fa-file-pdf"></i> Buku Panduan:</strong> <a href="../../../Uploads/event_panduan/' + event.buku_panduan + '" target="_blank" class="btn btn-sm btn-danger"><i class="fas fa-download"></i> Download PDF</a></p>';
        }
        
        html += '</div>';
        html += '</div>';
        
        document.getElementById('detailEventContent').innerHTML = html;
    }
}

function editEventFromButton(button) {
    const id = button.getAttribute('data-id');
    const nama = button.getAttribute('data-nama');
    const kategori = button.getAttribute('data-kategori');
    const deskripsi = button.getAttribute('data-deskripsi');
    const tgl_mulai = button.getAttribute('data-tgl_mulai');
    const tgl_selesai = button.getAttribute('data-tgl_selesai');
    const waktuMulai = button.getAttribute('data-waktu_mulai');
    const waktuSelesai = button.getAttribute('data-waktu_selesai');
    const lokasi = button.getAttribute('data-lokasi');
    const ormawa_id = button.getAttribute('data-ormawa_id');
    const gambar = button.getAttribute('data-gambar');
    const buku_panduan = button.getAttribute('data-buku_panduan');
    
    document.getElementById('edit_event_id').value = id || '';
    document.getElementById('edit_nama_event').value = nama || '';
    document.getElementById('edit_kategori').value = kategori || '';
    document.getElementById('edit_deskripsi').value = deskripsi || '';
    document.getElementById('edit_tgl_mulai').value = tgl_mulai || '';
    document.getElementById('edit_tgl_selesai').value = tgl_selesai || '';
    document.getElementById('edit_waktu_mulai').value = waktuMulai || '';
    document.getElementById('edit_waktu_selesai').value = waktuSelesai || '';
    document.getElementById('edit_lokasi').value = lokasi || '';
    document.getElementById('edit_gambar_lama').value = gambar || '';
    document.getElementById('edit_buku_panduan_lama').value = buku_panduan || '';
    
    const ormawaSelect = document.getElementById('edit_ormawa_id');
    if (ormawaSelect) {
        ormawaSelect.value = ormawa_id || '';
    }
    
    const imagePreview = document.getElementById('current_image_display');
    if (gambar) {
        imagePreview.innerHTML = 
            '<img src="../../../Uploads/event/' + gambar + '" alt="Current Poster" style="max-width: 150px; max-height: 200px;" class="border rounded">' +
            '<p class="text-muted small mt-1">Poster saat ini (upload file baru untuk mengganti)</p>';
    } else {
        imagePreview.innerHTML = 
            '<p class="text-muted small">Tidak ada poster. Upload file untuk menambahkan poster.</p>';
    }
    
    const bukuPreview = document.getElementById('current_buku_display');
    if (buku_panduan) {
        bukuPreview.innerHTML = 
            '<span class="badge badge-danger">' + buku_panduan + '</span>' +
            '<p class="text-muted small mt-1">Buku panduan saat ini (upload file baru untuk mengganti)</p>';
    } else {
        bukuPreview.innerHTML = 
            '<span class="badge badge-secondary">Tidak ada</span>' +
            '<p class="text-muted small mt-1">Upload file PDF untuk menambahkan buku panduan</p>';
    }
}

function resetForm() {
    const modalTambah = document.getElementById('tambahEventModal');
    if (modalTambah) {
        const form = modalTambah.querySelector('form');
        if (form) form.reset();
    }
}

function deleteEvent(button) {
    const id = button.getAttribute('data-id');
    const nama = button.getAttribute('data-nama');
    
    if (confirm('Yakin ingin menghapus event "' + nama + '"?\n\nData yang dihapus tidak dapat dikembalikan!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'hapus';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'event_id';
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

document.getElementById('edit_buku_panduan')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 5000000) {
            alert('Ukuran file terlalu besar! Maksimal 5MB.');
            this.value = '';
            return;
        }
        
        if (file.type !== 'application/pdf') {
            alert('Format file tidak didukung! Gunakan PDF.');
            this.value = '';
            return;
        }
        
        document.getElementById('current_buku_display').innerHTML = 
            '<span class="badge badge-danger">' + file.name + '</span>' +
            '<p class="text-muted small mt-1">File PDF baru dipilih</p>';
    }
});

// Loading state saat submit
$('form').on('submit', function() {
    $(this).find('button[type="submit"]').prop('disabled', true);
    $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
});
</script>

<?php include('../SuperAdmin/Footer.php'); ?>