<?php
include('Header.php');
include('../../../Config/ConnectDB.php');

// Ambil semua dokumen + nama ormawa (tanpa nama user)
$sql = "
    SELECT 
        d.id,
        d.nama_dokumen,
        d.tanggal_upload,
        d.file_path,
        o.nama_ormawa
    FROM dokumen d
    INNER JOIN ormawa o ON d.id_ormawa = o.id
    ORDER BY d.tanggal_upload DESC
";
$result = mysqli_query($koneksi, $sql);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-alt me-2"></i>Arsip Dokumen Semua Ormawa</h2>
    </div>

    <!-- Tabel Dokumen -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="documentTable">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nama Dokumen</th>
                            <th scope="col">Ormawa</th>
                            <th scope="col">Tanggal Upload</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama_dokumen']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_ormawa']) ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal_upload'])) ?></td>
                                    <td>
                                        <!-- Unduh -->
                                        <a href="../../../uploads/dokumen/<?= urlencode($row['file_path']) ?>" 
                                           class="btn btn-sm btn-success" 
                                           title="Unduh dokumen"
                                           target="_blank">
                                            <i class="fas fa-download"></i> Unduh
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada dokumen.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('Footer.php'); ?>
<?php mysqli_close($koneksi); ?>