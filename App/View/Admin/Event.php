<?php
// JANGAN panggil session_start() — sudah dipanggil di Index.php
include('Header.php');
include('../../../Config/ConnectDB.php');
include('../../../Function/EventFunction.php');

// Pastikan user login
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 2) {
    // Tapi karena Index.php sudah cek, ini hanya cadangan
    header("Location: ../SuperAdmin/Login.php");
    exit();
}

handleEventOperations($koneksi);

// Ambil data
$event_list = getEventData($koneksi);
$all_ormawa_list = getOrmawaList($koneksi);
$admin_ormawa_info = getAdminOrmawaInfo($koneksi);
$is_super_admin = ($_SESSION['user_level'] === 1);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Event <?php echo htmlspecialchars($_SESSION['ormawa_nama'] ?? ''); ?></h1>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#tambahEventModal">
                <i class="fas fa-plus fa-sm"></i> Tambah Event
            </button>
        </div>
    </div>

    <!-- Alert -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php
        unset($_SESSION['message']);
        unset($_SESSION['msg_type']);
        ?>
    <?php endif; ?>

    <!-- Tabel Event -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Event</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTableEvent" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Event</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($event_list)): ?>
                            <?php foreach ($event_list as $index => $event): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <?php
                                        $gambar_path = '../../../Uploads/event/' . htmlspecialchars($event['gambar']);
                                        $image_src = (file_exists($gambar_path) && !empty($event['gambar'])) 
                                            ? $gambar_path 
                                            : 'https://via.placeholder.com/50';
                                        ?>
                                        <img src="<?php echo $image_src; ?>" width="50" height="50" class="img-thumbnail">
                                    </td>
                                    <td><?php echo htmlspecialchars($event['nama_event']); ?></td>
                                    <td><?php echo htmlspecialchars($event['nama_ormawa']); ?></td>
                                    <td><?php echo htmlspecialchars($event['tgl_mulai']); ?> s/d <?php echo htmlspecialchars($event['tgl_selesai']); ?></td>
                                    <td><?php echo htmlspecialchars($event['lokasi']); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm edit-btn" 
                                            data-id="<?php echo $event['id']; ?>"
                                            data-nama="<?php echo htmlspecialchars($event['nama_event'], ENT_QUOTES); ?>"
                                            data-deskripsi="<?php echo htmlspecialchars($event['deskripsi'], ENT_QUOTES); ?>"
                                            data-tgl_mulai="<?php echo $event['tgl_mulai']; ?>"
                                            data-tgl_selesai="<?php echo $event['tgl_selesai']; ?>"
                                            data-lokasi="<?php echo htmlspecialchars($event['lokasi'], ENT_QUOTES); ?>"
                                            data-ormawa_id="<?php echo $event['ormawa_id']; ?>"
                                            data-gambar="<?php echo htmlspecialchars($event['gambar']); ?>"
                                            data-toggle="modal" data-target="#editEventModal">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $event['id']; ?>">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada event untuk <?php echo htmlspecialchars($_SESSION['ormawa_nama']); ?>.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Event</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($event_list); ?></div>
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

<!-- Modal Tambah (asumsi ada di TambahEvent.php) -->
<?php include('../FormData/TambahEvent.php'); ?>

<!-- Modal Edit -->
<div class="modal fade" id="editEventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Event</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="event_id" id="edit_event_id">
                    <?php if (!$_SESSION['user_level'] === 1): ?>
                        <input type="hidden" name="ormawa_id" value="<?php echo $_SESSION['ormawa_id']; ?>">
                        <div class="form-group">
                            <label>Ormawa</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['ormawa_nama']); ?>" readonly>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label>Ormawa</label>
                            <select class="form-control" name="ormawa_id" required>
                                <?php foreach ($all_ormawa_list as $ormawa): ?>
                                    <option value="<?php echo $ormawa['id']; ?>"><?php echo htmlspecialchars($ormawa['nama_ormawa']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Nama Event</label>
                        <input type="text" class="form-control" name="nama_event" id="edit_nama_event" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" class="form-control" name="tgl_mulai" id="edit_tgl_mulai" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" class="form-control" name="tgl_selesai" id="edit_tgl_selesai" required>
                    </div>
                    <div class="form-group">
                        <label>Lokasi</label>
                        <input type="text" class="form-control" name="lokasi" id="edit_lokasi" required>
                    </div>
                    <div class="form-group">
                        <label>Gambar Saat Ini</label>
                        <img id="current_image_display" src="" width="100" class="img-thumbnail">
                        <input type="hidden" name="gambar_lama" id="edit_gambar_lama">
                    </div>
                    <div class="form-group">
                        <label>Ganti Gambar (Opsional)</label>
                        <input type="file" class="form-control-file" name="gambar" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('Footer.php'); ?>