// Fungsi untuk menyimpan event baru
function simpanEvent() {
    const form = document.getElementById('formTambahEvent');
    const formData = new FormData(form);
    formData.append('action', 'create');

    fetch('../FormData/EventFunction.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ ' + data.message);
            $('#tambahEventModal').modal('hide');
            form.reset(); // Reset form
            document.getElementById('previewTambah').innerHTML = ''; // Hapus preview gambar
            loadEvents(); // Refresh daftar event
        } else {
            alert('❌ Gagal: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data.');
    });
}

// Fungsi untuk memuat daftar event (dipanggil setelah simpan/update/hapus)
function loadEvents(kategori = '', tanggal = '', search = '') {
    // Kosongkan kontainer dulu
    document.getElementById('eventCardsContainer').innerHTML = '<div class="col-12 text-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>';
    document.getElementById('eventTableBody').innerHTML = '<tr><td colspan="7" class="text-center">Memuat data...</td></tr>';

    const url = `../FormData/EventFunction.php?action=read&kategori=${encodeURIComponent(kategori)}&tanggal=${encodeURIComponent(tanggal)}&search=${encodeURIComponent(search)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                renderEventCards(data.data);
                renderEventTable(data.data);
                loadStatistics();
            } else {
                document.getElementById('eventCardsContainer').innerHTML = '<div class="col-12 text-center text-danger">Gagal memuat data</div>';
                document.getElementById('eventTableBody').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>';
            }
        })
        .catch(() => {
            document.getElementById('eventCardsContainer').innerHTML = '<div class="col-12 text-center text-danger">Kesalahan jaringan</div>';
            document.getElementById('eventTableBody').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Kesalahan jaringan</td></tr>';
        });
}

// Fungsi render card event
function renderEventCards(events) {
    const container = document.getElementById('eventCardsContainer');
    if (events.length === 0) {
        container.innerHTML = '<div class="col-12 text-center text-muted">Tidak ada event ditemukan</div>';
        return;
    }

    let html = '';
    events.forEach(event => {
        const startDate = new Date(event.tgl_mulai).toLocaleDateString('id-ID', {
            weekday: 'short',
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        html += `
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow h-100">
                ${event.gambar ? `<img src="../uploads/${event.gambar}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="${event.nama_event}">` : ''}
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title">${event.nama_event}</h6>
                    <span class="badge badge-${getBadgeColor(event.kategori)} mb-2">${event.kategori}</span>
                    <p class="card-text small text-muted mb-2">
                        <i class="fas fa-calendar"></i> ${startDate}
                        ${event.tgl_selesai ? `<br><i class="fas fa-clock"></i> Sampai ${new Date(event.tgl_selesai).toLocaleDateString('id-ID')}` : ''}
                    </p>
                    <p class="card-text small"><i class="fas fa-map-marker-alt"></i> ${event.lokasi}</p>
                    <div class="mt-auto">
                        <button class="btn btn-sm btn-outline-primary mr-1" onclick="showDetailEvent(${event.id})">Detail</button>
                        <button class="btn btn-sm btn-outline-warning mr-1" onclick="editEvent(${event.id})">Edit</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteEvent(${event.id})">Hapus</button>
                    </div>
                </div>
            </div>
        </div>`;
    });
    container.innerHTML = html;
}

// Fungsi render tabel event
function renderEventTable(events) {
    const tbody = document.getElementById('eventTableBody');
    if (events.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>';
        return;
    }

    let html = '';
    events.forEach((event, index) => {
        const startDate = new Date(event.tgl_mulai).toLocaleDateString('id-ID');
        const endDate = event.tgl_selesai ? new Date(event.tgl_selesai).toLocaleDateString('id-ID') : '-';
        const tanggal = `${startDate} ${event.tgl_selesai ? ' - ' + endDate : ''}`;

        html += `
        <tr>
            <td>${index + 1}</td>
            <td>${event.gambar ? `<img src="../uploads/${event.gambar}" width="50" class="img-thumbnail">` : '-'}</td>
            <td>${event.nama_event}</td>
            <td><span class="badge badge-${getBadgeColor(event.kategori)}">${event.kategori}</span></td>
            <td>${tanggal}</td>
            <td>${event.lokasi}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="showDetailEvent(${event.id})"><i class="fas fa-eye"></i></button>
                <button class="btn btn-sm btn-warning" onclick="editEvent(${event.id})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-danger" onclick="deleteEvent(${event.id})"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

// Warna badge berdasarkan kategori
function getBadgeColor(kategori) {
    const colors = {
        'Art': 'info',
        'Music': 'success',
        'Workshop': 'warning',
        'Festival': 'danger',
        'Education': 'primary',
        'Sports': 'secondary'
    };
    return colors[kategori] || 'light';
}

// Fungsi load statistik
function loadStatistics() {
    fetch('../FormData/EventFunction.php?action=statistics')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('statTotalEvent').textContent = data.data.total;
                document.getElementById('statEventAktif').textContent = data.data.aktif;
            }
        });
}

// Fungsi lainnya (showDetailEvent, editEvent, deleteEvent, updateEvent) bisa ditambahkan di sini

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    loadEvents();

    // Event listener untuk filter
    document.getElementById('filterData').addEventListener('click', function() {
        const kategori = document.getElementById('filterKategori').value;
        const tanggal = document.getElementById('filterTanggal').value;
        const search = document.getElementById('searchEvent').value;
        loadEvents(kategori, tanggal, search);
    });

    document.getElementById('resetFilter').addEventListener('click', function() {
        document.getElementById('filterKategori').value = '';
        document.getElementById('filterTanggal').value = '';
        document.getElementById('searchEvent').value = '';
        loadEvents();
    });

    // Generate sample data
    document.getElementById('generateSampleEvents')?.addEventListener('click', function() {
        if (confirm('Yakin ingin generate data sample?')) {
            fetch('../FormData/EventFunction.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'generate_sample' })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                loadEvents();
            });
        }
    });
});