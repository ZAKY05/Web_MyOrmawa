<?php
session_start();

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    // Redirect ke login yang benar
    header("Location: ../SuperAdmin/Login.php");
    exit();
}

// Baru boleh include file lain
include('Header.php');
include('Route.php');
?>

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
                <?php
                $page = isset($_GET['page']) ? $_GET['page'] : 'anggota';
                if (array_key_exists($page, $routes)) {
                    include($routes[$page]);
                } else {
                    echo "<h1 class='text-center'>404 Halaman tidak ada</h1>";
                }
                ?>
            </div>

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; MyOrmawa 2025</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Apakah anda ingin "Logout" dan meninggalkan halaman ini?</div>
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