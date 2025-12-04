<?php
// Admin/Ormawa/Event.php

// 1. Inisialisasi dan Keamanan
// Path relatif disesuaikan dengan asumsi file berada di Admin/Ormawa/
include('../../../Config/ConnectDB.php');
include('../../../Function/EventFunction.php');

// Batasi akses hanya untuk level 2 (Admin Ormawa)
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 2) {
    header("Location: ../SuperAdmin/Login.php");
    exit();
}

// Tangani operasi CRUD (Tambah/Edit/Hapus)
handleEventOperations($koneksi);
include('Header.php'); // Asumsi ini meng-include header HTML dan memulai SESSION

// 2. Pengambilan Data
// Asumsi fungsi ini mengambil data event yang relevan (difilter berdasarkan ormawa_id di Function)
$event_list = getEventData($koneksi);
$all_ormawa_list = getOrmawaList($koneksi); // Untuk Modal Edit
$admin_ormawa_info = getAdminOrmawaInfo($koneksi);

// Daftar Kategori Event untuk Filter & Modal Edit
$kategori_event_list = [
    'Semua', 'Perayaan', 'Workshop', 'Seminar', 'Kompetisi',
    'Festival', 'Olahraga', 'Seni', 'Akademik', 'Lainnya'
];
?>

<style>
    /* Styling untuk menyembunyikan fitur default DataTables jika digunakan */
    #dataTableEvent_wrapper .row:first-child {
        display: none;
    }
    #dataTableEvent_wrapper .row:last-child {
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
            <i class="fas fa-calendar-star me-2"></i>Manajemen Event
            <?= htmlspecialchars($_SESSION['ormawa_nama'] ?? ''); ?>
        </h1>
        <button type="button" class="btn btn-success btn-icon-split"
                data-bs-toggle="modal" data-bs-target="#tambahEventModal">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Tambah Event</span>
        </button>
    </div>

    <div class="filter-section mb-4">
        <div class="row align-items-center">

            <div class="col-md-5">
                <div class="search-box">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control"
                                placeholder="Cari: Nama Event, Lokasi..."
                                onkeyup="filterAll()">
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <select class="form-select" id="filterKategori" onchange="filterAll()">
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
                    <i class="fas fa-redo me-1"></i> Reset Filter
                </button>
            </div>

        </div>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Daftar Event
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableEvent" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Gambar</th>
                            <th>Nama Event</th>
                            <th>Ormawa</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBodyEvent">
                        <?php if (!empty($event_list)): ?>
                            <?php foreach ($event_list as $index => $event): ?>
                                <?php
                                $gambar_path = '../../../Uploads/event/' . $event['gambar'];
                                $image_src = (file_exists($gambar_path) && !empty($event['gambar']))
                                    ? $gambar_path
                                    : 'https://via.placeholder.com/50?text=No+Image';

                                $searchText = strtolower(
                                    $event['nama_event'] . ' ' .
                                    ($event['kategori'] ?? '') . ' ' .
                                    $event['nama_ormawa'] . ' ' .
                                    $event['lokasi']
                                );
                                ?>
                                <tr data-search="<?= htmlspecialchars($searchText, ENT_QUOTES); ?>"
                                    data-kategori="<?= htmlspecialchars($event['kategori'] ?? '', ENT_QUOTES); ?>">
                                    <td><?= $index + 1; ?></td>
                                    <td>
                                        <img src="<?= $image_src; ?>" width="50" height="50" class="img-thumbnail rounded">
                                    </td>
                                    <td><?= htmlspecialchars($event['nama_event']); ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($event['nama_ormawa']); ?></span></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($event['kategori'] ?? 'Lainnya'); ?></span></td>
                                    <td>
                                        <small>
                                            <?= date('d M Y', strtotime($event['tgl_mulai'])); ?><br>
                                            <i class="fas fa-arrow-right"></i>
                                            <?= date('d M Y', strtotime($event['tgl_selesai'])); ?>
                                        </small>
                                    </td>
                                    <td><?= htmlspecialchars($event['lokasi']); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-circle btn-sm edit-btn"
                                            data-id="<?= (int)$event['id']; ?>"
                                            data-nama="<?= htmlspecialchars($event['nama_event'], ENT_QUOTES); ?>"
                                            data-deskripsi="<?= htmlspecialchars($event['deskripsi'], ENT_QUOTES); ?>"
                                            data-tgl_mulai="<?= $event['tgl_mulai']; ?>"
                                            data-tgl_selesai="<?= $event['tgl_selesai']; ?>"
                                            data-lokasi="<?= htmlspecialchars($event['lokasi'], ENT_QUOTES); ?>"
                                            data-ormawa_id="<?= (int)$event['ormawa_id']; ?>"
                                            data-kategori="<?= htmlspecialchars($event['kategori'] ?? '', ENT_QUOTES); ?>"
                                            data-gambar="<?= htmlspecialchars($event['gambar']); ?>"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button class="btn btn-danger btn-circle btn-sm delete-btn"
                                                data-id="<?= (int)$event['id']; ?>"
                                                data-nama="<?= htmlspecialchars($event['nama_event'], ENT_QUOTES); ?>"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="default-no-data">
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                    <p class="mb-0">Belum ada event terdaftar.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr id="noResults" class="text-center text-muted d-none">
                            <td colspan="8" class="py-4">
                                <i class="fas fa-magnifying-glass fa-2x mb-2"></i>
                                <p>Tidak ada data yang sesuai dengan pencarian atau filter Anda.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include('../FormData/TambahEvent.php'); ?>

