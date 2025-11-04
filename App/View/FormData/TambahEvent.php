<?php
// File ini berisi modal untuk Tambah, Edit, dan Detail Event
// Harus disimpan dengan ekstensi .php agar diproses oleh server PHP
?>

<!-- Modal Tambah Event -->
<div class="modal fade" id="tambahEventModal" tabindex="-1" role="dialog" aria-labelledby="tambahEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahEventModalLabel">Tambah Event Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formTambahEvent" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tambahNamaEvent">Nama Event <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_event" id="tambahNamaEvent" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tambahKategori">Kategori <span class="text-danger">*</span></label>
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
                                <label for="tambahTanggalMulai">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="tgl_mulai" id="tambahTanggalMulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tambahTanggalSelesai">Tanggal Selesai</label>
                                <input type="datetime-local" class="form-control" name="tgl_selesai" id="tambahTanggalSelesai">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tambahLokasi">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lokasi" id="tambahLokasi" required>
                    </div>

                    <div class="form-group">
                        <label for="tambahDeskripsi">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="tambahDeskripsi" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="tambahGambar">Upload Gambar Event</label>
                        <input type="file" class="form-control-file" name="gambar" id="tambahGambar" accept="image/*">
                        <small class="form-text text-muted">Format: JPG, PNG, maksimal 2MB</small>
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

<!-- Modal Edit Event -->
<div class="modal fade" id="editEventModal" tabindex="-1" role="dialog" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditEvent" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="editId">
                    <input type="hidden" name="gambar_lama" id="editGambarLama">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editNamaEvent">Nama Event <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_event" id="editNamaEvent" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editKategori">Kategori <span class="text-danger">*</span></label>
                                <select class="form-control" name="kategori" id="editKategori" required>
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
                                <label for="editTanggalMulai">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="tgl_mulai" id="editTanggalMulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editTanggalSelesai">Tanggal Selesai</label>
                                <input type="datetime-local" class="form-control" name="tgl_selesai" id="editTanggalSelesai">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editLokasi">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lokasi" id="editLokasi" required>
                    </div>

                    <div class="form-group">
                        <label for="editDeskripsi">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="editDeskripsi" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="editGambar">Upload Gambar Baru (Opsional)</label>
                        <input type="file" class="form-control-file" name="gambar" id="editGambar" accept="image/*">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti gambar</small>
                        <div id="previewEdit" class="mt-2"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="updateEvent()">
                    <i class="fas fa-save"></i> Update Event
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Event -->
<div class="modal fade" id="detailEventModal" tabindex="-1" role="dialog" aria-labelledby="detailEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailEventModalLabel">Detail Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailEventContent">
                <!-- Content akan diisi oleh JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>