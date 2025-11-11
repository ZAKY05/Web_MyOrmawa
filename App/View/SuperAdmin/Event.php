<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../../../Config/ConnectDB.php');
include('../../../Function/EventFunction.php');
handleEventOperations($koneksi); // Proses operasi tambah/edit/hapus
include('../SuperAdmin/Header.php'); // Ganti dengan path header untuk Admin Organisasi jika berbeda

// Ambil informasi ormawa admin jika login sebagai admin organisasi
$admin_ormawa_info = getAdminOrmawaInfo($koneksi);

// Ambil daftar semua ormawa untuk SuperAdmin
$all_ormawa_list = getOrmawaList($koneksi);

?>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Event</h1>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#tambahEventModal">
                <i class="fas fa-plus fa-sm"></i> Tambah Event
            </button>
            <!-- Hanya tampilkan tombol generate jika SuperAdmin -->
            <?php if (isset($_SESSION['user_level']) && $_SESSION['user_level'] == 1): ?>
            <button type="button" class="btn btn-success btn-sm" id="generateSampleEvents">
                <i class="fas fa-database fa-sm"></i> Generate Sample Data
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert untuk pesan sukses/gagal -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php
        // Hapus pesan setelah ditampilkan
        unset($_SESSION['message']);
        unset($_SESSION['msg_type']);
        ?>
    <?php endif; ?>


    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Event</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label>Kategori:</label>
                    <select class="form-control" id="filterKategori">
                        <option value="">Semua Kategori</option>
                        <option value="Art">Art</option>
                        <option value="Music">Music</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Festival">Festival</option>
                        <option value="Education">Education</option>
                        <option value="Sports">Sports</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Tanggal Mulai:</label>
                    <input type="date" class="form-control" id="filterTanggal">
                </div>
                <div class="col-md-3">
                    <label>Cari Event:</label>
                    <input type="text" class="form-control" id="searchEvent" placeholder="Nama event...">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="button" class="btn btn-info btn-sm" id="filterData">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm ml-2" id="resetFilter">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Cards Grid -->
    <div class="row" id="eventCardsContainer">
        <!-- Event cards will be dynamically loaded here -->
    </div>

    <!-- Event Table -->
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
                    <tbody id="eventTableBody">
                        <!-- Data akan diisi oleh PHP -->
                        <?php
                        $event_list = getEventData($koneksi); // Ambil data dari fungsi
                        $no = 1;
                        foreach ($event_list as $event):
                            // Tentukan path gambar
                            $gambar_path = '../../../Uploads/event/' . htmlspecialchars($event['gambar']);
                            $image_src = (file_exists($gambar_path) && !empty($event['gambar'])) ? $gambar_path : 'path/to/default/event/image.jpg';
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><img src="<?php echo $image_src; ?>" alt="Gambar <?php echo htmlspecialchars($event['nama_event']); ?>" width="50" height="50" class="img-thumbnail"></td>
                                <td>
                                    <?php echo htmlspecialchars($event['nama_event']); ?>
                                    <?php if (isset($event['pembuat_nama'])): ?>
                                        <small class="text-muted d-block">oleh: <?php echo htmlspecialchars($event['pembuat_nama']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($event['nama_ormawa']); ?></td>
                                <td><?php echo $event['tgl_mulai']; ?> s/d <?php echo $event['tgl_selesai']; ?></td>
                                <td><?php echo htmlspecialchars($event['lokasi']); ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $event['id']; ?>" data-nama="<?php echo htmlspecialchars($event['nama_event'], ENT_QUOTES); ?>" data-deskripsi="<?php echo htmlspecialchars($event['deskripsi'], ENT_QUOTES); ?>" data-tgl_mulai="<?php echo $event['tgl_mulai']; ?>" data-tgl_selesai="<?php echo $event['tgl_selesai']; ?>" data-lokasi="<?php echo htmlspecialchars($event['lokasi'], ENT_QUOTES); ?>" data-ormawa_id="<?php echo $event['ormawa_id']; ?>" data-gambar="<?php echo htmlspecialchars($event['gambar']); ?>" data-toggle="modal" data-target="#editEventModal">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $event['id']; ?>">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Statistik Event -->
    <div class="row">
        <div class="col  col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Event</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statTotalEvent"><?php echo count($event_list); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Event Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statEventAktif">0</div> <!-- Hitung dinamis jika perlu -->
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include('../FormData/TambahEvent.php')?>
<!-- Modal Edit Event -->
<div class="modal fade" id="editEventModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="event_id" id="edit_event_id">
                    <?php
                    // Jika admin organisasi, sembunyikan dropdown dan gunakan hidden input
                    if (isset($_SESSION['user_level']) && $_SESSION['user_level'] == 2 && isset($admin_ormawa_info)):
                    ?>
                        <input type="hidden" name="ormawa_id" id="edit_ormawa_id_hidden" value="<?php echo $admin_ormawa_info['id']; ?>">
                        <div class="form-group">
                            <label>Ormawa Penyelenggara</label>
                            <input type="text" class="form-control" id="edit_ormawa_nama_display" value="<?php echo htmlspecialchars($admin_ormawa_info['nama_ormawa']); ?>" readonly>
                        </div>
                    <?php else: // Jika SuperAdmin, tampilkan dropdown ?>
                        <div class="form-group">
                            <label for="edit_ormawa_id">Ormawa Penyelenggara</label>
                            <select class="form-control" id="edit_ormawa_id" name="ormawa_id" required>
                                <option value="">Pilih Ormawa</option>
                                <?php
                                foreach ($all_ormawa_list as $ormawa): // Gunakan $all_ormawa_list
                                ?>
                                    <option value="<?php echo $ormawa['id']; ?>"><?php echo htmlspecialchars($ormawa['nama_ormawa']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="edit_nama_event">Nama Event</label>
                        <input type="text" class="form-control" id="edit_nama_event" name="nama_event" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_deskripsi">Deskripsi</label>
                        <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_tgl_mulai">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="edit_tgl_mulai" name="tgl_mulai" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_tgl_selesai">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="edit_tgl_selesai" name="tgl_selesai" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_lokasi">Lokasi</label>
                        <input type="text" class="form-control" id="edit_lokasi" name="lokasi" required>
                    </div>
                    <div class="form-group">
                        <label>Gambar Saat Ini</label>
                        <br>
                        <img id="current_image_display" src="" alt="Gambar Saat Ini" width="100" height="100" class="img-thumbnail">
                        <input type="hidden" name="gambar_lama" id="edit_gambar_lama">
                    </div>
                    <div class="form-group">
                        <label for="edit_gambar">Ganti Gambar (Opsional)</label>
                        <input type="file" class="form-control-file" id="edit_gambar" name="gambar" accept="image/*">
                        <small class="form-text text-muted">Jika ingin mengganti gambar, pilih file baru. Jenis file yang diperbolehkan: JPG, JPEG, PNG.</small>
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

<?php include('../SuperAdmin/Footer.php'); // Ganti dengan path footer untuk Admin Organisasi jika berbeda ?>