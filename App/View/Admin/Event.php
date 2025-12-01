<?php
include('../../../Config/ConnectDB.php');
include('../../../Function/EventFunction.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 2) {
    header("Location: ../SuperAdmin/Login.php");
    exit();
}

handleEventOperations($koneksi);
include('Header.php');

$event_list = getEventData($koneksi);
$all_ormawa_list = getOrmawaList($koneksi);
$admin_ormawa_info = getAdminOrmawaInfo($koneksi);
$is_super_admin = ($_SESSION['user_level'] === 1);
?>

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

    <!-- SweetAlert Notif dari SESSION -->
    <!-- Alert lama dihapus, ganti oleh JS di bawah -->

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Daftar Event
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTableEvent" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Gambar</th>
                            <th>Nama Event</th>
                            <th>Ormawa</th>
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($event_list)): ?>
                            <?php foreach ($event_list as $index => $event): ?>
                                <tr>
                                    <td><?= $index + 1; ?></td>
                                    <td>
                                        <?php
                                        $gambar_path = '../../../Uploads/event/' . $event['gambar'];
                                        $image_src = (file_exists($gambar_path) && !empty($event['gambar'])) 
                                            ? $gambar_path 
                                            : 'https://via.placeholder.com/50?text=No+Image';
                                        ?>
                                        <img src="<?= $image_src; ?>" width="50" height="50" class="img-thumbnail rounded">
                                    </td>
                                    <td><?= htmlspecialchars($event['nama_event']); ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($event['nama_ormawa']); ?></span></td>
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
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                    <p class="mb-0">Belum ada event terdaftar.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Event</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($event_list); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<?php include('../FormData/TambahEvent.php'); ?>

<!-- Modal Edit -->
<div class="modal fade" id="editEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Event
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editEventForm" method="POST" enctype="multipart/form-data" action="../../../Function/EventFunction.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="event_id" id="edit_event_id">
                
                <div class="modal-body">
                    <?php if ($_SESSION['user_level'] == 2): ?>
                        <input type="hidden" name="ormawa_id" value="<?= (int)$_SESSION['ormawa_id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Ormawa</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['ormawa_nama']); ?>" readonly>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label class="form-label">Ormawa</label>
                            <select class="form-select" name="ormawa_id" required>
                                <?php foreach ($all_ormawa_list as $ormawa): ?>
                                    <option value="<?= $ormawa['id']; ?>"><?= htmlspecialchars($ormawa['nama_ormawa']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nama Event</label>
                        <input type="text" class="form-control" name="nama_event" id="edit_nama_event" required>
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Notifikasi dari SESSION
document.addEventListener('DOMContentLoaded', function() {
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

    // === Edit Event ===
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            const deskripsi = this.getAttribute('data-deskripsi');
            const tglMulai = this.getAttribute('data-tgl_mulai');
            const tglSelesai = this.getAttribute('data-tgl_selesai');
            const lokasi = this.getAttribute('data-lokasi');
            const ormawaId = this.getAttribute('data-ormawa_id');
            const gambar = this.getAttribute('data-gambar');

            document.getElementById('edit_event_id').value = id;
            document.getElementById('edit_nama_event').value = nama;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('edit_tgl_mulai').value = tglMulai;
            document.getElementById('edit_tgl_selesai').value = tglSelesai;
            document.getElementById('edit_lokasi').value = lokasi;
            document.getElementById('edit_gambar_lama').value = gambar;

            // Tampilkan preview gambar
            const imgDisplay = document.getElementById('current_image_display');
            if (gambar) {
                imgDisplay.src = '../../../Uploads/event/' + encodeURIComponent(gambar);
                imgDisplay.style.display = 'inline-block';
            } else {
                imgDisplay.style.display = 'none';
            }

            // Set select ormawa (jika super admin)
            const selectOrmawa = document.querySelector('[name="ormawa_id"]');
            if (selectOrmawa) {
                selectOrmawa.value = ormawaId;
            }

            const modal = bootstrap.Modal.getInstance(document.getElementById('editEventModal')) ||
                          new bootstrap.Modal(document.getElementById('editEventModal'));
            modal.show();
        });
    });

    // === Hapus Event dengan SweetAlert2 ===
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama') || 'event ini';

            Swal.fire({
                title: 'Yakin hapus event?',
                html: `Anda akan menghapus event <strong>"${nama}"</strong> secara permanen.<br>
                       Gambar dan data terkait juga akan dihapus.`,
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

                    fetch(`../../../Function/EventFunction.php?action=delete&id=${id}`)
                    .then(response => {
                        if (response.redirected) {
                            window.location.href = response.url;
                        } else {
                            return response.json().catch(() => ({}));
                        }
                    })
                    .then(data => {
                        if (data?.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Dihapus!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Gagal!', 'Tidak dapat menghapus event.', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error!', 'Terjadi kesalahan jaringan.', 'error');
                    });
                }
            });
        });
    });
});
</script>