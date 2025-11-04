<?php
require_once '../Database/ConnectDB.php';

// Set header JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Opsional, jika perlu CORS
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Tutup koneksi saat script selesai
register_shutdown_function(function() use ($koneksi) {
    if ($koneksi) {
        mysqli_close($koneksi);
    }
});

// Ambil action dari POST atau GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'create':
        createEvent($koneksi);
        break;
    case 'read':
        readEvents($koneksi);
        break;
    case 'read_single':
        readSingleEvent($koneksi);
        break;
    case 'update':
        updateEvent($koneksi);
        break;
    case 'delete':
        deleteEvent($koneksi);
        break;
    case 'statistics':
        getStatistics($koneksi);
        break;
    case 'generate_sample':
        generateSampleData($koneksi);
        break;
    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
}

// CREATE - Tambah Event
function createEvent($koneksi) {
    // Validasi input wajib
    if (!isset($_POST['nama_event']) || !isset($_POST['kategori']) || !isset($_POST['tgl_mulai']) || !isset($_POST['lokasi'])) {
        echo json_encode(['status' => 'error', 'message' => 'Data wajib tidak lengkap']);
        return;
    }

    $nama_event = trim($_POST['nama_event']);
    $kategori = trim($_POST['kategori']);
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = !empty($_POST['tgl_selesai']) ? $_POST['tgl_selesai'] : null;
    $lokasi = trim($_POST['lokasi']);
    $deskripsi = !empty($_POST['deskripsi']) ? trim($_POST['deskripsi']) : null;

    // Validasi format tanggal
    if (!strtotime($tgl_mulai)) {
        echo json_encode(['status' => 'error', 'message' => 'Format tanggal mulai tidak valid']);
        return;
    }

    // Handle upload gambar
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $gambar = uploadGambar($_FILES['gambar']);
        if ($gambar === false) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengupload gambar. Pastikan format JPG/PNG dan ukuran < 2MB']);
            return;
        }
    }

    // Gunakan prepared statement untuk keamanan maksimal
    $sql = "INSERT INTO event (nama_event, kategori, tgl_mulai, tgl_selesai, lokasi, deskripsi, gambar) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $sql);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query: ' . mysqli_error($koneksi)]);
        return;
    }

    mysqli_stmt_bind_param($stmt, 'sssssss', 
        $nama_event, 
        $kategori, 
        $tgl_mulai, 
        $tgl_selesai, 
        $lokasi, 
        $deskripsi, 
        $gambar
    );

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Event berhasil ditambahkan',
            'id' => mysqli_insert_id($koneksi)
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database: ' . mysqli_error($koneksi)]);
    }

    mysqli_stmt_close($stmt);
}

// READ - Ambil semua event dengan filter
function readEvents($koneksi) {
    $kategori = $_GET['kategori'] ?? '';
    $tanggal = $_GET['tanggal'] ?? '';
    $search = $_GET['search'] ?? '';

    $sql = "SELECT * FROM event WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($kategori)) {
        $sql .= " AND kategori = ?";
        $params[] = $kategori;
        $types .= "s";
    }

    if (!empty($tanggal)) {
        $sql .= " AND DATE(tgl_mulai) = ?";
        $params[] = $tanggal;
        $types .= "s";
    }

    if (!empty($search)) {
        $sql .= " AND (nama_event LIKE ? OR lokasi LIKE ?)";
        $searchLike = "%$search%";
        $params[] = $searchLike;
        $params[] = $searchLike;
        $types .= "ss";
    }

    $sql .= " ORDER BY tgl_mulai DESC";

    $stmt = mysqli_prepare($koneksi, $sql);
    if ($types) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil data']);
        return;
    }

    $result = mysqli_stmt_get_result($stmt);
    $events = mysqli_fetch_all($result, MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $events]);
    mysqli_stmt_close($stmt);
}

// READ SINGLE - Ambil detail satu event
function readSingleEvent($koneksi) {
    if (!isset($_GET['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID event tidak diberikan']);
        return;
    }

    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM event WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil data']);
        return;
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Event tidak ditemukan']);
    }

    mysqli_stmt_close($stmt);
}

