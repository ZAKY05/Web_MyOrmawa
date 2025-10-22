<?php include('Header.php'); ?>
<?php include('../../../Config/ConnectDB.php'); ?>

<?php
function getOrmawaData($koneksi) {
    $sql = "SELECT id, nama_ormawa, deskripsi, logo FROM ormawa ORDER BY nama_ormawa ASC";
    $result = mysqli_query($koneksi, $sql);
    $data = [];
    if ($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_free_result($result);
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
    return $data;
}
$ormawa_list = getOrmawaData($koneksi);
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Data Organisasi Mahasiswa (Ormawa)</h1>
        <button class="btn btn-success btn-icon-split" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="resetForm()">
            <span class="icon text-white-50">
                <i class="fas fa-plus"></i>
            </span>
            <span class="text">Tambah Ormawa</span>
        </button>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Data berhasil disimpan.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_berhasil'): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            Data berhasil dihapus.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- DataTabels Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Ormawa</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Ormawa</th>
                            <th>Deskripsi</th>
                            <th>Logo</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($ormawa_list as $ormawa) {
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . htmlspecialchars($ormawa['nama_ormawa']) . "</td>";
                            echo "<td>" . htmlspecialchars(substr($ormawa['deskripsi'], 0, 50)) . "...</td>";
                            echo "<td>";
                            if ($ormawa['logo']) {
                                echo '<img src="../../../uploads/logos/' . htmlspecialchars($ormawa['logo']) . '" alt="Logo" style="max-width: 50px; max-height: 50px;">';
                            } else {
                                echo 'Tidak ada';
                            }
                            echo "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-warning btn-circle btn-sm' data-bs-toggle='modal' data-bs-target='#modalForm' onclick='editOrmawa("
                                . $ormawa['id'] . ", \""
                                . addslashes($ormawa['nama_ormawa']) . "\", \""
                                . addslashes($ormawa['deskripsi']) . "\", \""
                                . addslashes($ormawa['logo']) . "\")'>";
                            echo "<i class='fas fa-edit'></i>";
                            echo "</button> ";
                            echo "<a href='../../../Function/OrmawaFunction.php?action=delete&id=" . $ormawa['id'] . "' class='btn btn-danger btn-circle btn-sm' onclick='return confirm(\"Yakin hapus data ini?\")'>";
                            echo "<i class='fas fa-trash'></i>";
                            echo "</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<?php include('FormData/TambahOrmawa.php'); ?>
<?php include('Footer.php'); ?>
<?php mysqli_close($koneksi); ?>