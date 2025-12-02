<!-- Modal Tambah Event -->
<div class="modal fade" id="tambahEventModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Event Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="tambah">

                    <?php
                    $admin_ormawa_info = getAdminOrmawaInfo($koneksi);
                    $is_admin_organisasi = (isset($_SESSION['user_level']) && $_SESSION['user_level'] === 2);
                    ?>

                    <?php if ($is_admin_organisasi && $admin_ormawa_info): ?>
                        <input type="hidden" name="ormawa_id" value="<?php echo (int)$admin_ormawa_info['id']; ?>">
                        <div class="form-group">
                            <label>Ormawa Penyelenggara</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin_ormawa_info['nama_ormawa']); ?>" readonly>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="ormawa_id">Ormawa Penyelenggara <span class="text-danger">*</span></label>
                            <select class="form-control" id="ormawa_id" name="ormawa_id" required>
                                <option value="">Pilih Ormawa</option>
                                <?php foreach ($all_ormawa_list as $ormawa): ?>
                                    <option value="<?php echo (int)$ormawa['id']; ?>">
                                        <?php echo htmlspecialchars($ormawa['nama_ormawa']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="nama_event">Nama Event <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_event" name="nama_event" placeholder="Contoh: Workshop UI/UX Design" required>
                    </div>

                    <div class="form-group">
                        <label for="kategori">Kategori Event <span class="text-danger">*</span></label>
                        <select class="form-control" id="kategori" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Perayaan">Perayaan</option>
                            <option value="Workshop">Workshop</option>
                            <option value="Seminar">Seminar</option>
                            <option value="Kompetisi">Kompetisi</option>
                            <option value="Festival">Festival</option>
                            <option value="Olahraga">Olahraga</option>
                            <option value="Seni">Seni</option>
                            <option value="Akademik">Akademik</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Jelaskan tentang event ini..." required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tgl_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tgl_mulai" name="tgl_mulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tgl_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tgl_selesai" name="tgl_selesai" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="lokasi">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Contoh: Aula Gedung JTI" required>
                    </div>

                    <div class="form-group">
                        <label for="gambar">Gambar Poster Event (Rasio 3:4)</label>
                        <input type="file" class="form-control-file" id="gambar" name="gambar" accept="image/*">
                        <small class="form-text text-muted">Format: JPG/PNG. Maksimal: 2MB. Rekomendasi: 600x800px</small>
                    </div>

                    <div class="form-group">
                        <label for="buku_panduan">Buku Panduan (PDF)</label>
                        <input type="file" class="form-control-file" id="buku_panduan" name="buku_panduan" accept=".pdf">
                        <small class="form-text text-muted">Format: PDF. Maksimal: 5MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>