    <!-- Modal Tambah/Edit Kegiatan -->
    <div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFormLabel">Tambah Kegiatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../../../Function/KegiatanFunction.php" method="POST">
                    <input type="hidden" name="action" id="form_action" value="add">
                    <input type="hidden" name="id" id="kegiatan_id">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" required>
                        </div>
                        <div class="mb-3">
                            <label for="agenda" class="form-label">Agenda <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="agenda" name="agenda" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="jam_mulai" class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="jam_selesai" class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                            </div>
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

    <!-- JS untuk reset & edit -->
    <script>
    function resetForm() {
        document.getElementById('form_action').value = 'add';
        document.getElementById('kegiatan_id').value = '';
        document.getElementById('nama_kegiatan').value = '';
        document.getElementById('agenda').value = '';
        document.getElementById('tanggal').value = '';
        document.getElementById('jam_mulai').value = '';
        document.getElementById('jam_selesai').value = '';
        document.getElementById('lokasi').value = '';
        document.querySelector('#modalFormLabel').textContent = 'Tambah Kegiatan';
    }

    function editKegiatan(id, nama, agenda, tanggal, jam_mulai, jam_selesai, lokasi) {
        document.getElementById('form_action').value = 'edit';
        document.getElementById('kegiatan_id').value = id;
        document.getElementById('nama_kegiatan').value = nama;
        document.getElementById('agenda').value = agenda;
        document.getElementById('tanggal').value = tanggal;
        document.getElementById('jam_mulai').value = jam_mulai;
        document.getElementById('jam_selesai').value = jam_selesai;
        document.getElementById('lokasi').value = lokasi;
        document.querySelector('#modalFormLabel').textContent = 'Edit Kegiatan';
    }
    </script>