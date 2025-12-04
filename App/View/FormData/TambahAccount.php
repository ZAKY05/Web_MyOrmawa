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
                        <!-- SuperAdmin: Pilih Level & Ormawa (conditional) -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Level <span class="text-danger">*</span></label>
                                <select class="form-select" name="level" id="level" required>
                                    <option value="">Pilih Level</option>
                                    <option value="2">Admin (Ketua Ormawa)</option>
                                    <option value="3">Mahasiswa</option>
                                    <option value="4">Pengurus</option>
                                </select>
                                <small class="text-muted">
                                    • Admin: Ketua Ormawa (1 per Ormawa)<br>
                                    • Mahasiswa: Tidak wajib pilih Ormawa<br>
                                    • Pengurus: Anggota/Pengurus Ormawa
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    Ormawa <span class="text-danger" id="ormawa-required">*</span>
                                </label>
                                <select class="form-select" name="id_ormawa" id="ormawa_id">
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
                                <small class="text-muted" id="ormawa-help">
                                    Wajib untuk Admin & Pengurus
                                </small>
                            </div>
                        </div>

                    <?php elseif ($user_level === 2): ?>
                        <!-- Admin ORMawa: Hanya level 3/4, Ormawa otomatis -->
                        <?php
                        $ormawa_id = (int)($_SESSION['ormawa_id'] ?? 0);
                        $ormawa_nama = htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ormawa Anda');
                        ?>
                        <input type="hidden" name="id_ormawa" value="<?= $ormawa_id; ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Level <span class="text-danger">*</span></label>
                                <select class="form-select" name="level" id="level" required>
                                    <option value="">Pilih Level</option>
                                    <option value="3">Mahasiswa</option>
                                    <option value="4">Pengurus</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ormawa</label>
                                <input type="text" class="form-control" value="<?= $ormawa_nama; ?>" readonly>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-danger">
                            Anda tidak memiliki izin.
                        </div>
                        <script>document.querySelector('#accountForm button[type="submit"]')?.setAttribute('disabled', 'true');</script>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" id="nama" required maxlength="100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nim" id="nim" required maxlength="20">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Program Studi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="program_studi" id="program_studi" required maxlength="100" placeholder="Contoh: Teknik Informatika">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Angkatan <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="angkatan" id="angkatan" required min="2000" max="2100" placeholder="Contoh: 2024">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" id="email" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Password <span class="text-danger" id="password-required">*</span>
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
    const ormawaRequired = document.getElementById('ormawa-required');
    
    if (level) level.value = '';
    if (ormawa) {
        ormawa.value = '';
        ormawa.removeAttribute('required');
    }
    if (ormawaRequired) ormawaRequired.style.display = 'inline';
}

// Logika conditional Ormawa berdasarkan Level
document.addEventListener('DOMContentLoaded', function() {
    const levelSelect = document.getElementById('level');
    const ormawaSelect = document.getElementById('ormawa_id');
    const ormawaRequired = document.getElementById('ormawa-required');
    const ormawaHelp = document.getElementById('ormawa-help');

    if (levelSelect && ormawaSelect) {
        levelSelect.addEventListener('change', function() {
            const level = this.value;
            
            if (level === '3') {
                // Level 3 (Mahasiswa): Ormawa OPSIONAL
                ormawaSelect.removeAttribute('required');
                ormawaSelect.value = ''; // Reset ormawa
                if (ormawaRequired) ormawaRequired.style.display = 'none';
                if (ormawaHelp) ormawaHelp.textContent = 'Opsional untuk Mahasiswa';
                ormawaSelect.disabled = false;
            } else if (level === '2' || level === '4') {
                // Level 2 (Admin) & 4 (Pengurus): Ormawa WAJIB
                ormawaSelect.setAttribute('required', 'required');
                if (ormawaRequired) ormawaRequired.style.display = 'inline';
                if (ormawaHelp) {
                    ormawaHelp.textContent = level === '2' 
                        ? 'Wajib untuk Admin (1 per Ormawa)' 
                        : 'Wajib untuk Pengurus';
                }
                ormawaSelect.disabled = false;
            } else {
                // Belum pilih level
                ormawaSelect.removeAttribute('required');
                if (ormawaRequired) ormawaRequired.style.display = 'inline';
                ormawaSelect.disabled = false;
            }
        });

        // Trigger saat form edit dibuka
        if (levelSelect.value) {
            levelSelect.dispatchEvent(new Event('change'));
        }
    }

    // AUTO UPDATE LEVEL: Jika Mahasiswa (level 3) memilih Ormawa, ubah otomatis jadi Pengurus (level 4)
    if (ormawaSelect && levelSelect) {
        ormawaSelect.addEventListener('change', function() {
            const currentLevel = levelSelect.value;
            const ormawaId = this.value;
            
            // Jika level Mahasiswa (3) dan memilih Ormawa, ubah jadi Pengurus (4)
            if (currentLevel === '3' && ormawaId && ormawaId !== '') {
                Swal.fire({
                    icon: 'info',
                    title: 'Level Diubah Otomatis',
                    text: 'Karena Anda memilih Ormawa, level akan diubah dari Mahasiswa menjadi Pengurus.',
                    timer: 3000,
                    showConfirmButton: false
                });
                levelSelect.value = '4';
                levelSelect.dispatchEvent(new Event('change'));
            }
        });
    }
});

function submitAccountForm() {
    const form = document.getElementById('accountForm');
    const nama = document.getElementById('nama').value.trim().toLowerCase();
    const nim = document.getElementById('nim').value.trim().toLowerCase();
    const level = document.getElementById('level').value;
    const ormawaSelect = document.getElementById('ormawa_id');
    const ormawaId = ormawaSelect ? ormawaSelect.value : '';
    const action = document.getElementById('formAction').value;

    // Validasi kata terlarang
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

    // Validasi Ormawa untuk Level 2 (Admin) dan Level 4 (Pengurus)
    if ((level === '2' || level === '4') && !ormawaId) {
        Swal.fire({
            icon: 'warning',
            title: 'Ormawa Wajib',
            text: level === '2' 
                ? 'Admin (Ketua Ormawa) wajib memilih Ormawa!' 
                : 'Pengurus wajib memilih Ormawa!',
            timer: 3000,
            showConfirmButton: true
        });
        return;
    }

    // AUTO UPDATE: Jika saat edit, Mahasiswa pilih Ormawa, ubah level jadi Pengurus
    if (action === 'edit' && level === '3' && ormawaId && ormawaId !== '') {
        document.getElementById('level').value = '4';
    }

    // Submit form jika valid
    if (form.reportValidity()) {
        form.submit();
    }
}
</script>