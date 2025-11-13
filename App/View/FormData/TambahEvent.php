<!-- Modal Tambah Event -->
<div class="modal fade" id="tambahEventModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
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
                    // Jika admin organisasi (level 2), sembunyikan dropdown dan gunakan hidden input
                    if (isset($_SESSION['user_level']) && $_SESSION['user_level'] == 2):
                    ?>
                        <input type="hidden" name="ormawa_id" value="<?php echo $_SESSION['ormawa_id']; ?>">
                        <div class="form-group">
                            <label>Ormawa Penyelenggara</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['ormawa_nama']); ?>" readonly>
                        </div>
                    <?php else: // Jika SuperAdmin (atau level lain), tampilkan dropdown ?>
                        <div class="form-group">
                            <label for="ormawa_id">Ormawa Penyelenggara</label>
                            <select class="form-control" id="ormawa_id" name="ormawa_id" required>
                                <option value="">Pilih Ormawa</option>
                                <?php
                                foreach ($all_ormawa_list as $ormawa):
                                ?>
                                    <option value="<?php echo $ormawa['id']; ?>"><?php echo htmlspecialchars($ormawa['nama_ormawa']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="nama_event">Nama Event</label>
                        <input type="text" class="form-control" id="nama_event" name="nama_event" required>
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="tgl_mulai">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="tgl_mulai" name="tgl_mulai" required>
                    </div>
                    <div class="form-group">
                        <label for="tgl_selesai">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="tgl_selesai" name="tgl_selesai" required>
                    </div>
                    <div class="form-group">
                        <label for="lokasi">Lokasi</label>
                        <input type="text" class="form-control" id="lokasi" name="lokasi" required>
                    </div>
                    <div class="form-group">
                        <label for="gambar">Gambar Event</label>
                        <input type="file" class="form-control-file" id="gambar" name="gambar" accept="image/*">
                        <small class="form-text text-muted">Jenis file yang diperbolehkan: JPG, JPEG, PNG. Maksimal ukuran: 2MB.</small>
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
