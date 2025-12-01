<!-- Modal Upload Dokumen -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="../../../Function/DocumentFunction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id_ormawa" value="<?= (int)($_SESSION['ormawa_id'] ?? 0) ?>">
                <input type="hidden" name="id_user" value="<?= (int)($_SESSION['user_id'] ?? 0) ?>">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-upload me-2"></i>
                        Tambah Dokumen untuk <?= htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ormawa') ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Dokumen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_dokumen" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Dokumen <span class="text-danger">*</span></label>
                        <select class="form-select" name="jenis_dokumen" required>
                            <option value="">Pilih Jenis</option>
                            <option value="Proposal">Proposal</option>
                            <option value="SPJ">SPJ (Surat Pertanggungjawaban)</option>
                            <option value="LPJ">LPJ (Laporan Pertanggungjawaban)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Dokumen <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="file_dokumen" 
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt" required>
                        <div class="form-text text-muted">
                            Format: PDF, DOC/X, XLS/X, JPG, PNG, TXT | Maks: 10 MB
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Simpan Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Dokumen -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="../../../Function/DocumentFunction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">

                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i> Edit Dokumen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Dokumen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_dokumen" id="edit_nama" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Dokumen <span class="text-danger">*</span></label>
                        <select class="form-select" name="jenis_dokumen" id="edit_jenis" required>
                            <option value="Proposal">Proposal</option>
                            <option value="SPJ">SPJ</option>
                            <option value="LPJ">LPJ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ganti File (Opsional)</label>
                        <input type="file" class="form-control" name="file_dokumen"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt">
                        <div class="form-text text-muted">
                            Biarkan kosong jika tidak ingin mengganti file.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>