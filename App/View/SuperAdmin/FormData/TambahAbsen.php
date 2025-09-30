<!-- Modal Tambah Absensi -->
<div class="modal fade" id="tambahAbsensiModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Absensi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formTambahAbsensi">
                    <div class="form-group">
                        <label>NIK Karyawan</label>
                        <select class="form-control" name="nik" id="tambahNik" required>
                            <option value="">Pilih Karyawan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" id="tambahTanggal" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Masuk</label>
                        <input type="time" class="form-control" name="jam_masuk" id="tambahJamMasuk">
                    </div>
                    <div class="form-group">
                        <label>Jam Keluar</label>
                        <input type="time" class="form-control" name="jam_keluar" id="tambahJamKeluar">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status" id="tambahStatus" required>
                            <option value="">Pilih Status</option>
                            <option value="Hadir">Hadir</option>
                            <option value="Terlambat">Terlambat</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Alpa">Alpa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="tambahKeterangan" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanAbsensi()">Simpan</button>
            </div>
        </div>
    </div>
</div>