<?php
include_once(__DIR__ . '/Header.php');
include_once(__DIR__ . '/../FormData/TambahAbsensi.php');
$path_koneksi = __DIR__ . '/../../../Config/ConnectDB.php';
if (file_exists($path_koneksi)) {
    $koneksi = include($path_koneksi);
} else {
    die("Error: File koneksi tidak ditemukan di $path_koneksi");
}

function getPesertaCount($koneksi, $kehadiran_id)
{
    if (!$koneksi) return 0;
    $stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) as c FROM absensi_log WHERE kehadiran_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $kehadiran_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ? (int) $row['c'] : 0;
}

$user_level = (int) ($_SESSION['user_level'] ?? 0);
$session_ormawa_id = (int) ($_SESSION['ormawa_id'] ?? 0);

$selected_ormawa_id = 0;
if ($user_level === 1) {
    $selected_ormawa_id = (int) ($_GET['ormawa_id'] ?? 0);
} elseif ($user_level === 2) {
    $selected_ormawa_id = $session_ormawa_id;
}

if ($user_level !== 1 && $user_level !== 2) {
    die("Akses ditolak. Hanya SuperAdmin atau Admin ORMAWA yang dapat mengakses halaman ini.");
}

$sessions = [];
$allOrmawas = [];

if ($koneksi) {
    $ormawaResult = mysqli_query($koneksi, "SELECT id, nama_ormawa FROM ormawa ORDER BY nama_ormawa");
    while ($o = mysqli_fetch_assoc($ormawaResult)) {
        $allOrmawas[] = $o;
    }

    if ($user_level === 2) {
        $query = "
            SELECT id, judul_rapat, waktu_mulai, waktu_selesai, kode_unik,
                CASE 
                    WHEN status = 'selesai' THEN 'selesai'
                    WHEN waktu_selesai <= NOW() THEN 'selesai'
                    ELSE status
                END AS status,
                ormawa_id
            FROM kehadiran 
            WHERE ormawa_id = ? 
            ORDER BY waktu_mulai DESC
        ";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $selected_ormawa_id);
    } else {
        if ($selected_ormawa_id > 0) {
            $query = "
                SELECT k.id, k.judul_rapat, k.waktu_mulai, k.waktu_selesai, k.kode_unik,
                       CASE 
                           WHEN k.status = 'selesai' THEN 'selesai'
                           WHEN k.waktu_selesai <= NOW() THEN 'selesai'
                           ELSE k.status
                       END AS status,
                       k.ormawa_id, o.nama_ormawa 
                FROM kehadiran k
                JOIN ormawa o ON k.ormawa_id = o.id
                WHERE k.ormawa_id = ?
                ORDER BY k.waktu_mulai DESC
            ";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "i", $selected_ormawa_id);
        } else {
            $query = "
                SELECT k.id, k.judul_rapat, k.waktu_mulai, k.waktu_selesai, k.kode_unik,
                       CASE 
                           WHEN k.status = 'selesai' THEN 'selesai'
                           WHEN k.waktu_selesai <= NOW() THEN 'selesai'
                           ELSE k.status
                       END AS status,
                       k.ormawa_id, o.nama_ormawa 
                FROM kehadiran k
                JOIN ormawa o ON k.ormawa_id = o.id
                ORDER BY k.waktu_mulai DESC
            ";
            $stmt = mysqli_prepare($koneksi, $query);
        }
    }

    if ($stmt && mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $sessions[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-qrcode me-2"></i>Absensi Rapat Internal
        </h1>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#buatSesiModal">
            <i class="fas fa-plus me-1"></i> Buat Sesi Absensi
        </button>
    </div>

    <?php if ($user_level === 1): ?>
        <div class="row mb-4">
            <div class="col-md-4">
                <label for="filterOrmawa" class="form-label"><i class="fas fa-building me-1"></i>Filter berdasarkan ORMAWA</label>
                <select class="form-select" id="filterOrmawa">
                    <option value="">— Semua ORMAWA —</option>
                    <?php foreach ($allOrmawas as $o): ?>
                        <option value="<?= (int) $o['id']; ?>" <?= $selected_ormawa_id == $o['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($o['nama_ormawa']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Riwayat Sesi Absensi
                <?php if ($user_level === 1 && $selected_ormawa_id > 0): ?>
                    <small class="text-muted">—
                        <?= htmlspecialchars(array_column($allOrmawas, 'nama_ormawa', 'id')[$selected_ormawa_id] ?? 'ORMAWA'); ?>
                    </small>
                <?php endif; ?>
            </h6>
            <?php if (!empty($sessions)): ?>
                <small class="badge bg-info text-dark">Total: <?= count($sessions); ?> sesi</small>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (empty($sessions)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-calendar-times fa-3x mb-2 opacity-50"></i>
                    <h5 class="mt-2">Belum ada sesi absensi.</h5>
                    <p class="mb-0">
                        <?php if ($user_level === 1 && $selected_ormawa_id == 0): ?>
                            Pilih ORMAWA di filter untuk melihat sesinya.
                        <?php else: ?>
                            Buat sesi absensi pertama Anda.
                        <?php endif; ?>
                    </p>
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
                                <th>Judul</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Peserta</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($sessions as $s): ?>
                                <tr data-ormawa-id="<?= (int) $s['ormawa_id']; ?>"
                                    data-status="<?= htmlspecialchars($s['status']); ?>">
                                    <td><?= $no++; ?></td>
                                    <?php if ($user_level === 1): ?>
                                        <td><?= htmlspecialchars($s['nama_ormawa'] ?? '—'); ?></td>
                                    <?php endif; ?>
                                    <td><?= htmlspecialchars($s['judul_rapat']); ?></td>
                                    <td>
                                        <?= date('d M Y', strtotime($s['waktu_mulai'])); ?><br>
                                        <small class="text-muted">
                                            <?= date('H:i', strtotime($s['waktu_mulai'])); ?> –
                                            <?= date('H:i', strtotime($s['waktu_selesai'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $s['status'] == 'aktif' ? 'success' : 'secondary'; ?> rounded-pill px-3 py-2">
                                            <?= ucfirst(htmlspecialchars($s['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= getPesertaCount($koneksi, $s['id']); ?></span>
                                        <small>orang</small>
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-info me-1"
                                            onclick="tampilkanQR('<?= addslashes($s['kode_unik']); ?>', '<?= addslashes($s['judul_rapat']); ?>', '<?= $s['waktu_selesai']; ?>', '<?= $s['status']; ?>')"
                                            data-bs-toggle="modal" data-bs-target="#qrModal" title="Tampilkan QR">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning me-1"
                                            onclick="loadPeserta(<?= (int) $s['id']; ?>)" data-bs-toggle="modal"
                                            data-bs-target="#lihatPesertaModal" title="Lihat Peserta">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <!-- Tombol Export Excel -->
                                        <a href="/Function/ExportAbsensiFunction.php?kehadiran_id=<?= (int) $s['id']; ?>"
                                           class="btn btn-sm btn-success me-1"
                                           target="_blank"
                                           title="Export ke Excel">
                                            <i class="fas fa-file-excel"></i>
                                        </a>
                                        <!-- Akhir dari Tombol Export Excel -->
                                        <?php if ($s['status'] == 'aktif'): ?>
                                            <button class="btn btn-sm btn-danger" onclick="selesaiSesi(<?= (int) $s['id']; ?>)"
                                                title="Akhiri Sesi">
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
                <h5 class="modal-title">QR Code Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <h5 id="qrJudul" class="mb-3"></h5>
                <div id="qrCanvas" class="d-flex justify-content-center my-3"></div>
                <p class="mt-2">
                    <i class="far fa-clock me-1"></i>
                    <span id="countdownTimer">Memuat...</span>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="lihatPesertaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Peserta Hadir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="pesertaList">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Memuat data peserta...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php include_once(__DIR__ . '/Footer.php'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js  "></script>
<script>
    const BASE_URL_API = "../../../Function/AbsensiFunction.php";
    let countdownInterval;

    function tampilkanQR(kode, judul, waktuSelesai, status) {
        document.getElementById('qrJudul').innerText = judul;
        const container = document.getElementById('qrCanvas');
        container.innerHTML = "";
        
        new QRCode(container, {
            text: JSON.stringify({ type: "ABSENSI_ORMAWA", kode: kode }),
            width: 220, height: 220
        });

        clearInterval(countdownInterval);

        // ✅ PERBAIKAN: Cek status sesi
        if (status === 'selesai') {
            document.getElementById('countdownTimer').innerHTML = 
                '<span class="text-danger fw-bold">Sesi Sudah Berakhir</span>';
            return;
        }

        // ✅ PERBAIKAN: Validasi waktu_selesai
        if (!waktuSelesai || waktuSelesai === '0000-00-00 00:00:00') {
            document.getElementById('countdownTimer').innerText = 'Waktu tidak tersedia';
            return;
        }

        startCountdown(waktuSelesai);
    }

    function startCountdown(endTimeString) {
        // ✅ PERBAIKAN: Parse waktu dengan benar
        const endTime = new Date(endTimeString.replace(' ', 'T')).getTime();
        const el = document.getElementById('countdownTimer');

        if (isNaN(endTime)) {
            el.innerHTML = '<span class="text-warning">Format waktu tidak valid</span>';
            return;
        }

        countdownInterval = setInterval(() => {
            const now = new Date().getTime();
            const diff = endTime - now;

            if (diff < 0) {
                clearInterval(countdownInterval);
                el.innerHTML = '<span class="text-danger fw-bold">Waktu Habis!</span>';
                return;
            }

            // ✅ PERBAIKAN: Hitung dengan benar
            const h = Math.floor(diff / (1000 * 60 * 60));
            const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((diff % (1000 * 60)) / 1000);

            el.innerText = `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }, 1000);
    }

    document.getElementById('qrModal')?.addEventListener('hidden.bs.modal', () => {
        clearInterval(countdownInterval);
    });

    function selesaiSesi(id) {
        const row = event.target.closest('tr');
        const ormawaId = row ? row.getAttribute('data-ormawa-id') : null;

        if (!ormawaId || ormawaId === '0') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'ORMAWA tidak ditemukan untuk sesi ini.',
                confirmButtonColor: '#d33'
            });
            return;
        }

        Swal.fire({
            title: 'Akhiri Sesi Absensi?',
            html: `Apakah Anda yakin ingin mengakhiri sesi ini?<br>
                   <small class="text-muted">Peserta tidak dapat absen lagi setelah sesi dihentikan.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-stop me-1"></i> Ya, Hentikan!',
            cancelButtonText: '<i class="fas fa-times me-1"></i> Batal',
            reverseButtons: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${BASE_URL_API}?action=selesai&id=${id}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Sesi Dihentikan!' : 'Gagal',
                        text: data.message,
                        timer: data.success ? 2000 : null,
                        showConfirmButton: !data.success,
                        confirmButtonColor: '#1cc88a'
                    }).then(() => {
                        if (data.success) location.reload();
                    });
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Gagal',
                        text: 'Tidak dapat terhubung ke server.',
                        confirmButtonColor: '#d33'
                    });
                });
            }
        });
    }

    function loadPeserta(id) {
        const el = document.getElementById('pesertaList');
        el.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Memuat...</p></div>';

        fetch(`${BASE_URL_API}?action=get_peserta&kehadiran_id=${id}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.peserta?.length) {
                    el.innerHTML = `<div class="alert alert-warning text-center py-4">
                        <i class="fas fa-users fa-2x mb-2"></i><br>
                        Belum ada peserta yang hadir.
                    </div>`;
                    return;
                }

                let tepat = 0, terlambat = 0;
                data.peserta.forEach(p => {
                    if (p.status_kehadiran === 'Hadir') tepat++;
                    else if (p.status_kehadiran === 'Terlambat') terlambat++;
                });

                let html = `<div class="alert alert-info mb-3">
                    <div class="row text-center">
                        <div class="col-4">
                            <i class="fas fa-users"></i><br>
                            <strong>${data.peserta.length}</strong><br>
                            <small>Total</small>
                        </div>
                        <div class="col-4">
                            <i class="fas fa-check-circle text-success"></i><br>
                            <strong>${tepat}</strong><br>
                            <small>Tepat Waktu</small>
                        </div>
                        <div class="col-4">
                            <i class="fas fa-exclamation-triangle text-warning"></i><br>
                            <strong>${terlambat}</strong><br>
                            <small>Terlambat</small>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Waktu Absen</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>`;

                data.peserta.forEach((p, i) => {
                    const nama = p.full_name || '—';
                    const waktu = p.waktu_absen
                        ? new Date(p.waktu_absen).toLocaleString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: false
                        })
                        : '—';
                    const status = p.status_kehadiran || 'Hadir';
                    const badgeClass = status === 'Terlambat' ? 'bg-warning text-dark' : 'bg-success';
                    const icon = status === 'Terlambat' ? 'fa-exclamation-triangle' : 'fa-check-circle';

                    html += `<tr>
                        <td>${i + 1}</td>
                        <td><strong>${nama}</strong></td>
                        <td><small><i class="far fa-clock"></i> ${waktu}</small></td>
                        <td><span class="badge ${badgeClass}"><i class="fas ${icon} me-1"></i>${status}</span></td>
                    </tr>`;
                });

                html += '</tbody></table></div>';
                el.innerHTML = html;
            })
            .catch(err => {
                console.error('Error:', err);
                el.innerHTML = `<div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Gagal memuat data peserta.
                </div>`;
            });
    }

    document.getElementById('filterOrmawa')?.addEventListener('change', function () {
        const ormawaId = this.value;
        const url = new URL(window.location.href);
        if (ormawaId) {
            url.searchParams.set('ormawa_id', ormawaId);
        } else {
            url.searchParams.delete('ormawa_id');
        }
        window.location.href = url.toString();
    });
</script>