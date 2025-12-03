<?php
include_once(__DIR__ . '/Header.php'); 
include_once(__DIR__ . '/../FormData/TambahLokasi.php');

$path_koneksi = __DIR__ . '/../../../Config/ConnectDB.php';
if (file_exists($path_koneksi)) {
    $koneksi = include($path_koneksi);
} else {
    die("Error: File koneksi tidak ditemukan di $path_koneksi");
}

$user_level = $_SESSION['user_level'] ?? 0;
$ormawa_id = $_SESSION['ormawa_id'] ?? 0;

// ✅ Izinkan level 1 (SuperAdmin) & level 2 (Admin ORMAWA)
if ($user_level != 1 && $user_level != 2) {
    die("Akses ditolak. Hanya SuperAdmin atau Admin ORMAWA yang bisa mengakses halaman ini.");
}

$locations = [];

if ($koneksi) {
    // 🔑 Query sesuai role:
    // - SuperAdmin: tampilkan SEMUA lokasi (opsional bisa dibatasi per ormawa terpilih)
    // - Admin: hanya milik ormawanya
    $query = $user_level === 1 
        ? "SELECT l.*, o.nama_ormawa FROM lokasi_absen l JOIN ormawa o ON l.ormawa_id = o.id ORDER BY o.nama_ormawa, l.nama_lokasi"
        : "SELECT * FROM lokasi_absen WHERE ormawa_id = ? ORDER BY nama_lokasi ASC";

    $stmt = mysqli_prepare($koneksi, $query);
    if ($user_level === 2) {
        mysqli_stmt_bind_param($stmt, "i", $ormawa_id);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $locations[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <?= $user_level === 1 ? 'Bank Lokasi (Semua ORMAWA)' : 'Lokasi Absensi'; ?>
        </h1>
        <button type="button" class="btn btn-primary btn-icon-split" 
                data-bs-toggle="modal" data-bs-target="#lokasiModal" 
                onclick="prepareAdd()">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Tambah Lokasi</span>
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <?= $user_level === 1 ? 'Daftar Lokasi Semua ORMAWA' : 'Daftar Lokasi Tersimpan'; ?>
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($locations)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-map-marked-alt fa-2x mb-2"></i>
                    <p class="mb-0">Belum ada lokasi tersimpan.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <?php if ($user_level === 1): ?>
                                    <th>ORMAWA</th>
                                <?php endif; ?>
                                <th>Nama Lokasi</th>
                                <th>Koordinat (Lat, Lng)</th>
                                <th>Radius (m)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($locations as $loc): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <?php if ($user_level === 1): ?>
                                    <td><?= htmlspecialchars($loc['nama_ormawa']); ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($loc['nama_lokasi']); ?></td>
                                <td><code><?= number_format((float)$loc['lat'], 6); ?>, <?= number_format((float)$loc['lng'], 6); ?></code></td>
                                <td><?= (int)$loc['radius_default']; ?></td>
                                <td>
                                    <button class="btn btn-warning btn-circle btn-sm edit-btn" 
                                            data-id="<?= (int)$loc['id']; ?>"
                                            data-nama="<?= htmlspecialchars($loc['nama_lokasi']); ?>"
                                            data-lat="<?= $loc['lat']; ?>"
                                            data-lng="<?= $loc['lng']; ?>"
                                            data-radius="<?= (int)$loc['radius_default']; ?>"
                                            data-ormawa-id="<?= (int)$loc['ormawa_id']; ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#lokasiModal"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-circle btn-sm delete-btn" 
                                            data-id="<?= (int)$loc['id']; ?>" 
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
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

<?php include_once(__DIR__ . '/Footer.php'); ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const LOCATION_API = "../../../Function/LokasiFunction.php";

function prepareAdd() {
    document.getElementById('formLokasi').reset();
    document.getElementById('lokasiModalTitle').innerText = 'Tambah Lokasi';
    document.getElementById('lokasi_id').value = '';
    // Reset select Ormawa (jika SuperAdmin)
    const select = document.getElementById('ormawaSelect');
    if (select) select.value = '';
}

document.addEventListener('DOMContentLoaded', () => {
    // ✅ Edit: ambil ormawa_id saat edit (untuk SuperAdmin)
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            const lat = this.getAttribute('data-lat');
            const lng = this.getAttribute('data-lng');
            const radius = this.getAttribute('data-radius');
            const ormawaId = this.getAttribute('data-ormawa-id'); // ✅ Tambahkan ini

            document.getElementById('lokasiModalTitle').innerText = 'Edit Lokasi';
            document.getElementById('lokasi_id').value = id;
            document.getElementById('nama_lokasi').value = nama;
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
            document.getElementById('radius_default').value = radius;

            // Set ormawa_id di form (hidden/select)
            const ormawaInput = document.querySelector('input[name="ormawa_id"]');
            const ormawaSelect = document.getElementById('ormawaSelect');
            if (ormawaInput) ormawaInput.value = ormawaId;
            if (ormawaSelect) ormawaSelect.value = ormawaId;
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Yakin hapus lokasi?',
                text: "Lokasi ini akan dihapus permanen dari bank lokasi.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${LOCATION_API}?action=delete&id=${id}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Dihapus!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Gagal!', data.message || 'Tidak dapat menghapus lokasi.', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error!', 'Gagal terhubung ke server.', 'error'));
                }
            });
        });
    });
});

async function saveLocation() {
    const form = document.getElementById('formLokasi');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const id = document.getElementById('lokasi_id').value;
    const action = id ? 'edit' : 'add';
    formData.append('action', action);

    const btn = document.getElementById('btnSimpanLokasi');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    try {
        const response = await fetch(LOCATION_API, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        });

        const data = await response.json();

        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('lokasiModal'));
            if (modal) modal.hide();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message || 'Terjadi kesalahan saat menyimpan.',
                timer: 3000,
                showConfirmButton: false
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Gagal terhubung ke server.',
            timer: 3000,
            showConfirmButton: false
        });
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
</script>