<?php include('../SuperAdmin/Header.php'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-alt me-2"></i>Arsip Dokumen Anggota Ormawa</h2>
        <!-- Tombol Tambah Dokumen membuka modal -->
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-plus me-1"></i> Tambah Dokumen
        </button>
    </div>

    <!-- Filter & Search -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" placeholder="Cari nama anggota atau dokumen...">
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-select">
                <option selected>Semua Jenis Dokumen</option>
                <option>Proposal</option>
                <option>Laporan</option>
                <option>Surat</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select">
                <option selected>Semua Anggota</option>
                <option>Ahmad Fauzi</option>
                <option>Siti Nurhaliza</option>
                <option>Budi Santoso</option>
            </select>
        </div>
    </div>

    <!-- Tabel Dokumen -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nama Anggota</th>
                            <th scope="col">Jenis Dokumen</th>
                            <th scope="col">Nama Dokumen</th>
                            <th scope="col">Tanggal Upload</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">1</th>
                            <td>Ahmad Fauzi</td>
                            <td><span class="badge bg-primary">Proposal</span></td>
                            <td>Proposal Kegiatan Bakti Sosial</td>
                            <td>12 Apr 2024</td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary" title="Lihat"><i class="fas fa-eye"></i></a>
                                <a href="#" class="btn btn-sm btn-outline-success" title="Unduh"><i class="fas fa-download"></i></a>
                                <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <!-- Data lainnya tetap sama -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Pagination dokumen">
                <ul class="pagination justify-content-end mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">Sebelumnya</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">Berikutnya</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modal Upload Dokumen -->


<?php include('../SuperAdmin/Footer.php'); ?>