<!-- Modal Upload Dokumen -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="../../../Function/DocumentFunction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <!-- Gunakan session yang benar -->
                <input type="hidden" name="id_ormawa" value="<?= $_SESSION['ormawa_id'] ?>">
                <input type="hidden" name="id_user" value="<?= $_SESSION['user_id'] ?>">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-upload me-2"></i>
                        Tambah Dokumen untuk <?php echo htmlspecialchars($_SESSION['ormawa_nama']); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Dokumen</label>
                        <input type="text" class="form-control" name="nama_dokumen" required>
                    </div>
                    <div class="mb-3">
                        <label>Jenis Dokumen</label>
                        <select class="form-select" name="jenis_dokumen" required>
                            <option value="">Pilih Jenis</option>
                            <option value="Proposal">Proposal</option>
                            <option value="SPJ">SPJ</option>
                            <option value="LPJ">LPJ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>File Dokumen</label>
                        <input class="form-control" type="file" name="file_dokumen" 
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                        <div class="form-text">PDF, DOC/X, XLS/X, JPG, PNG (max 10MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>