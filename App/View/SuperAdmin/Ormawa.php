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
                    <thead class="table-dark">
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
<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalFormLabel">Form Ormawa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama_ormawa" class="form-label">Nama Ormawa</label>
                            <input type="text" class="form-control" id="nama_ormawa" placeholder="Contoh: BEM Fakultas Teknik">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ketua" class="form-label">Ketua</label>
                            <input type="text" class="form-control" id="ketua" placeholder="Nama Ketua">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="email@univ.ac.id">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="no_telp" class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" id="no_telp" placeholder="081234567890">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="fakultas" class="form-label">Fakultas</label>
                        <select class="form-select" id="fakultas">
                            <option selected>Pilih Fakultas</option>
                            <option>Teknik</option>
                            <option>Ekonomi</option>
                            <option>Hukum</option>
                            <option>Kedokteran</option>
                            <option>Ilmu Sosial & Ilmu Politik</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control" id="deskripsi" rows="3" placeholder="Deskripsi singkat tentang ormawa ini..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>

<?php include('../SuperAdmin/Footer.php') ?>