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
                    <div class="mb-3">
                        <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_lokasi" id="nama_lokasi" required 
                            >
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="lat" id="lat" required 
                             pattern="-?[0-9]+(\.[0-9]+)?">
                            <small class="text-muted">Contoh: -7.123456</small>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="lng" id="lng" required 
                                 pattern="-?[0-9]+(\.[0-9]+)?">
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