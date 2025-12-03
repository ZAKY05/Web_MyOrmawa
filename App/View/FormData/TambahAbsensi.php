<!-- Modal Tambah Sesi Absensi -->
<div class="modal fade" id="buatSesiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-plus me-2"></i>Buat Sesi Absensi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formBuatSesi" action="../../../Function/AbsensiFunction.php" method="POST">
                    <input type="hidden" name="action" value="buat">

                    <?php
                    // Ambil level dari session
                    $user_level = (int)($_SESSION['user_level'] ?? 0);
                    ?>

                    <!-- 🔑 Pilih ORMawa (hanya untuk SuperAdmin) -->
                    <?php if ($user_level === 1): ?>
                        <div class="mb-3">
                            <label class="form-label">Ormawa <span class="text-danger">*</span></label>
                            <select class="form-control" name="id_ormawa" id="ormawaSelect" required>
                                <option value="">— Pilih Ormawa —</option>
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

                    <?php elseif ($user_level === 2): ?>
                        <!-- Admin: hidden input -->
                        <?php
                        $ormawa_id = (int)($_SESSION['ormawa_id'] ?? 0);
                        $ormawa_nama = htmlspecialchars($_SESSION['ormawa_nama'] ?? 'Ormawa Anda');
                        if ($ormawa_id <= 0):
                            echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-1"></i> Anda tidak terdaftar di ORMawa manapun.</div>';
                        endif;
                        ?>
                        <input type="hidden" name="id_ormawa" value="<?= $ormawa_id; ?>">
                        <div class="mb-3">
                            <label class="form-label">Ormawa Penyelenggara</label>
                            <input type="text" class="form-control" value="<?= $ormawa_nama; ?>" readonly>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-lock me-1"></i> Anda tidak memiliki izin membuat sesi absensi.
                        </div>
                        <script>document.getElementById('btnSimpanSesi').disabled = true;</script>
                    <?php endif; ?>

                    <!-- Judul Rapat -->
                    <div class="mb-3">
                        <label class="form-label">Judul Rapat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul_rapat" required 
                               placeholder="Contoh: Rapat Evaluasi Program" maxlength="150">
                    </div>
                    
                    <!-- Waktu -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="waktu_mulai" required 
                                   value="<?= date('Y-m-d\TH:i'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="waktu_selesai" required 
                                   value="<?= date('Y-m-d\TH:i', strtotime('+1 hour')); ?>">
                        </div>
                    </div>
                    
                    <!-- Lokasi -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="useLocation" name="use_location" value="1">
                        <label class="form-check-label" for="useLocation">
                            <i class="fas fa-map-marker-alt me-1"></i> Batasi absen berdasarkan lokasi
                        </label>
                    </div>
                    
                    <div id="locationFields" class="border rounded p-3 bg-light mt-3" style="display:none;">
                        <h6 class="mb-3"><i class="fas fa-location-arrow me-2"></i>Lokasi Absensi</h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Pilih dari Bank Lokasi</label>
                            <select class="form-select" id="bankLokasiSelect">
                                <option value="">-- Pilih lokasi tersimpan --</option>
                            </select>
                            <small class="form-text text-muted">Lokasi harus sudah disimpan di Bank Lokasi.</small>
                        </div>

                        <!-- Hidden input untuk mengirim id_lokasi_absen -->
                        <input type="hidden" name="id_lokasi_absen" id="id_lokasi_absen" value="">
                        
                        <!-- Info lokasi terpilih -->
                        <div id="selectedLocationInfo" class="alert alert-info mt-3 mb-0" style="display:none;"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-success" id="btnSimpanSesi">
                    <i class="fas fa-save me-1"></i> Buat Sesi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 & Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const useLocation = document.getElementById('useLocation');
    const locationFields = document.getElementById('locationFields');
    const bankLokasiSelect = document.getElementById('bankLokasiSelect');
    const idLokasiInput = document.getElementById('id_lokasi_absen');
    const infoDiv = document.getElementById('selectedLocationInfo');
    const ormawaSelect = document.getElementById('ormawaSelect');

    // Toggle lokasi
    useLocation?.addEventListener('change', function() {
        locationFields.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
            idLokasiInput.value = '';
            infoDiv.style.display = 'none';
        }
    });

    // ✅ Load Bank Lokasi (dengan id_ormawa dinamis)
    async function loadBankLokasi() {
        if (!bankLokasiSelect) return;

        // ✅ Ambil id_ormawa tergantung level
        let id_ormawa = null;
        if (ormawaSelect && ormawaSelect.value) {
            id_ormawa = ormawaSelect.value; // SuperAdmin
        } else {
            // Coba ambil dari hidden input (Admin)
            const hiddenInput = document.querySelector('input[name="id_ormawa"]');
            if (hiddenInput) id_ormawa = hiddenInput.value;
        }

        if (!id_ormawa) {
            bankLokasiSelect.innerHTML = '<option value="">Pilih ORMawa terlebih dahulu</option>';
            return;
        }

        bankLokasiSelect.innerHTML = '<option value="">Memuat data lokasi...</option>';

        try {
            const res = await fetch(`../../../Function/AbsensiFunction.php?action=get_bank&id_ormawa=${id_ormawa}`);
            
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            bankLokasiSelect.innerHTML = '<option value="">-- Pilih lokasi tersimpan --</option>';
            
            if (data.success && Array.isArray(data.locations) && data.locations.length > 0) {
                data.locations.forEach(loc => {
                    const opt = document.createElement('option');
                    opt.value = loc.id;
                    opt.setAttribute('data-nama', loc.nama_lokasi);
                    opt.setAttribute('data-lat', loc.lat);
                    opt.setAttribute('data-lng', loc.lng);
                    opt.setAttribute('data-radius', loc.radius_default || 100);
                    opt.textContent = `${loc.nama_lokasi} (${loc.radius_default || 100} m)`;
                    bankLokasiSelect.appendChild(opt);
                });
            } else {
                const opt = document.createElement('option');
                opt.textContent = 'Belum ada lokasi tersimpan';
                opt.disabled = true;
                bankLokasiSelect.appendChild(opt);
            }
        } catch (e) {
            console.error('❌ Gagal memuat bank lokasi:', e);
            bankLokasiSelect.innerHTML = '<option value="">⚠️ Gagal memuat</option>';
            Swal.fire({
                icon: 'error',
                title: 'Gagal memuat lokasi',
                text: 'Pastikan ORMawa valid dan ada lokasi tersimpan.',
                footer: `<small>Error: ${e.message}</small>`
            });
        }
    }

    // Isi hidden input lokasi
    bankLokasiSelect?.addEventListener('change', function() {
        const id = this.value;
        idLokasiInput.value = id || '';

        if (id) {
            const opt = this.options[this.selectedIndex];
            const nama = opt.getAttribute('data-nama');
            const lat = opt.getAttribute('data-lat');
            const lng = opt.getAttribute('data-lng');
            const radius = opt.getAttribute('data-radius') || 100;

            infoDiv.innerHTML = `
                <i class="fas fa-check-circle me-1"></i>
                <strong>${nama}</strong> • Radius: ${radius} m
                <br><small><code>Lat: ${lat}, Lng: ${lng}</code></small>
            `;
            infoDiv.style.display = 'block';
        } else {
            infoDiv.style.display = 'none';
        }
    });

    // 🔁 Reload lokasi saat ORMawa berubah (SuperAdmin)
    ormawaSelect?.addEventListener('change', loadBankLokasi);

    // Muat lokasi saat modal dibuka
    const modal = document.getElementById('buatSesiModal');
    if (modal) {
        modal.addEventListener('shown.bs.modal', function() {
            // Untuk Admin: load langsung
            if (!ormawaSelect) {
                loadBankLokasi();
            }
            // Untuk SuperAdmin: load jika sudah pilih ORMawa
            else if (ormawaSelect.value) {
                loadBankLokasi();
            }
        });
    }

    // Submit
    document.getElementById('btnSimpanSesi')?.addEventListener('click', function() {
        const form = document.getElementById('formBuatSesi');
        if (!form) return;

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const useLoc = useLocation?.checked || false;
        if (useLoc && !idLokasiInput.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Lokasi belum dipilih',
                text: 'Silakan pilih lokasi dari daftar tersedia.'
            });
            return;
        }

        // ✅ Pastikan id_ormawa terisi (untuk SuperAdmin)
        if (ormawaSelect && !ormawaSelect.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Ormawa belum dipilih',
                text: 'Silakan pilih ORMawa terlebih dahulu.'
            });
            return;
        }

        // Gunakan submit biasa (bukan fetch) agar redirect otomatis ke notifikasi
        form.submit();
    });
});
</script>