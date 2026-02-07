<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../FrontOffice/index.php");
    exit;
}

// Get models
require_once __DIR__ . '/../../model/UserModel.php';
require_once __DIR__ . '/../../model/DonationModel.php';
require_once __DIR__ . '/../../model/CaseModel.php';
require_once __DIR__ . '/../../model/EventModel.php';

$userModel = new UserModel();
$donationModel = new DonationModel();
$caseModel = new CaseModel();
$eventModel = new EventModel();

$userId = $_SESSION['user_id'];
$userData = $userModel->getUserById($userId);
$userDonations = $donationModel->getUserDonations($userId);
$donationImpact = $donationModel->calculateDonationImpact($userId);

// Get all active cases
$allCases = $caseModel->getAllCases();
$activeCases = array_filter($allCases, function ($case) {
    return $case['status'] === 'active';
});

$categories = ['santé', 'handicap', 'enfants', 'éducation', 'rénovation'];

// --- VIEW ROUTER ---
$view = $_GET['view'] ?? 'discover';
$isAjax = isset($_GET['ajax']);

if ($isAjax && $view === 'profile') {
    include __DIR__ . '/profile_view.php';
    exit;
}
?>
<?php if (!$isAjax): ?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mon Espace Donateur - UniVersElle Ariana</title>
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
                    <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.5rem;">Espace Donateur</p>
                    <button id="sidebarToggle" class="btn-icon"
                        style="position: absolute; right: -20px; top: 30px; background: var(--bg-body); border: 1px solid rgba(255,255,255,0.1); z-index: 101;">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>
                <nav class="sidebar-nav">
                    <a href="?view=discover" class="nav-item <?php echo $view === 'discover' ? 'active' : ''; ?>">
                        <i class="fas fa-heart"></i>
                        <span>Découvrir</span>
                    </a>
                    <a href="?view=donations" class="nav-item <?php echo $view === 'donations' ? 'active' : ''; ?>">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>Mes Dons</span>
                    </a>
                    <a href="?view=saved" class="nav-item <?php echo $view === 'saved' ? 'active' : ''; ?>">
                        <i class="fas fa-bookmark"></i>
                        <span>Cas Sauvegardés</span>
                    </a>
                    <a href="?view=profile" class="nav-item <?php echo $view === 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-user-circle"></i>
                        <span>Mon Profil</span>
                    </a>
                    <a href="../../controller/AuthController.php?action=logout" class="nav-item logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
            <?php endif; ?>

            <!-- Header -->
            <header class="content-header">
                <div>
                    <h2 class="text-gradient" style="font-size: 1.75rem;">Tableau de Bord</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">
                        Heureux de vous revoir, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-outline" id="btnRegisterFace" onclick="registerMyFace()"
                        style="margin-right: 10px; border-radius: 999px;">
                        <i class="fas fa-face-smile"></i> Face ID
                    </button>
                    <button class="btn-icon">
                        <i class="fas fa-bell"></i>
                    </button>
                    <div class="user-profile-sm"
                        style="border: 2px solid var(--primary); width: 45px; height: 45px; border-radius: 50%; overflow: hidden;">
                        <img src="../<?php echo $_SESSION['user_image'] ?? 'https://ui-avatars.com/api/?name=User&background=3b82f6&color=fff'; ?>"
                            alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>
            </header>

            <div class="max-w-7xl" style="width: 100%;">
                <?php if ($view === 'discover'): ?>
                    <!-- Impact Statistics -->
                    <section class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon success"><i class="fas fa-hand-holding-usd"></i></div>
                            <div class="stat-info">
                                <h3><?php echo number_format($donationImpact['total_donated'], 0, ',', ' '); ?> DT</h3>
                                <p>Total Donné</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon primary"><i class="fas fa-users"></i></div>
                            <div class="stat-info">
                                <h3><?php echo $donationImpact['cases_supported']; ?></h3>
                                <p>Cas Soutenus</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon warning"><i class="fas fa-gift"></i></div>
                            <div class="stat-info">
                                <h3><?php echo $donationImpact['total_donations']; ?></h3>
                                <p>Nombre de Dons</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon danger"><i class="fas fa-star"></i></div>
                            <div class="stat-info">
                                <h3><?php echo count($activeCases); ?></h3>
                                <p>Cas Actifs</p>
                            </div>
                        </div>
                    </section>
                        <!-- Face ID Toggle Switch -->
                    <div class="face-toggle-wrapper" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 10px; border-radius: 12px; margin-top: 10px; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-face-smile" style="color: var(--primary-color);"></i>
                            <span style="font-size: 0.85rem; font-weight: 600;">Enregistrer mon visage</span>
                        </div>
                        <label class="switch" style="position: relative; display: inline-block; width: 40px; height: 20px;">
                            <input type="checkbox" id="btnFaceRegister" onchange="this.checked ? registerMyFace() : null">
                            <span class="slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px;"></span>
                        </label>
                    </div>

                    <!-- Filter & Search Section -->
                    <div class="glass-card mb-3">
                        <div class="flex justify-between items-center gap-4 flex-wrap">
                            <div class="pill-group" style="margin-bottom: 0;">
                                <button class="pill-filter active" onclick="filterByCategory('')">Tous</button>
                                <?php foreach ($categories as $cat): ?>
                                    <button class="pill-filter"
                                        onclick="filterByCategory('<?php echo $cat; ?>')"><?php echo ucfirst($cat); ?></button>
                                <?php endforeach; ?>
                            </div>
                            <div style="position: relative; flex: 1; min-width: 250px; max-width: 400px;">
                                <i class="fas fa-search"
                                    style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                                <input type="text" id="searchInput" placeholder="Rechercher par titre..." class="form-input"
                                    style="padding-left: 3rem; border-radius: 999px;">
                            </div>
                            <select id="sortFilter" class="form-select" style="width: auto; border-radius: 999px;">
                                <option value="recent">Plus récents</option>
                                <option value="urgent">Plus urgents</option>
                                <option value="progress">Progression</option>
                            </select>
                        </div>
                    </div>

                    <!-- Cases Grid -->
                    <div id="casesGrid" class="cases-grid"
                        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
                        <?php foreach ($activeCases as $case):
                            $progress = $case['goal_amount'] > 0 ? ($case['progress_amount'] / $case['goal_amount']) * 100 : 0;
                            $progress = min($progress, 100);
                            ?>
                            <div class="glass-card case-card" data-category="<?php echo htmlspecialchars($case['category']); ?>"
                                data-title="<?php echo htmlspecialchars(strtolower($case['title'])); ?>">
                                <div class="case-card-image-wrapper">
                                    <img src="<?php echo $case['image_url'] ?? 'https://via.placeholder.com/400x200?text=Cas+Social'; ?>"
                                        alt="<?php echo htmlspecialchars($case['title']); ?>"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div class="case-card-body">
                                    <h4 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 0.75rem;">
                                        <?php echo htmlspecialchars($case['title']); ?>
                                    </h4>
                                    <div class="progress-container">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between mt-3">
                                        <span
                                            style="font-weight: 700; color: #3b82f6;"><?php echo number_format($progress, 0); ?>%</span>
                                        <a href="<?php echo $case['cha9a9a_link']; ?>" target="_blank"
                                            class="btn btn-outline btn-sm">Soutenir</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($view === 'donations'): ?>
                    <div class="table-container">
                        <h3 class="mb-3">Mes Dons</h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Cas</th>
                                    <th>Montant</th>
                                    <th>Méthode</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userDonations as $donation): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($donation['case_title']); ?></strong></td>
                                        <td><?php echo number_format($donation['amount'], 2); ?> DT</td>
                                        <td><?php echo ucfirst($donation['payment_method']); ?></td>
                                        <td><span class="badge badge-success"><?php echo ucfirst($donation['status']); ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($donation['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div> <!-- Closing max-w-7xl -->

            <?php if (!$isAjax): ?>
            </main>
        </div>
        <!-- AI Canvas/Video Container (Hidden) -->
        <div id="ai-container"
            style="position: fixed; top: 10px; right: 10px; width: 150px; height: 110px; z-index: 9999; border-radius: 10px; overflow: hidden; background: #000; border: 2px solid var(--primary); display: none;">
            <video id="ai-video" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
            <canvas id="ai-canvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></canvas>
            <div class="face-guide"></div>
            <div class="scan-line"></div>
            <div id="ai-status"
                style="position: absolute; bottom: 0; width: 100%; background: rgba(0,0,0,0.7); color: #fff; font-size: 8px; text-align: center; padding: 2px;">
                IA Active</div>
        </div>

        <!-- Toast Notifications -->
        <div id="toast-container" class="toast-container"></div>

        <!-- AI Dependencies -->
        <script src="../FrontOffice/face-api.min.js"></script>
        <script>
            const FACE_AUTH_PATH = '../../controller/FaceAuthController.php';
            window.announceNotification = window.announceNotification || function (msg) { console.log("A11y:", msg); };
        </script>
        <script src="../FrontOffice/face-auth.js?v=3.0"></script>

        <script src="dashboard.js?v=1.0"></script>
    </body>

    </html>
<?php endif; ?>