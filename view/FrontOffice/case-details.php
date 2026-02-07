<?php
require_once __DIR__ . '/../../model/DashboardModel.php';
require_once __DIR__ . '/../../model/CaseModel.php';

$caseModel = new CaseModel();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$caseId = intval($_GET['id']);
$case = $caseModel->getCaseById($caseId);

if (!$case) {
    header('Location: index.php');
    exit;
}

// Increment views
$caseModel->incrementViews($caseId);

// Calculate progress
$percent = $case['goal_amount'] > 0 ? ($case['progress_amount'] / $case['goal_amount']) * 100 : 0;
$percent = min($percent, 100);

// Category Icon Map
function getCategoryIcon($cat)
{
    switch (strtolower($cat)) {
        case 'santé':
            return 'fa-heartbeat';
        case 'éducation':
            return 'fa-graduation-cap';
        case 'handicap':
            return 'fa-wheelchair';
        case 'enfants':
            return 'fa-child';
        case 'rénovation':
            return 'fa-home';
        case 'urgence':
            return 'fa-exclamation-triangle';
        default:
            return 'fa-hand-holding-heart';
    }
}
$catIcon = getCategoryIcon($case['category']);

// Status Label
$statusLabel = 'En cours';
$statusClass = 'active';
if ($case['status'] === 'resolved') {
    $statusLabel = 'Résolu';
    $statusClass = 'resolved'; // You might need CSS for this
}

// Image fallback
$imageUrl = !empty($case['image_url']) ? htmlspecialchars($case['image_url']) : '../assets/img/default-case.jpg';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= htmlspecialchars($case['title']) ?> | UniVersElle Ariana
    </title>
    <!-- Fonts & Icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="accessibility.css">
</head>

