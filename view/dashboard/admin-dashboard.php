<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'moderator'])) {
    header("Location: ../FrontOffice/index.php");
    exit;
}

// Get data using specialized models
require_once __DIR__ . '/../../model/DashboardModel.php';
require_once __DIR__ . '/../../model/UserModel.php';
require_once __DIR__ . '/../../model/AssociationModel.php';
require_once __DIR__ . '/../../model/CaseModel.php';
require_once __DIR__ . '/../../model/DonationModel.php';

$dbModel = new DashboardModel();
$userModel = new UserModel();
$assocModel = new AssociationModel();
$caseModel = new CaseModel();
$donationModel = new DonationModel();

$dashboardStats = $dashboardStats ?? $dbModel->getDashboardStats();
$recentCases = $recentCases ?? $caseModel->getRecentCases();
$recentDonations = $recentDonations ?? $donationModel->getRecentDonations();
$allAssociations = $allAssociations ?? $assocModel->getAllAssociations();
$allUsers = $allUsers ?? $userModel->getAllUsers();

// --- VIEW ROUTER ---
$view = $_GET['view'] ?? 'dashboard';
$crudEntities = ['users', 'associations', 'cases', 'donations', 'events', 'messages', 'volunteers'];

if (in_array($view, $crudEntities)) {
    require_once __DIR__ . '/../../controller/DashboardController.php';
    $controller = new DashboardController();
    $viewData = $controller->fetchListViewData($view);
    $entity = $view;
    if (isset($_GET['ajax'])) {
        include __DIR__ . '/crud_view.php';
        exit;
    }
}

// Fetch chart data
$donationsTrend = $dbModel->getDonationsTrend();
$casesByCategory = $dbModel->getCasesByCategory();