<div class="modal fade" id="editEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Event
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editEventForm" method="POST" enctype="multipart/form-data" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="event_id" id="edit_event_id">

                <div class="modal-body">
                    <input type="hidden" name="ormawa_id" value="<?= (int)$_SESSION['ormawa_id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Ormawa</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['ormawa_nama']); ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Event</label>
                        <input type="text" class="form-control" name="nama_event" id="edit_nama_event" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="kategori" id="edit_kategori" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($kategori_event_list as $kategori): ?>
                                <?php if ($kategori !== 'Semua'): ?>
                                    <option value="<?= $kategori; ?>"><?= htmlspecialchars($kategori); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
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
                        <label class="form-label">Lokasi</label>
                        <input type="text" class="form-control" name="lokasi" id="edit_lokasi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Saat Ini</label><br>
                        <img id="current_image_display" src="" width="120" class="img-thumbnail rounded">
                        <input type="hidden" name="gambar_lama" id="edit_gambar_lama">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ganti Gambar (Opsional)</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                        <div class="form-text text-muted">Format: JPG, PNG (maks 2MB)</div>
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

<!-- Form POST tersembunyi untuk operasi hapus -->
<form id="deleteEventForm" method="POST" action="../../../Function/EventFunction.php" style="display:none;">
    <input type="hidden" name="action" value="hapus">
    <input type="hidden" name="event_id" id="delete_event_id">
</form>

<script>
/**
 * Logika Filter dan Pencarian Client-Side
 */
function filterAll() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const kategoriFilter = document.getElementById('filterKategori').value;
    const tableBody = document.getElementById('tableBodyEvent');
    const rows = tableBody.getElementsByTagName('tr');
    let visibleRowCount = 0;

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];

        // Lewati baris yang tidak berisi data (seperti noResults atau default-no-data)
        if (row.id === 'noResults' || row.classList.contains('default-no-data')) continue;

        const searchTextData = row.getAttribute('data-search') || '';
        const kategoriData = row.getAttribute('data-kategori') || '';

        const searchMatch = searchTextData.includes(searchInput);
        const kategoriMatch = kategoriFilter === 'Semua' || kategoriData === kategoriFilter;

        if (searchMatch && kategoriMatch) {
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
        // Jika baris data asli memang kosong (defaultNoDataRow ada), sembunyikan noResults
        if (defaultNoDataRow) {
            noResultsRow.classList.add('d-none');
        } else {
             // Jika baris data ada tapi difilter habis
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
    document.getElementById('filterKategori').value = 'Semua';
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

    // 2. Logic Edit Event
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            const deskripsi = this.getAttribute('data-deskripsi');
            const tglMulai = this.getAttribute('data-tgl_mulai');
            const tglSelesai = this.getAttribute('data-tgl_selesai');
            const lokasi = this.getAttribute('data-lokasi');
            const gambar = this.getAttribute('data-gambar');
            const kategori = this.getAttribute('data-kategori');

            document.getElementById('edit_event_id').value = id;
            document.getElementById('edit_nama_event').value = nama;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('edit_tgl_mulai').value = tglMulai;
            document.getElementById('edit_tgl_selesai').value = tglSelesai;
            document.getElementById('edit_lokasi').value = lokasi;
            document.getElementById('edit_gambar_lama').value = gambar;

            // Set Kategori
            document.getElementById('edit_kategori').value = kategori;

            // Tampilkan preview gambar
            const imgDisplay = document.getElementById('current_image_display');
            if (gambar) {
                imgDisplay.src = '../../../Uploads/event/' + encodeURIComponent(gambar);
                imgDisplay.style.display = 'inline-block';
            } else {
                imgDisplay.src = 'https://via.placeholder.com/120?text=No+Image';
                imgDisplay.style.display = 'inline-block';
            }

            const editModal = new bootstrap.Modal(document.getElementById('editEventModal'));
            editModal.show();
        });
    });

    // Logic untuk Hapus Event sudah diganti dengan form POST standar.
    // Kode AJAX di bawah ini tidak lagi digunakan.

    // Inisialisasi filter saat halaman dimuat
    filterAll();
    // 3. Logic Hapus Event dengan SweetAlert2
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama') || 'event ini';

            Swal.fire({
                title: 'Yakin hapus event?',
                html: `Anda akan menghapus event <strong>"${nama}"</strong> secara permanen.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading SweetAlert
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
                    idInput.name = 'event_id';
                    idInput.value = id;
                    form.appendChild(idInput);

                    document.body.appendChild(form);
                    form.submit(); // Submit form

                    // SweetAlert akan hilang setelah halaman reload
                }
            });
        });
    });
});
</script>