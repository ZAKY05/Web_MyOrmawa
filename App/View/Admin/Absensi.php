<?php
// Gunakan __DIR__ untuk memastikan path relatif terhadap file ini
include_once(__DIR__ . '/Header.php');
// Path ke FormData (Satu folder di atas Admin, lalu masuk FormData)
include_once(__DIR__ . '/../FormData/TambahAbsensi.php');
// Path ke Config (Naik 3 level: Admin -> View -> App -> Root, lalu masuk Config)
$path_koneksi = __DIR__ . '/../../../Config/ConnectDB.php';

if (file_exists($path_koneksi)) {
    $koneksi = include($path_koneksi);
} else {
    die("Error: File koneksi tidak ditemukan di $path_koneksi");
}

// Cek Session
$user_level = $_SESSION['user_level'] ?? 0;
$ormawa_id = $_SESSION['ormawa_id'] ?? 0;

// Helper Function
function getPesertaCount($koneksi, $kehadiran_id) {
    if (!$koneksi) return 0;
    $stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) as c FROM absensi_log WHERE kehadiran_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $kehadiran_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    return $row ? (int)$row['c'] : 0;
}

// Ambil Data Sesi
$sessions = [];
if ($user_level == 2 && $koneksi) {
    // ✅ Query dengan CASE: otomatis ubah status jadi 'selesai' jika waktu_selesai < sekarang
    $query = "
        SELECT 
            id, judul_rapat, waktu_mulai, waktu_selesai, kode_unik,
            CASE 
                WHEN status = 'selesai' THEN 'selesai'
                WHEN waktu_selesai <= NOW() THEN 'selesai'  -- ✅ Waktu habis → selesai
                ELSE status
            END AS status,
            ormawa_id
        FROM kehadiran 
        WHERE ormawa_id = ? 
        ORDER BY waktu_mulai DESC
    ";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $ormawa_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $sessions[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-qrcode me-2"></i>Absensi Rapat Internal</h1>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#buatSesiModal">
            <i class="fas fa-plus me-1"></i> Buat Sesi Absensi
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Sesi</h6>
        </div>
        <div class="card-body">
            <?php if (empty($sessions)): ?>
                <div class="text-center text-muted py-3">Belum ada sesi absensi.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Peserta</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no=1; foreach ($sessions as $s): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($s['judul_rapat']); ?></td>
                                <td>
                                    <?= date('d M Y', strtotime($s['waktu_mulai'])); ?><br>
                                    <small><?= date('H:i', strtotime($s['waktu_mulai'])); ?> - <?= date('H:i', strtotime($s['waktu_selesai'])); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $s['status'] == 'aktif' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($s['status']); ?>
                                    </span>
                                </td>
                                <td><?= getPesertaCount($koneksi, $s['id']); ?> orang</td>
                                <td>
                                    <button class="btn btn-sm btn-info" 
                                        onclick="tampilkanQR('<?= $s['kode_unik'] ?>', '<?= addslashes($s['judul_rapat']) ?>', '<?= $s['waktu_selesai'] ?>')" 
                                        data-bs-toggle="modal" data-bs-target="#qrModal">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="loadPeserta(<?= $s['id'] ?>)" data-bs-toggle="modal" data-bs-target="#lihatPesertaModal">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if($s['status'] == 'aktif'): ?>
                                        <button class="btn btn-sm btn-danger" onclick="selesaiSesi(<?= $s['id'] ?>)">
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

<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <h5 id="qrJudul"></h5>
                <div id="qrCanvas" class="d-flex justify-content-center my-3"></div>
                <p class="mt-3">Sesi berakhir dalam: <strong id="countdownTimer"></strong></p>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="lihatPesertaModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Daftar Hadir</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="pesertaList">Loading...</div></div></div></div>

<?php include_once(__DIR__ . '/Footer.php'); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// Definisikan BASE_URL JS secara manual jika window.BASE_URL belum ada
// Path ini naik 3 level dari View/Admin ke Root
const BASE_URL_API = "../../../Function/AbsensiFunction.php"; 

let countdownInterval; // Variabel global untuk menyimpan interval hitungan mundur

// PERUBAHAN 3: Update fungsi tampilkanQR untuk menerima waktu_selesai
function tampilkanQR(kode, judul, waktuSelesai) {
    document.getElementById('qrJudul').innerText = judul;
    const container = document.getElementById('qrCanvas');
    container.innerHTML = "";
    new QRCode(container, {
        text: JSON.stringify({type: "ABSENSI_ORMAWA", kode: kode}),
        width: 200, height: 200
    });

    // Hentikan interval sebelumnya jika ada
    clearInterval(countdownInterval); 

    // Mulai hitungan mundur hanya jika statusnya 'aktif' (berakhir dalam waktuSelesai)
    // Di sini kita berasumsi hanya sesi 'aktif' yang bisa menampilkan countdown, 
    // meskipun tombol QR tampil untuk semua status
    if (waktuSelesai) {
        // Mulai hitungan mundur
        startCountdown(waktuSelesai);
    } else {
        document.getElementById('countdownTimer').innerText = 'Tidak Tersedia';
    }
}

// FUNGSI BARU: Untuk memulai hitungan mundur
function startCountdown(endTimeString) {
    // Ubah string waktu selesai menjadi objek Date (dalam milidetik)
    const endTime = new Date(endTimeString).getTime();
    const timerElement = document.getElementById('countdownTimer');

    // Update setiap 1 detik
    countdownInterval = setInterval(function() {
        const now = new Date().getTime();
        const distance = endTime - now;

        // Perhitungan waktu untuk hari, jam, menit, dan detik
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Tampilkan hasilnya
        let countdownText = "";
        if (days > 0) countdownText += days + "h ";
        if (hours > 0 || days > 0) countdownText += hours.toString().padStart(2, '0') + "j ";
        countdownText += minutes.toString().padStart(2, '0') + "m ";
        countdownText += seconds.toString().padStart(2, '0') + "d";
        
        timerElement.innerHTML = countdownText;

        // Jika hitungan mundur selesai
        if (distance < 0) {
            clearInterval(countdownInterval);
            timerElement.innerHTML = "Waktu Habis!";
        }
    }, 1000); // 1000ms = 1 detik
}

// FUNGSI BARU: Tambahkan event listener untuk menghapus interval saat modal ditutup
document.getElementById('qrModal').addEventListener('hidden.bs.modal', function () {
    clearInterval(countdownInterval);
});


function selesaiSesi(id) {
    if(!confirm("Akhiri sesi ini?")) return;
    
    fetch(BASE_URL_API + "?action=selesai&id=" + id)
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if(data.success) location.reload();
    })
    .catch(e => alert("Error: " + e));
}

function loadPeserta(id) {
    const container = document.getElementById('pesertaList');
    container.innerHTML = '<div class="text-center spinner-border text-primary"></div>';
    
    fetch(BASE_URL_API + "?action=get_peserta&kehadiran_id=" + id)
    .then(r => r.json())
    .then(data => {
        if(!data.success) {
            container.innerHTML = data.message; return;
        }
        if(data.peserta.length === 0) {
            container.innerHTML = "<p class='text-center'>Belum ada peserta.</p>"; return;
        }
        let html = "<ul class='list-group'>";
        data.peserta.forEach(p => {
            html += `<li class='list-group-item d-flex justify-content-between align-items-center'>
                        ${p.nama}
                        <span class='badge bg-primary'>${p.waktu_absen}</span>
                     </li>`;
        });
        html += "</ul>";
        container.innerHTML = html;
    })
    .catch(e => container.innerHTML = "Error loading data.");
}
</script>