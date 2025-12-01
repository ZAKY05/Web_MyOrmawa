<!-- Modal Tambah Beasiswa -->
<div class="modal fade" id="tambahBeasiswaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-graduation-cap me-2"></i> Tambah Beasiswa Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formBeasiswa" action="../../../Function/BeasiswaFunction.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Beasiswa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_beasiswa" required 
                               placeholder="Contoh: Beasiswa Prestasi">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="penyelenggara" required 
                               placeholder="Contoh: BEM Universitas">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Periode (Opsional)</label>
                            <input type="text" class="form-control" name="periode" 
                                   placeholder="Contoh: Semester Ganjil 2025">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Deadline (Opsional)</label>
                            <input type="date" class="form-control" name="deadline">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="deskripsi" rows="3" required 
                                  placeholder="Deskripsi beasiswa..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar (Opsional)</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                        <small class="form-text text-muted">JPG, PNG (≤2MB)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Panduan (Opsional)</label>
                        <input type="file" class="form-control" name="file_panduan" accept=".pdf,.doc,.docx">
                        <small class="form-text text-muted">PDF, DOC (≤10MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>