<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['association', 'partner'])) {
    header("Location: ../FrontOffice/index.php");
    exit;
}

// Get association data
require_once __DIR__ . '/../../controller/DashboardController.php';
$controller = new DashboardController();

$userId = $_SESSION['user_id'];
$associationId = $_SESSION['association_id'] ?? null;

// If no association_id in session, try to get it from database
if (!$associationId) {
    require_once __DIR__ . '/../../model/AssociationModel.php';
    $assocModel = new AssociationModel();
    $assocData = $assocModel->getAssociationByUserId($userId);
    if ($assocData) {
        $associationId = $assocData['id'];
        $_SESSION['association_id'] = $associationId;
    }
}

// Fetch dynamic data if not provided by controller
require_once __DIR__ . '/../../model/AssociationModel.php';
require_once __DIR__ . '/../../model/CaseModel.php';
require_once __DIR__ . '/../../model/EventModel.php';

$assocModel = new AssociationModel();
$caseModel = new CaseModel();
$eventModel = new EventModel();

$associationData = $associationData ?? $assocModel->getAssociationById($associationId);
$associationStats = $associationStats ?? $assocModel->getAssociationStats($associationId);
$associationCases = $associationCases ?? $caseModel->getAssociationCases($associationId);
$associationEvents = $associationEvents ?? $eventModel->getAssociationEvents($associationId);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Association - UniVersElle Ariana</title>
    <link rel="stylesheet" href="dashboard.css?v=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo-text">UniVersElle</h1>
                <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 0.5rem; font-weight: 500;">
                    <?php echo htmlspecialchars($associationData['name'] ?? 'Association'); ?>
                </p>
            </div>
            <nav class="sidebar-nav">
                <a href="?view=dashboard" class="nav-item active">
                    <i class="fas fa-chart-pie"></i>
                    <span>Tableau de Bord</span>
                </a>
                <a href="?view=cases" class="nav-item">
                    <i class="fas fa-folder-open"></i>
                    <span>Mes Cas</span>
                </a>
                <a href="?view=events" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Événements</span>
                </a>
                <a href="?view=profile" class="nav-item">
                    <i class="fas fa-building"></i>
                    <span>Mon Profil</span>
                </a>
                <!-- Face ID Toggle Switch -->
                <div class="face-toggle-wrapper"
                    style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 10px; border-radius: 12px; margin-top: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-face-smile" style="color: var(--primary-color);"></i>
                        <span style="font-size: 0.85rem; font-weight: 600;">Enregistrer mon visage</span>
                    </div>
                    <label class="switch" style="position: relative; display: inline-block; width: 40px; height: 20px;">
                        <input type="checkbox" id="btnFaceRegister" onchange="this.checked ? registerMyFace() : null">
                        <span class="slider"
                            style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px;"></span>
                    </label>
                </div>
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
            <!-- Header -->
            <header class="content-header">
                <div>
                    <h2 style="font-size: 1.5rem;">Bienvenue, <span
                            style="color: #60a5fa;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span></h2>
                    <p style="color: #94a3b8; font-size: 0.9rem; font-weight: 500;">Gestion de votre association Ariana
                    </p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-outline" id="btnRegisterFace" onclick="registerMyFace()"
                        style="margin-right: 10px;">
                        <i class="fas fa-face-smile"></i> Face ID
                    </button>
                    <button class="btn btn-primary" onclick="openModal('newCaseModal')">
                        <i class="fas fa-plus-circle"></i> Ajouter un Cas
                    </button>
                    <div class="user-profile-sm"
                        style="border: 2px solid var(--primary); width: 45px; height: 45px; border-radius: 50%; overflow: hidden;">
                        <?php
                        $logoUrl = $associationData['logo_url'] ?? null;
                        if ($logoUrl && strpos($logoUrl, 'http') === false)
                            $logoUrl = '../' . $logoUrl;
                        if (!$logoUrl)
                            $logoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($associationData['name'] ?? 'Assoc') . '&background=3b82f6&color=fff';
                        ?>
                        <img src="<?php echo $logoUrl; ?>" alt="Logo"
                            style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>
            </header>

            <div class="max-w-7xl" style="width: 100%;">
                <?php if ($view === 'dashboard'): ?>
                    <!-- Statistics Grid -->
                    <section class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $associationStats['total_cases']; ?></h3>
                                <p>Total des Cas</p>
                                <div class="stat-trend up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span><?php echo $associationStats['active_cases']; ?> actifs</span>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($associationStats['total_raised'], 0, ',', ' '); ?> DT</h3>
                                <p>Fonds Collectés</p>
                                <div class="stat-trend up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span><?php echo $associationStats['total_donors']; ?> donateurs</span>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo number_format($associationStats['total_views']); ?></h3>
                                <p>Vues Totales</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon danger">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $associationStats['resolved_cases']; ?></h3>
                                <p>Cas Résolus</p>
                            </div>
                        </div>
                    </section>

                    <!-- Cases Table -->
                    <div class="table-container"
                        style="background: white; border-radius: 1.5rem; padding: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 2rem;">
                        <div class="table-header" style="margin-bottom: 1.5rem;">
                            <h3 class="table-title" style="font-weight: 800; color: #1e293b;">Mes Cas Sociaux</h3>
                            <div class="table-actions">
                                <input type="text" id="searchCases" placeholder="Filtrer mes cas..." class="form-input"
                                    style="width: 300px; border-radius: 999px; background: #f8fafc; padding-left: 1.5rem;">
                            </div>
                        </div>

                        <table class="data-table" id="casesTable">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Catégorie</th>
                                    <th>Objectif</th>
                                    <th>Collecté</th>
                                    <th>Stats</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($associationCases as $case):
                                    $progress = $case['goal_amount'] > 0 ? ($case['progress_amount'] / $case['goal_amount']) * 100 : 0;
                                    $progress = min($progress, 100);
                                    ?>
                                    <tr data-category="<?php echo htmlspecialchars($case['category']); ?>">
                                        <td style="padding: 1.25rem 1rem;">
                                            <div style="font-weight: 700; color: #1e293b;">
                                                <?php echo htmlspecialchars($case['title']); ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: #94a3b8;">ID: #<?php echo $case['id']; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"
                                                style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                                                <?php echo ucfirst($case['category']); ?>
                                            </span>
                                        </td>
                                        <td style="font-weight: 600; color: #64748b;">
                                            <?php echo number_format($case['goal_amount'], 0, ',', ' '); ?> DT
                                        </td>
                                        <td style="font-weight: 700; color: #3b82f6;">
                                            <?php echo number_format($case['progress_amount'], 0, ',', ' '); ?> DT
                                        </td>
                                        <td>
                                            <div
                                                style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: #94a3b8;">
                                                <i class="far fa-eye"></i> <?php echo $case['views']; ?>
                                                <i class="far fa-heart" style="margin-left: 5px;"></i>
                                                <?php echo number_format($progress, 0); ?>%
                                            </div>
                                            <div class="progress-bar" style="height: 6px; margin-top: 5px; width: 80px;">
                                                <div class="progress-fill"
                                                    style="width: <?php echo $progress; ?>%; background: #3b82f6;"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = $case['status'] === 'active' ? 'success' : ($case['status'] === 'resolved' ? 'primary' : 'warning');
                                            ?>
                                            <span class="badge badge-<?php echo $statusClass; ?>"
                                                style="font-size: 0.7rem; font-weight: 800;">
                                                <?php echo strtoupper($case['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <a href="?view=cases&edit_id=<?php echo $case['id']; ?>"
                                                    class="btn btn-sm btn-outline"
                                                    style="padding: 0.5rem; border-radius: 0.75rem; color: #64748b;">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline"
                                                    style="padding: 0.5rem; border-radius: 0.75rem; color: #ef4444; border-color: rgba(239, 68, 68, 0.2);"
                                                    onclick="confirmDelete(<?php echo $case['id']; ?>, 'cases', () => location.reload())">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($associationCases)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center" style="padding: 4rem 2rem;">
                                            <div
                                                style="background: #f8fafc; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                                                <i class="fas fa-folder-open" style="font-size: 2rem; color: #cbd5e1;"></i>
                                            </div>
                                            <h4 style="color: #64748b;">Aucun cas enregistré</h4>
                                            <button class="btn btn-primary mt-3" onclick="openModal('newCaseModal')">Lancer un
                                                nouvel appel</button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Events Section -->
                    <div class="table-container"
                        style="background: white; border-radius: 1.5rem; padding: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                        <div class="table-header" style="margin-bottom: 1.5rem;">
                            <h3 class="table-title" style="font-weight: 800; color: #1e293b;">Événements Solidaire</h3>
                            <button class="btn btn-primary btn-sm" onclick="openModal('newEventModal')"
                                style="border-radius: 999px;">
                                <i class="fas fa-calendar-plus"></i>
                                Programmer
                            </button>
                        </div>

                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Détails de l'Événement</th>
                                    <th>Date & Heure</th>
                                    <th>Lieu</th>
                                    <th>Participants</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($associationEvents as $event): ?>
                                    <tr>
                                        <td style="padding: 1.25rem 1rem;">
                                            <div style="font-weight: 700; color: #1e293b;">
                                                <?php echo htmlspecialchars($event['title']); ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: #94a3b8;">
                                                <?php echo ucfirst($event['status']); ?>
                                            </div>
                                        </td>
                                        <td style="color: #64748b; font-weight: 500;">
                                            <i class="far fa-calendar-alt mr-1"></i>
                                            <?php echo date('d/m/Y', strtotime($event['event_date'])); ?><br>
                                            <i class="far fa-clock mr-1"></i>
                                            <?php echo date('H:i', strtotime($event['event_date'])); ?>
                                        </td>
                                        <td style="color: #64748b;"><i class="fas fa-map-marker-alt mr-1"
                                                style="color: #ef4444;"></i>
                                            <?php echo htmlspecialchars($event['location'] ?? 'Ariana'); ?></td>
                                        <td>
                                            <div style="font-weight: 700; color: #3b82f6;">
                                                <?php echo $event['current_participants']; ?> <span
                                                    style="font-weight: 500; color: #94a3b8; font-size: 0.8rem;">/
                                                    <?php echo $event['max_participants'] ?? '∞'; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <a href="?view=events&edit_id=<?php echo $event['id']; ?>"
                                                    class="btn btn-sm btn-outline"
                                                    style="padding: 0.5rem; border-radius: 0.75rem; color: #64748b;">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline"
                                                    style="padding: 0.5rem; border-radius: 0.75rem; color: #ef4444; border-color: rgba(239, 68, 68, 0.2);"
                                                    onclick="confirmDelete(<?php echo $event['id']; ?>, 'events', () => location.reload())">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($associationEvents)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center" style="padding: 4rem 2rem;">
                                            <i class="fas fa-calendar-alt"
                                                style="font-size: 2rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                                            <p style="color: #64748b;">Avenirs solidaire à programmer...</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif (in_array($view, $crudEntities)): ?>
                    <?php
                    $isPartial = true;
                    $entity = $view;
                    include __DIR__ . '/crud_view.php';
                    ?>
                <?php endif; ?>
            </div> <!-- Closing max-w-7xl -->
        </main>
    </div>

    <!-- New Case Modal -->
    <div id="newCaseModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Créer un Nouveau Cas</h3>
                <button class="modal-close" onclick="closeModal('newCaseModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="newCaseForm" method="POST"
                action="../../controller/DashboardController.php?action=crud&entity=cases&act=create"
                enctype="multipart/form-data" style="margin-top: 1rem;">
                <input type="hidden" name="association_id" value="<?php echo $associationId; ?>">

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Titre du Cas Solidaire *</label>
                        <input type="text" name="title" class="form-input" placeholder="Ex: Soutien pour soins médicaux"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Catégorie *</label>
                        <select name="category" class="form-select" required>
                            <option value="Santé">Santé</option>
                            <option value="Handicap">Handicap</option>
                            <option value="Enfants">Enfants</option>
                            <option value="Éducation">Éducation</option>
                            <option value="Rénovation">Rénovation</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description détaillée *</label>
                    <textarea name="description" class="form-textarea" rows="4"
                        placeholder="Expliquez le cas et l'impact du don..." required></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Objectif de Collecte (DT) *</label>
                        <input type="number" name="goal_amount" class="form-input" step="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lien Cha9a9a.tn *</label>
                        <input type="url" name="cha9a9a_link" class="form-input" placeholder="Lien de la cagnotte"
                            required>
                    </div>
                </div>

                <div
                    style="background: #f8fafc; padding: 1rem; border-radius: 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                    <input type="hidden" name="is_urgent" value="0">
                    <input type="checkbox" name="is_urgent" value="1" style="width: 20px; height: 20px;">
                    <span style="font-weight: 700; color: #1e293b;">Ce cas est-il URGENT ?</span>
                    <span style="font-size: 0.8rem; color: #94a3b8;">(Il apparaîtra en haut de liste avec un badge
                        rouge)</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Photo d'illustration (URL)</label>
                    <input type="url" name="image_url" class="form-input" placeholder="Lien vers une image">
                </div>

                <div class="flex gap-2" style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-check-circle"></i>
                        Publier mon appel
                    </button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('newCaseModal')">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- New Event Modal -->
    <div id="newEventModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Créer un Événement</h3>
                <button class="modal-close" onclick="closeModal('newEventModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="newEventForm" method="POST"
                action="../../controller/DashboardController.php?action=crud&entity=events&act=create">
                <input type="hidden" name="association_id" value="<?php echo $associationId; ?>">

                <div class="form-group">
                    <label class="form-label">Titre de l'Événement *</label>
                    <input type="text" name="title" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Date et Heure *</label>
                    <input type="datetime-local" name="event_date" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Lieu</label>
                    <input type="text" name="location" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Nombre Maximum de Participants</label>
                    <input type="number" name="max_attendees" class="form-input" min="1">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Créer l'Événement
                    </button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('newEventModal')">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
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
        // Adjust paths for dashboard
        const FACE_AUTH_PATH = '../../controller/FaceAuthController.php';
        // Mock announceNotification if not present
        window.announceNotification = window.announceNotification || function (msg) { console.log("A11y:", msg); };
    </script>
    <script src="../FrontOffice/face-auth.js?v=3.0"></script>

    <script src="dashboard.js?v=1.1"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Dashboard initialized');

            // Initialize search
            const searchInput = document.getElementById('searchCases');
            if (searchInput) {
                setupSearch('searchCases', 'casesTable');
            }

            // Animate progress bars on load
            animateProgressBars();

            // AJAX Form Handling
            handleFormSubmit('newCaseForm', '../../controller/DashboardController.php?action=crud&entity=cases&act=create', (res) => {
                closeModal('newCaseModal');
                showNotification('Cas créé avec succès !', 'success');
                setTimeout(() => location.reload(), 1000);
            });

            handleFormSubmit('newEventForm', '../../controller/DashboardController.php?action=crud&entity=events&act=create', (res) => {
                closeModal('newEventModal');
                showNotification('Événement créé avec succès !', 'success');
                setTimeout(() => location.reload(), 1000);
            });
        });

        // Edit case function
        function editCase(id) {
            // TODO: Implement edit modal
            alert('Fonctionnalité d\'édition à venir');
        }

        // Edit event function
        function editEvent(id) {
            // TODO: Implement edit modal
            alert('Fonctionnalité d\'édition à venir');
        }
    </script>
</body>

</html>