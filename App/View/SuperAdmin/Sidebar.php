<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
        <div class="sidebar-brand-icon">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div class="sidebar-brand-text mx-3">MyOrmawa</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="Index.php?page=dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Data Master
    </div>

    <!-- Nav Item - Pages Collapse Menu -->
    <li class="nav-item">
        <a class="nav-link" href="Index.php ?page=ormawa">
            <i class="fa-solid fa-users"></i>
            <span>Data Ormawa</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="Index.php ?page=account">
            <i class="fa-solid fa-id-card"></i>
            <span>Account Ormawa</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="Index.php ?page=event">
            <i class="fas fa-calendar-week"></i>
            <span>Event</span></a>
    </li>

    <!-- Nav Item - Utilities Collapse Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fa-solid fa-user-plus"></i>
            <span>Recruitmen</span>
        </a>
        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Openn Recruitmen</h6>
                <a class="collapse-item" href="Index.php?page=oprec">Anggota Ormawa</a>
                <a class="collapse-item" href="utilities-border.html">Panitia Event</a>
            </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Addons
    </div>

    <!-- Nav Item - Pages Collapse Menu -->
    <li class="nav-item">
        <a class="nav-link" href="Index.php ?page=doc">
            <i class="fa-solid fa-folder-open"></i>
            <span>Document</span></a>
    </li>

    <!-- Nav Item - Charts -->
    <li class="nav-item">
        <a class="nav-link" href="Index.php ?page=kompetisi">
            <i class="fa-solid fa-trophy"></i>
            <span>Kompetisi</span></a>
    </li>

    <!-- Nav Item - Tables -->
    <li class="nav-item">
        <a class="nav-link" href="Index.php ?page=absensi">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Absensi</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>

<?php
include('../SuperAdmin/Footer.php');
?>