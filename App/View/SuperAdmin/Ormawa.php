<?php
include('Header.php');
include('../../../Config/ConnectDB.php');

function getOrmawaData($koneksi) {
    $category_order = "'Lembaga', 'Akademik', 'Rohani', 'Minat', 'Olahraga', 'Seni'";
    $sql = "SELECT id, nama_ormawa, deskripsi, kategori, visi, misi, email, contact_person, logo, created_at, update_at 
            FROM ormawa 
            ORDER BY FIELD(kategori, $category_order) ASC, nama_ormawa ASC";
    $result = mysqli_query($koneksi, $sql);
    $data = [];
    if ($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_free_result($result);
    }
    return $data;
}

$ormawa_list = getOrmawaData($koneksi);

// Daftar Kategori untuk Filter
$kategori_list = ['Semua', 'Lembaga', 'Akademik', 'Rohani', 'Minat', 'Olahraga', 'Seni'];
?>

<style>
    .table-cell-fixed {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 1px;
    }
    .action-cell {
        min-width: 80px;
        text-align: center;
    }
    
    .search-box {
        margin-bottom: 0; 
    }
    
    .search-box .input-group-text {
        border-right: none; /* Hilangkan garis kanan pada input-group-text agar menyatu */
    }

    .search-box input {
        border-left: none; /* Hilangkan garis kiri pada input agar menyatu dengan input-group-text */
    }

    .row.align-items-center > div {
        margin-bottom: 0.5rem; 
    }
    @media (min-width: 768px) {
        .row.align-items-center > div {
            margin-bottom: 0; 
        }
    }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users"></i> Data Organisasi Mahasiswa
        </h1>
        <button class="btn btn-success btn-icon-split" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="resetForm()">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Tambah Ormawa</span>
        </button>
    </div>

    <div class="row mb-3 align-items-center">
        
        <div class="col-md-5">
            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control" 
                            placeholder="Cari: Nama, Kategori, Email, Contact Person..." 
                            onkeyup="filterAll()">
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <select class="form-control" id="filterKategori" onchange="filterAll()">
                <option value="Semua">Semua Kategori</option>
                <?php foreach ($kategori_list as $kategori): ?>
                    <?php if ($kategori !== 'Semua'): ?>
                        <option value="<?php echo htmlspecialchars($kategori); ?>">
                            <?php echo htmlspecialchars($kategori); ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-3">
            <button class="btn btn-secondary w-100" onclick="resetFilters()">
                <i class="fas fa-redo"></i> Reset Filter
            </button>
        </div>
        
    </div>
    <?php if (isset($_GET['success']) && $_GET['success'] == 'form'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> Data berhasil disimpan!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['success']) && $_GET['success'] == 'updated'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> Data berhasil diperbarui!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i> Data berhasil dihapus!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> 
            <?php 
                switch($_GET['error']) {
                    case 'nama_kosong': echo "Nama ormawa tidak boleh kosong!"; break;
                    case 'kategori_kosong': echo "Kategori harus dipilih!"; break;
                    case 'invalid_data': echo "Data tidak valid!"; break;
                    case 'query_gagal': echo "Terjadi kesalahan pada database!"; break;
                    case 'invalid_id': echo "ID tidak valid!"; break;
                    case 'data_not_found': echo "Data tidak ditemukan!"; break;
                    case 'upload_error': echo "Terjadi kesalahan saat upload logo!"; break;
                    default: echo "Terjadi kesalahan!";
                }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Daftar Ormawa
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="ormawaTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th width="2%">No</th>
                            <th width="3%">Logo</th>
                            <th width="9%">Nama Ormawa</th>
                            <th width="5%">Kategori</th>
                            <th width="18%">Deskripsi</th> 
                            <th width="25%">Visi</th>      
                            <th width="25%">Misi</th>      
                            <th width="3%">Email</th>     
                            <th width="13%">Contact Person</th> 
                            <th width="2%">Aksi</th>      
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php
                        if (count($ormawa_list) > 0) {
                            $no = 1;
                            foreach ($ormawa_list as $ormawa) {
                                $badgeClass = 'bg-dark';
                                switch($ormawa['kategori']) {
                                    case 'Akademik': $badgeClass = 'bg-primary'; break;
                                    case 'Lembaga': $badgeClass = 'bg-success'; break;
                                    case 'Rohani': $badgeClass = 'bg-warning'; break;
                                    case 'Minat': $badgeClass = 'bg-info'; break;
                                    case 'Seni': $badgeClass = 'bg-danger'; break;
                                    case 'Olahraga': $badgeClass = 'bg-secondary'; break;
                                }
                                
                                // Simpan data ke atribut `data-search` & `data-kategori`
                                $searchText = strtolower(
                                    $ormawa['nama_ormawa'] . ' ' .
                                    $ormawa['kategori'] . ' ' .
                                    $ormawa['email'] . ' ' .
                                    $ormawa['contact_person'] . ' ' .
                                    $ormawa['deskripsi']
                                );
                                
                                echo "<tr data-search=\"" . htmlspecialchars($searchText, ENT_QUOTES) . "\" 
                                          data-kategori=\"" . htmlspecialchars($ormawa['kategori'], ENT_QUOTES) . "\">";
                                echo "<td class='text-center'>" . $no++ . "</td>";
                                
                                echo "<td class='text-center'>";
                                if (!empty($ormawa['logo'])) {
                                    echo '<img src="../../../uploads/logos/' . htmlspecialchars($ormawa['logo']) . '" 
                                                alt="Logo" class="img-thumbnail" style="max-width: 60px; max-height: 60px; object-fit: cover;">';
                                } else {
                                    echo '<div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                                style="width: 60px; height: 60px;">
                                                <i class="fas fa-image text-muted"></i></div>';
                                }
                                echo "</td>";
                                
                                echo "<td><strong>" . htmlspecialchars($ormawa['nama_ormawa']) . "</strong></td>";
                                echo "<td><span class='badge " . $badgeClass . "'>" . htmlspecialchars($ormawa['kategori']) . "</span></td>";
                                
                                $deskripsi = !empty($ormawa['deskripsi']) ? htmlspecialchars($ormawa['deskripsi']) : '-';
                                echo "<td>" . (strlen($deskripsi) > 150 ? substr($deskripsi, 0, 150) . "..." : $deskripsi) . "</td>";
                                
                                $visi = !empty($ormawa['visi']) ? htmlspecialchars($ormawa['visi']) : '-';
                                echo "<td>" . (strlen($visi) > 200 ? substr($visi, 0, 200) . "..." : $visi) . "</td>";
                                
                                $misi = !empty($ormawa['misi']) ? htmlspecialchars($ormawa['misi']) : '-';
                                echo "<td>" . (strlen($misi) > 200 ? substr($misi, 0, 200) . "..." : $misi) . "</td>";
                                
                                echo "<td class='table-cell-fixed'>" . (!empty($ormawa['email']) ? '<a href="mailto:' . htmlspecialchars($ormawa['email']) . '">' . 
                                             htmlspecialchars($ormawa['email']) . '</a>' : '-') . "</td>";
                                
                                echo "<td class='table-cell-fixed'>" . (!empty($ormawa['contact_person']) ? htmlspecialchars($ormawa['contact_person']) : '-') . "</td>";
                                
                                echo "<td class='action-cell'>";
                                
                                echo "<button class='btn btn-info btn-circle btn-sm me-1 mb-1' 
                                                data-bs-toggle='modal' data-bs-target='#modalDetail' 
                                                onclick='viewDetail(" . $ormawa['id'] . ")' 
                                                title='Lihat Detail'>
                                                <i class='fas fa-eye'></i>
                                                </button> ";
                                
                                echo "<button class='btn btn-warning btn-circle btn-sm me-1 mb-1' 
                                                data-bs-toggle='modal' 
                                                data-bs-target='#modalForm' 
                                                data-id='" . $ormawa['id'] . "'
                                                data-nama='" . htmlspecialchars($ormawa['nama_ormawa'], ENT_QUOTES) . "'
                                                data-deskripsi='" . htmlspecialchars($ormawa['deskripsi'], ENT_QUOTES) . "'
                                                data-kategori='" . htmlspecialchars($ormawa['kategori'], ENT_QUOTES) . "'
                                                data-visi='" . htmlspecialchars($ormawa['visi'], ENT_QUOTES) . "'
                                                data-misi='" . htmlspecialchars($ormawa['misi'], ENT_QUOTES) . "'
                                                data-email='" . htmlspecialchars($ormawa['email'], ENT_QUOTES) . "'
                                                data-contact='" . htmlspecialchars($ormawa['contact_person'], ENT_QUOTES) . "'
                                                data-logo='" . htmlspecialchars($ormawa['logo'], ENT_QUOTES) . "'
                                                onclick='editOrmawaFromButton(this)'
                                                title='Edit'>
                                                <i class='fas fa-edit'></i>
                                                </button> ";
                                
                                echo "<a href='../../../Function/OrmawaFunction.php?action=delete&id=" . $ormawa['id'] . "' 
                                                class='btn btn-danger btn-circle btn-sm mb-1' 
                                                onclick='return confirm(\"Yakin ingin menghapus data " . htmlspecialchars($ormawa['nama_ormawa']) . "?\")' 
                                                title='Hapus'>
                                                <i class='fas fa-trash'></i>
                                                </a>";
                                
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10' class='text-center'>Tidak ada data ormawa.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div id="noResults" class="text-center text-muted mt-3 d-none">
                    <i class="fas fa-magnifying-glass fa-2x mb-2"></i>
                    <p>Tidak ada data yang sesuai dengan pencarian atau filter Anda.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalDetailLabel">
                    <i class="fas fa-info-circle"></i> Detail Ormawa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
// ✅ FUNGSI GABUNGAN SEARCH DAN FILTER
function filterAll() {
    const searchInput = document.getElementById('searchInput');
    const kategoriFilter = document.getElementById('filterKategori');
    
    const searchText = searchInput.value.toLowerCase().trim();
    const selectedKategori = kategoriFilter.value; // 'Semua', 'Lembaga', 'Akademik', dll.
    
    const table = document.getElementById('ormawaTable');
    const rows = table.querySelectorAll('tbody tr');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowSearchText = row.getAttribute('data-search') || '';
        const rowKategori = row.getAttribute('data-kategori') || '';

        // 1. Cek Pencarian
        const isSearchMatch = searchText === '' || rowSearchText.includes(searchText);

        // 2. Cek Filter Kategori
        const isKategoriMatch = selectedKategori === 'Semua' || rowKategori === selectedKategori;

        // Tampilkan baris jika cocok dengan pencarian DAN kategori
        if (isSearchMatch && isKategoriMatch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Tampilkan/menyembunyikan pesan "tidak ada hasil"
    const noResults = document.getElementById('noResults');
    if (visibleCount === 0) {
        noResults.classList.remove('d-none');
    } else {
        noResults.classList.add('d-none');
    }
}

// ✅ FUNGSI RESET FILTER
function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterKategori').value = 'Semua';
    filterAll(); // Terapkan semua filter/search yang kosong
}

function viewDetail(id) {
    const ormawaData = <?php echo json_encode($ormawa_list); ?>;
    const ormawa = ormawaData.find(item => parseInt(item.id) === id);
    if (ormawa) {
        let html = '<div class="row">';
        html += '<div class="col-md-3 text-center mb-3">';
        if (ormawa.logo) {
            html += '<img src="../../../uploads/logos/' + ormawa.logo + '" alt="Logo" class="img-thumbnail" style="max-width: 150px;">';
        } else {
            html += '<div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width: 150px; height: 150px;"><i class="fas fa-image fa-3x text-muted"></i></div>';
        }
        html += '</div>';
        html += '<div class="col-md-9">';
        html += '<h4>' + ormawa.nama_ormawa + '</h4>';
        html += '<p><span class="badge bg-primary">' + ormawa.kategori + '</span></p>';
        html += '<hr>';
        html += '<p><strong><i class="fas fa-align-left"></i> Deskripsi:</strong><br>' + (ormawa.deskripsi || '-') + '</p>';
        html += '<p><strong><i class="fas fa-bullseye"></i> Visi:</strong><br>' + (ormawa.visi || '-') + '</p>';
        html += '<p><strong><i class="fas fa-tasks"></i> Misi:</strong><br>' + (ormawa.misi ? ormawa.misi.replace(/\n/g, '<br>') : '-') + '</p>';
        html += '<p><strong><i class="fas fa-envelope"></i> Email:</strong> ' + (ormawa.email || '-') + '</p>';
        html += '<p><strong><i class="fas fa-phone"></i> Contact Person:</strong> ' + (ormawa.contact_person || '-') + '</p>';
        html += '</div></div>';
        document.getElementById('detailContent').innerHTML = html;
    }
}

function editOrmawaFromButton(button) {
    const id = button.getAttribute('data-id');
    const nama = button.getAttribute('data-nama');
    const deskripsi = button.getAttribute('data-deskripsi');
    const kategori = button.getAttribute('data-kategori');
    const visi = button.getAttribute('data-visi');
    const misi = button.getAttribute('data-misi');
    const email = button.getAttribute('data-email');
    const contact = button.getAttribute('data-contact');
    const logo = button.getAttribute('data-logo');
    
    document.getElementById('formAction').value = 'edit';
    document.getElementById('editId').value = id;
    document.getElementById('nama_ormawa').value = nama || '';
    document.getElementById('deskripsi').value = deskripsi || '';
    document.getElementById('kategori').value = kategori || '';
    document.getElementById('visi').value = visi || '';
    document.getElementById('misi').value = misi || '';
    document.getElementById('email').value = email || '';
    document.getElementById('contact_person').value = contact || '';
    document.getElementById('modalFormLabel').textContent = 'Edit Ormawa';
    
    const logoPreview = document.getElementById('logoPreview');
    if (logo) {
        logoPreview.innerHTML = 
            '<img src="../../../uploads/logos/' + logo + '" alt="Current Logo" style="max-width: 150px; max-height: 150px;" class="border rounded mt-2">' +
            '<p class="text-muted small mt-1">Logo saat ini (upload file baru untuk mengganti)</p>';
    } else {
        logoPreview.innerHTML = 
            '<p class="text-muted small">Tidak ada logo. Upload file untuk menambahkan logo.</p>';
    }
}

function resetForm() {
    document.getElementById('ormawaForm')?.reset(); 
    document.getElementById('formAction').value = 'add';
    document.getElementById('editId').value = '';
    document.getElementById('modalFormLabel').textContent = 'Tambah Ormawa';
    document.getElementById('logoPreview').innerHTML = '';
}

// Event Listener untuk Preview Logo
document.getElementById('logo')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 5000000) {
            alert('Ukuran file terlalu besar! Maksimal 5MB.');
            this.value = '';
            document.getElementById('logoPreview').innerHTML = '';
            return;
        }
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung! Gunakan JPG, JPEG, PNG, atau GIF.');
            this.value = '';
            document.getElementById('logoPreview').innerHTML = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').innerHTML = 
                '<img src="' + e.target.result + '" alt="Preview" style="max-width: 150px; max-height: 150px;" class="border rounded mt-2">' +
                '<p class="text-muted small mt-1">Preview logo baru</p>';
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include('../FormData/TambahOrmawa.php'); ?>
<?php include('Footer.php'); ?>
<?php mysqli_close($koneksi); ?>