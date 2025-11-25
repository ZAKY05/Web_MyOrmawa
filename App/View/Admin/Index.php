<?php
// 🔐 SESSION SETUP — HARUS DI PALING ATAS, SEBELUM APA PUN
// Atur cookie session agar berlaku untuk seluruh /MyOrmawa/ (termasuk /Function/)
session_set_cookie_params([
    'lifetime' => 0,           // Session berakhir saat browser ditutup
    'path'     => '/MyOrmawa/', // 🔑 KUNCINYA: agar Function/ bisa baca session
    'domain'   => '',          // localhost
    'secure'   => false,       // false untuk HTTP (ubah ke true jika pakai HTTPS)
    'httponly' => true,        // lebih aman
    'samesite' => 'Lax'        // proteksi CSRF
]);
session_start();

// 🚫 Cek login — redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    // Pastikan path redirect benar
    header("Location: ../SuperAdmin/Login.php");
    exit();
}

// 📁 Include file dependensi
include('Header.php');
include('Route.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyOrmawa - Dashboard</title>
    <!-- Base URL untuk AJAX (opsional tapi direkomendasikan) -->
    <script>
        // ✅ BASE_URL untuk path absolut — aman di semua halaman dinamis
        window.BASE_URL = "http://<?php echo $_SERVER['HTTP_HOST']; ?>/MyOrmawa";
        console.log('🌐 Base URL:', window.BASE_URL);
    </script>
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <?php include('Sidebar.php'); ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <?php include('Topbar.php'); ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php
                    $page = isset($_GET['page']) ? $_GET['page'] : 'anggota';
                    if (array_key_exists($page, $routes)) {
                        include($routes[$page]);
                    } else {
                        echo "<div class='alert alert-danger text-center'>404 — Halaman <strong>" . htmlspecialchars($page) . "</strong> tidak ditemukan.</div>";
                    }
                    ?>
                </div>
            </div>

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; MyOrmawa <?= date('Y') ?></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    Apakah Anda ingin "Logout" dan meninggalkan halaman ini?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="../SuperAdmin/LoginFunction.php?logout=true">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <?php include('Footer.php'); ?>
</body>
</html>