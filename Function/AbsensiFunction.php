<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function jsonResponse($success, $message, $data = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

try {
    $config_path = __DIR__ . '/../Config/ConnectDB.php';
    if (!file_exists($config_path)) {
        throw new Exception("File koneksi tidak ditemukan.");
    }
    $koneksi = include($config_path);
    if (!$koneksi) throw new Exception("Koneksi database gagal.");

    $user_id = $_SESSION['user_id'] ?? null;
    $user_level = (int)($_SESSION['user_level'] ?? 0);
    $session_ormawa_id = (int)($_SESSION['ormawa_id'] ?? 0);

    if (!$user_id || ($user_level !== 1 && $user_level !== 2)) {
        throw new Exception("Akses ditolak. Hanya SuperAdmin atau Admin ORMAWA.");
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    // --- PING ---
    if ($action === 'ping') {
        jsonResponse(true, 'OK');
    }

    // --- GET BANK LOKASI ---
    if ($action === 'get_bank') {
        // 🔑 Tentukan ormawa_id untuk bank lokasi
        $ormawa_id = null;
        if ($user_level === 2) {
            $ormawa_id = $session_ormawa_id;
            if ($ormawa_id <= 0) {
                throw new Exception("Anda tidak terdaftar di ORMAWA manapun.");
            }
        } elseif ($user_level === 1) {
            $ormawa_id = (int)($_GET['id_ormawa'] ?? 0);
            if ($ormawa_id <= 0) {
                throw new Exception("Pilih ORMAWA terlebih dahulu.");
            }
        }

        $stmt = mysqli_prepare($koneksi, "SELECT id, nama_lokasi, lat, lng, radius_default FROM lokasi_absen WHERE ormawa_id = ?");
        if (!$stmt) throw new Exception("Gagal menyiapkan query bank lokasi.");
        mysqli_stmt_bind_param($stmt, "i", $ormawa_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if (!$res) {
            mysqli_stmt_close($stmt);
            throw new Exception("Gagal mengambil data lokasi.");
        }

        $locations = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $locations[] = $row;
        }
        mysqli_stmt_close($stmt);
        jsonResponse(true, 'OK', ['locations' => $locations]);
    }

    // --- BUAT SESI ---
    if ($action === 'buat') {
        // 🔑 Tentukan ormawa_id
        $ormawa_id = null;
        if ($user_level === 2) {
            $ormawa_id = $session_ormawa_id;
            if ($ormawa_id <= 0) {
                throw new Exception("Anda tidak terdaftar di ORMAWA manapun.");
            }
        } elseif ($user_level === 1) {
            $ormawa_id = (int)($_POST['id_ormawa'] ?? 0);
            if ($ormawa_id <= 0) {
                throw new Exception("Pilih ORMAWA terlebih dahulu.");
            }
        }

        // ✅ Validasi ORMawa ID eksis
        $checkStmt = mysqli_prepare($koneksi, "SELECT 1 FROM ormawa WHERE id = ?");
        if (!$checkStmt) throw new Exception("Gagal mempersiapkan query validasi ORMawa.");
        mysqli_stmt_bind_param($checkStmt, "i", $ormawa_id);
        mysqli_stmt_execute($checkStmt);
        $result = mysqli_stmt_get_result($checkStmt);
        $ormawaExists = $result ? $result->fetch_assoc() : null;
        mysqli_stmt_close($checkStmt);
        if (!$ormawaExists) {
            throw new Exception("ORMAWA tidak ditemukan.");
        }

        $judul = trim($_POST['judul_rapat'] ?? '');
        $mulai = $_POST['waktu_mulai'] ?? '';
        $selesai = $_POST['waktu_selesai'] ?? '';

        if (empty($judul)) throw new Exception("Judul wajib diisi.");
        if (empty($mulai) || empty($selesai)) throw new Exception("Waktu mulai & selesai wajib diisi.");

        $dt_mulai = DateTime::createFromFormat('Y-m-d\TH:i', $mulai);
        $dt_selesai = DateTime::createFromFormat('Y-m-d\TH:i', $selesai);
        if (!$dt_mulai || !$dt_selesai) {
            throw new Exception("Format waktu tidak valid.");
        }
        if ($dt_mulai >= $dt_selesai) {
            throw new Exception("Waktu selesai harus lebih besar dari waktu mulai.");
        }

        $waktu_mulai = $dt_mulai->format('Y-m-d H:i:00');
        $waktu_selesai = $dt_selesai->format('Y-m-d H:i:00');
        $kode_unik = strtoupper(substr(md5(uniqid() . time()), 0, 6));

        $id_lokasi_absen = null;
        if (!empty($_POST['id_lokasi_absen']) && !empty($_POST['use_location'])) {
            $id_lokasi_absen = (int)$_POST['id_lokasi_absen'];
            $checkLoc = mysqli_prepare($koneksi, "SELECT 1 FROM lokasi_absen WHERE id = ? AND ormawa_id = ?");
            if (!$checkLoc) throw new Exception("Query validasi lokasi gagal.");
            mysqli_stmt_bind_param($checkLoc, "ii", $id_lokasi_absen, $ormawa_id);
            mysqli_stmt_execute($checkLoc);
            $resLoc = mysqli_stmt_get_result($checkLoc);
            $validLoc = $resLoc ? mysqli_fetch_assoc($resLoc) : null;
            mysqli_stmt_close($checkLoc);
            if (!$validLoc) {
                throw new Exception("Lokasi tidak valid atau bukan milik ORMawa ini.");
            }
        }

        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO kehadiran 
             (ormawa_id, judul_rapat, waktu_mulai, waktu_selesai, kode_unik, status, id_lokasi_absen) 
             VALUES (?, ?, ?, ?, ?, 'aktif', ?)"
        );
        if (!$stmt) {
            throw new Exception("Gagal menyiapkan query insert: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param(
            $stmt,
            "issssi",
            $ormawa_id,
            $judul,
            $waktu_mulai,
            $waktu_selesai,
            $kode_unik,
            $id_lokasi_absen
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            jsonResponse(true, 'Sesi absensi berhasil dibuat!');
        } else {
            $err = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new Exception("Gagal menyimpan sesi: " . $err);
        }
    }

    // --- SELESAI SESI ---
    if ($action === 'selesai') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) throw new Exception("ID tidak valid.");

        // ✅ Ambil ormawa_id dari database berdasarkan id sesi
        $getOrmawa = mysqli_prepare($koneksi, "SELECT ormawa_id FROM kehadiran WHERE id = ?");
        if (!$getOrmawa) throw new Exception("Query gagal.");
        mysqli_stmt_bind_param($getOrmawa, "i", $id);
        mysqli_stmt_execute($getOrmawa);
        $result = mysqli_stmt_get_result($getOrmawa);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($getOrmawa);

        if (!$row) {
            throw new Exception("Sesi tidak ditemukan.");
        }

        $sesi_ormawa_id = (int)$row['ormawa_id'];

        // ✅ Validasi akses: Admin hanya bisa selesaikan sesi ormawa-nya sendiri
        if ($user_level === 2 && $sesi_ormawa_id !== $session_ormawa_id) {
            throw new Exception("Anda tidak memiliki akses ke sesi ini.");
        }

        // ✅ Update status
        $stmt = mysqli_prepare($koneksi, "UPDATE kehadiran SET status = 'selesai' WHERE id = ?");
        if (!$stmt) throw new Exception("Query update gagal.");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($affected > 0) {
            jsonResponse(true, 'Sesi berhasil diakhiri.');
        } else {
            throw new Exception("Gagal mengakhiri sesi.");
        }
    }

    // --- GET PESERTA ---
    if ($action === 'get_peserta') {
        $id = (int)($_GET['kehadiran_id'] ?? 0);
        if ($id <= 0) throw new Exception("ID sesi tidak valid.");

        // ✅ Ambil ormawa_id dari database berdasarkan kehadiran_id
        $getOrmawa = mysqli_prepare($koneksi, "SELECT ormawa_id FROM kehadiran WHERE id = ?");
        if (!$getOrmawa) throw new Exception("Query gagal.");
        mysqli_stmt_bind_param($getOrmawa, "i", $id);
        mysqli_stmt_execute($getOrmawa);
        $result = mysqli_stmt_get_result($getOrmawa);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($getOrmawa);

        if (!$row) {
            throw new Exception("Sesi tidak ditemukan.");
        }

        $sesi_ormawa_id = (int)$row['ormawa_id'];

        // ✅ Validasi akses: Admin hanya bisa lihat peserta sesi ormawa-nya sendiri
        if ($user_level === 2 && $sesi_ormawa_id !== $session_ormawa_id) {
            throw new Exception("Anda tidak memiliki akses ke sesi ini.");
        }

        $query = "SELECT u.full_name, al.waktu_absen, al.tipe_absen 
                  FROM absensi_log al
                  JOIN user u ON al.user_id = u.id
                  WHERE al.kehadiran_id = ?
                  ORDER BY al.waktu_absen DESC";
        $stmt = mysqli_prepare($koneksi, $query);
        if (!$stmt) throw new Exception("Gagal query peserta.");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if (!$res) {
            mysqli_stmt_close($stmt);
            throw new Exception("Gagal mengambil data peserta.");
        }

        $peserta = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $peserta[] = $row;
        }
        mysqli_stmt_close($stmt);
        jsonResponse(true, 'OK', ['peserta' => $peserta]);
    }

    throw new Exception("Aksi tidak dikenali.");

} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(200);
    jsonResponse(false, $e->getMessage());
}
?>