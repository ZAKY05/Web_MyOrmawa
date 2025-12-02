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

    <!-- 🔍 Search Box -->
    <div class="mb-3">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Cari dokumen atau ormawa...">
        </div>
        <small class="text-muted">Cari berdasarkan nama dokumen atau nama ormawa.</small>
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
                    <tbody id="documentTableBody">
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td class="search-col"><?= htmlspecialchars($row['nama_dokumen']) ?></td>
                                    <td class="search-col"><?= htmlspecialchars($row['nama_ormawa']) ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal_upload'])) ?></td>
                                    <td>
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

                <!-- Optional: Pesan saat tidak ada hasil -->
                <div id="noResultsMessage" class="text-center text-muted d-none py-3">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p>Tidak ada dokumen yang sesuai dengan kata kunci.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('Footer.php'); ?>
<?php mysqli_close($koneksi); ?>

<!-- ✅ Script Pencarian Real-Time -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('documentTableBody');
    const noResultsMsg = document.getElementById('noResultsMessage');
    const rows = Array.from(tableBody.querySelectorAll('tr'));

    // Sembunyikan pesan "tidak ada hasil" saat ada data awal
    if (rows.length > 0 && !rows[0].querySelector('td[colspan]')) {
        noResultsMsg.classList.add('d-none');
    }

    searchInput.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
            // Abaikan baris "tidak ada dokumen"
            if (row.querySelector('td[colspan]')) {
                row.style.display = (query === '') ? '' : 'none';
                return;
            }

            // Ambil teks dari kolom yang bisa dicari: Nama Dokumen & Ormawa (kelas 'search-col')
            const searchableCells = row.querySelectorAll('.search-col');
            const text = Array.from(searchableCells)
                .map(cell => cell.textContent.toLowerCase())
                .join(' ');

            if (query === '' || text.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Tampilkan/sembunyikan pesan "tidak ada hasil"
        if (visibleCount === 0 && query !== '') {
            noResultsMsg.classList.remove('d-none');
        } else {
            noResultsMsg.classList.add('d-none');
        }
    });
});
</script>