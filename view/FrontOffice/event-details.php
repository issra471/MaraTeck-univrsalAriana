<?php
require_once __DIR__ . '/../../model/DashboardModel.php';
require_once __DIR__ . '/../../model/EventModel.php';

$eventModel = new EventModel();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$eventId = intval($_GET['id']);
$event = $eventModel->getEventById($eventId);

if (!$event) {
    header('Location: index.php');
    exit;
}

// Date formatting
$date = strtotime($event['event_date']);
$day = date('d', $date);
$month = date('M', $date);
$months = ['Jan' => 'JAN', 'Feb' => 'FÉV', 'Mar' => 'MAR', 'Apr' => 'AVR', 'May' => 'MAI', 'Jun' => 'JUIN', 'Jul' => 'JUIL', 'Aug' => 'AOÛT', 'Sep' => 'SEP', 'Oct' => 'OCT', 'Nov' => 'NOV', 'Dec' => 'DÉC'];
$monthFr = $months[$month] ?? $month;
$fullDate = date('d/m/Y', $date);
$time = date('H:i', $date);

// Image fallback
$imageUrl = !empty($event['image_url']) ? htmlspecialchars($event['image_url']) : '../assets/img/default-event.jpg';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= htmlspecialchars($event['title']) ?> | UniVersElle Ariana
    </title>
    <!-- Fonts & Icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body class="case-detail-page secondary-page">
    <!-- Navigation (Simplified version for detail page) -->
    <nav class="navbar scrolled">
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <span class="logo-text">UniVersElle <span class="highlight">Ariana</span></span>
            </a>
            <div class="nav-menu" id="navMenu">
                <a href="index.php" class="nav-link">Accueil</a>
                <a href="index.php#cases" class="nav-link">Voir tous les cas</a>
                <a href="index.php#events" class="nav-link">Événements</a>
                <a href="../dashboard/association-dashboard.php" class="nav-link">Publier un cas</a>
                <a href="../dashboard/admin-dashboard.php" class="nav-link">Tableau de bord</a>
            </div>
            <div class="nav-actions">
                <div class="search-box small">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
            </div>
        </div>
    </nav>

    <main class="case-detail-main">
        <div class="container">
            <!-- Breadcrumbs -->
            <div class="breadcrumbs">
                <a href="index.php">Accueil</a>
                <i class="fas fa-chevron-right"></i>
                <a href="index.php#events">Événements</a>
                <i class="fas fa-chevron-right"></i>
                <span>Détail de l'Événement</span>
            </div>

            <div class="case-grid-layout">
                <!-- Left Column: Content (Sections 1, 2, 5) -->
                <div class="case-main-column">
                    <!-- Section 1: Presentation -->
                    <section class="case-presentation glass-card">
                        <div class="case-header-content">
                            <div class="case-labels">
                                <span class="badge badge-category"><i class="fas fa-calendar-alt"></i> Événement</span>
                                <span class="badge badge-urgent"><i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($event['location']) ?>
                                </span>
                            </div>
                            <h1 class="case-title" id="caseTitle">
                                <?= htmlspecialchars($event['title']) ?>
                            </h1>
                            <div class="case-meta-header">
                                <span><i class="far fa-clock"></i>
                                    <?= $fullDate ?> à
                                    <?= $time ?>
                                </span>
                                <span><i class="fas fa-users"></i> Max Participants:
                                    <?= $event['max_attendees'] ?>
                                </span>
                            </div>
                        </div>

                        <div class="case-visuals">
                            <div class="main-image-container">
                                <img src="<?= $imageUrl ?>" alt="<?= htmlspecialchars($event['title']) ?>"
                                    class="main-case-img"
                                    onerror="this.src='https://placehold.co/1200x600?text=Event+Image'">
                                <div class="img-overlay-brand">
                                    <img src="../../assets/img/logo-white.png" alt="Logo" class="overlay-logo"
                                        onerror="this.style.display='none'">
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Section 2: Histoire Complète -->
                    <section class="case-story glass-card">
                        <h2 class="section-heading"><i class="fas fa-info-circle"></i> À Propos de l'Événement</h2>
                        <div class="story-content" id="caseDescription">
                            <p>
                                <?= nl2br(htmlspecialchars($event['description'])) ?>
                            </p>

                            <div class="case-impact-quote">
                                <i class="fas fa-quote-left"></i>
                                <p>Rejoignez-nous pour faire la différence !</p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Right Column: Sidebar (Sections 3, 4) -->
                <aside class="case-sidebar">
                    <!-- Section 3: Progression -->
                    <div class="sticky-sidebar">
                        <section class="case-progress-box glass-card">
                            <div class="progress-header">
                                <span class="percentage">Date</span>
                            </div>
                            <div class="progress-bar-large" style="background: transparent; height: auto;">
                                <div class="event-date-large"
                                    style="text-align: center; font-size: 2rem; font-weight: bold; color: var(--primary-color);">
                                    <?= $day ?> <span style="font-size: 1.5rem; color: #fff;">
                                        <?= $monthFr ?>
                                    </span>
                                </div>
                            </div>
                            <div class="progress-stats-vertical">
                                <div class="stat-group">
                                    <span class="stat-value">
                                        <?= $time ?>
                                    </span>
                                    <span class="stat-label">Heure</span>
                                </div>
                                <div class="stat-divider"></div>
                                <div class="stat-group">
                                    <span class="stat-value">
                                        <?= htmlspecialchars($event['location']) ?>
                                    </span>
                                    <span class="stat-label">Lieu</span>
                                </div>
                            </div>
                        </section>

                        <!-- Section 4: Bouton de don principal -->
                        <section class="case-donation-cta">
                            <button class="btn-primary btn-giant" onclick="alert('Inscription bientôt disponible !')">
                                <span class="btn-text">Participer</span>
                                <i class="fas fa-check-circle"></i>
                            </button>

                            <div class="trust-badge">
                                <i class="fas fa-info-circle"></i>
                                <p>L'inscription est gratuite mais obligatoire pour organiser la logistique.</p>
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
</body>

</html>