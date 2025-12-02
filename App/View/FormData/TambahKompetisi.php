<!-- Modal Tambah/Edit Kompetisi -->
<div class="modal fade" id="tambahKompetisiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Tambah Kompetisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formKompetisi" action="../../../Function/KompetisiFunction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="form_action" value="add">
                <input type="hidden" name="id" id="kompetisi_id">

                <div class="modal-body">
                    <?php
                    // ✅ Tentukan level user dari session
                    $user_level = (int)($_SESSION['user_level'] ?? 0);
                    ?>

                    <?php if ($user_level === 1): ?>
                        <!-- SuperAdmin: Harus pilih Ormawa -->
                        <div class="mb-3">
                            <label class="form-label">Ormawa<span class="text-danger">*</span></label>
                            <select class="form-control" name="id_ormawa" required>
                                <option value="">Pilih Ormawa</option>
                                <?php
                                // Ambil semua Ormawa
                                $ormawa_list = mysqli_query($koneksi, "SELECT id, nama_ormawa FROM ormawa ORDER BY nama_ormawa");
                                while ($o = mysqli_fetch_assoc($ormawa_list)):
                                ?>
                                    <option value="<?= (int)$o['id']; ?>">
                                        <?= htmlspecialchars($o['nama_ormawa']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                    <?php elseif ($user_level === 2): ?>
                        <!-- Admin Organisasi: Otomatis dari session -->
                        <?php
                        $ormawa_id = (int)($_SESSION['ormawa_id'] ?? 0);
                        $ormawa_nama = htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ormawa Anda');
                        if ($ormawa_id <= 0):
                            echo '<div class="alert alert-danger">Error: Anda tidak terdaftar di ORMawa manapun.</div>';
                        endif;
                        ?>
                        <input type="hidden" name="id_ormawa" value="<?= $ormawa_id; ?>">
                        <div class="mb-3">
                            <label class="form-label">Ormawa</label>
                            <input type="text" class="form-control" value="<?= $ormawa_nama; ?>" readonly>
                        </div>

                    <?php else: ?>
                        <!-- Fallback: tidak valid -->
                        <div class="alert alert-danger">
                            Anda tidak memiliki izin untuk menambah kompetisi.
                        </div>
                        <script>document.querySelector('#formKompetisi button[type="submit"]').disabled = true;</script>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nama Kompetisi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_kompetisi" id="nama_kompetisi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="penyelenggara" id="penyelenggara" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Periode (Opsional)</label>
                            <input type="text" class="form-control" name="periode" id="periode">
                            <small class="form-text text-muted">Contoh: 15-20 November 2025</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Deadline (Opsional)</label>
                            <input type="date" class="form-control" name="deadline" id="deadline">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar (Opsional)</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                        <small class="form-text text-muted">Format: JPG, PNG (≤2MB)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Buku Panduan (Opsional)</label>
                        <input type="file" class="form-control" name="file_panduan" accept=".pdf,.doc,.docx">
                        <small class="form-text text-muted">Format: PDF, DOC (≤10MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>