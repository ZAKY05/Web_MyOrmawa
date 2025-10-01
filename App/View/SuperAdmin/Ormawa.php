<?php include('../SuperAdmin/Header.php') ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Data Organisasi Mahasiswa (Ormawa)</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalForm">
            <i class="fas fa-plus"></i> Tambah Ormawa
        </button>
    </div>

    <!-- Tabel Data Ormawa -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="">
                        <tr>
                            <th>No</th>
                            <th>Nama Ormawa</th>
                            <th>Ketua</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Fakultas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Contoh data statis (nanti diganti dengan loop PHP) -->
                        <tr>
                            <td>1</td>
                            <td>BEM Fakultas Teknik</td>
                            <td>Ahmad Fauzi</td>
                            <td>ahmad.fauzi@univ.ac.id</td>
                            <td>081234567890</td>
                            <td>Teknik</td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Himpunan Mahasiswa Informatika</td>
                            <td>Siti Rahayu</td>
                            <td>siti.rahayu@univ.ac.id</td>
                            <td>082198765432</td>
                            <td>Teknik</td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm">
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
    </div>
</div>

<!-- Modal Form Tambah/Edit Ormawa -->
<?php include('../SuperAdmin/FormData/TambahOrmawa.php');?>

<?php include('../SuperAdmin/Footer.php') ?>