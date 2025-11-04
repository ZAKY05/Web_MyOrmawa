<?php 
include('../SuperAdmin/Header.php'); 
include('../../../Config/ConnectDB.php'); 
include('../FormData/TambahAccount.php');
?>

<div class="container mt-4">
    <h2>Daftar Akun Ormawa</h2>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div class="col-md-3">
            <label for="filterRole" class="form-label">Filter Berdasarkan Level</label>
            <select id="filterRole" class="form-select">
                <option value="">Semua</option>
                <option value="1">Super Admin</option>
                <option value="2" selected>Ormawa</option>
            </select>
        </div>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#tambahAccountModal">
                <i class="fas fa-plus fa-sm"></i> Tambah Account
            </button>
        </div>
    </div>

    <!-- Tabel Akun -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="accountTableBody">
                <?php
                $no = 1;
                // ambil akun dengan level = 2
                $query = mysqli_query($koneksi, "SELECT * FROM user WHERE level = 2");

                if ($query && mysqli_num_rows($query) > 0) {
                    while ($row = mysqli_fetch_assoc($query)) {
                ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nama']); ?></td>
                            <td><?= htmlspecialchars($row['nim']); ?></td>
                            <td><?= htmlspecialchars($row['username']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['password']); ?></td>
                            <td>
                                <?php 
                                    if ($row['level'] == 1) echo "Super Admin";
                                    elseif ($row['level'] == 2) echo "Ormawa";
                                    else echo "Tidak diketahui";
                                ?>
                            </td>
                            <td>
                                <a href="edit_account.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="hapus_account.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                <?php 
                    }
                } else {
                    echo '<tr><td colspan="8" class="text-center">Belum ada akun Ormawa</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Filter level di client-side
    document.getElementById('filterRole').addEventListener('change', function() {
        let selected = this.value;
        let rows = document.querySelectorAll('#accountTableBody tr');

        rows.forEach(row => {
            let levelText = row.querySelector('td:nth-child(7)').textContent.trim();
            if (selected === "" || levelText.includes(selected === "1" ? "Super" : "Ormawa")) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

<?php include('../SuperAdmin/Footer.php'); ?>