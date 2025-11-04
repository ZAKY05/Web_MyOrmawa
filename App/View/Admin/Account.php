<?php include('Header.php'); ?>

<div class="container mt-4">
    <h2>Daftar Akun Ormawa</h2>

    <!-- Filter Role -->
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="filterRole" class="form-label">Filter Berdasarkan Role</label>
            <select id="filterRole" class="form-select">
                <option value="">Semua Role</option>
                <option value="admin">Admin</option>
                <option value="pengurus">Pengurus</option>
            </select>
        </div>
    </div>

    <!-- Tabel Akun -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Nim</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Level</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="accountTableBody">
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                    <button class="btn btn-sm btn-warning" onclick="return confirm('Yakin hapus data ini?')">
                        <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data ini?')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </td>
                    
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('filterRole').addEventListener('change', function() {
        let selectedRole = this.value;
        let rows = document.querySelectorAll('#accountTableBody tr');

        rows.forEach(row => {
            let roleCell = row.querySelector('td:nth-child(5)').textContent.trim().toLowerCase();

            if (selectedRole === "" || roleCell.includes(selectedRole.toLowerCase())) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

<?php include('Footer.php'); ?>