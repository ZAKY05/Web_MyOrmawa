<!-- Modal Upload Dokumen -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="../../../Function/DocumentFunction.php" method="POST" enctype="multipart/form-data">
                <!-- Tambahkan ini! -->
                <input type="hidden" name="action" value="add">
                
                <!-- Ambil id_ormawa dan id_user dari session (sesuaikan) -->
                <input type="hidden" name="id_ormawa" value="<?= $_SESSION['id_ormawa'] ?? 1 ?>">
                <input type="hidden" name="id_user" value="<?= $_SESSION['id_user'] ?? 1 ?>">

                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">
                        <i class="fas fa-file-upload me-2"></i>Tambah Dokumen Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_dokumen" class="form-label">Nama Dokumen</label>
                        <input type="text" class="form-control" id="nama_dokumen" name="nama_dokumen" required>
                    </div>

                    <div class="mb-3">
                        <label for="jenis_dokumen" class="form-label">Jenis Dokumen</label>
                        <select class="form-select" id="jenis_dokumen" name="jenis_dokumen" required>
                            <option value="">Pilih Jenis Dokumen</option>
                            <option value="Proposal">Proposal</option>
                            <option value="SPJ">SPJ</option>
                            <option value="LPJ">LPJ</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="file_dokumen" class="form-label">File Dokumen</label>
                        <input class="form-control" type="file" id="file_dokumen" name="file_dokumen" 
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                        <div class="form-text">Format: PDF, DOC/DOCX, XLS/XLSX, JPG, PNG (max 10MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Simpan Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>