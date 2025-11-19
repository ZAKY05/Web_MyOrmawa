<?php
include('Header.php');
include('../FormData/TambahAbsensi.php');
include('../../../Config/ConnectDB.php');

$user_level = $_SESSION['user_level'] ?? 0;
$ormawa_id = $_SESSION['ormawa_id'] ?? 0;

// Helper: ambil jumlah peserta
function getPesertaCount($koneksi, $kehadiran_id) {
    $stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) as c FROM absensi_log WHERE kehadiran_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $kehadiran_id);
    mysqli_stmt_execute($stmt);
    $count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];
    mysqli_stmt_close($stmt);
    return (int)$count;
}

// Ambil semua sesi
$sessions = [];
if ($user_level === 2) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT id, judul_rapat, waktu_mulai, waktu_selesai, status, kode_unik
        FROM kehadiran 
        WHERE ormawa_id = ? 
        ORDER BY waktu_mulai DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $ormawa_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $sessions = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
?>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-qrcode me-2"></i>Absensi Rapat Internal
        </h1>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#buatSesiModal">
            <i class="fas fa-plus me-1"></i> Buat Sesi Absensi
        </button>
    </div>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">
                        <i class="fas fa-qrcode me-2"></i>QR Code Absensi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-2">
                        <strong id="qrJudul">Rapat Internal</strong>
                    </p>
                    <div id="qrCanvas" class="d-inline-block bg-white p-2 rounded border" style="min-height: 220px;">
                        <div class="text-muted">Tunggu sebentar...</div>
                    </div>
                    <p class="mt-2 text-muted small">
                        Scan dengan aplikasi absensi mobile
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Sesi -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Sesi Absensi</h6>
        </div>
        <div class="card-body">
            <?php if (empty($sessions)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-history fa-3x mb-2"></i>
                    <p>Belum ada sesi absensi.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Judul Rapat</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Peserta</th>
                                <th>QR</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach ($sessions as $s): 
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($s['judul_rapat']); ?></td>
                                <td>
                                    <?= date('d M Y', strtotime($s['waktu_mulai'])); ?><br>
                                    <small><?= date('H:i', strtotime($s['waktu_mulai'])); ?> - <?= date('H:i', strtotime($s['waktu_selesai'])); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $s['status'] === 'aktif' ? 'success' : ($s['status'] === 'selesai' ? 'secondary' : 'danger'); ?>">
                                        <?= ucfirst($s['status']); ?>
                                    </span>
                                </td>
                                <td><?= getPesertaCount($koneksi, $s['id']); ?> orang</td>
                                <td>
                                    <!-- Tombol Lihat QR -->
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick='tampilkanQR("<?= addslashes($s['kode_unik']); ?>", "<?= addslashes($s['judul_rapat']); ?>")' 
                                            data-bs-toggle="modal" data-bs-target="#qrModal">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#lihatPesertaModal" 
                                            onclick="loadPeserta(<?= $s['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($s['status'] === 'aktif'): ?>
                                    <button class="btn btn-danger btn-sm btn-sm" onclick="selesaiSesi(<?= $s['id']; ?>)">
                                        <i class="fas fa-stop"></i>
                                    </button>
                                    <?php endif; ?>
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

<!-- Modal: Lihat Peserta -->
<div class="modal fade" id="lihatPesertaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users me-2"></i>Daftar Peserta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="pesertaList" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php include('Footer.php'); ?>

<!-- ✅ GANTI LIBRARY: Pakai QRCode.js yang support browser -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
console.log('🚀 Script loaded');

// Toggle lokasi
document.addEventListener('DOMContentLoaded', function() {
    const useLocation = document.getElementById('useLocation');
    const locationFields = document.getElementById('locationFields');
    if (useLocation && locationFields) {
        useLocation.addEventListener('change', function() {
            locationFields.style.display = this.checked ? 'block' : 'none';
        });
    }
});

// ✅ FUNGSI TAMPILKAN QR (DIPERBAIKI)
function tampilkanQR(kode_unik, judul) {
    console.log('📱 Tampilkan QR untuk:', kode_unik, judul);
    
    document.getElementById('qrJudul').innerText = judul;
    
    const qrContainer = document.getElementById('qrCanvas');
    qrContainer.innerHTML = ''; // Clear dulu
    
    // Cek apakah library sudah loaded
    if (typeof QRCode === 'undefined') {
        console.error('❌ QRCode library belum loaded!');
        qrContainer.innerHTML = '<div class="text-danger">Library QR Code gagal dimuat</div>';
        return;
    }
    
    const qrData = JSON.stringify({
        type: "ABSENSI_ORMAWA",
        kode: kode_unik,
        lokasi_dibutuhkan: false
    });
    
    console.log('📦 QR Data:', qrData);
    
    try {
        // ✅ CARA YANG BENAR: new QRCode()
        new QRCode(qrContainer, {
            text: qrData,
            width: 220,
            height: 220,
            colorDark: "#007bff",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        console.log('✅ QR Code berhasil dibuat!');
    } catch (error) {
        console.error('❌ Error creating QR:', error);
        qrContainer.innerHTML = '<div class="text-danger">❌ Gagal generate QR: ' + error.message + '</div>';
    }
}

// Akhiri sesi
function selesaiSesi(id) {
    if (!confirm('Akhiri sesi absensi ini? Peserta tidak akan bisa absen lagi.')) return;
    
    console.log('🛑 Mengakhiri sesi:', id);
    
    fetch('/MyOrmawa/Function/AbsensiFunction.php?action=selesai&id=' + id)
        .then(r => {
            console.log('Response status:', r.status);
            return r.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                alert('✅ ' + data.message);
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('❌ Gagal terhubung ke server: ' + err.message);
        });
}

// Load peserta
function loadPeserta(kehadiran_id) {
    console.log('👥 Load peserta untuk sesi:', kehadiran_id);
    
    document.getElementById('pesertaList').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Memuat...</p>
        </div>
    `;
    
    fetch('/MyOrmawa/Function/AbsensiFunction.php?action=get_peserta&kehadiran_id=' + kehadiran_id)
        .then(r => {
            console.log('Response status:', r.status);
            return r.json();
        })
        .then(data => {
            console.log('Peserta data:', data);
            
            if (data.success) {
                if (data.peserta.length === 0) {
                    document.getElementById('pesertaList').innerHTML = 
                        '<p class="text-center text-muted py-4">Belum ada peserta yang absen.</p>';
                    return;
                }
                
                let html = `
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Waktu Absen</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>`;
                
                data.peserta.forEach((p, i) => {
                    html += `
                    <tr>
                        <td>${i+1}</td>
                        <td>${p.nama}</td>
                        <td>${p.waktu_absen}</td>
                        <td>
                            <span class="badge bg-${p.tipe_absen === 'hadir' ? 'success' : 'warning'}">
                                ${p.tipe_absen === 'hadir' ? 'Hadir' : 'Terlambat'}
                            </span>
                        </td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                document.getElementById('pesertaList').innerHTML = html;
            } else {
                document.getElementById('pesertaList').innerHTML = 
                    '<div class="alert alert-danger text-center py-4">❌ ' + (data.message || 'Gagal memuat data') + '</div>';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            document.getElementById('pesertaList').innerHTML = 
                '<div class="alert alert-danger text-center py-4">❌ Gagal terhubung ke server: ' + err.message + '</div>';
        });
}

// ✅ Test library saat page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Page loaded');
    console.log('✅ QRCode library status:', typeof QRCode);
    
    if (typeof QRCode === 'undefined') {
        console.error('❌ QRCode library TIDAK TERSEDIA!');
    } else {
        console.log('✅ QRCode library SIAP DIGUNAKAN');
    }
});
</script>

<style>
/* Style untuk QR Container */
#qrCanvas {
    min-height: 220px;
    min-width: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    padding: 10px;
    border-radius: 8px;
}

#qrCanvas img {
    border-radius: 4px;
}
</style>