<?php
include('Header.php');
include('../../../Config/ConnectDB.php');

// Fungsi ambil data akun Ormawa (level = 2)
function getAccountOrmawa($koneksi) {
    $sql = "SELECT id, full_name, nim, username, email, level FROM user WHERE level = 3 ORDER BY full_name ASC";
    $result = mysqli_query($koneksi, $sql);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_free_result($result);
    }
    return $data;
}
$accounts = getAccountOrmawa($koneksi);
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Akun Anggota</h1>
        <button class="btn btn-success btn-icon-split" data-bs-toggle="modal" data-bs-target="#tambahAccountModal" onclick="resetAccountForm()">
            <span class="icon text-white-50">
                <i class="fas fa-plus"></i>
            </span>
            <span class="text">Tambah Akun</span>
        </button>
    </div>

    <!-- Alert Messages dari Session -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- DataTabels Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Akun Ormawa</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIM</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($accounts)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($accounts as $acc): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($acc['full_name)']); ?></td>
                                    <td><?= htmlspecialchars($acc['nim']); ?></td>
                                    <td><?= htmlspecialchars($acc['username']); ?></td>
                                    <td><?= htmlspecialchars($acc['email']); ?></td>
                                    <td>
                                        <?php
                                        if ($acc['level'] == 1) echo "Super Admin";
                                        elseif ($acc['level'] == 2) echo "Ormawa";
                                        else echo "Lainnya";
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-warning btn-circle btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#tambahAccountModal"
                                            onclick='editAccount(
                                                <?= (int)$acc['id'] ?>,
                                                <?= json_encode(htmlspecialchars($acc['nama'])) ?>,
                                                <?= json_encode(htmlspecialchars($acc['nim'])) ?>,
                                                <?= json_encode(htmlspecialchars($acc['username'])) ?>,
                                                <?= json_encode(htmlspecialchars($acc['email'])) ?>
                                            )'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="../../../Function/AccountFunction.php?action=delete&id=<?= $acc['id']; ?>"
                                           class="btn btn-danger btn-circle btn-sm"
                                           onclick="return confirm('Yakin hapus akun ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada akun Ormawa.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<?php include('../FormData/TambahAccount.php'); ?>
<?php include('Footer.php'); ?>
<?php mysqli_close($koneksi); ?>