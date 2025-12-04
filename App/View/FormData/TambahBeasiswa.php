<!-- Modal Tambah Beasiswa -->
<div class="modal fade" id="tambahBeasiswaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Tambah Beasiswa Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambahBeasiswa" action="../../../Function/BeasiswaFunction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <div class="modal-body">
                    <?php
                    $user_level = (int)($_SESSION['user_level'] ?? 0);
                    ?>

                    <?php if ($user_level === 1): ?>
                        <!-- SuperAdmin: Harus pilih Ormawa -->
                        <div class="mb-3">
                            <label class="form-label">Ormawa Penyelenggara <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_ormawa" required>
                                <option value="">— Pilih Ormawa —</option>
                                <?php
                                $ormawa_list = mysqli_query($koneksi, "SELECT id, nama_ormawa FROM ormawa ORDER BY nama_ormawa");
                                while ($o = mysqli_fetch_assoc($ormawa_list)):
                                ?>
                                    <option value="<?= (int)$o['id']; ?>"><?= htmlspecialchars($o['nama_ormawa']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                    <?php elseif ($user_level === 2): ?>
                        <!-- Admin Organisasi: Otomatis isi dari session -->
                        <?php
                        $ormawa_id = (int)($_SESSION['ormawa_id'] ?? 0);
                        $ormawa_nama = htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ormawa Anda');
                        ?>

                        <?php if ($ormawa_id <= 0): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Anda tidak terdaftar di Ormawa manapun. Silakan hubungi SuperAdmin.
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const submitBtn = document.querySelector('#formTambahBeasiswa button[type="submit"]');
                                    if (submitBtn) submitBtn.disabled = true;
                                });
                            </script>
                        <?php else: ?>
                            <input type="hidden" name="id_ormawa" value="<?= $ormawa_id; ?>">
                            <div class="mb-3">
                                <label class="form-label">Ormawa Penyelenggara</label>
                                <input type="text" class="form-control" value="<?= $ormawa_nama; ?>" readonly>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-lock me-1"></i>
                            Anda tidak memiliki izin untuk menambah beasiswa.
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const submitBtn = document.querySelector('#formTambahBeasiswa button[type="submit"]');
                                if (submitBtn) submitBtn.disabled = true;
                            });
                        </script>
                    <?php endif; ?>

                    <!-- Nama Beasiswa -->
                    <div class="mb-3">
                        <label class="form-label">Nama Beasiswa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_beasiswa" required 
                               placeholder="Contoh: Beasiswa Prestasi Akademik" maxlength="200">
                    </div>

                    <!-- Penyelenggara -->
                    <div class="mb-3">
                        <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="penyelenggara" required 
                               placeholder="Contoh: BEM Universitas / Kemendikbud" maxlength="150">
                    </div>

                    <!-- Deadline -->
                    <div class="mb-3">
                        <label class="form-label">Deadline <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="deadline" required>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="deskripsi" rows="4" required 
                                  placeholder="Jelaskan syarat, benefit, cara daftar, dll."></textarea>
                    </div>

                    <!-- Gambar -->
                    <div class="mb-3">
                        <label class="form-label">Poster</label>
                        <input type="file" class="form-control" name="gambar" accept="image/jpeg,image/png,image/jpg">
                        <div class="form-text text-muted">Format: JPG, PNG (maks 2MB)</div>
                    </div>

                    <!-- File Panduan -->
                    <div class="mb-3">
                        <label class="form-label">Buku Panduan</label>
                        <input type="file" class="form-control" name="file_panduan" accept=".pdf,.doc,.docx">
                        <div class="form-text text-muted">Format: PDF (maks 10MB)</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Simpan Beasiswa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset form saat modal ditutup -->
<script>
    document.getElementById('tambahBeasiswaModal')?.addEventListener('hidden.bs.modal', function () {
        const form = document.getElementById('formTambahBeasiswa');
        if (form) {
            form.reset();
            const fileInputs = form.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => input.value = '');
        }
    });
</script>