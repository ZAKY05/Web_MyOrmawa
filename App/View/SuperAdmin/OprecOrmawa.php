<?php include('../SuperAdmin/Header.php'); ?>
<div class="form-builder-container">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="/recruitment">Recruitment</a></li>
                        <li class="breadcrumb-item active">Form Builder</li>
                    </ol>
                </nav>
                <h2 class="mb-1">Form Builder</h2>
                <p class="text-muted">Buat formulir recruitment yang dapat disesuaikan</p>
            </div>
        </div>

        <div class="row">
            <!-- Form Elements Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="builder-card h-100">
                    <div class="builder-header">
                        <h5><i class="fas fa-cube"></i>Form Elements</h5>
                    </div>
                    <div class="p-3">
                        <div class="field-type-selector">
                            <h6>Input Types</h6>
                            <div class="d-flex flex-wrap justify-content-center">
                                <span class="field-type-btn" onclick="addField('text')">
                                    <i class="fas fa-font"></i>
                                    Text
                                </span>
                                <span class="field-type-btn" onclick="addField('number')">
                                    <i class="fas fa-hashtag"></i>
                                    Number
                                </span>
                                <span class="field-type-btn" onclick="addField('file')">
                                    <i class="fas fa-paperclip"></i>
                                    File
                                </span>
                                <span class="field-type-btn" onclick="addField('textarea')">
                                    <i class="fas fa-align-left"></i>
                                    Textarea
                                </span>
                                <span class="field-type-btn" onclick="addField('radio')">
                                    <i class="fas fa-dot-circle"></i>
                                    Radio
                                </span>
                                <span class="field-type-btn" onclick="addField('select')">
                                    <i class="fas fa-list"></i>
                                    Select
                                </span>
                            </div>
                        </div>

                        <div class="stats-card">
                            <i class="fas fa-layer-group fa-2x mb-2"></i>
                            <h6>Total Fields</h6>
                            <h4 id="fieldCount">0</h4>
                        </div>

                        <?php if (isset($totalForms)) : ?>
                            <div class="mt-3 text-center">
                                <small class="text-muted">Total Forms: <?= $totalForms ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Form Builder -->
            <div class="col-lg-6 col-md-8">
                <div class="builder-card">
                    <div class="builder-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5><i class="fas fa-tools"></i>Form Builder</h5>
                                <small>Drag & drop form elements untuk membuat formulir recruitment</small>
                            </div>
                            <div>
                                <button class="btn-action btn-outline" onclick="previewForm()">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                                <button class="btn-action btn-success-custom" onclick="saveForm()">
                                    <i class="fas fa-save"></i>
                                    <?= isset($mode) && $mode === 'edit' ? 'Update' : 'Save' ?> Form
                                </button>
                                <?php if (isset($mode) && $mode === 'edit') : ?>
                                    <a href="/form-builder" class="btn-action btn-outline">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include('../SuperAdmin/Footer.php'); ?>