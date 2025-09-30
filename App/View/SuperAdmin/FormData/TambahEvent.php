<!-- Modal Tambah Event -->
<div class="modal fade" id="tambahEventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Event Baru</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formTambahEvent">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Event <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_event" id="tambahNamaEvent" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kategori <span class="text-danger">*</span></label>
                                <select class="form-control" name="kategori" id="tambahKategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Art">Art</option>
                                    <option value="Music">Music</option>
                                    <option value="Workshop">Workshop</option>
                                    <option value="Festival">Festival</option>
                                    <option value="Education">Education</option>
                                    <option value="Sports">Sports</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="tanggal_mulai" id="tambahTanggalMulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Selesai</label>
                                <input type="datetime-local" class="form-control" name="tanggal_selesai" id="tambahTanggalSelesai">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lokasi" id="tambahLokasi" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="tambahDeskripsi" rows="4"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Harga Tiket</label>
                                <input type="number" class="form-control" name="harga" id="tambahHarga" placeholder="0">
                                <small class="text-muted">Kosongkan jika gratis</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kapasitas</label>
                                <input type="number" class="form-control" name="kapasitas" id="tambahKapasitas">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Upload Gambar Event</label>
                        <input type="file" class="form-control-file" name="gambar" id="tambahGambar" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG, max 2MB</small>
                        <div id="previewTambah" class="mt-2"></div>
                    </div>
                    
                    
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanEvent()">
                    <i class="fas fa-save"></i> Simpan Event
                </button>
            </div>
        </div>
    </div>
</div>