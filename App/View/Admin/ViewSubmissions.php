<?php
// App/View/Admin/ViewSubmissions.php

function displaySubmissionsForForm($koneksi, $form_info_id, $current_user_id) {
    // 1. Validasi Kepemilikan Form
    $check_owner_query = "SELECT id FROM form_info WHERE id = ? AND user_id = ?";
    $stmt_check = $koneksi->prepare($check_owner_query);
    $stmt_check->bind_param("ii", $form_info_id, $current_user_id);
    $stmt_check->execute();
    $owner_result = $stmt_check->get_result();
    if ($owner_result->num_rows === 0) {
        echo "<div class='alert alert-danger'>Anda tidak memiliki izin untuk melihat submission ini.</div>";
        return;
    }
    $stmt_check->close();

    // 2. Logika yang sama dengan SuperAdmin/ViewSubmissions.php
    // (Anda bisa menyalin-tempel kode dari SuperAdmin/ViewSubmissions.php ke sini,
    // karena logikanya sudah benar setelah kepemilikan divalidasi)
    if (!is_numeric($form_info_id) || $form_info_id <= 0) {
        echo "<p class='text-danger'>Invalid form ID.</p>";
        return;
    }

    // --- AMBIL FILTER STATUS DARI REQUEST ---
    $filter_status = isset($_GET['status']) ? $_GET['status'] : 'all'; // Default 'all'
    $allowed_filters = ['all', 'approved', 'rejected', 'pending'];
    if (!in_array($filter_status, $allowed_filters)) {
        $filter_status = 'all';
    }
    // --- SAMPAI SINI ---

    // Ambil detail formulir (untuk judul halaman)
    $form_detail_query = "SELECT id, judul FROM form_info WHERE id = ? LIMIT 1";
    $stmt = $koneksi->prepare($form_detail_query);
    if (!$stmt) {
        echo "<p class='text-danger'>Error preparing statement for form info: " . htmlspecialchars($koneksi->error) . "</p>";
        return;
    }
    $stmt->bind_param("i", $form_info_id);
    $stmt->execute();
    $form_detail = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$form_detail) {
        echo "<div class='alert alert-warning'>Form tidak ditemukan.</div>";
        return;
    }

    // Ambil semua submission untuk form_info_id ini
    // --- PERUBAHAN: Tambahkan kondisi WHERE untuk filter status ---
    $status_condition = "";
    if ($filter_status !== 'all') {
        $status_condition = " AND s.status = ?";
    }

    $all_submissions_query = "
        SELECT 
            s.id as submission_id,
            s.user_id, 
            u.nama as user_nama, 
            u.nim, 
            u.username, 
            u.email, 
            s.form_id, 
            s.field_name, 
            s.field_value, 
            f.label as field_label,
            f.tipe as field_type,
            f.id as field_id,
            s.status as submission_status
        FROM submit s
        INNER JOIN user u ON s.user_id = u.id
        INNER JOIN form f ON s.form_id = f.id AND s.field_name = f.nama
        WHERE f.form_info_id = ? $status_condition
        ORDER BY s.user_id ASC, f.id ASC
    ";
    // --- SAMPAI SINI ---

    $params = [$form_info_id];
    $types = "i";

    if ($filter_status !== 'all') {
        $params[] = $filter_status;
        $types .= "s";
    }

    $stmt = $koneksi->prepare($all_submissions_query);
    if (!$stmt) {
        echo "<p class='text-danger'>Error preparing statement for submissions: " . htmlspecialchars($koneksi->error) . "</p>";
        return;
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result_all_submissions = $stmt->get_result();
    $all_submissions_raw = [];
    while ($row = $result_all_submissions->fetch_assoc()) {
        $all_submissions_raw[] = $row;
    }
    $stmt->close();

    // Organisir jawaban berdasarkan user_id
    $organized_submissions = [];
    $unique_users = [];
    foreach ($all_submissions_raw as $sub) {
        $user_id = $sub['user_id'];

        if (!isset($unique_users[$user_id])) {
            $unique_users[$user_id] = [
                'nama'      => $sub['user_nama'],
                'nim'       => $sub['nim'],
                'username'  => $sub['username'],
                'email'     => $sub['email']
            ];
            $organized_submissions[$user_id] = [];
        }

        $organized_submissions[$user_id][] = [
            'field_name'    => $sub['field_name'],
            'field_label'   => $sub['field_label'],
            'field_value'   => $sub['field_value'],
            'field_type'    => $sub['field_type'],
            'status'        => $sub['submission_status']
        ];
    }

    // --- FUNGSI UNTUK MEMBANGUN URL DENGAN FILTER ---
    // Fungsi ini membangun kembali URL saat ini dengan parameter status yang baru
    // Ini penting agar filter bekerja tanpa mengacaukan parameter lain
    function buildFilterUrl($new_status) {
        $current_params = $_GET; // Ambil semua parameter GET saat ini
        $current_params['status'] = $new_status; // Ganti atau tambahkan parameter status
        $current_params = array_filter($current_params, function($v) { return $v !== ''; }); // Hapus nilai kosong
        return '?' . http_build_query($current_params);
    }
    // --- SAMPAI SINI ---

    // Render HTML Tabel dan Modal
    ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-users"></i> Submissions: <?= htmlspecialchars($form_detail['judul']) ?>
            </h6>
            <div class="d-flex">
                <!-- Tombol Kembali -->
                <!-- CATATAN: Ganti 'page=oprec' dengan parameter yang sesuai untuk kembali ke edit form Anda -->
                <a href="?page=oprec&form_id=<?= $form_info_id ?>" class="btn btn-info btn-sm mr-2" title="Kembali ke Edit Form">
                    <i class="fas fa-edit"></i> Edit Form
                </a>
                <!-- Filter Status -->
                <div class="btn-group btn-group-sm" role="group">
                    <a href="<?= buildFilterUrl('all') ?>" 
                       class="btn <?= $filter_status === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">Semua</a>
                    <a href="<?= buildFilterUrl('pending') ?>" 
                       class="btn <?= $filter_status === 'pending' ? 'btn-warning' : 'btn-outline-secondary' ?>">Pending</a>
                    <a href="<?= buildFilterUrl('approved') ?>" 
                       class="btn <?= $filter_status === 'approved' ? 'btn-success' : 'btn-outline-secondary' ?>">Approved</a>
                    <a href="<?= buildFilterUrl('rejected') ?>" 
                       class="btn <?= $filter_status === 'rejected' ? 'btn-danger' : 'btn-outline-secondary' ?>">Rejected</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($unique_users)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                    <?php if ($filter_status !== 'all'): ?>
                        <p class="text-gray-500">Tidak ada pengguna dengan status <strong><?= ucfirst($filter_status) ?></strong> untuk formulir ini.</p>
                        <a href="<?= buildFilterUrl('all') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> Lihat Semua
                        </a>
                    <?php else: ?>
                        <p class="text-gray-500">Belum ada pengguna yang mengisi formulir ini.</p>
                        <a href="?page=oprec&form_id=<?= $form_info_id ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-edit"></i> Edit Form
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="submissionsTable" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Pengguna</th>
                                <th width="15%">NIM</th>
                                <th width="15%">Username</th>
                                <th width="20%">Email</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="10%" class="text-center">Jawaban</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach ($unique_users as $user_id => $user_info):
                                $submissions = $organized_submissions[$user_id] ?? [];
                                $modalId = 'viewSubmissionModal_' . $user_id;
                                $total_answers = count($submissions);
                                $deleteModalId = 'deleteSubmissionModal_' . $user_id . '_' . $form_info_id;
                                $approveModalId = 'approveSubmissionModal_' . $user_id . '_' . $form_info_id;
                                $rejectModalId = 'rejectSubmissionModal_' . $user_id . '_' . $form_info_id;
                                
                                $current_status = $submissions[0]['status'] ?? 'pending';
                            ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><i class="fas fa-user-circle text-primary"></i> <?= htmlspecialchars($user_info['nama']) ?></td>
                                    <td><?= htmlspecialchars($user_info['nim']) ?></td>
                                    <td><?= htmlspecialchars($user_info['username']) ?></td>
                                    <td><?= htmlspecialchars($user_info['email']) ?></td>
                                    <td class="text-center">
                                        <?php if ($current_status === 'approved'): ?>
                                            <span class="badge badge-success">Approved</span>
                                        <?php elseif ($current_status === 'rejected'): ?>
                                            <span class="badge badge-danger">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-primary badge-pill" style="font-size: 0.9rem; padding: 0.4rem 0.8rem;">
                                            <?= $total_answers ?> Jawaban
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#<?= $modalId ?>" title="Lihat Detail Submission">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($current_status === 'pending'): ?>
                                            <button type="button" class="btn btn-success btn-sm ml-1" 
                                                    data-toggle="modal"
                                                    data-target="#<?= $approveModalId ?>"
                                                    title="Approve Submission">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm ml-1" 
                                                    data-toggle="modal"
                                                    data-target="#<?= $rejectModalId ?>"
                                                    title="Reject Submission">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-info btn-sm ml-1" 
                                                    data-toggle="modal"
                                                    data-target="#<?= $approveModalId ?>"
                                                    title="Approve Submission">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm ml-1" 
                                                    data-toggle="modal"
                                                    data-target="#<?= $rejectModalId ?>"
                                                    title="Reject Submission">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-danger btn-sm ml-1" 
                                                data-toggle="modal"
                                                data-target="#<?= $deleteModalId ?>"
                                                title="Hapus Submission">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="<?= $deleteModalId ?>" tabindex="-1" role="dialog" aria-labelledby="deleteSubmissionModalLabel_<?= $user_id ?>_<?= $form_info_id ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteSubmissionModalLabel_<?= $user_id ?>_<?= $form_info_id ?>">Konfirmasi Hapus Submission</h5>
                                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus submission dari <strong>"<?= htmlspecialchars($user_info['nama']) ?>"</strong>?
                                                <br><br>
                                                <span class="text-danger">Semua jawaban dari user ini untuk formulir ini akan dihapus!</span>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                                                <a class="btn btn-danger" href="../../../Function/SubmissionFunction.php?action=delete_user_submissions&user_id=<?= $user_id ?>&form_info_id=<?= $form_info_id ?>">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="<?= $approveModalId ?>" tabindex="-1" role="dialog" aria-labelledby="approveSubmissionModalLabel_<?= $user_id ?>_<?= $form_info_id ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="approveSubmissionModalLabel_<?= $user_id ?>_<?= $form_info_id ?>">Konfirmasi Approve Submission</h5>
                                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin <strong>menyetujui</strong> submission dari <strong>"<?= htmlspecialchars($user_info['nama']) ?>"</strong>?
                                                <br><br>
                                                <span class="text-success">Status akan diubah menjadi 'Approved'.</span>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                                                <a class="btn btn-success" href="../../../Function/SubmissionFunction.php?action=approve_user_submissions&user_id=<?= $user_id ?>&form_info_id=<?= $form_info_id ?>">
                                                    <i class="fas fa-check"></i> Approve
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="<?= $rejectModalId ?>" tabindex="-1" role="dialog" aria-labelledby="rejectSubmissionModalLabel_<?= $user_id ?>_<?= $form_info_id ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="rejectSubmissionModalLabel_<?= $user_id ?>_<?= $form_info_id ?>">Konfirmasi Reject Submission</h5>
                                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin <strong>menolak</strong> submission dari <strong>"<?= htmlspecialchars($user_info['nama']) ?>"</strong>?
                                                <br><br>
                                                <span class="text-danger">Status akan diubah menjadi 'Rejected'.</span>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                                                <a class="btn btn-warning" href="../../../Function/SubmissionFunction.php?action=reject_user_submissions&user_id=<?= $user_id ?>&form_info_id=<?= $form_info_id ?>">
                                                    <i class="fas fa-times"></i> Reject
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                foreach ($unique_users as $user_id => $user_info):
                    $submissions = $organized_submissions[$user_id] ?? [];
                    $modalId = 'viewSubmissionModal_' . $user_id;
                    $total_answers = count($submissions);
                    $current_status = $submissions[0]['status'] ?? 'pending';
                ?>
                    <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabel_<?= $user_id ?>" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="modalLabel_<?= $user_id ?>">
                                        <i class="fas fa-clipboard-check"></i> Detail Submission: <?= htmlspecialchars($user_info['nama']) ?>
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="card border-left-primary shadow mb-3">
                                        <div class="card-body py-3">
                                            <div class="row">
                                                <div class="col-md-3"><strong>Nama:</strong> <?= htmlspecialchars($user_info['nama']) ?></div>
                                                <div class="col-md-3"><strong>NIM:</strong> <?= htmlspecialchars($user_info['nim']) ?></div>
                                                <div class="col-md-3"><strong>Username:</strong> <?= htmlspecialchars($user_info['username']) ?></div>
                                                <div class="col-md-3"><strong>Email:</strong> <?= htmlspecialchars($user_info['email']) ?></div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-md-12">
                                                    <strong>Status:</strong>
                                                    <?php if ($current_status === 'approved'): ?>
                                                        <span class="badge badge-success">Approved</span>
                                                    <?php elseif ($current_status === 'rejected'): ?>
                                                        <span class="badge badge-danger">Rejected</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">Pending</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="font-weight-bold text-primary mb-3">
                                        <i class="fas fa-clipboard-list"></i> Jawaban Formulir 
                                        <span class="badge badge-info ml-2"><?= $total_answers ?> Jawaban</span>
                                    </h6>
                                    
                                    <?php if (empty($submissions)): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Tidak ada jawaban yang tersedia.
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th width="5%" class="text-center">No</th>
                                                        <th width="30%">Pertanyaan</th>
                                                        <th width="65%">Jawaban</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($submissions as $index => $submission): ?>
                                                        <tr>
                                                            <td class="text-center align-middle">
                                                                <strong><?= $index + 1 ?></strong>
                                                            </td>
                                                            <td class="align-middle">
                                                                <?= htmlspecialchars($submission['field_label']) ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $value = $submission['field_value'];
                                                                $field_type = $submission['field_type'] ?? '';
                                                                
                                                                if ($field_type === 'file' || preg_match('/\.(jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt|zip|rar)$/i', $value)) {
                                                                    $file_path = "../../../uploads/submissions/" . basename($value);
                                                                    
                                                                    if (file_exists($file_path)) {
                                                                        $file_ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));
                                                                        $file_name = basename($value);
                                                                        
                                                                        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                                            echo '<div class="text-center mb-2">';
                                                                            echo '<a href="' . htmlspecialchars($file_path) . '" target="_blank">';
                                                                            echo '<img src="' . htmlspecialchars($file_path) . '" class="img-fluid rounded" style="max-height: 200px;">';
                                                                            echo '</a></div>';
                                                                            echo '<a href="' . htmlspecialchars($file_path) . '" class="btn btn-sm btn-primary" download>';
                                                                            echo '<i class="fas fa-download"></i> Download Image</a>';
                                                                        } else {
                                                                            $file_icons = [
                                                                                'pdf' => 'fa-file-pdf text-danger',
                                                                                'doc' => 'fa-file-word text-primary',
                                                                                'docx' => 'fa-file-word text-primary',
                                                                                'xls' => 'fa-file-excel text-success',
                                                                                'xlsx' => 'fa-file-excel text-success',
                                                                                'txt' => 'fa-file-alt text-secondary',
                                                                                'zip' => 'fa-file-archive text-warning',
                                                                                'rar' => 'fa-file-archive text-warning'
                                                                            ];
                                                                            $icon = $file_icons[$file_ext] ?? 'fa-file';
                                                                            
                                                                            echo '<i class="fas ' . $icon . ' fa-2x mr-2"></i>';
                                                                            echo '<span>' . htmlspecialchars($file_name) . '</span><br>';
                                                                            echo '<a href="' . htmlspecialchars($file_path) . '" class="btn btn-sm btn-primary mt-2" download>';
                                                                            echo '<i class="fas fa-download"></i> Download</a>';
                                                                        }
                                                                    } else {
                                                                        echo '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> File tidak ditemukan</span>';
                                                                    }
                                                                } else {
                                                                    if (filter_var($value, FILTER_VALIDATE_URL)) {
                                                                        echo '<a href="' . htmlspecialchars($value) . '" target="_blank">';
                                                                        echo '<i class="fas fa-link"></i> ' . htmlspecialchars($value) . '</a>';
                                                                    } else {
                                                                        echo nl2br(htmlspecialchars($value));
                                                                    }
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        <i class="fas fa-times"></i> Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </div>

    <script>
        // Initialize DataTable
        <?php if (!empty($unique_users)): ?>
        $(document).ready(function() {
            $('#submissionsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                },
                "pageLength": 10,
                "order": [[0, "asc"]],
                "columnDefs": [
                    { "orderable": false, "targets": [5, 6, 7] }
                ]
            });
        });
        <?php endif; ?>
    </script>
    <?php
}
?>
