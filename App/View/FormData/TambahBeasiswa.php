<!-- Modal Tambah Kompetisi -->
<div class="modal fade" id="tambahBeasiswaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Beasiswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahBeasiswa">
                    <div class="mb-3">
                        <label>Nama Beasiswa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_beasiswa" required placeholder="Hackathon AI Innovation">
                    </div>
                    <div class="mb-3">
                        <label>Penyelenggara <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="penyelenggara" required placeholder="HMJ Teknologi Informasi">
                    </div>
                    <div class="mb-3">
                        <label>Periode (Opsional)</label>
                        <input type="text" class="form-control" name="periode" placeholder="10-12 Januari 2026">
                    </div>
                    <div class="mb-3">
                        <label>Deadline (Opsional)</label>
                        <input type="date" class="form-control" name="deadline">
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="deskripsi" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Gambar (Opsional)</label>
                        <input type="file" class="form-control-file" name="gambar" accept="image/*">
                        <small class="form-text text-muted">JPG, JPEG, PNG</small>
                    </div>
                    <div class="mb-3">
                        <label>Buku Panduan (Opsional)</label>
                        <input type="file" class="form-control-file" name="file_panduan" accept=".pdf,.doc,.docx">
                        <small class="form-text text-muted">PDF, DOC, DOCX</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanBeasiswa()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
function simpanBeasiswa() {
    const formData = new FormData(document.getElementById('formTambahBeasiswa'));
    
    fetch('../../../Function/BeasiswaFunction.php?action=add', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ Berhasil!');
            location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(err => alert('❌ Error: ' + err.message));
}
</script>