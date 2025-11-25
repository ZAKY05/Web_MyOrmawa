<?php
include('Header.php');
include('../../../Config/ConnectDB.php');

// Pastikan session dimulai (biasanya sudah di Header.php, tapi untuk jaga-jaga)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$current_user_id = $_SESSION['user']['id'] ?? 0; 
$current_level   = $_SESSION['user']['level'] ?? 0;


function getAccountMember($koneksi, $id_ormawa) {
    
    $sql = "SELECT id, full_name, nim, email, password, level 
            FROM user 
            WHERE id_ormawa = ? AND level = 3 
            ORDER BY full_name ASC";
            
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("i", $id_ormawa);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    return $data;
}

// Jika Super Admin (Level 1), mungkin ingin lihat semua, tapi jika Ormawa (Level 2), lihat anggotanya saja.
if ($current_level == 2) { // Level 2 = Admin Ormawa
    $accounts = getAccountMember($koneksi, $current_user_id);
} else {
    // Fallback jika logic lain (misal Super Admin lihat semua)
    $accounts = []; 
}
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Anggota Ormawa</h1>
        <button class="btn btn-success btn-icon-split" data-bs-toggle="modal" data-bs-target="#tambahAccountModal" onclick="resetAccountForm()">
            <span class="icon text-white-50">
                <i class="fas fa-plus"></i>
            </span>
            <span class="text">Tambah Anggota</span>
        </button>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Anggota Anda</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIM</th>
                            <th>Email</th>
                            <th>Password (Encrypted)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($accounts)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($accounts as $acc): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($acc['full_name']); ?></td>
                                    <td><?= htmlspecialchars($acc['nim']); ?></td>
                                    <td><?= htmlspecialchars($acc['email']); ?></td>
                                    <td style="word-break: break-all; font-size: 0.8em;">
                                        <?= htmlspecialchars(substr($acc['password'], 0, 10)) . '...'; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-warning btn-circle btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#tambahAccountModal"
                                            onclick='editAccount(
                                                <?= (int)$acc['id'] ?>,
                                                <?= json_encode(htmlspecialchars($acc['full_name'])) ?>,
                                                <?= json_encode(htmlspecialchars($acc['nim'])) ?>,
                                                <?= json_encode(htmlspecialchars($acc['email'])) ?>
                                            )'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="../../../Function/AccountFunction.php?action=delete&id=<?= $acc['id']; ?>"
                                           class="btn btn-danger btn-circle btn-sm"
                                           onclick="return confirm('Yakin hapus anggota ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data anggota untuk Ormawa ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('../FormData/TambahAccount.php'); ?>
<?php include('Footer.php'); ?>
<?php mysqli_close($koneksi); ?>