<!-- Modal: Buat Sesi Absensi -->
<div class="modal fade" id="buatSesiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Buat Sesi Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formBuatSesi">
                    <div class="mb-3">
                        <label class="form-label">Judul Rapat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul_rapat" required placeholder="Rapat Evaluasi Program">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_mulai" required value="<?= date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="jam_mulai" required value="<?= date('H:i'); ?>">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_selesai" required value="<?= date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="jam_selesai" required value="<?= date('H:i', strtotime('+1 hour')); ?>">
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="useLocation" name="use_location">
                        <label class="form-check-label" for="useLocation">
                            Batasi absen berdasarkan lokasi rapat
                        </label>
                    </div>
                    <div id="locationFields" class="mt-3" style="display:none;">
                        <div class="mb-2">
                            <label class="form-label">Nama Lokasi</label>
                            <input type="text" class="form-control" name="lokasi_nama" placeholder="Ruang HMJ, Kafe Kopiin, dll">
                        </div>
                        <div class="row">
                            <div class="col">
                                <label class="form-label">Latitude</label>
                                <input type="text" class="form-control" name="lat" placeholder="-7.1234567">
                            </div>
                            <div class="col">
                                <label class="form-label">Longitude</label>
                                <input type="text" class="form-control" name="lng" placeholder="112.1234567">
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="form-label">Radius Valid (meter)</label>
                            <input type="number" class="form-control" name="radius" value="100" min="10" max="500">
                            <small class="form-text text-muted">Hanya yang berada dalam radius ini yang bisa absen</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSimpanSesi">Buat & Generate QR</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btnSimpanSesi')?.addEventListener('click', async function() {
    const form = document.getElementById('formBuatSesi');
    if (!form) {
        alert('Form tidak ditemukan!');
        return;
    }

    const formData = new FormData(form);
    
    // ✅ PATH ABSOLUT - Selalu benar
    const url = '/MYORMAWA/Function/AbsensiFunction.php?action=buat';

    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

    try {
        console.log('🔄 Mengirim ke:', url);
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        console.log('📡 Status HTTP:', response.status);
        
        // ✅ Cek apakah response JSON valid
        const text = await response.text();
        console.log('📄 Response:', text);
        
        const data = JSON.parse(text);
        
        if (data.success) {
            alert('✅ Sesi berhasil dibuat!');
            location.reload();
        } else {
            alert('❌ ' + (data.message || 'Gagal tanpa pesan'));
            console.error('Error data:', data);
        }
    } catch (err) {
        console.error('❌ Error:', err);
        alert('❌ Gagal terhubung ke server.\n\nDetail:\n' + err.message + '\n\nBuka Console (F12) untuk info lengkap.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>