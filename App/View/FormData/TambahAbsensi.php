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
                <form id="formBuatSesi">
                    <div class="mb-3">
                        <label class="form-label">Judul Rapat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul_rapat" required 
                               placeholder="Contoh: Rapat Evaluasi Program">
                    </div>
                    
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
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="useLocation" name="use_location">
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
                        
                        <!-- Info lokasi terpilih (non-editable) -->
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

<!-- SweetAlert2 & Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const useLocation = document.getElementById('useLocation');
    const locationFields = document.getElementById('locationFields');
    const bankLokasiSelect = document.getElementById('bankLokasiSelect');
    const idLokasiInput = document.getElementById('id_lokasi_absen');
    const infoDiv = document.getElementById('selectedLocationInfo');

    // Toggle lokasi
    useLocation?.addEventListener('change', function() {
        locationFields.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
            idLokasiInput.value = '';
            infoDiv.style.display = 'none';
        }
    });

    // === Load Bank Lokasi dari AbsensiFunction.php ===
    async function loadBankLokasi() {
        if (!bankLokasiSelect) return;
        
        bankLokasiSelect.innerHTML = '<option value="">Memuat data lokasi...</option>';
        
        try {
            const res = await fetch('../../../Function/AbsensiFunction.php?action=get_bank');
            
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            bankLokasiSelect.innerHTML = '<option value="">-- Pilih lokasi tersimpan --</option>';
            
            if (data.success && Array.isArray(data.locations)) {
                data.locations.forEach(loc => {
                    const opt = document.createElement('option');
                    opt.value = loc.id; // ✅ hanya kirim ID
                    opt.setAttribute('data-nama', loc.nama_lokasi);
                    opt.setAttribute('data-lat', loc.lat);
                    opt.setAttribute('data-lng', loc.lng);
                    opt.setAttribute('data-radius', loc.radius_default || 100);
                    opt.textContent = `${loc.nama_lokasi} (${loc.radius_default || 100} m)`;
                    bankLokasiSelect.appendChild(opt);
                });

                if (data.locations.length === 0) {
                    const opt = document.createElement('option');
                    opt.textContent = 'Belum ada lokasi tersimpan';
                    opt.disabled = true;
                    bankLokasiSelect.appendChild(opt);
                }
            }
        } catch (e) {
            console.error('❌ Gagal memuat bank lokasi:', e);
            bankLokasiSelect.innerHTML = '<option value="">⚠️ Gagal memuat</option>';
            Swal.fire({
                icon: 'error',
                title: 'Gagal memuat lokasi',
                text: 'Pastikan Anda login dan memiliki akses ORMawa.',
                footer: `<small>Error: ${e.message}</small>`
            });
        }
    }

    // Isi hidden input saat pilih lokasi
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

    // Muat bank lokasi saat modal dibuka
    const modal = document.getElementById('buatSesiModal');
    if (modal) {
        modal.addEventListener('shown.bs.modal', loadBankLokasi);
    }

    // Submit form
    document.getElementById('btnSimpanSesi')?.addEventListener('click', async function() {
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

        const btn = this;
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Menyimpan...';

        const formData = new FormData(form);

        try {
            const res = await fetch('../../../Function/AbsensiFunction.php?action=buat', {
                method: 'POST',
                body: formData
            });

            let result;
            try {
                result = await res.json();
            } catch (e) {
                throw new Error('Respon server tidak valid (bukan JSON).');
            }

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    form.reset();
                    locationFields.style.display = 'none';
                    infoDiv.style.display = 'none';
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    modalInstance?.hide();
                    if (typeof refreshDaftarAbsensi === 'function') {
                        refreshDaftarAbsensi();
                    } else {
                        location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: result.message || 'Terjadi kesalahan tak terduga.',
                    confirmButtonText: 'OK'
                });
            }
        } catch (err) {
            console.error('❌ Error saat submit:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Gagal menyimpan sesi: ' + (err.message || 'Koneksi gagal.'),
                footer: `<small>Cek konsol browser untuk detail error.</small>`
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    });
});
</script>