<div class="modal fade" id="tambahAccountModal" tabindex="-1" aria-labelledby="tambahAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="formTitle">Tambah Akun Ormawa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="accountForm" action="../../../Function/AccountFunction.php" method="POST">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="editId" value="">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="full_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nim" class="form-label">NIM</label>
                            <input type="text" class="form-control" id="nim" name="nim" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Password
                            <small class="text-muted d-block">
                                <em>Wajib saat tambah. Kosongkan saat edit jika tidak ingin ganti password.</em>
                            </small>
                        </label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitAccountForm()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
function resetAccountForm() {
    document.getElementById('accountForm').reset();
    document.getElementById('formAction').value = 'add';
    document.getElementById('editId').value = '';
    document.getElementById('formTitle').textContent = 'Tambah Akun Ormawa';
    
    const passField = document.getElementById('password');
    passField.setAttribute('required', 'required');
    passField.closest('.mb-3').querySelector('small').textContent =
        'Wajib saat tambah. Kosongkan saat edit jika tidak ingin ganti password.';
}

function editAccount(id, nama, nim, email) {
    document.getElementById('formAction').value = 'edit';
    document.getElementById('editId').value = id;
    document.getElementById('nama').value = nama;
    document.getElementById('nim').value = nim;
    document.getElementById('email').value = email;
    document.getElementById('password').value = '';
    document.getElementById('formTitle').textContent = 'Edit Akun Ormawa';
    
    const passField = document.getElementById('password');
    passField.removeAttribute('required');
    passField.closest('.mb-3').querySelector('small').textContent =
        'Kosongkan jika tidak ingin mengganti password.';
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('tambahAccountModal')) ||
                  new bootstrap.Modal(document.getElementById('tambahAccountModal'));
    modal.show();
}

function submitAccountForm() {
    const form = document.getElementById('accountForm');
    if (form.reportValidity()) {
        const btn = document.querySelector('.modal-footer .btn-primary');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Memproses...';
        btn.disabled = true;
        form.submit();
    }
}

// Reset form saat modal ditutup
document.getElementById('tambahAccountModal').addEventListener('hidden.bs.modal', resetAccountForm);
</script>