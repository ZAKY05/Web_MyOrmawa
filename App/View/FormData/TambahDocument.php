<!-- Modal Tambah Dokumen -->
<div class="modal fade" id="tambahDokumenModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Dokumen Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" id="formTambahDokumen">
                <div class="modal-body">
                    <input type="hidden" name="action" value="tambah">

                    <?php
                    $admin_ormawa_info = getAdminOrmawaInfo($koneksi);
                    $is_admin_organisasi = (isset($_SESSION['user_level']) && $_SESSION['user_level'] === 2);
                    ?>

                    <?php if ($is_admin_organisasi && $admin_ormawa_info): ?>
                        <input type="hidden" name="id_ormawa" value="<?php echo (int)$admin_ormawa_info['id']; ?>">
                        <div class="form-group">
                            <label>Ormawa</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin_ormawa_info['nama_ormawa']); ?>" readonly>
                            <small class="form-text text-muted">Dokumen ini akan tersimpan untuk organisasi Anda.</small>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="id_ormawa">Pilih Ormawa <span class="text-danger">*</span></label>
                            <select class="form-control" id="id_ormawa" name="id_ormawa" required>
                                <option value="">Pilih Ormawa</option>
                                <?php foreach ($all_ormawa_list as $ormawa): ?>
                                    <option value="<?php echo (int)$ormawa['id']; ?>">
                                        <?php echo htmlspecialchars($ormawa['nama_ormawa']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Pilih organisasi pemilik dokumen ini.</small>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="nama_dokumen">Nama Dokumen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_dokumen" name="nama_dokumen" placeholder="Contoh: Proposal Kegiatan Seminar Nasional 2025" required maxlength="255">
                        <small class="form-text text-muted">Berikan nama yang jelas dan deskriptif.</small>
                    </div>

                    <div class="form-group">
                        <label for="jenis_dokumen">Jenis Dokumen <span class="text-danger">*</span></label>
                        <select class="form-control" id="jenis_dokumen" name="jenis_dokumen" required>
                            <option value="">Pilih Jenis Dokumen</option>
                            <option value="Proposal">Proposal</option>
                            <option value="SPJ">SPJ (Surat Pertanggungjawaban)</option>
                            <option value="LPJ">LPJ (Laporan Pertanggungjawaban)</option>
                        </select>
                        <small class="form-text text-muted">Tentukan kategori dokumen untuk memudahkan pencarian.</small>
                    </div>

                    <div class="form-group">
                        <label for="file_dokumen">Upload File Dokumen <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" id="file_dokumen" name="file_dokumen" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                        <small class="form-text text-muted">
                            <strong>Format yang didukung:</strong> PDF, DOC, DOCX, XLS, XLSX<br>
                            <strong>Ukuran maksimal:</strong> 10 MB
                        </small>
                    </div>

                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Catatan:</strong> Pastikan file yang Anda upload sudah benar karena dokumen ini akan tersimpan dalam sistem dan dapat diakses oleh admin terkait.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validasi file saat dipilih
document.getElementById('file_dokumen')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Cek ukuran file (max 10MB)
        if (file.size > 10000000) {
            alert('Ukuran file terlalu besar! Maksimal 10MB.');
            this.value = '';
            return;
        }
        
        // Cek tipe file
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung! Gunakan PDF, DOC, DOCX, XLS, atau XLSX.');
            this.value = '';
            return;
        }
        
        // Tampilkan info file
        const fileInfo = document.createElement('div');
        fileInfo.className = 'alert alert-success mt-2';
        fileInfo.innerHTML = '<i class="fas fa-check-circle"></i> File dipilih: <strong>' + file.name + '</strong> (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        
        // Hapus info sebelumnya jika ada
        const oldInfo = this.parentElement.querySelector('.alert-success');
        if (oldInfo) oldInfo.remove();
        
        this.parentElement.appendChild(fileInfo);
    }
});

// Reset form saat modal ditutup
$('#tambahDokumenModal').on('hidden.bs.modal', function () {
    $(this).find('form')[0].reset();
    $(this).find('.alert-success').remove();
});
</script>