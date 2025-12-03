<?php
include('../SuperAdmin/Header.php');
include('../../../Config/ConnectDB.php');

// Fungsi-fungsi statistik
function getTotalEvents($koneksi) {
    $query = "SELECT COUNT(*) AS total FROM event";
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'];
}

function getTotalUsers($koneksi, $level = null) {
    $query = "SELECT COUNT(*) AS total FROM user";
    if ($level !== null) {
        $query .= " WHERE level = '$level'";
    }
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'];
}

function getTotalSubmissions($koneksi) {
    $query = "SELECT COUNT(*) AS total FROM submit";
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'];
}

function getOrmawaByCategory($koneksi) {
    $query = "SELECT kategori, COUNT(*) as jumlah FROM ormawa GROUP BY kategori";
    $result = mysqli_query($koneksi, $query);
    $data = [];
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function getRecentEvents($koneksi, $limit = 5) {
    $query = "SELECT e.nama_event, e.tgl_mulai, e.tgl_selesai, o.nama_ormawa FROM event e JOIN ormawa o ON e.ormawa_id = o.id ORDER BY e.id DESC LIMIT $limit";
    $result = mysqli_query($koneksi, $query);
    $data = [];
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function getTotalForms($koneksi) {
    $query = "SELECT COUNT(*) AS total FROM form_info";
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'];
}

function getTotalDocuments($koneksi) {
    $query = "SELECT COUNT(*) AS total FROM dokumen";
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'];
}

function getActiveEvents($koneksi) {
    $query = "SELECT COUNT(*) AS total FROM event WHERE tgl_mulai <= CURDATE() AND tgl_selesai >= CURDATE()";
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'];
}

function getUpcomingEvents($koneksi, $days = 7) {
    $query = "SELECT COUNT(*) AS total FROM event WHERE tgl_mulai BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL $days DAY)";
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'];
}

function getEventTrend($koneksi) {
    $query = "SELECT DATE_FORMAT(tgl_mulai, '%Y-%m') as bulan, COUNT(*) as jumlah FROM event GROUP BY bulan ORDER BY bulan";
    $result = mysqli_query($koneksi, $query);
    $data = [];
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function getUserDistribution($koneksi) {
    $query = "SELECT level, COUNT(*) as jumlah FROM user GROUP BY level";
    $result = mysqli_query($koneksi, $query);
    $data = [];
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function getTotalOrmawa($koneksi) {
    $query = "SELECT COUNT(*) AS total FROM ormawa";
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'];
}
?>

<div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
                    </div>
                    <!-- Content Row -->
                    <div class="row">

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Ormawa</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php
                                                echo getTotalOrmawa($koneksi);
                                            ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-building fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Total Dokumen</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php
                                                echo getTotalDocuments($koneksi);
                                            ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-pdf fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Event Aktif</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php
                                                echo getActiveEvents($koneksi);
                                            ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-play-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Requests Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Event Mendatang</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php
                                                echo getUpcomingEvents($koneksi);
                                            ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    

                    <!-- Content Row -->

                    <div class="row">

                        <!-- Area Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Tren Event per Bulan</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Dropdown Header:</div>
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="eventTrendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Distribusi User Berdasarkan Level</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Dropdown Header:</div>
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="userDistributionChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <?php
                                        $user_distribution = getUserDistribution($koneksi);
                                        $level_labels = [
                                            '1' => 'Super Admin',
                                            '2' => 'Admin Ormawa',
                                            '3' => 'Pengurus',
                                            '4' => 'Mahasiswa'
                                        ];
                                        foreach ($user_distribution as $index => $user_data) {
                                            $level = $user_data['level'];
                                            $level_name = $level_labels[$level] ?? 'Level ' . $level;
                                            $colors = ['text-primary', 'text-success', 'text-info', 'text-warning', 'text-danger', 'text-secondary'];
                                            $color_class = $colors[$index % count($colors)];
                                            echo '<span class="mr-2"><i class="fas fa-circle ' . $color_class . '"></i> ' . $level_name . '</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Content Row -->

                    <!-- Notifications Row -->
                    <div class="row">
                        <!-- Upcoming Events Card -->
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Event Mendatang</h6>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Query untuk mendapatkan event mendatang dalam 7 hari ke depan
                                    $upcoming_events_query = "SELECT e.nama_event, e.tgl_mulai, e.tgl_selesai, e.deskripsi, o.nama_ormawa
                                                              FROM event e
                                                              JOIN ormawa o ON e.ormawa_id = o.id
                                                              WHERE e.tgl_mulai BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                                                              ORDER BY e.tgl_mulai ASC";
                                    $upcoming_events_result = mysqli_query($koneksi, $upcoming_events_query);
                                    $upcoming_events = [];
                                    while($row = mysqli_fetch_assoc($upcoming_events_result)) {
                                        $upcoming_events[] = $row;
                                    }
                                    ?>
                                    <?php if (!empty($upcoming_events)): ?>
                                        <div class="list-group">
                                            <?php foreach ($upcoming_events as $event): ?>
                                                <a href="Index.php?page=event" class="list-group-item list-group-item-action">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($event['nama_event']); ?></h6>
                                                        <small><?php echo date('d M', strtotime($event['tgl_mulai'])); ?> - <?php echo date('d M', strtotime($event['tgl_selesai'])); ?></small>
                                                    </div>
                                                    <p class="mb-1"><?php echo htmlspecialchars($event['deskripsi']); ?></p>
                                                    <small>Oleh: <?php echo htmlspecialchars($event['nama_ormawa']); ?></small>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-3">
                                            <i class="fas fa-calendar-check fa-3x text-gray-300 mb-3"></i>
                                            <p class="text-gray-500">Tidak ada event mendatang dalam 7 hari ke depan.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Access Row -->
                    <div class="row">
                        <!-- Quick Access Card -->
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Akses Cepat</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Ormawa Management -->
                                        <div class="col-xl-2 col-md-4 mb-4">
                                            <div class="card border-left-primary shadow h-100 py-2">
                                                <a href="Index.php?page=ormawa" class="text-decoration-none">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                                    Ormawa</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Manajemen</div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="fas fa-users fa-2x text-gray-300"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Event Management -->
                                        <div class="col-xl-2 col-md-4 mb-4">
                                            <div class="card border-left-success shadow h-100 py-2">
                                                <a href="Index.php?page=event" class="text-decoration-none">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                                    Event</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Manajemen</div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Account Management -->
                                        <div class="col-xl-2 col-md-4 mb-4">
                                            <div class="card border-left-info shadow h-100 py-2">
                                                <a href="Index.php?page=account" class="text-decoration-none">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                                    Akun</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Manajemen</div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="fas fa-user fa-2x text-gray-300"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Form Management -->
                                        <div class="col-xl-2 col-md-4 mb-4">
                                            <div class="card border-left-warning shadow h-100 py-2">
                                                <a href="Index.php?page=oprec" class="text-decoration-none">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                                    Form Anggota</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Manajemen</div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Event Form Management -->
                                        <div class="col-xl-2 col-md-4 mb-4">
                                            <div class="card border-left-danger shadow h-100 py-2">
                                                <a href="Index.php?page=oprec-event" class="text-decoration-none">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                                    Form Event</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Manajemen</div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="fas fa-calendar-plus fa-2x text-gray-300"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Document Management -->
                                        <div class="col-xl-2 col-md-4 mb-4">
                                            <div class="card border-left-secondary shadow h-100 py-2">
                                                <a href="Index.php?page=doc" class="text-decoration-none">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                                                    Dokumen</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Manajemen</div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="fas fa-file-pdf fa-2x text-gray-300"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Row -->
                    <div class="row">
                        <!-- Recent Events Table -->
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Event Terbaru</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="recentEventsTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Nama Event</th>
                                                    <th>Ormawa</th>
                                                    <th>Tanggal Mulai</th>
                                                    <th>Tanggal Selesai</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $recent_events = getRecentEvents($koneksi);
                                                foreach ($recent_events as $event) {
                                                    echo "<tr>";
                                                    echo "<td><a href='Index.php?page=event' class='text-primary text-decoration-none'>" . htmlspecialchars($event['nama_event']) . "</a></td>";
                                                    echo "<td>" . htmlspecialchars($event['nama_ormawa']) . "</td>";
                                                    echo "<td>" . date('d M Y', strtotime($event['tgl_mulai'])) . "</td>";
                                                    echo "<td>" . date('d M Y', strtotime($event['tgl_selesai'])) . "</td>";
                                                    echo "</tr>";
                                                }
                                                if (empty($recent_events)) {
                                                    echo "<tr><td colspan='4' class='text-center'>Tidak ada event terbaru</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

    
<!-- Script untuk grafik distribusi kategori ormawa -->
<script>
// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Pie Chart Example for Ormawa Categories
var ctx = document.getElementById("ormawaCategoryChart");
var ormawaCategoryChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: [
      <?php
      $ormawa_by_category = getOrmawaByCategory($koneksi);
      foreach ($ormawa_by_category as $category) {
        echo '"' . addslashes(htmlspecialchars($category['kategori'])) . '",';
      }
      ?>
    ],
    datasets: [{
      data: [
        <?php
        foreach ($ormawa_by_category as $category) {
          echo $category['jumlah'] . ',';
        }
        ?>
      ],
      backgroundColor: [
        '#4e73df',
        '#1cc88a',
        '#36b9cc',
        '#f6c23e',
        '#e74a3b',
        '#858796',
        '#5a5c69'
      ],
      hoverBackgroundColor: [
        '#2e59d9',
        '#17a673',
        '#2c9faf',
        '#f5b507',
        '#d62828',
        '#757786',
        '#4a4c5b'
      ],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
  },
  options: {
    maintainAspectRatio: false,
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
    },
    legend: {
      display: false
    },
    cutoutPercentage: 80,
  },
});
</script>


<!-- Script untuk grafik tren event per bulan -->
<script>
// Line Chart Example for Event Trend
var ctx2 = document.getElementById("eventTrendChart");
var eventTrendChart = new Chart(ctx2, {
  type: 'line',
  data: {
    labels: [
      <?php
      $event_trend = getEventTrend($koneksi);
      foreach ($event_trend as $trend) {
        echo '"' . $trend['bulan'] . '",';
      }
      ?>
    ],
    datasets: [{
      label: "Jumlah Event",
      lineTension: 0.3,
      backgroundColor: "rgba(78, 115, 223, 0.05)",
      borderColor: "rgba(78, 115, 223, 1)",
      pointRadius: 3,
      pointBackgroundColor: "rgba(78, 115, 223, 1)",
      pointBorderColor: "rgba(78, 115, 223, 1)",
      pointHoverRadius: 3,
      pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
      pointHoverBorderColor: "rgba(78, 115, 223, 1)",
      pointHitRadius: 10,
      pointBorderWidth: 2,
      data: [
        <?php
        foreach ($event_trend as $trend) {
          echo $trend['jumlah'] . ',';
        }
        ?>
      ],
    }],
  },
  options: {
    maintainAspectRatio: false,
    layout: {
      padding: {
        left: 10,
        right: 25,
        top: 25,
        bottom: 0
      }
    },
    scales: {
      xAxes: [{
        gridLines: {
          display: false,
          drawBorder: false
        },
        ticks: {
          maxTicksLimit: 7
        }
      }],
      yAxes: [{
        ticks: {
          maxTicksLimit: 5,
          padding: 10,
        },
        gridLines: {
          color: "rgb(234, 236, 244)",
          zeroLineColor: "rgb(234, 236, 244)",
          drawBorder: false,
          borderDash: [2],
          zeroLineBorderDash: [2]
        }
      }],
    },
    legend: {
      display: false
    },
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      titleMarginBottom: 10,
      titleFontColor: '#6e707e',
      titleFontSize: 14,
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      intersect: false,
      mode: 'index',
      caretPadding: 10,
      callbacks: {
        label: function(tooltipItem, chart) {
          var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
          return datasetLabel + ': ' + tooltipItem.yLabel;
        }
      }
    }
  }
});
</script>

<!-- Script untuk grafik distribusi user per level -->
<script>
// Pie Chart Example for User Distribution
var ctx3 = document.getElementById("userDistributionChart");
var user_distribution = <?php echo json_encode(getUserDistribution($koneksi)); ?>;
var labels = [];
var data = [];

for(var i = 0; i < user_distribution.length; i++) {
    // Konversi level ke label yang lebih deskriptif
    var levelLabel = user_distribution[i].level;
    switch(levelLabel) {
        case '1': levelLabel = 'Super Admin'; break;
        case '2': levelLabel = 'Admin Ormawa'; break;
        case '3': levelLabel = 'Pengurus'; break;
        case '4': levelLabel = 'Mahasiswa'; break;
        default: levelLabel = 'Level ' + levelLabel; break;
    }
    labels.push(levelLabel);
    data.push(user_distribution[i].jumlah);
}

var userDistributionChart = new Chart(ctx3, {
  type: 'doughnut',
  data: {
    labels: labels,
    datasets: [{
      data: data,
      backgroundColor: [
        '#4e73df',
        '#1cc88a',
        '#36b9cc',
        '#f6c23e',
        '#e74a3b',
        '#858796',
        '#5a5c69'
      ],
      hoverBackgroundColor: [
        '#2e59d9',
        '#17a673',
        '#2c9faf',
        '#f5b507',
        '#d62828',
        '#757786',
        '#4a4c5b'
      ],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
  },
  options: {
    maintainAspectRatio: false,
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
    },
    legend: {
      display: false
    },
    cutoutPercentage: 80,
  },
});
</script>

<?php
include('../SuperAdmin/Footer.php');
?>
