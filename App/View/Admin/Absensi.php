<?php
include('Header.php');
include('../SuperAdmin/FormData/TambahAbsen.php');
?>
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Absensi</h1>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary btn-sm btn-ml-2" data-toggle="modal" data-target="#tambahAbsensiModal">
                <i class="fas fa-plus fa-sm"></i> Tambah Absensi
            </button>
            <button type="button" class="btn btn-success btn-sm" id="exportExcel">
                <i class="fas fa-file-excel fa-sm"></i> Export Excel
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Absensi</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label>Tanggal Dari:</label>
                    <input type="date" class="form-control" id="tanggalDari">
                </div>
                <div class="col-md-3">
                    <label>Tanggal Sampai:</label>
                    <input type="date" class="form-control" id="tanggalSampai">
                </div>
                <div class="col-md-3">
                    <label>Departemen:</label>
                    <select class="form-control" id="filterDepartemen">
                        <option value="">Semua Departemen</option>
                        <option value="IT">Humas</option>
                        
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Status:</label>
                    <select class="form-control" id="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="Hadir">Hadir</option>
                        <option value="Terlambat">Terlambat</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Alpa">Alpa</option>
                    </select>
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

    <!-- Data Absensi Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Absensi Panitia</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTableAbsensi" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Departemen</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="absensiTableBody">
                        <!-- Data akan diisi oleh JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Statistik Absensi -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Hadir Hari Ini</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statHadir">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Terlambat</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statTerlambat">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Izin</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statIzin">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Alpa</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="statAlpa">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<?php
include('Footer.php');
?>