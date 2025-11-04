<?php include('Header.php'); ?>

<div class="container mt-4">
    <h2>Manajemen Kompetisi</h2>

    <!-- Form Tambah Kompetisi -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5>Tambah Kompetisi Baru</h5>
        </div>
        <div class="card-body">
            <form id="formTambahKompetisi">
                <div class="mb-3">
                    <label for="namaKompetisi" class="form-label">Nama Kompetisi</label>
                    <input type="text" class="form-control" id="namaKompetisi" placeholder="Masukkan nama kompetisi" required>
                </div>
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" rows="3" placeholder="Deskripsi kompetisi" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="tanggalMulai" class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="tanggalMulai" required>
                </div>
                <div class="mb-3">
                    <label for="tanggalAkhir" class="form-label">Tanggal Akhir Pendaftaran</label>
                    <input type="date" class="form-control" id="tanggalAkhir" required>
                </div>
                <div class="mb-3">
                    <label for="linkPendaftaran" class="form-label">Link Pendaftaran</label>
                    <input type="url" class="form-control" id="linkPendaftaran" placeholder="https://example.com/daftar">
                </div>
                <button type="submit" class="btn btn-success">Tambah Kompetisi</button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Kompetisi -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5>Daftar Kompetisi</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="">
                    <tr>
                        <th>No</th>
                        <th>Nama Kompetisi</th>
                        <th>Deskripsi</th>
                        <th>Mulai</th>
                        <th>Batas Akhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="kompetisiTableBody">
                    <!-- Contoh data statis -->
                    <tr>
                        <td>1</td>
                        <td>Kompetisi Inovasi Mahasiswa 2025</td>
                        <td>Kompetisi inovasi untuk mahasiswa seluruh Indonesia.</td>
                        <td>2025-01-15</td>
                        <td>2024-12-30</td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning">Edit</button>
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Lomba Debat Nasional</td>
                        <td>Lomba debat antar universitas se-Indonesia.</td>
                        <td>2025-02-01</td>
                        <td>2025-01-10</td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning">Edit</button>
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('formTambahKompetisi').addEventListener('submit', function(e) {
    e.preventDefault();

    // Ambil data dari form
    const nama = document.getElementById('namaKompetisi').value;
    const deskripsi = document.getElementById('deskripsi').value;
    const mulai = document.getElementById('tanggalMulai').value;
    const akhir = document.getElementById('tanggalAkhir').value;
    const link = document.getElementById('linkPendaftaran').value;

    // Buat baris baru di tabel
    const tbody = document.getElementById('kompetisiTableBody');
    const newRow = `
        <tr>
            <td>${tbody.children.length + 1}</td>
            <td>${nama}</td>
            <td>${deskripsi}</td>
            <td>${mulai}</td>
            <td>${akhir}</td>
            <td>
                <button class="btn btn-sm btn-outline-warning">Edit</button>
                <button class="btn btn-sm btn-outline-danger">Hapus</button>
            </td>
        </tr>
    `;

    tbody.insertAdjacentHTML('beforeend', newRow);

    // Reset form
    this.reset();
});
</script>

<?php include('Footer.php'); ?>