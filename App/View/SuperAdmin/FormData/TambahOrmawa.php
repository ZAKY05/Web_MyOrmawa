<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalFormLabel">Form Ormawa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="ormawaForm" action="../../../Function/OrmawaFunction.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="formAction" name="action" value="add">
                    <input type="hidden" id="editId" name="id" value="">

                    <div class="mb-3">
                        <label for="nama_ormawa" class="form-label">Nama Ormawa</label>
                        <input type="text" class="form-control" id="nama_ormawa" name="nama_ormawa" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo (Opsional)</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG, GIF. Maks: 2MB.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitForm()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('ormawaForm').reset();
    document.getElementById('formAction').value = 'add';
    document.getElementById('editId').value = '';
    document.getElementById('modalFormLabel').textContent = 'Tambah Ormawa';
}

function editOrmawa(id, nama, deskripsi, logo) {
    document.getElementById('formAction').value = 'edit';
    document.getElementById('editId').value = id;
    document.getElementById('nama_ormawa').value = nama;
    document.getElementById('deskripsi').value = deskripsi;
    document.getElementById('modalFormLabel').textContent = 'Edit Ormawa';
    // Logo tidak diisi ke input file, hanya untuk info
}

function submitForm() {
    document.getElementById('ormawaForm').submit();
}
</script>