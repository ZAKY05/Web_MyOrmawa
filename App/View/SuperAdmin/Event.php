<?php
include('../SuperAdmin/Header.php');
include('../../../Config/ConnectDB.php');
include('../../../Function/EventFunction.php');
handleEventOperations($koneksi);

$admin_ormawa_info = getAdminOrmawaInfo($koneksi);
$all_ormawa_list = getOrmawaList($koneksi);
?>

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
        // ✅ REDIRECT via JavaScript setelah operasi berhasil
        if (isset($_SESSION['redirect']) && $_SESSION['redirect'] === true):
        ?>
            <script>
                setTimeout(function() {
                    window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>?page=event';
                }, 1500); // Redirect setelah 1.5 detik
            </script>
        <?php
            unset($_SESSION['redirect']);
        endif;
        
        unset($_SESSION['message']);
        unset($_SESSION['msg_type']);
        ?>
    <?php endif; ?>

    <!-- Event Table -->
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
                            <th width="20%">Nama Event</th>
                            <th width="10%">Kategori</th>
                            <th width="15%">Penyelenggara</th>
                            <th width="15%">Tanggal</th>
                            <th width="15%">Lokasi</th>
                            <th width="14%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $event_list = getEventData($koneksi);
                        if (count($event_list) > 0) {
                            $no = 1;
                            foreach ($event_list as $event):
                                $gambar_path = '../../../Uploads/event/' . htmlspecialchars($event['gambar'] ?? '');
                                $image_src = (file_exists($gambar_path) && !empty($event['gambar'])) ? $gambar_path : '../../../Asset/img/default-event.jpg';
                        ?>
                            <tr>
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
                                <td><?php echo htmlspecialchars($event['lokasi'] ?? ''); ?></td>
                                <td class="text-center">
                                    <!-- Tombol Detail -->
                                    <button class="btn btn-info btn-circle btn-sm mb-1" 
                                        data-toggle="modal" 
                                        data-target="#modalDetailEvent" 
                                        onclick="viewEventDetail(<?php echo $event['id']; ?>)" 
                                        title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <!-- Tombol Edit dengan data-attribute lengkap -->
                                    <button class="btn btn-warning btn-circle btn-sm mb-1 edit-event-btn" 
                                        data-toggle="modal" 
                                        data-target="#editEventModal"
                                        data-id="<?php echo $event['id']; ?>" 
                                        data-nama="<?php echo htmlspecialchars($event['nama_event'] ?? '', ENT_QUOTES); ?>" 
                                        data-kategori="<?php echo htmlspecialchars($event['kategori'] ?? '', ENT_QUOTES); ?>"
                                        data-deskripsi="<?php echo htmlspecialchars($event['deskripsi'] ?? '', ENT_QUOTES); ?>" 
                                        data-tgl_mulai="<?php echo $event['tgl_mulai'] ?? ''; ?>" 
                                        data-tgl_selesai="<?php echo $event['tgl_selesai'] ?? ''; ?>" 
                                        data-lokasi="<?php echo htmlspecialchars($event['lokasi'] ?? '', ENT_QUOTES); ?>" 
                                        data-ormawa_id="<?php echo $event['ormawa_id'] ?? ''; ?>" 
                                        data-gambar="<?php echo htmlspecialchars($event['gambar'] ?? '', ENT_QUOTES); ?>"
                                        data-buku_panduan="<?php echo htmlspecialchars($event['buku_panduan'] ?? '', ENT_QUOTES); ?>"
                                        onclick="editEventFromButton(this)"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <!-- Tombol Hapus dengan konfirmasi -->
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
                            echo "<tr><td colspan='8' class='text-center'>Tidak ada data event.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal Detail Event -->
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

<!-- Modal Edit Event -->
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
                            <option value="Perayaan">Perayaan</option>
                            <option value="Workshop">Workshop</option>
                            <option value="Seminar">Seminar</option>
                            <option value="Kompetisi">Kompetisi</option>
                            <option value="Festival">Festival</option>
                            <option value="Olahraga">Olahraga</option>
                            <option value="Seni">Seni</option>
                            <option value="Akademik">Akademik</option>
                            <option value="Lainnya">Lainnya</option>
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
// Data event untuk JavaScript
const eventData = <?php echo json_encode($event_list); ?>;

$(document).ready(function() {
    $('#dataTableEvent').DataTable({
        "order": [[0, "asc"]]
    });
});

/**
 * Fungsi untuk view detail event
 */
function viewEventDetail(id) {
    const event = eventData.find(item => item.id == id);
    
    if (event) {
        let html = '<div class="row">';
        
        // Poster
        html += '<div class="col-md-4 text-center mb-3">';
        if (event.gambar) {
            html += '<img src="../../../Uploads/event/' + event.gambar + '" alt="Poster" class="img-thumbnail" style="max-width: 100%; max-height: 300px; object-fit: cover;">';
        } else {
            html += '<div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width: 100%; height: 300px;"><i class="fas fa-image fa-3x text-muted"></i></div>';
        }
        html += '</div>';
        
        // Info Detail
        html += '<div class="col-md-8">';
        html += '<h4>' + event.nama_event + '</h4>';
        html += '<p><span class="badge badge-info">' + event.kategori + '</span></p>';
        html += '<hr>';
        
        html += '<p><strong><i class="fas fa-users"></i> Penyelenggara:</strong><br>' + event.nama_ormawa + '</p>';
        html += '<p><strong><i class="fas fa-align-left"></i> Deskripsi:</strong><br>' + (event.deskripsi || '-') + '</p>';
        html += '<p><strong><i class="fas fa-calendar"></i> Tanggal:</strong><br>' + event.tgl_mulai + ' s/d ' + event.tgl_selesai + '</p>';
        html += '<p><strong><i class="fas fa-map-marker-alt"></i> Lokasi:</strong> ' + (event.lokasi || '-') + '</p>';
        
        // ✅ PERBAIKAN: Tombol download warna MERAH
        if (event.buku_panduan) {
            html += '<p><strong><i class="fas fa-file-pdf"></i> Buku Panduan:</strong> <a href="../../../Uploads/event_panduan/' + event.buku_panduan + '" target="_blank" class="btn btn-sm btn-danger"><i class="fas fa-download"></i> Download PDF</a></p>';
        }
        
        html += '</div>';
        html += '</div>';
        
        document.getElementById('detailEventContent').innerHTML = html;
    }
}

/**
 * Fungsi untuk edit event dari button
 */
function editEventFromButton(button) {
    const id = button.getAttribute('data-id');
    const nama = button.getAttribute('data-nama');
    const kategori = button.getAttribute('data-kategori');
    const deskripsi = button.getAttribute('data-deskripsi');
    const tgl_mulai = button.getAttribute('data-tgl_mulai');
    const tgl_selesai = button.getAttribute('data-tgl_selesai');
    const lokasi = button.getAttribute('data-lokasi');
    const ormawa_id = button.getAttribute('data-ormawa_id');
    const gambar = button.getAttribute('data-gambar');
    const buku_panduan = button.getAttribute('data-buku_panduan');
    
    // Populate form
    document.getElementById('edit_event_id').value = id || '';
    document.getElementById('edit_nama_event').value = nama || '';
    document.getElementById('edit_kategori').value = kategori || '';
    document.getElementById('edit_deskripsi').value = deskripsi || '';
    document.getElementById('edit_tgl_mulai').value = tgl_mulai || '';
    document.getElementById('edit_tgl_selesai').value = tgl_selesai || '';
    document.getElementById('edit_lokasi').value = lokasi || '';
    document.getElementById('edit_gambar_lama').value = gambar || '';
    document.getElementById('edit_buku_panduan_lama').value = buku_panduan || '';
    
    // Set ormawa_id untuk SuperAdmin
    const ormawaSelect = document.getElementById('edit_ormawa_id');
    if (ormawaSelect) {
        ormawaSelect.value = ormawa_id || '';
    }
    
    // Tampilkan preview gambar jika ada
    const imagePreview = document.getElementById('current_image_display');
    if (gambar) {
        imagePreview.innerHTML = 
            '<img src="../../../Uploads/event/' + gambar + '" alt="Current Poster" style="max-width: 150px; max-height: 200px;" class="border rounded">' +
            '<p class="text-muted small mt-1">Poster saat ini (upload file baru untuk mengganti)</p>';
    } else {
        imagePreview.innerHTML = 
            '<p class="text-muted small">Tidak ada poster. Upload file untuk menambahkan poster.</p>';
    }
    
    // ✅ PERBAIKAN: Badge buku panduan warna MERAH
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

/**
 * Fungsi untuk reset form tambah event
 */
function resetForm() {
    const modalTambah = document.getElementById('tambahEventModal');
    if (modalTambah) {
        const form = modalTambah.querySelector('form');
        if (form) form.reset();
    }
}

/**
 * Fungsi untuk delete event dengan konfirmasi
 */
function deleteEvent(button) {
    const id = button.getAttribute('data-id');
    const nama = button.getAttribute('data-nama');
    
    if (confirm('Yakin ingin menghapus event "' + nama + '"?\n\nData yang dihapus tidak dapat dikembalikan!')) {
        // Create form untuk submit delete
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

// Preview gambar saat dipilih di form Edit
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

// Preview PDF saat dipilih di form Edit
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
        
        // ✅ PERBAIKAN: Badge preview PDF warna MERAH
        document.getElementById('current_buku_display').innerHTML = 
            '<span class="badge badge-danger">' + file.name + '</span>' +
            '<p class="text-muted small mt-1">File PDF baru dipilih</p>';
    }
});

// ✅ PERBAIKAN UTAMA: Prevent multiple form submission
$('form').on('submit', function() {
    $(this).find('button[type="submit"]').prop('disabled', true);
    $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
});
</script>

<?php include('../SuperAdmin/Footer.php'); ?>