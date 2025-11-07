<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalFormLabel">Form Anggota</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="anggotaForm" action="../../../Function/AnggotaFunction.php" method="POST">
                    <input type="hidden" id="formAction" name="action" value="add">
                    <input type="hidden" id="editId" name="id" value="">

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Anggota</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>

                    <div class="mb-3">
                        <label for="departemen" class="form-label">Departemen</label>
                        <input type="text" class="form-control" id="departemen" name="departemen" required>
                    </div>
                    <div class="mb-3">
                        <label for="jabatan" class="form-label">Jabatan</label>
                        <input type="text" class="form-control" id="jabatan" name="jabatan" required>
                    </div>

                    <div class="mb-3">
                        <label for="no_telpon" class="form-label">No Telepon</label>
                        <input type="text" class="form-control" id="no_telpon" name="no_telpon" required>
                    </div>

                    <div class="mb-3">
                        <label for="prodi" class="form-label">Prodi</label>
                        <input type="text" class="form-control" id="prodi" name="prodi" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetForm()">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitForm()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('anggotaForm').reset();
    document.getElementById('formAction').value = 'add';
    document.getElementById('editId').value = '';
    document.getElementById('modalFormLabel').textContent = 'Form Anggota';
}

function submitForm() {
    document.getElementById('anggotaForm').submit();
}

// Fungsi edit (bisa dipakai nanti)
function editAnggota(id, nama, departemen, no_telpon, prodi) {
    document.getElementById('formAction').value = 'edit';
    document.getElementById('editId').value = id;
    document.getElementById('nama').value = nama;
    document.getElementById('departemen').value = departemen;
    document.getElementById('no_telpon').value = no_telpon;
    document.getElementById('prodi').value = prodi;
    document.getElementById('modalFormLabel').textContent = 'Edit Anggota';
    // Buka modal
    const modal = new bootstrap.Modal(document.getElementById('modalForm'));
    modal.show();
}
</script>