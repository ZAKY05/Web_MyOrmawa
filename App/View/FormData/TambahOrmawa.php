<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalFormLabel">Tambah Ormawa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="ormawaForm" action="../../../Function/OrmawaFunction.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="formAction" name="action" value="add">
                    <input type="hidden" id="editId" name="id" value="">

                    <div class="row">
                        <!-- Nama Ormawa -->
                        <div class="col-md-6 mb-3">
                            <label for="nama_ormawa" class="form-label">Nama Ormawa <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_ormawa" name="nama_ormawa" required>
                        </div>

                        <!-- Kategori -->
                        <div class="col-md-6 mb-3">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-control" id="kategori" name="kategori" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Akademik">Akademik</option>
                                <option value="Lembaga">Lembaga</option>
                                <option value="Rohani">Rohani</option>
                                <option value="Minat">Minat</option>
                                <option value="Seni">Seni</option>
                            </select>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>

                    <!-- Visi -->
                    <div class="mb-3">
                        <label for="visi" class="form-label">Visi</label>
                        <textarea class="form-control" id="visi" name="visi" rows="2"></textarea>
                    </div>

                    <!-- Misi -->
                    <div class="mb-3">
                        <label for="misi" class="form-label">Misi</label>
                        <textarea class="form-control" id="misi" name="misi" rows="4" placeholder="Pisahkan setiap misi dengan enter/baris baru"></textarea>
                        <small class="text-muted">Tip: Pisahkan setiap misi dengan baris baru</small>
                    </div>

                    <div class="row">
                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="contoh@ormawa.polije.ac.id">
                        </div>

                        <!-- Contact Person -->
                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" placeholder="Ketua Ormawa - 08123456789">
                        </div>
                    </div>

                    <!-- Logo -->
                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Maksimal: 5MB.</small>
                        <div id="logoPreview" class="mt-2"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="submitForm()">
                    <i class="fas fa-save"></i> Simpan
                </button>
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
    document.getElementById('logoPreview').innerHTML = '';
}

function editOrmawa(id, nama, deskripsi, kategori, visi, misi, email, contact, logo) {
    document.getElementById('formAction').value = 'edit';
    document.getElementById('editId').value = id;
    document.getElementById('nama_ormawa').value = nama;
    document.getElementById('deskripsi').value = deskripsi;
    document.getElementById('kategori').value = kategori;
    document.getElementById('visi').value = visi;
    document.getElementById('misi').value = misi;
    document.getElementById('email').value = email;
    document.getElementById('contact_person').value = contact;
    document.getElementById('modalFormLabel').textContent = 'Edit Ormawa';
    
    // Tampilkan preview logo jika ada
    if (logo) {
        document.getElementById('logoPreview').innerHTML = 
            '<img src="../../../uploads/logos/' + logo + '" alt="Current Logo" style="max-width: 150px; max-height: 150px;" class="border rounded">' +
            '<p class="text-muted small mt-1">Logo saat ini (upload file baru untuk mengganti)</p>';
    } else {
        document.getElementById('logoPreview').innerHTML = '';
    }
}

function submitForm() {
    // Validasi form
    const form = document.getElementById('ormawaForm');
    if (form.checkValidity()) {
        form.submit();
    } else {
        form.reportValidity();
    }
}

// Preview logo saat dipilih
document.getElementById('logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').innerHTML = 
                '<img src="' + e.target.result + '" alt="Preview" style="max-width: 150px; max-height: 150px;" class="border rounded mt-2">' +
                '<p class="text-muted small mt-1">Preview logo baru</p>';
        };
        reader.readAsDataURL(file);
    }
});
</script>