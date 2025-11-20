<?php
include('../SuperAdmin/Header.php');
include('../SuperAdmin/Route.php');

session_start();

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    // Redirect ke login yang benar
    header("Location: ../SuperAdmin/Login.php");
    exit();
}
?>


<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include('../SuperAdmin/Sidebar.php'); ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php
                include('../SuperAdmin/Topbar.php');
                ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <?php
                $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
                if (array_key_exists($page, $routes)) {
                    include($routes[$page]);
                } else {
                    echo "<h1 class='text-center'>404 Halaman tidak ada</h1>";
                }
                ?>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; MyOrmawa 2025</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    

    <?php
    include('../SuperAdmin/Footer.php');
    ?>

</body>

</ht..