<body class="case-detail-page secondary-page">
    <a href="#main-content" class="skip-link">Passer au contenu principal</a>

    <!-- Navigation (Simplified version for detail page) -->
    <nav class="navbar scrolled" aria-label="Navigation détaillée">
        <div class="container nav-container">
            <a href="index.php" class="logo" aria-label="Retour à l'accueil">
                <span class="logo-text">UniVersElle <span class="highlight">Ariana</span></span>
            </a>
            <div class="nav-menu" id="navMenu">
                <a href="index.php" class="nav-link">Accueil</a>
                <a href="index.php#cases" class="nav-link">Voir tous les cas</a>
                <a href="../dashboard/association-dashboard.php" class="nav-link">Publier un cas</a>
                <a href="../dashboard/admin-dashboard.php" class="nav-link">Tableau de bord</a>
            </div>
            <div class="nav-actions">
                <button id="contrast-toggle" class="btn-icon" aria-label="Activer le mode haut contraste"
                    style="color:var(--text-main); margin-right:10px;">
                    <i class="fas fa-adjust" aria-hidden="true"></i>
                </button>
                <div class="search-box small">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="text" placeholder="Rechercher..." aria-label="Rechercher un cas">
                </div>
            </div>
        </div>
    </nav>

    <main class="case-detail-main" id="main-content">
        <div class="container">
            <!-- Breadcrumbs -->
            <nav class="breadcrumbs" aria-label="Fil d'ariane">
                <a href="index.php">Accueil</a>
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                <a href="index.php#cases">Cas Sociaux</a>
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                <span aria-current="page">Détail du Cas</span>
            </nav>

            <div class="case-grid-layout">
                <!-- Left Column: Content (Sections 1, 2, 5) -->
                <div class="case-main-column">
                    <!-- Section 1: Presentation -->
                    <section class="case-presentation glass-card" aria-labelledby="caseTitle">
                        <div class="case-header-content">
                            <div class="case-labels">
                                <?php if (($case['is_urgent'] ?? 0) == 1): ?>
                                    <span class="badge badge-urgent" role="status"><i class="fas fa-exclamation-circle"
                                            aria-hidden="true"></i> Urgent</span>
                                <?php endif; ?>
                                <span class="badge badge-category"><i class="fas <?= $catIcon ?>"
                                        aria-hidden="true"></i>
                                    <?= htmlspecialchars($case['category']) ?>
                                </span>
                            </div>
                            <h1 class="case-title" id="caseTitle">
                                <?= htmlspecialchars($case['title']) ?>
                            </h1>
                            <div class="case-meta-header">
                                <span><i class="far fa-calendar-alt" aria-hidden="true"></i> Publié le
                                    <?= date('d/m/Y', strtotime($case['created_at'])) ?>
                                </span>
                                <span><i class="fas fa-sync-alt" aria-hidden="true"></i> Statut: <span
                                        class="status-<?= $statusClass ?>">
                                        <?= $statusLabel ?>
                                    </span></span>
                                <span><i class="far fa-eye" aria-hidden="true"></i>
                                    <?= $case['views'] ?> vues
                                </span>
                            </div>
                        </div>

                        <div class="case-visuals">
                            <div class="main-image-container">
                                <img src="<?= $imageUrl ?>" alt="Photo du cas : <?= htmlspecialchars($case['title']) ?>"
                                    class="main-case-img"
                                    onerror="this.src='https://placehold.co/1200x600?text=Image+Missing'">
                                <div class="img-overlay-brand">
                                    <img src="../../assets/img/logo-white.png" alt="" class="overlay-logo"
                                        aria-hidden="true" onerror="this.style.display='none'">
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Section 2: Histoire Complète -->
                    <section class="case-story glass-card" aria-labelledby="storyHeading">
                        <h2 class="section-heading" id="storyHeading"><i class="fas fa-book-open"
                                aria-hidden="true"></i> Histoire Complète</h2>
                        <div class="story-content" id="caseDescription">
                            <p>
                                <?= nl2br(htmlspecialchars($case['description'])) ?>
                            </p>

                            <div class="case-impact-quote">
                                <i class="fas fa-quote-left" aria-hidden="true"></i>
                                <p>Chaque geste compte. Aidez-nous à changer cette vie aujourd'hui.</p>
                            </div>
                        </div>
                        <div class="case-technical-details">
                            <div class="detail-item">
                                <span class="label">Objectif Financier</span>
                                <span class="val">
                                    <?= number_format($case['goal_amount'], 2) ?> TND
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Montant Récolté</span>
                                <span class="val">
                                    <?= number_format($case['progress_amount'], 2) ?> TND
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Catégorie</span>
                                <span class="val">
                                    <?= htmlspecialchars($case['category']) ?>
                                </span>
                            </div>
                        </div>
                    </section>

                    <!-- Section 5: Bonus (Sharing & More) -->
                    <section class="case-actions glass-card" aria-labelledby="shareHeading">
                        <div class="share-container">
                            <h3 id="shareHeading">Partager cette histoire</h3>
                            <p>Donner de la visibilité est aussi une forme d'aide précieuse.</p>
                            <div class="share-buttons">
                                <button class="btn-share fb" aria-label="Partager sur Facebook"><i
                                        class="fab fa-facebook-f" aria-hidden="true"></i> Facebook</button>
                                <button class="btn-share wa" aria-label="Partager sur WhatsApp"><i
                                        class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp</button>
                                <button class="btn-share tw" aria-label="Partager sur Twitter"><i class="fab fa-twitter"
                                        aria-hidden="true"></i> Twitter</button>
                                <button class="btn-share link" aria-label="Copier le lien"><i class="fas fa-link"
                                        aria-hidden="true"></i> Copier</button>
                            </div>
                        </div>
                        <div class="related-cta">
                            <a href="index.php#cases" class="btn-minimal">
                                <i class="fas fa-arrow-left" aria-hidden="true"></i> Voir d'autres cas urgents
                            </a>
                        </div>
                    </section>
                </div>

                <!-- Right Column: Sidebar (Sections 3, 4) -->
                <aside class="case-sidebar">
                    <!-- Section 3: Progression -->
                    <div class="sticky-sidebar">
                        <section class="case-progress-box glass-card" aria-labelledby="progressHeading">
                            <h2 id="progressHeading" class="sr-only">Statistiques de collecte</h2>
                            <div class="progress-header">
                                <span class="percentage" aria-label="<?= number_format($percent, 0) ?> pourcent">
                                    <?= number_format($percent, 0) ?>%
                                </span>
                                <span class="donors-count"><i class="fas fa-users" aria-hidden="true"></i> Donateurs
                                    (est.)</span>
                            </div>

                            <div class="sr-only">
                                <?= number_format($case['progress_amount'], 0, ',', ' ') ?> dinars collectés sur
                                <?= number_format($case['goal_amount'], 0, ',', ' ') ?>.
                            </div>

                            <div class="progress-bar-large" role="progressbar"
                                aria-valuenow="<?= number_format($percent, 0) ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-fill neon-red" style="width: <?= $percent ?>%"></div>
                            </div>
                            <div class="progress-stats-vertical" aria-hidden="true">
                                <div class="stat-group">
                                    <span class="stat-value">
                                        <?= number_format($case['progress_amount'], 0, ',', ' ') ?> TND
                                    </span>
                                    <span class="stat-label">Récoltés</span>
                                </div>
                                <div class="stat-divider"></div>
                                <div class="stat-group">
                                    <span class="stat-value">
                                        <?= number_format($case['goal_amount'], 0, ',', ' ') ?> TND
                                    </span>
                                    <span class="stat-label">Objectif Total</span>
                                </div>
                            </div>
                            <?php if (!empty($case['cha9a9a_link'])): ?>
                                <div class="recent-activity">
                                    <h4>Plateforme Partenaire</h4>
                                    <div class="activity-item">
                                        <div class="activity-icon"><i class="fas fa-link"></i></div>
                                        <div class="activity-info">
                                            <span class="time">Lien direct</span>
                                            <span class="amount">Cha9a9a.tn</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </section>

                        <!-- Section 4: Bouton de don principal -->
                        <section class="case-donation-cta">
                            <?php if (!empty($case['cha9a9a_link'])): ?>
                                <button class="btn-primary btn-giant"
                                    onclick="window.open('<?= htmlspecialchars($case['cha9a9a_link']) ?>', '_blank')"
                                    aria-label="Soutenir maintenant sur Cha9a9a (ouvre un nouvel onglet)">
                                    <span class="btn-text">Soutenir maintenant</span>
                                    <i class="fas fa-heart" aria-hidden="true"></i>
                                </button>
                            <?php else: ?>
                                <button class="btn-primary btn-giant" disabled style="opacity: 0.6; cursor: not-allowed;"
                                    aria-disabled="true">
                                    <span class="btn-text">Don bientôt disponible</span>
                                    <i class="fas fa-lock" aria-hidden="true"></i>
                                </button>
                            <?php endif; ?>

                            <div class="trust-badge">
                                <i class="fas fa-shield-alt" aria-hidden="true"></i>
                                <p>Votre don est sécurisé, traçable et 100% destiné au bénéficiaire via la plateforme
                                    <strong>Cha9a9a</strong>.
                                </p>
                            </div>
                        </section>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <footer class="case-footer-simple">
        <div class="container">
            <p>&copy; 2026 UniVersElle Ariana - Tous droits réservés.</p>
        </div>
    </footer>

    <script src="script.js"></script>
    <script src="accessibility.js?v=1.0"></script>
</body>

</html>