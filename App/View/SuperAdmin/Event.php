<?php include('../SuperAdmin/Header.php');?>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Event</h1>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#tambahEventModal">
                <i class="fas fa-plus fa-sm"></i> Tambah Event
            </button>
            <button type="button" class="btn btn-success btn-sm" id="generateSampleEvents">
                <i class="fas fa-database fa-sm"></i> Generate Sample Data
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Event</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label>Kategori:</label>
                    <select class="form-control" id="filterKategori">
                        <option value="">Semua Kategori</option>
                        <option value="Art">Art</option>
                        <option value="Music">Music</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Festival">Festival</option>
                        <option value="Education">Education</option>
                        <option value="Sports">Sports</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Tanggal Mulai:</label>
                    <input type="date" class="form-control" id="filterTanggal">
                </div>
                <div class="col-md-3">
                    <label>Cari Event:</label>
                    <input type="text" class="form-control" id="searchEvent" placeholder="Nama event...">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="button" class="btn btn-info btn-sm" id="filterData">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm ml-2" id="resetFilter">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Cards Grid -->
    <div class="row" id="eventCardsContainer">
        <!-- Event cards will be dynamically loaded here -->
    </div>

    <!-- Event Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Event</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTableEvent" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Event</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="eventTableBody">
                        <!-- Data akan diisi oleh JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Statistik Event -->
    <div class="row">
        <div class="col  col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Event</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statTotalEvent">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Event Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statEventAktif">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Tambah Event -->
<div class="modal fade" id="tambahEventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Event Baru</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formTambahEvent">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Event <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_event" id="tambahNamaEvent" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kategori <span class="text-danger">*</span></label>
                                <select class="form-control" name="kategori" id="tambahKategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Art">Art</option>
                                    <option value="Music">Music</option>
                                    <option value="Workshop">Workshop</option>
                                    <option value="Festival">Festival</option>
                                    <option value="Education">Education</option>
                                    <option value="Sports">Sports</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="tanggal_mulai" id="tambahTanggalMulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Selesai</label>
                                <input type="datetime-local" class="form-control" name="tanggal_selesai" id="tambahTanggalSelesai">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lokasi" id="tambahLokasi" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="tambahDeskripsi" rows="4"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Harga Tiket</label>
                                <input type="number" class="form-control" name="harga" id="tambahHarga" placeholder="0">
                                <small class="text-muted">Kosongkan jika gratis</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kapasitas</label>
                                <input type="number" class="form-control" name="kapasitas" id="tambahKapasitas">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Upload Gambar Event</label>
                        <input type="file" class="form-control-file" name="gambar" id="tambahGambar" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG, max 2MB</small>
                        <div id="previewTambah" class="mt-2"></div>
                    </div>
                    
                    
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanEvent()">
                    <i class="fas fa-save"></i> Simpan Event
                </button>
            </div>
        </div>
    </div>
</div>
<?php include('../SuperAdmin/Footer.php');?>