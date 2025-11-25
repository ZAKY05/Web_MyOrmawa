<!-- Modal: Buat Sesi Absensi -->
<div class="modal fade" id="buatSesiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Buat Sesi Absensi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_mulai" required 
                                   value="<?= date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="jam_mulai" required 
                                   value="<?= date('H:i'); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_selesai" required 
                                   value="<?= date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="jam_selesai" required 
                                   value="<?= date('H:i', strtotime('+1 hour')); ?>">
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="useLocation" name="use_location">
                        <label class="form-check-label" for="useLocation">
                            Batasi absen berdasarkan lokasi rapat
                        </label>
                    </div>
                    
                    <div id="locationFields" class="mt-3" style="display:none;">
                        <div class="mb-2">
                            <label class="form-label">Nama Lokasi</label>
                            <input type="text" class="form-control" name="lokasi_nama" 
                                   placeholder="Contoh: Ruang HMJ Teknik Informatika">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">Latitude</label>
                                <input type="number" step="any" class="form-control" name="lat" 
                                       placeholder="-7.1234567">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Longitude</label>
                                <input type="number" step="any" class="form-control" name="lng" 
                                       placeholder="112.1234567">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Radius Valid (meter)</label>
                            <input type="number" class="form-control" name="radius" 
                                   value="100" min="10" max="500">
                            <small class="form-text text-muted">
                                Hanya yang berada dalam radius ini yang bisa absen
                            </small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSimpanSesi">
                    <i class="fas fa-save me-1"></i>Buat Sesi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('useLocation')?.addEventListener('change', function() {
    const locationFields = document.getElementById('locationFields');
    if (locationFields) {
        locationFields.style.display = this.checked ? 'block' : 'none';
    }
});

document.getElementById('btnSimpanSesi')?.addEventListener('click', async function() {
    const form = document.getElementById('formBuatSesi');
    if (!form) {
        alert('❌ Form tidak ditemukan!');
        return;
    }

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    
    // ✅ PATH FINAL: ../../../Function/AbsensiFunction.php
    const apiUrl = '../../../Function/AbsensiFunction.php?action=buat';
    
    console.log('📍 Submitting to:', apiUrl);
    console.log('📍 Full URL:', new URL(apiUrl, window.location.href).href);

    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

    try {
        const response = await fetch(apiUrl, {
            method: 'POST',
            body: formData
        });

        console.log('📡 HTTP Status:', response.status);
        
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }

        const responseText = await response.text();
        console.log('📄 Raw response:', responseText);

        if (!responseText.trim()) {
            throw new Error('Server mengembalikan response kosong');
        }

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            throw new Error('Response bukan JSON valid. Cek Console untuk raw response.');
        }

        console.log('✅ Data:', data);

        if (data.success) {
            alert('✅ ' + data.message);
            const modal = bootstrap.Modal.getInstance(document.getElementById('buatSesiModal'));
            if (modal) modal.hide();
            location.reload();
        } else {
            alert('❌ ' + (data.message || 'Gagal'));
        }
        
    } catch (error) {
        console.error('💥 Fatal error:', error);
        alert(
            '❌ Gagal terhubung ke server!\n\n' +
            'Error: ' + error.message + '\n\n' +
            '👉 Buka DevTools (F12) → tab Console untuk detail.'
        );
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

// Ping test saat load
window.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 System check...');
    
    const apiUrl = '../../../Function/AbsensiFunction.php?action=buat';
    console.log('🏓 Testing ping to:', new URL(pingUrl, window.location.href).href);

    fetch(pingUrl)
        .then(r => {
            console.log('📡 Ping status:', r.status);
            return r.json();
        })
        .then(data => {
            console.log('✅ Ping OK:', data);
        })
        .catch(err => {
            console.error('❌ Ping failed:', err);
            alert('⚠️ Peringatan: Server function tidak merespons. Cek koneksi ke Function/AbsensiFunction.php');
        });
});
</script>