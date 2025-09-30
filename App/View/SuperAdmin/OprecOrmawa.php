<?php include('../SuperAdmin/Header.php'); ?>

<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-users text-primary"></i>
                Form Builder Recruitment
            </h1>
            <p class="page-subtitle">Buat formulir aplikasi yang sempurna untuk kandidat</p>
        </div>
        <div class="action-buttons">
            <button class="btn btn-outline-secondary">
                <i class="fas fa-eye me-2"></i>Preview
            </button>
            <button class="btn btn-warning">
                <i class="fas fa-save me-2"></i>Simpan Draft
            </button>
            <button class="btn btn-success">
                <i class="fas fa-paper-plane me-2"></i>Publikasikan
            </button>
        </div>
    </div>

    <!-- Form Builder -->
    <div class="form-builder-wrapper">
        <!-- Components Panel -->
        <div class="components-panel">
            <h6 class="components-title">
                <i class="fas fa-puzzle-piece"></i>
                Komponen Form
            </h6>

            <div class="component-group">
                <div class="component-group-title">Field Dasar</div>
                <div class="component-item" data-type="text">
                    <i class="fas fa-font"></i>
                    <span>Text Input</span>
                </div>
                <div class="component-item" data-type="textarea">
                    <i class="fas fa-align-left"></i>
                    <span>Textarea</span>
                </div>
                <div class="component-item" data-type="email">
                    <i class="fas fa-envelope"></i>
                    <span>Email</span>
                </div>
                <div class="component-item" data-type="phone">
                    <i class="fas fa-phone"></i>
                    <span>Nomor Telepon</span>
                </div>
                <div class="component-item" data-type="date">
                    <i class="fas fa-calendar"></i>
                    <span>Tanggal</span>
                </div>
            </div>

            <div class="component-group">
                <div class="component-group-title">Field Pilihan</div>
                <div class="component-item" data-type="select">
                    <i class="fas fa-list"></i>
                    <span>Dropdown</span>
                </div>
                <div class="component-item" data-type="radio">
                    <i class="fas fa-dot-circle"></i>
                    <span>Radio Button</span>
                </div>
                <div class="component-item" data-type="checkbox">
                    <i class="fas fa-check-square"></i>
                    <span>Checkbox</span>
                </div>
            </div>

            <div class="component-group">
                <div class="component-group-title">Field Khusus</div>
                <div class="component-item" data-type="file">
                    <i class="fas fa-file-upload"></i>
                    <span>Upload File</span>
                </div>
                <div class="component-item" data-type="rating">
                    <i class="fas fa-star"></i>
                    <span>Rating</span>
                </div>
                <div class="component-item" data-type="header">
                    <i class="fas fa-heading"></i>
                    <span>Section Header</span>
                </div>
            </div>
        </div>

        <!-- Form Canvas -->
        <div class="form-canvas">
            <div class="canvas-header">
                <h6 class="settings-title">
                    <i class="fas fa-cog"></i>
                    Pengaturan Form
                </h6>
                <div class="form-settings-grid">
                    <div>
                        <label class="form-label">Judul Form</label>
                        <input type="text" class="form-control" value="Aplikasi Lowongan Kerja">
                    </div>
                    <div>
                        <label class="form-label">Posisi</label>
                        <input type="text" class="form-control" placeholder="e.g. Software Developer">
                    </div>
                </div>
                <div>
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" rows="2">Silakan lengkapi formulir berikut untuk melamar posisi yang tersedia di perusahaan kami.</textarea>
                </div>
            </div>

            <div class="canvas-body" id="formCanvas">
                <div class="empty-canvas" id="emptyCanvas">
                    <i class="fas fa-mouse-pointer"></i>
                    <h6>Drag & Drop komponen dari sidebar untuk memulai</h6>
                    <p>Atau klik komponen untuk menambahkannya ke form</p>
                </div>

                <!-- Sample fields (hidden initially) -->
                <div id="sampleFields" style="display: none;">
                    <div class="form-field">
                        <div class="field-controls">
                            <button class="btn btn-outline-primary btn-icon">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-icon">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="Masukkan nama lengkap Anda">
                        <small class="form-text">Nama sesuai dengan dokumen resmi</small>
                    </div>

                    <div class="form-field">
                        <div class="field-controls">
                            <button class="btn btn-outline-primary btn-icon">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-icon">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" placeholder="email@example.com">
                    </div>

                    <div class="form-field">
                        <div class="field-controls">
                            <button class="btn btn-outline-primary btn-icon">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-icon">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <label class="form-label">Posisi yang Dilamar <span class="text-danger">*</span></label>
                        <select class="form-select">
                            <option>Pilih posisi...</option>
                            <option>Frontend Developer</option>
                            <option>Backend Developer</option>
                            <option>UI/UX Designer</option>
                            <option>Product Manager</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <div class="field-controls">
                            <button class="btn btn-outline-primary btn-icon">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-icon">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <label class="form-label">Upload CV <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" accept=".pdf,.doc,.docx">
                        <small class="form-text">Format: PDF, DOC, DOCX (Max 5MB)</small>
                    </div>

                    <div class="form-field">
                        <div class="field-controls">
                            <button class="btn btn-outline-primary btn-icon">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-icon">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <label class="form-label">Pengalaman Kerja</label>
                        <textarea class="form-control" rows="4" placeholder="Ceritakan pengalaman kerja Anda..."></textarea>
                    </div>
                </div>
            </div>

            <div class="canvas-actions">
                <button class="btn btn-primary" onclick="showSampleFields()">
                    <i class="fas fa-magic me-2"></i>Tampilkan Contoh Form
                </button>
                <button class="btn btn-outline-secondary" onclick="clearForm()">
                    <i class="fas fa-broom me-2"></i>Bersihkan Form
                </button>
            </div>
        </div>

        <!-- Properties Panel -->
        <div class="properties-panel">
            <h6 class="properties-title">
                <i class="fas fa-sliders-h"></i>
                Properties
            </h6>

            <div class="info-alert">
                <i class="fas fa-info-circle"></i>
                Pilih field untuk mengedit properties
            </div>

            <div class="stats-section">
                <h6 class="stats-title">
                    <i class="fas fa-chart-bar"></i>
                    Statistik Form
                </h6>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number text-primary" id="totalFields">0</div>
                        <div class="stat-label">Total Fields</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number text-success" id="requiredFields">0</div>
                        <div class="stat-label">Required Fields</div>
                    </div>
                </div>
            </div>

            <div class="mobile-preview">
                <h6 class="stats-title">
                    <i class="fas fa-mobile-alt"></i>
                    Mobile Preview
                </h6>
                <div class="preview-device">
                    <i class="fas fa-mobile-alt"></i>
                    <p class="text-muted mb-0 small">Preview akan muncul di sini</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
    function showSampleFields() {
        document.getElementById('emptyCanvas').style.display = 'none';
        document.getElementById('sampleFields').style.display = 'block';
        updateStats();
    }

    function clearForm() {
        document.getElementById('emptyCanvas').style.display = 'flex';
        document.getElementById('sampleFields').style.display = 'none';
        updateStats();
    }

    function updateStats() {
        const fields = document.querySelectorAll('#sampleFields .form-field');
        const requiredFields = document.querySelectorAll('#sampleFields .text-danger');

        document.getElementById('totalFields').textContent = fields.length;
        document.getElementById('requiredFields').textContent = requiredFields.length;
    }

    // Component interactions
    document.addEventListener('DOMContentLoaded', function() {
        const componentItems = document.querySelectorAll('.component-item');

        componentItems.forEach(item => {
            item.addEventListener('click', function() {
                // Visual feedback
                this.style.transform = 'scale(0.95)';
                this.style.background = '#bbdefb';

                setTimeout(() => {
                    this.style.transform = '';
                    this.style.background = '';
                }, 200);

                // Show a simple notification
                console.log(`Clicked: ${this.querySelector('span').textContent}`);
            });
        });

        // Form field selection
        document.addEventListener('click', function(e) {
            if (e.target.closest('.form-field')) {
                // Remove active state from all fields
                document.querySelectorAll('.form-field').forEach(field => {
                    field.style.borderColor = '#e9ecef';
                });

                // Add active state to clicked field
                const clickedField = e.target.closest('.form-field');
                clickedField.style.borderColor = '#2196f3';
                clickedField.style.boxShadow = '0 2px 12px rgba(33, 150, 243, 0.15)';
            }
        });
    });
</script>
<?php include('../SuperAdmin/Footer.php'); ?>