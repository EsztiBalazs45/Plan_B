<?php
require_once __DIR__ . '/config.php';

// Set default user_name if not set
if (isset($_SESSION['user_id']) && !isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Felhasználó';
}

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Könyvelő Rendszer</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.8/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.8/main.min.css' rel='stylesheet' />
    
    <style>
    :root {
        --sidebar-width: 250px;
        --header-height: 60px;
        --primary-color: #0d6efd;
        --sidebar-bg: #2c3e50;
        --sidebar-hover: #1a252f;
    }

    body {
        padding-left: var(--sidebar-width);
        min-height: 100vh;
        background-color: #f8f9fa;
    }

    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        width: var(--sidebar-width);
        background-color: var(--sidebar-bg);
        padding: 1rem;
        z-index: 1000;
    }

    .sidebar .navbar-brand {
        color: white;
        font-size: 1.5rem;
        margin-bottom: 2rem;
        display: block;
        text-decoration: none;
    }

    .sidebar .nav-link {
        color: rgba(255,255,255,0.8);
        padding: 0.8rem 1rem;
        margin: 0.2rem 0;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        color: white;
        background-color: var(--sidebar-hover);
    }

    .sidebar .nav-link.active {
        color: white;
        background-color: var(--sidebar-hover);
        box-shadow: inset 4px 0 0 #3498db;
        padding-left: calc(1rem - 4px);
        position: relative;
    }

    .sidebar .nav-link.active::after {
        content: '•';
        position: absolute;
        right: 1rem;
        color: #3498db;
    }

    .sidebar .nav-link i {
        width: 24px;
        text-align: center;
        margin-right: 8px;
    }

    .content-wrapper {
        padding: 2rem;
        margin-left: var(--sidebar-width);
    }

    .user-menu {
        position: absolute;
        bottom: 1rem;
        left: 1rem;
        right: 1rem;
        padding: 1rem;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .user-menu .nav-link {
        display: flex;
        align-items: center;
    }

    .user-menu i {
        margin-right: 8px;
    }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <?php if (isLoggedIn()): ?>
            <a class="navbar-brand" href="/Bozont_cucc/accounting">
                <i class="fas fa-calculator"></i> Kajtár Könyvelés
            </a>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="/Bozont_cucc/accounting">
                        <i class="fas fa-home"></i> Főoldal
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'calendar.php' ? 'active' : ''; ?>" href="/Bozont_cucc/accounting/pages/calendar.php">
                        <i class="fas fa-calendar"></i> Naptár
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'clients.php' ? 'active' : ''; ?>" href="/Bozont_cucc/accounting/pages/clients.php">
                        <i class="fas fa-users"></i> Ügyfelek
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'invoices.php' ? 'active' : ''; ?>" href="/Bozont_cucc/accounting/pages/invoices.php">
                        <i class="fas fa-file-invoice"></i> Számlák
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'services.php' ? 'active' : ''; ?>" href="/Bozont_cucc/accounting/pages/services.php">
                        <i class="fas fa-calculator"></i> Szolgáltatások
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'documents.php' ? 'active' : ''; ?>" href="/Bozont_cucc/accounting/pages/documents.php">
                        <i class="fas fa-comments-dollar"></i> Dokumentumok
                    </a>
                </li>


            </ul>
            
            <!-- User Menu -->
            <div class="user-menu">
                <div class="nav flex-column">
                    <a class="nav-link <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>" href="/Bozont_cucc/accounting/pages/profile.php">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </a>
                    <a class="nav-link" href="/Bozont_cucc/accounting/pages/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Kijelentkezés
                    </a>
                </div>
            </div>
        <?php else: ?>
            <a class="navbar-brand" href="/Bozont_cucc">Kajtár Könyvelés</a>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'login.php' ? 'active' : ''; ?>" href="/Bozont_cucc/accounting/pages/login.php">
                        <i class="fas fa-sign-in-alt"></i> Bejelentkezés
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'register.php' ? 'active' : ''; ?>" href="/Bozont_cucc/accounting/pages/register.php">
                        <i class="fas fa-user-plus"></i> Regisztráció
                    </a>
                </li>
            </ul>
        <?php endif; ?>
    </nav>

    <div class="container my-4">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['error']); clearMessages(); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['success']); clearMessages(); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- FullCalendar JavaScript -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/locales/hu.js'></script>
