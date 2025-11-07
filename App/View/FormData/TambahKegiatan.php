<!-- Modal Tambah/Edit Kegiatan -->
<div class="modal fade" id="tambahKegiatanModal" tabindex="-1" aria-labelledby="tambahKegiatanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="formKegiatanTitle">Tambah Kegiatan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="kegiatanForm" action="../../../Function/KegiatanFunction.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="editId" value="">

                    <div class="mb-3">
                        <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" required>
                    </div>
                    <div class="mb-3">
                        <label for="agenda" class="form-label">Agenda <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="agenda" name="agenda" required>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                    </div>
                    <div class="mb-3">
                        <label for="jam_mulai" class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                    </div>
                    <div class="mb-3">
                        <label for="jam_selesai" class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                    </div>
                    <div class="mb-3">
                        <label for="lokasi" class="form-label">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lokasi" name="lokasi" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetFormKegiatan() {
    document.getElementById('kegiatanForm').reset();
    document.getElementById('formAction').value = 'add';
    document.getElementById('editId').value = '';
    document.getElementById('formKegiatanTitle').textContent = 'Tambah Kegiatan';
}

function editKegiatan(id, nama_kegiatan, agenda, tanggal, jam_mulai, jam_selesai, lokasi) {
    document.getElementById('formAction').value = 'edit';
    document.getElementById('editId').value = id;
    document.getElementById('nama_kegiatan').value = nama_kegiatan;
    document.getElementById('agenda').value = agenda;
    document.getElementById('tanggal').value = tanggal;
    document.getElementById('jam_mulai').value = jam_mulai;
    document.getElementById('jam_selesai').value = jam_selesai;
    document.getElementById('lokasi').value = lokasi;
    document.getElementById('formKegiatanTitle').textContent = 'Edit Kegiatan';
}
</script>