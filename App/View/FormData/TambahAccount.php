<div class="modal fade" id="tambahAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="formTitle">Tambah Akun</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="accountForm" action="../../../Function/AccountFunction.php" method="POST">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="editId" value="">

                    <?php
                    $user_level = (int)($_SESSION['user_level'] ?? 0);
                    ?>

                    <?php if ($user_level === 1): ?>
                        <!-- SuperAdmin: Pilih Ormawa & Level -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Ormawa <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_ormawa" id="ormawa_id" required>
                                    <option value="">Pilih Ormawa</option>
                                    <?php
                                    $ormawas = mysqli_query($koneksi, "SELECT id, nama_ormawa FROM ormawa ORDER BY nama_ormawa");
                                    while ($o = mysqli_fetch_assoc($ormawas)):
                                    ?>
                                        <option value="<?= (int)$o['id']; ?>">
                                            <?= htmlspecialchars($o['nama_ormawa']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Level <span class="text-danger">*</span></label>
                                <select class="form-select" name="level" id="level" required>
                                    <option value="">Pilih Level</option>
                                    <option value="2">Admin</option>
                                    <option value="3">Pengurus</option>
                                    <option value="4">Mahasiswa</option>
                                </select>
                            </div>
                        </div>

                    <?php elseif ($user_level === 2): ?>
                        <!-- Admin ORMawa: Hanya level 3/4 -->
                        <?php
                        $ormawa_id = (int)($_SESSION['ormawa_id'] ?? 0);
                        $ormawa_nama = htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ormawa Anda');
                        ?>
                        <input type="hidden" name="id_ormawa" value="<?= $ormawa_id; ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Ormawa</label>
                                <input type="text" class="form-control" value="<?= $ormawa_nama; ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Level <span class="text-danger">*</span></label>
                                <select class="form-select" name="level" id="level" required>
                                    <option value="3">Pengurus</option>
                                    <option value="4">Mahasiswa</option>
                                </select>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-danger">
                            Anda tidak memiliki izin.
                        </div>
                        <script>document.querySelector('#accountForm button[type="submit"]').disabled = true;</script>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" id="nama" required maxlength="50">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nim" id="nim" required maxlength="20">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" id="email" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Password <span class="text-danger">*</span>
                            <small class="text-muted d-block" id="passwordHelp">
                                Wajib saat tambah. Kosongkan saat edit jika tidak ingin ganti password.
                            </small>
                        </label>
                        <input type="password" class="form-control" name="password" id="password" required minlength="6">
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
    document.getElementById('formTitle').textContent = 'Tambah Akun';
    document.getElementById('password').setAttribute('required', 'required');
    document.getElementById('passwordHelp').textContent =
        'Wajib saat tambah. Kosongkan saat edit jika tidak ingin ganti password.';

    const level = document.getElementById('level');
    const ormawa = document.getElementById('ormawa_id');
    if (level) level.value = '3'; // default Pengurus
    if (ormawa) ormawa.value = '';
}

function submitAccountForm() {
    const nama = document.getElementById('nama').value.trim().toLowerCase();
    const nim = document.getElementById('nim').value.trim().toLowerCase();
    const forbidden = ['admin', 'hmj-ti', 'hmj ti', 'superadmin', 'root', 'guest'];

    for (const term of forbidden) {
        if (nama.includes(term) || nim.includes(term)) {
            Swal.fire({
                icon: 'warning',
                title: 'Input Tidak Diperbolehkan',
                text: 'Nama/NIM tidak boleh mengandung: admin, HMJ-TI, superadmin, dll.',
                timer: 3000,
                showConfirmButton: false
            });
            return;
        }
    }

    const form = document.getElementById('accountForm');
    if (form.reportValidity()) {
        form.submit();
    }
}
</script>