// UPDATE - Edit Event
function updateEvent($koneksi) {
    if (!isset($_POST['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID event tidak diberikan']);
        return;
    }

    $id = (int)$_POST['id'];
    $nama_event = trim($_POST['nama_event']);
    $kategori = trim($_POST['kategori']);
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = !empty($_POST['tgl_selesai']) ? $_POST['tgl_selesai'] : null;
    $lokasi = trim($_POST['lokasi']);
    $deskripsi = !empty($_POST['deskripsi']) ? trim($_POST['deskripsi']) : null;
    $gambar_lama = $_POST['gambar_lama'] ?? null;

    // Handle upload gambar baru
    $gambar = $gambar_lama;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $gambar_baru = uploadGambar($_FILES['gambar']);
        if ($gambar_baru !== false) {
            // Hapus gambar lama
            if (!empty($gambar_lama) && file_exists("../uploads/" . $gambar_lama)) {
                unlink("../uploads/" . $gambar_lama);
            }
            $gambar = $gambar_baru;
        }
    }

    $sql = "UPDATE event SET 
            nama_event = ?, 
            kategori = ?, 
            tgl_mulai = ?, 
            tgl_selesai = ?, 
            lokasi = ?, 
            deskripsi = ?, 
            gambar = ?
            WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $sql);

    mysqli_stmt_bind_param($stmt, 'sssssssi',
        $nama_event,
        $kategori,
        $tgl_mulai,
        $tgl_selesai,
        $lokasi,
        $deskripsi,
        $gambar,
        $id
    );

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Event berhasil diupdate']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update: ' . mysqli_error($koneksi)]);
    }

    mysqli_stmt_close($stmt);
}

// DELETE - Hapus Event
function deleteEvent($koneksi) {
    if (!isset($_POST['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID event tidak diberikan']);
        return;
    }

    $id = (int)$_POST['id'];

    // Ambil nama file gambar
    $sql = "SELECT gambar FROM event WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Hapus dari database
    $sql = "DELETE FROM event WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (mysqli_stmt_execute($stmt)) {
        // Hapus file gambar
        if (!empty($row['gambar']) && file_exists("../uploads/" . $row['gambar'])) {
            unlink("../uploads/" . $row['gambar']);
        }
        echo json_encode(['status' => 'success', 'message' => 'Event berhasil dihapus']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus: ' . mysqli_error($koneksi)]);
    }

    mysqli_stmt_close($stmt);
}

// GET STATISTICS
function getStatistics($koneksi) {
    $sqlTotal = "SELECT COUNT(*) as total FROM event";
    $resultTotal = mysqli_query($koneksi, $sqlTotal);
    $total = (int) mysqli_fetch_assoc($resultTotal)['total'];

    $sqlAktif = "SELECT COUNT(*) as aktif FROM event WHERE DATE(tgl_mulai) >= CURDATE()";
    $resultAktif = mysqli_query($koneksi, $sqlAktif);
    $aktif = (int) mysqli_fetch_assoc($resultAktif)['aktif'];

    echo json_encode([
        'status' => 'success',
        'data' => ['total' => $total, 'aktif' => $aktif]
    ]);
}

// GENERATE SAMPLE DATA
function generateSampleData($koneksi) {
    $sampleEvents = [
        ['Art Festival 2025', 'Festival', '2025-11-15 09:00:00', '2025-11-17 18:00:00', 'Gedung Kesenian Jakarta', 'Festival seni tahunan menampilkan karya seniman lokal dan internasional'],
        ['Workshop Digital Marketing', 'Workshop', '2025-11-20 13:00:00', '2025-11-20 16:00:00', 'Hotel Santika Jember', 'Pelatihan strategi pemasaran digital untuk UMKM'],
        ['Jazz Night Concert', 'Music', '2025-12-01 19:00:00', '2025-12-01 22:00:00', 'Balai Kota Jember', 'Konser musik jazz dengan musisi ternama'],
        ['Seminar Pendidikan', 'Education', '2025-11-25 08:00:00', '2025-11-25 12:00:00', 'Universitas Jember', 'Seminar tentang inovasi pendidikan di era digital'],
        ['Marathon Run 2025', 'Sports', '2025-12-10 06:00:00', '2025-12-10 10:00:00', 'Alun-alun Jember', 'Lomba lari marathon 10K dan 5K']
    ];

    $success = 0;
    foreach ($sampleEvents as $event) {
        $sql = "INSERT INTO event (nama_event, kategori, tgl_mulai, tgl_selesai, lokasi, deskripsi) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssss', ...$event);
        if (mysqli_stmt_execute($stmt)) {
            $success++;
        }
        mysqli_stmt_close($stmt);
    }

    echo json_encode([
        'status' => 'success',
        'message' => "$success sample event berhasil ditambahkan"
    ]);
}

// FUNCTION UPLOAD GAMBAR
function uploadGambar($file) {
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid('event_', true) . '.' . $imageFileType;
    $target_file = $target_dir . $newFileName;

    // Cek apakah file benar-benar gambar
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }

    // Cek ukuran (max 2MB)
    if ($file["size"] > 2 * 1024 * 1024) {
        return false;
    }

    // Cek format
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed)) {
        return false;
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $newFileName;
    }

    return false;
}
?>