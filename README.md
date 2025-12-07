# MyOrmawa - Sistem Manajemen Organisasi Mahasiswa

MyOrmawa adalah aplikasi berbasis web yang komprehensif yang dirancang untuk mengelola organisasi mahasiswa (Ormawa) di perguruan tinggi, terutama dikembangkan untuk Politeknik Negeri Jember. Sistem ini menyediakan platform terpusat untuk mengelola berbagai organisasi mahasiswa, acara, kompetisi, proses rekrutmen, dan pelacakan kehadiran.

## Daftar Isi
- [Ikhtisar](#ikhtisar)
- [Fitur-fitur](#fitur-fitur)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Struktur Proyek](#struktur-proyek)
- [Instalasi](#instalasi)
- [Struktur Database](#struktur-database)
- [Endpoint API](#endpoint-api)
- [Penggunaan](#penggunaan)
- [Kontribusi](#kontribusi)
- [Lisensi](#lisensi)

## Ikhtisar

MyOrmawa dibangun untuk menyederhanakan pengelolaan organisasi mahasiswa di perguruan tinggi. Platform ini menawarkan berbagai tingkatan akses untuk Super Admin, Admin, dan pengguna biasa, dengan fitur-fitur untuk mengelola acara, kompetisi, proses rekrutmen, dan sistem kehadiran. Sistem ini menggunakan mekanisme kontrol akses berbasis peran untuk memastikan otorisasi yang tepat di berbagai tingkatan pengguna.

## Fitur-fitur

### Fitur Inti
- **Manajemen Pengguna Multi-tingkat**: Super Admin, Admin, Pengurus, dan tingkatan Member biasa
- **Manajemen ORMAWA**: Kelola berbagai organisasi mahasiswa dengan detail, kategori, dan informasi mereka
- **Manajemen Acara**: Buat dan kelola acara untuk berbagai organisasi
- **Manajemen Kompetisi**: Tangani kompetisi dengan pendaftaran, panduan, dan materi
- **Sistem Kehadiran**: Sistem check-in berbasis kode QR dengan validasi lokasi
- **Manajemen Rekrutmen**: Sistem Open Recruitment (OpRec) untuk anggota organisasi dan panitia acara
- **Manajemen Dokumen**: Kelola dokumen yang terkait dengan organisasi dan kegiatan
- **Verifikasi Email**: Verifikasi email berbasis OTP untuk pendaftaran pengguna
- **Ekspor ke Excel**: Ekspor pengajuan dan data ke format Excel

### Otentikasi & Keamanan
- Pendaftaran pengguna dengan verifikasi email
- Fungsi reset kata sandi
- Kontrol akses multi-tingkat
- Manajemen sesi
- Verifikasi berbasis OTP untuk operasi sensitif

### Sistem Kehadiran
- Sistem check-in berbasis kode QR
- Validasi berbasis lokasi menggunakan koordinat GPS
- Pelacakan riwayat check-in
- Perhitungan jarak dari lokasi yang ditentukan
- Dukungan untuk skenario check-in online dan offline

### Pembuat Formulir & Pengajuan
- Pembuat formulir dinamis untuk rekrutmen
- Manajemen pengajuan dengan sistem persetujuan/penolakan
- Ekspor pengajuan formulir ke Excel
- Dukungan unggah file untuk pengajuan
- Pelacakan status (pending/disetujui/ditolak)

## Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Frontend**: HTML5, CSS3, JavaScript (Bootstrap 5, jQuery)
- **Database**: MySQL
- **Framework**: Framework PHP kustom dengan komponen UI Bootstrap
- **Library Eksternal**:
  - PHPMailer: Untuk fungsionalitas email
  - PhpSpreadsheet: Untuk fungsionalitas ekspor Excel
  - Bootstrap: Untuk komponen UI responsive
  - Font Awesome: Untuk ikon
  - Chart.js: Untuk visualisasi data
  - DataTables: Untuk tabel yang responsive
- **API**: Endpoint API RESTful untuk berbagai fungsionalitas
- **Otentikasi**: Otentikasi berbasis sesi dan token

## Struktur Proyek

```
MyOrmawa/
├── API/                     # Endpoint REST API
│   ├── attendance.php       # Fungsionalitas kehadiran
│   ├── auth.php             # Endpoint otentikasi
│   ├── calendar.php         # Acara kalender
│   └── competition.php      # Manajemen kompetisi
├── App/                     # View aplikasi dan logika
│   ├── View/                # File view untuk berbagai tingkatan pengguna
│   │   ├── LandingPage/     # Halaman landing publik
│   │   ├── SuperAdmin/      # Antarmuka super admin
│   │   ├── Admin/           # Antarmuka admin organisasi
│   │   ├── User/            # Antarmuka pengurus
│   │   └── Member/          # Antarmuka member biasa
├── Asset/                   # Aset CSS, JavaScript, dan gambar
├── Config/                  # File konfigurasi
├── Function/                # Fungsi dan utilitas PHP
├── includes/                # Fungsi dan utilitas pembantu
├── logs/                    # File log
├── uploads/                 # Upload file (gambar, dokumen)
├── vendor/                  # Dependensi Composer
├── composer.json            # Dependensi proyek
├── .htaccess               # Konfigurasi Apache
└── index.php               # Titik masuk
```

## Instalasi

1. **Prasyarat**
   - Web Server Apache (misalnya XAMPP, WAMP, LAMPP)
   - PHP 7.4 atau lebih tinggi
   - MySQL 5.7 atau lebih tinggi
   - Composer

2. **Instruksi Instalasi**

   ```bash
   # Clone repository
   git clone [repository-url]

   # Masuk ke direktori proyek
   cd MyOrmawa

   # Instal dependensi
   composer install
   ```

3. **Setup Database**
   - Buat database MySQL (misalnya `myormawa_db_complete`)
   - Impor skema database (jika disediakan)
   - Perbarui kredensial database di `Config/ConnectDB.php`

4. **Konfigurasi**
   - Atur kredensial database di `Config/ConnectDB.php`
   - Konfigurasi pengaturan email di `includes/email_sender.php`
   - Atur izin file yang tepat untuk direktori upload

5. **Jalankan Aplikasi**
   - Mulai web server Anda
   - Akses aplikasi melalui `http://localhost/MyOrmawa/`
   - Halaman landing akan mengarahkan ke halaman login yang sesuai

## Struktur Database

Aplikasi ini menggunakan database MySQL dengan tabel-tabel utama berikut:

- `user` - Menyimpan informasi pengguna dengan berbagai tingkatan akses
- `ormawa` - Berisi data organisasi mahasiswa
- `event` - Informasi acara untuk organisasi
- `kehadiran` - Sesi kehadiran untuk check-in
- `absensi_log` - Log dan catatan check-in
- `kompetisi` - Manajemen kompetisi
- `lokasi_absen` - Data lokasi kehadiran
- `form_info` - Template formulir untuk rekrutmen
- `form` - Bidang formulir individu
- `submit` - Data pengajuan formulir
- `dokumen` - Manajemen dokumen
- `otp_codes` - Kode sandi satu kali untuk verifikasi
- `login_sessions` - Manajemen sesi pengguna

## Endpoint API

### API Otentikasi (`API/auth.php`)
- `POST /auth.php?action=login` - Login pengguna
- `POST /auth.php?action=register` - Pendaftaran pengguna
- `POST /auth.php?action=verify_otp` - Verifikasi OTP
- `POST /auth.php?action=forgot_password` - Permintaan reset kata sandi
- `POST /auth.php?action=reset_password` - Reset kata sandi
- `POST /auth.php?action=change_password` - Ganti kata sandi
- `POST /auth.php?action=change_email` - Ganti alamat email
- `POST /auth.php?action=resend_otp` - Kirim ulang OTP

### API Kehadiran (`API/attendance.php`)
- `POST /attendance.php?action=verify_qr` - Verifikasi kode QR untuk check-in
- `POST /attendance.php?action=check_in` - Lakukan check-in
- `GET /attendance.php?action=get_history&user_id={id}` - Dapatkan riwayat check-in

### API Kalender (`API/calendar.php`)
- `GET /calendar.php` - Dapatkan acara-acara mendatang untuk kalender

### API Kompetisi (`API/competition.php`)
- `GET /competition.php` - Dapatkan semua kompetisi mendatang
- `GET /competition.php?id={id}` - Dapatkan kompetisi spesifik
- `GET /competition.php?ormawa_id={id}` - Dapatkan kompetisi berdasarkan ORMWA
- `POST /competition.php` - Buat kompetisi baru
- `PUT /competition.php` - Perbarui kompetisi
- `DELETE /competition.php` - Hapus kompetisi

## Penggunaan

### Pendaftaran Pengguna
1. Akses halaman pendaftaran
2. Isi informasi yang diperlukan (NIM, nama lengkap, email, program studi)
3. Verifikasi email melalui OTP yang dikirim ke alamat email yang disediakan

### Sistem Kehadiran
1. Penyelenggara membuat sesi kehadiran dengan kode QR
2. Peserta memindai kode QR menggunakan aplikasi kehadiran
3. Sistem memvalidasi lokasi (jika diperlukan) dan mencatat check-in
4. Admin dapat melihat laporan dan riwayat kehadiran

### Proses Rekrutmen
1. Admin membuat formulir rekrutmen dengan berbagai jenis pertanyaan
2. Kandidat mengisi formulir secara online
3. Admin meninjau pengajuan dan menyetujui/menolak aplikasi
4. Pengajuan dapat diekspor ke Excel untuk pemrosesan lebih lanjut

### Manajemen Acara
- Super Admin mengelola semua organisasi dan acara mereka
- Admin dapat membuat dan mengelola acara untuk organisasi spesifik mereka
- Pengguna dapat melihat acara dan berpartisipasi di dalamnya

## Kontribusi

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan Anda (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buka Pull Request

## Lisensi

Proyek ini dilisensikan di bawah Lisensi MIT - lihat file [LICENSE](LICENSE) untuk detailnya.

## Dukungan

Untuk dukungan, email myormawa@gmail.com atau buat issue di repository.

---
*Dikembangkan dengan ❤️ untuk organisasi mahasiswa Politeknik Negeri Jember*