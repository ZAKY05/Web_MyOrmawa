<div class="modal fade" id="lokasiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="lokasiModalTitle">Tambah Lokasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formLokasi">

                    <input type="hidden" name="id" id="lokasi_id">

                    <!-- 🔑 Pilih ORMAWA (hanya untuk SuperAdmin) -->
                    <?php if ($user_level === 1): ?>
                        <div class="mb-3">
                            <label class="form-label">Ormawa Penyelenggara <span class="text-danger">*</span></label>
                            <select class="form-control" name="ormawa_id" id="ormawaSelect" required>
                                <option value="">— Pilih Ormawa —</option>
                                <?php
                                $ormawas = mysqli_query($koneksi, "SELECT id, nama_ormawa FROM ormawa ORDER BY nama_ormawa");
                                while ($o = mysqli_fetch_assoc($ormawas)):
                                ?>
                                    <option value="<?= (int)$o['id']; ?>">
                                        <?= htmlspecialchars($o['nama_ormawa']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                    <?php elseif ($user_level === 2): ?>
                        <?php
                        $ormawa_id = (int)($_SESSION['ormawa_id'] ?? 0);
                        $ormawa_nama = htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ormawa Anda');
                        if ($ormawa_id <= 0):
                            echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-1"></i> Anda tidak terdaftar di ORMawa manapun.</div>';
                        endif;
                        ?>
                        <input type="hidden" name="ormawa_id" value="<?= $ormawa_id; ?>">
                        <div class="mb-3">
                            <label class="form-label">Ormawa Penyelenggara</label>
                            <input type="text" class="form-control" value="<?= $ormawa_nama; ?>" readonly>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-lock me-1"></i> Anda tidak memiliki izin.
                        </div>
                        <script>document.getElementById('btnSimpanLokasi')?.setAttribute('disabled', 'disabled');</script>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_lokasi" id="nama_lokasi" required 
                               placeholder="Contoh: Ruang Rapat HMJ TI">
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="lat" id="lat" required 
                                   pattern="-?[0-9]+(\.[0-9]+)?" placeholder="-7.123456">
                            <small class="text-muted">Contoh: -7.123456</small>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="lng" id="lng" required 
                                   pattern="-?[0-9]+(\.[0-9]+)?" placeholder="112.123456">
                            <small class="text-muted">Contoh: 112.123456</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Radius Default (meter) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="radius_default" id="radius_default" required 
                               value="100" min="10" max="500">
                        <small class="form-text text-muted">
                            Radius toleransi absen (10–500 meter)
                        </small>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="btnSimpanLokasi" onclick="saveLocation()">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>