// Handle AJAX view requests for the main dashboard content
$isAjax = isset($_GET['ajax']);
?>
<?php if (!$isAjax): ?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Administration - UniVersElle Ariana</title>
        <link rel="stylesheet" href="dashboard.css?v=1.0">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>

    <body>
        <div class="dashboard-container">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="sidebar-header">
                    <h1 class="logo-text">UniVersElle</h1>
                    <p
                        style="font-size: 0.8rem; color: #94a3b8; font-weight: 600; margin-top: 0.5rem; letter-spacing: 1px;">
                        ADMINISTRATION</p>
                    <button id="sidebarToggle" class="btn-icon"
                        style="position: absolute; right: -20px; top: 30px; background: var(--bg-body); border: 1px solid rgba(255,255,255,0.1); z-index: 101;">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>
                <nav class="sidebar-nav">
                    <a href="?view=dashboard" class="nav-item <?php echo $view === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Tableau de Bord</span>
                    </a>
                    <a href="?view=users" class="nav-item <?php echo $view === 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-users-cog"></i>
                        <span>Utilisateurs</span>
                    </a>
                    <a href="?view=associations" class="nav-item <?php echo $view === 'associations' ? 'active' : ''; ?>">
                        <i class="fas fa-handshake"></i>
                        <span>Associations</span>
                    </a>
                    <a href="?view=cases" class="nav-item <?php echo $view === 'cases' ? 'active' : ''; ?>">
                        <i class="fas fa-folder-open"></i>
                        <span>Cas Sociaux</span>
                    </a>
                    <a href="?view=donations" class="nav-item <?php echo $view === 'donations' ? 'active' : ''; ?>">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Dons</span>
                    </a>
                    <a href="?view=events" class="nav-item <?php echo $view === 'events' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Événements</span>
                    </a>
                    <div style="margin-top: auto; padding-top: 2rem;">
                        <a href="../../controller/AuthController.php?action=logout" class="nav-item logout"
                            style="color: #ef4444;">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Déconnexion</span>
                        </a>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
            <?php endif; ?>

            <!-- Header -->
            <header class="content-header">
                <div class="header-welcome">
                    <h2 class="text-gradient" style="font-size: 1.75rem;">Supervision Centrale</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">
                        Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </p>
                </div>
                <div class="header-actions">
                    <button class="btn-icon">
                        <i class="fas fa-bell"></i>
                        <span class="badge"
                            style="background: var(--primary); position: absolute; top: -5px; right: -5px; padding: 2px 6px; font-size: 0.6rem;">5</span>
                    </button>
                    <button class="btn-icon">
                        <i class="fas fa-cog"></i>
                    </button>
                    <div class="user-profile-sm"
                        style="border: 2px solid var(--primary); width: 45px; height: 45px; border-radius: 50%; overflow: hidden;">
                        <img src="../<?php echo $_SESSION['user_image'] ?? 'https://ui-avatars.com/api/?name=Admin&background=3b82f6&color=fff'; ?>"
                            alt="Admin" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>
            </header>

            <!-- Statistics Grid -->
            <div class="max-w-7xl" style="width: 100%;">
                <section class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $dashboardStats['total_users']; ?></h3>
                            <p>Utilisateurs</p>
                            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> <span>+12%</span></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon success"><i class="fas fa-hand-holding-heart"></i></div>
                        <div class="stat-info">
                            <h3><?php echo number_format($dashboardStats['total_donations_amount'], 0, ',', ' '); ?> DT
                            </h3>
                            <p>Dons Collectés</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon warning"><i class="fas fa-folder-open"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $dashboardStats['total_cases']; ?></h3>
                            <p>Cas Actifs</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon danger"><i class="fas fa-building"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $dashboardStats['total_associations']; ?></h3>
                            <p>Associations</p>
                        </div>
                    </div>
                </section>

                <?php if ($view === 'dashboard'): ?>
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                        <div class="glass-card">
                            <h3><i class="fas fa-chart-line" style="color: #3b82f6;"></i> Évolution des Dons</h3>
                            <canvas id="donationsChart" style="max-height: 300px;"></canvas>
                        </div>
                        <div class="glass-card">
                            <h3><i class="fas fa-chart-pie" style="color: #60a5fa;"></i> Cas par Catégorie</h3>
                            <canvas id="categoriesChart" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                <?php elseif (in_array($view, $crudEntities)): ?>
                    <?php
                    $isPartial = true;
                    include __DIR__ . '/crud_view.php';
                    ?>
                <?php endif; ?>

                <script>
                    // This script will re-run on AJAX load thanks to reinitViewScripts in dashboard.js
                    (function () {
                        try {
                            console.log('Initializing charts...');
                            // Use empty arrays as fallback if PHP fails
                            const donationsTrend = <?php echo json_encode($donationsTrend ?? []); ?>;
                            const casesByCategory = <?php echo json_encode($casesByCategory ?? []); ?>;

                            const donationsCtx = document.getElementById('donationsChart');
                            if (donationsCtx && donationsTrend.length > 0) {
                                new Chart(donationsCtx, {
                                    type: 'line',
                                    data: {
                                        labels: donationsTrend.map(d => d.month),
                                        datasets: [{
                                            label: 'Dons (DT)',
                                            data: donationsTrend.map(d => d.total),
                                            borderColor: '#2563eb',
                                            tension: 0.4,
                                            fill: true,
                                            backgroundColor: 'rgba(37, 99, 235, 0.1)'
                                        }]
                                    },
                                    options: { responsive: true, maintainAspectRatio: false }
                                });
                            }

                            const categoriesCtx = document.getElementById('categoriesChart');
                            if (categoriesCtx && casesByCategory.length > 0) {
                                new Chart(categoriesCtx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: casesByCategory.map(c => c.category),
                                        datasets: [{
                                            data: casesByCategory.map(c => c.count),
                                            backgroundColor: ['#2563eb', '#0ea5e9', '#8b5cf6', '#10b981', '#f59e0b']
                                        }]
                                    },
                                    options: { responsive: true, maintainAspectRatio: false }
                                });
                            }
                        } catch (e) {
                            console.error('Chart initialization error:', e);
                        }
                    })();
                </script>

                <?php if (!$isAjax): ?>
                </div> <!-- Closing max-w-7xl -->
            </main>
            <script src="dashboard.js?v=1.1"></script>
        </div>
        <!-- App Scripts -->
        <script src="../FrontOffice/script.js?v=3.0"></script>
        <script src="../FrontOffice/accessibility.js?v=1.0"></script>
        <script src="../FrontOffice/face-api.min.js"></script>
        <script src="../FrontOffice/face-auth.js?v=3.0"></script>
        <script src="../FrontOffice/gestures.js?v=2.0"></script>
    </body>

    </html>
<?php endif; ?>