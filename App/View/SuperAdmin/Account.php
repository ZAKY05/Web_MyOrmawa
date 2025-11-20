<?php include('Header.php'); ?>
<?php include('../../../Config/ConnectDB.php'); ?>

<?php
function getAccountOrmawa($koneksi) {
    $sql = "SELECT id, full_name, nim, email, password FROM user WHERE level = 2 ORDER BY full_name ASC";
    $result = mysqli_query($koneksi, $sql);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_free_result($result);
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
    return $data;
}
$accounts = getAccountOrmawa($koneksi);
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Akun Ormawa</h1>
        <button class="btn btn-success btn-icon-split" data-bs-toggle="modal" data-bs-target="#tambahAccountModal" onclick="resetAccountForm()">
            <span class="icon text-white-50">
                <i class="fas fa-plus"></i>
            </span>
            <span class="text">Tambah Akun</span>
        </button>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_GET['success']) && $_GET['success'] === 'akun_ditambah'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Akun Ormawa berhasil ditambahkan.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['success']) && $_GET['success'] === 'akun_diperbarui'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Akun Ormawa berhasil diperbarui.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['deleted']) && $_GET['deleted'] === 'akun'): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            Akun Ormawa berhasil dihapus.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <?php
        $messages = [
            'form_kosong' => 'Semua kolom wajib diisi.',
            'email_invalid' => 'Format email tidak valid.',
            'username_duplikat' => 'Username sudah digunakan.',
            'query_gagal' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
            'data_invalid' => 'Data tidak valid.',
            'user_tidak_ada' => 'Akun tidak ditemukan.',
            'id_invalid' => 'ID tidak valid.',
        ];
        $error_key = $_GET['error'];
        $message = $messages[$error_key] ?? 'Terjadi kesalahan tidak dikenal.';
        ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($accounts)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($accounts as $acc): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($acc['nama']); ?></td>
                                    <td><?= htmlspecialchars($acc['nim']); ?></td>
                                    <td><?= htmlspecialchars($acc['username']); ?></td>
                                    <td><?= htmlspecialchars($acc['email']); ?></td>
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
                                        <a href="../../../App/Function/AccountFunction.php?action=delete&id=<?= $acc['id']; ?>"
                                           class="btn btn-danger btn-circle btn-sm"
                                           onclick="return confirm('Yakin hapus akun ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada akun Ormawa.</td>
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