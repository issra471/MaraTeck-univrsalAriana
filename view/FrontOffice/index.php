<?php
require_once __DIR__ . '/../../model/DashboardModel.php';
require_once __DIR__ . '/../../model/CaseModel.php';
require_once __DIR__ . '/../../model/EventModel.php';

$dashboardModel = new DashboardModel();
$caseModel = new CaseModel();
$eventModel = new EventModel();

// Fetch Global Stats
$stats = $dashboardModel->getDashboardStats();
$totalBeneficiaries = $dashboardModel->getTotalBeneficiaries();

// Calculation for years of impact (Since 2021)
$yearsImpact = date('Y') - 2021;

// Fetch Urgent/Active Cases
$allCases = $caseModel->getAllCases();
// Filter active
$activeCases = array_filter($allCases, function ($c) {
    return $c['status'] === 'active';
});
// Prioritize Urgent
$urgentCases = array_filter($activeCases, function ($c) {
    return ($c['is_urgent'] ?? 0) == 1;
});
// Fill with normal active cases if needed
if (count($urgentCases) < 6) {
    $remaining = array_diff_key($activeCases, $urgentCases);
    $urgentCases = array_merge($urgentCases, array_slice($remaining, 0, 6 - count($urgentCases)));
}
$displayCases = array_slice($urgentCases, 0, 6);

// Fetch Category Counts
$categoryStats = $dashboardModel->getCasesByCategory(); // Returns [{category, count}]
$catMap = [];
foreach ($categoryStats as $stat) {
    $catMap[strtolower($stat['category'])] = $stat['count'];
}

// Fetch Upcoming Events
$upcomingEvents = $eventModel->getUpcomingEventsForUser(0); // 0 because it ignores ID anyway
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniVersElle Ariana - Votre code peut changer des vies</title>
    <link rel="stylesheet" href="style.css?v=2.4">
    <link rel="stylesheet" href="accessibility.css?v=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <a href="#main-content" class="skip-link">Passer au contenu principal</a>

    <!-- Screen Reader Guide -->
    <div class="sr-only" role="complementary" aria-label="Guide de navigation">
        <h2>Guide pour utilisateurs de lecteur d'écran</h2>
        <p>Bienvenue sur UniVersElle Ariana. Utilisez H pour naviguer entre les titres.</p>
    </div>

    <!-- Navigation -->
    <nav class="navbar" id="navbar" aria-label="Navigation principale">
        <div class="container nav-container">
            <a href="#home" class="logo-container" aria-label="Accueil UniVersElle Ariana">
                <div class="logo-placeholder" aria-hidden="true">
                    <span class="logo-text">UniVersElle Ariana</span>
                </div>
            </a>
            <ul class="nav-menu" id="navMenu" role="menubar">
                <li role="none"><a href="#home" class="nav-link active" role="menuitem">Accueil</a></li>
                <li role="none"><a href="#cases" class="nav-link" role="menuitem">Cas Sociaux</a></li>
                <li role="none"><a href="#about" class="nav-link" role="menuitem">À Propos</a></li>
                <li role="none"><a href="#events" class="nav-link" role="menuitem">Événements</a></li>
                <li role="none"><a href="#contact" class="nav-link" role="menuitem">Contact</a></li>
            </ul>
            <div class="nav-actions">
                <button id="contrast-toggle" class="btn-icon" aria-label="Activer le mode haut contraste"
                    style="color:white; margin-right:10px;" title="Haut Contraste">
                    <i class="fas fa-adjust" aria-hidden="true"></i>
                </button>
                <button id="gesture-toggle" class="btn-icon" aria-label="Contrôle par Gestes"
                    style="color:white; margin-right:10px;" title="Air Control (Gestes)">
                    <i class="fas fa-hand-paper" aria-hidden="true"></i>
                </button>
                <button class="btn-login" onclick="openLoginModal()">Connexion</button>
                <button class="btn-donate" onclick="window.location.href='#cases'">Faire un Don</button>
            </div>
            <div class="hamburger" id="hamburger" aria-label="Menu mobile" aria-expanded="false" role="button">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main id="main-content">

        <!-- Hero Section -->
        <section id="home" class="hero" aria-label="Introduction">
            <div class="hero-bg-video" aria-hidden="true">
                <video autoplay muted loop playsinline poster="../assets/video/fallback.jpg" class="hero-video">
                    <source src="../assets/video/hero-bg.mp4" type="video/mp4">
                </video>
                <div class="video-overlay"></div>
            </div>
            <div class="container hero-container">
                <div class="hero-content">
                    <div class="hero-badge">
                        <span class="badge-text">+300,000 vies touchées</span>
                    </div>
                    <h1 class="hero-title">
                        <span class="title-line">Donnez de la </span>
                        <span class="title-highlight">Visibilité</span>
                        <span class="title-line"> à l'Invisible</span>
                    </h1>
                    <p class="hero-subtitle">
                        Chaque don compte. Chaque vie mérite d'être entendue.
                        Rejoignez notre mission pour transformer des vies à travers la solidarité.
                    </p>
                    <div class="hero-cta">
                        <button class="btn-primary" onclick="window.location.href='#cases'">
                            <span>Découvrir les Cas</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                        <button class="btn-secondary" onclick="window.location.href='#about'">
                            <span>Comment Aider</span>
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-number" id="stat-beneficiaries" data-target="<?= $totalBeneficiaries ?>">0
                            </div>
                            <div class="stat-label">Bénéficiaires</div>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <div class="stat-number" id="stat-members" data-target="<?= $stats['total_users'] ?>">0
                            </div>
                            <div class="stat-label">Membres Actifs</div>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <div class="stat-number" id="stat-years" data-target="<?= $yearsImpact ?>">0</div>
                            <div class="stat-label">Années d'Impact</div>
                        </div>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="floating-card card-1" data-category="sante">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>Santé</span>
                    </div>
                    <div class="floating-card card-2" data-category="education">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Éducation</span>
                    </div>
                    <div class="floating-card card-3" data-category="enfants">
                        <i class="fas fa-child"></i>
                        <span>Enfants</span>
                    </div>
                    <div class="floating-card card-4" data-category="handicap">
                        <i class="fas fa-wheelchair"></i>
                        <span>Handicap</span>
                    </div>
                </div>
            </div>
            <div class="scroll-indicator">
                <div class="mouse"></div>
                <span>Scroll</span>
            </div>
        </section>

        <!-- Wave Divider -->
        <div class="divider-wave">
            <svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg">
                <path fill="rgba(255, 255, 255, 0.7)" fill-opacity="1"
                    d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,213.3C1248,235,1344,213,1392,202.7L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z">
                </path>
            </svg>
        </div>

        <!-- Categories Section -->
        <section class="categories">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Catégories d'Aide</h2>
                    <p class="section-subtitle">Choisissez la cause qui vous touche</p>
                </div>
                <div class="categories-grid">
                    <div class="category-card" data-category="sante">
                        <div class="category-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h3>Santé</h3>
                        <p>Soins médicaux et traitements urgents</p>
                        <div class="category-count"><?= $catMap['santé'] ?? 0 ?> cas actifs</div>
                    </div>
                    <div class="category-card" data-category="handicap">
                        <div class="category-icon">
                            <i class="fas fa-wheelchair"></i>
                        </div>
                        <h3>Handicap</h3>
                        <p>Équipements et assistance spécialisée</p>
                        <div class="category-count"><?= $catMap['handicap'] ?? 0 ?> cas actifs</div>
                    </div>
                    <div class="category-card" data-category="education">
                        <div class="category-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h3>Éducation</h3>
                        <p>Accès à l'éducation et fournitures</p>
                        <div class="category-count"><?= $catMap['éducation'] ?? 0 ?> cas actifs</div>
                    </div>
                    <div class="category-card" data-category="enfants">
                        <div class="category-icon">
                            <i class="fas fa-child"></i>
                        </div>
                        <h3>Enfants</h3>
                        <p>Protection et bien-être des enfants</p>
                        <div class="category-count"><?= $catMap['enfants'] ?? 0 ?> cas actifs</div>
                    </div>
                    <div class="category-card" data-category="renovation">
                        <div class="category-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3>Rénovation</h3>
                        <p>Logements et infrastructures</p>
                        <div class="category-count"><?= $catMap['rénovation'] ?? 0 ?> cas actifs</div>
                    </div>
                    <div class="category-card" data-category="urgence">
                        <div class="category-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3>Urgence</h3>
                        <p>Situations critiques immédiates</p>
                        <div class="category-count"><?= $catMap['urgence'] ?? 0 ?> cas actifs</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Wave Divider Inverted -->
        <div class="divider-wave inverted">
            <svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg">
                <path fill="rgba(255, 255, 255, 0.7)" fill-opacity="1"
                    d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,224C672,245,768,267,864,261.3C960,256,1056,224,1152,197.3C1248,171,1344,149,1392,138.7L1440,128L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z">
                </path>
            </svg>
        </div>

        <!-- Featured Cases Section -->
        <section id="cases" class="cases-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Cas Urgents</h2>
                    <p class="section-subtitle">Ces personnes ont besoin de votre aide maintenant</p>
                </div>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all">Tous</button>
                        <button class="filter-btn" data-filter="sante">Santé</button>
                        <button class="filter-btn" data-filter="handicap">Handicap</button>
                        <button class="filter-btn" data-filter="education">Éducation</button>
                        <button class="filter-btn" data-filter="enfants">Enfants</button>
                        <button class="filter-btn" data-filter="urgence">Urgence</button>
                    </div>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="caseSearch" placeholder="Rechercher un cas...">
                    </div>
                </div>

                <!-- Cases Grid -->
                <div class="cases-grid">
                    <?php if (empty($displayCases)): ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                            <i class="fas fa-search" aria-hidden="true"
                                style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                            <p style="color: #64748b; font-size: 1.2rem;">Aucun cas urgent pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($displayCases as $case):
                            $percent = $case['goal_amount'] > 0 ? ($case['progress_amount'] / $case['goal_amount']) * 100 : 0;
                            $percent = min($percent, 100);
                            $remaining = $case['goal_amount'] - $case['progress_amount'];

                            // Icon mapping
                            $icon = 'fa-heartbeat';
                            switch (strtolower($case['category'])) {
                                case 'éducation':
                                    $icon = 'fa-graduation-cap';
                                    break;
                                case 'handicap':
                                    $icon = 'fa-wheelchair';
                                    break;
                                case 'enfants':
                                    $icon = 'fa-child';
                                    break;
                                case 'rénovation':
                                    $icon = 'fa-home';
                                    break;
                                case 'urgence':
                                    $icon = 'fa-exclamation-triangle';
                                    break;
                            }
                            ?>
                            <article class="case-card" data-category="<?= htmlspecialchars(strtolower($case['category'])) ?>"
                                aria-labelledby="case-title-<?= $case['id'] ?>">
                                <?php if (($case['is_urgent'] ?? 0) == 1): ?>
                                    <div class="case-badge urgence" role="status" aria-label="Cas urgent">Urgent</div>
                                <?php endif; ?>

                                <div class="case-image">
                                    <img src="<?= htmlspecialchars($case['image_url'] ?? '../assets/img/default-case.jpg') ?>"
                                        alt="Photo pour le cas : <?= htmlspecialchars($case['title']) ?>. Catégorie : <?= htmlspecialchars($case['category']) ?>"
                                        onerror="this.src='https://placehold.co/600x400?text=UniVersElle'">
                                    <div class="case-overlay">
                                        <button class="btn-view-case"
                                            onclick="window.location.href='case-details.php?id=<?= $case['id'] ?>'"
                                            aria-label="Voir les détails du cas <?= htmlspecialchars($case['title']) ?>">Voir le
                                            Cas</button>
                                    </div>
                                </div>
                                <div class="case-content">
                                    <div class="case-category">
                                        <i class="fas <?= $icon ?>" aria-hidden="true"></i>
                                        <span><?= htmlspecialchars($case['category']) ?></span>
                                    </div>
                                    <h3 class="case-title" id="case-title-<?= $case['id'] ?>">
                                        <?= htmlspecialchars($case['title']) ?>
                                    </h3>
                                    <p class="case-description">
                                        <?= htmlspecialchars(substr($case['description'], 0, 100)) . (strlen($case['description']) > 100 ? '...' : '') ?>
                                    </p>

                                    <div class="case-progress" role="region" aria-label="Progression du don">
                                        <!-- Screen Reader info -->
                                        <div class="sr-only">
                                            <?= number_format($case['progress_amount'], 0, ',', ' ') ?> dinars collectés sur
                                            <?= number_format($case['goal_amount'], 0, ',', ' ') ?>.
                                            Il manque <?= number_format($remaining, 0, ',', ' ') ?> dinars.
                                            Progression à <?= number_format($percent, 0) ?>%.
                                        </div>

                                        <div class="progress-info" aria-hidden="true">
                                            <span class="progress-label">Progression</span>
                                            <span class="progress-percentage"><?= number_format($percent, 0) ?>%</span>
                                        </div>
                                        <div class="progress-bar" role="progressbar"
                                            aria-valuenow="<?= number_format($percent, 0) ?>" aria-valuemin="0"
                                            aria-valuemax="100">
                                            <div class="progress-fill" style="width: <?= $percent ?>%"></div>
                                        </div>
                                        <div class="progress-stats" aria-hidden="true">
                                            <span class="raised"><?= number_format($case['progress_amount'], 0, ',', ' ') ?>
                                                TND</span>
                                            <span class="goal">sur <?= number_format($case['goal_amount'], 0, ',', ' ') ?>
                                                TND</span>
                                        </div>
                                    </div>

                                    <div class="case-footer">
                                        <div class="case-meta">
                                            <span aria-label="Publié le <?= date('d/m/Y', strtotime($case['created_at'])) ?>"><i
                                                    class="far fa-clock" aria-hidden="true"></i>
                                                <?= date('d/m/Y', strtotime($case['created_at'])) ?></span>
                                            <span aria-label="<?= $case['views'] ?> vues"><i class="far fa-eye"
                                                    aria-hidden="true"></i> <?= $case['views'] ?></span>
                                        </div>
                                        <button class="btn-donate-case"
                                            onclick="window.location.href='case-details.php?id=<?= $case['id'] ?>'"
                                            aria-label="Soutenir ce cas : <?= htmlspecialchars($case['title']) ?>">
                                            <i class="fas fa-hand-holding-heart" aria-hidden="true"></i>
                                            Soutenir
                                        </button>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="load-more-container">
                    <button class="btn-load-more">
                        <span>Voir Plus de Cas</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
        </section>

        <!-- Impact Section -->
        <section class="impact-section">
            <div class="container">
                <div class="impact-content">
                    <div class="impact-left">
                        <h2 class="impact-title">Votre Impact en Chiffres</h2>
                        <p class="impact-description">
                            Chaque contribution, aussi petite soit-elle, crée un effet domino de changement positif dans
                            notre communauté.
                        </p>
                        <div class="impact-stats-detailed">
                            <div class="impact-stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-hands-helping"></i>
                                </div>
                                <div class="stat-details">
                                    <div class="stat-value" id="stat-donations"
                                        data-target="<?= $stats['total_donations_count'] ?>">0</div>
                                    <div class="stat-text">Dons Réalisés</div>
                                </div>
                            </div>
                            <div class="impact-stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-details">
                                    <div class="stat-value" id="stat-resolved"
                                        data-target="<?= $stats['resolved_cases'] ?>">0</div>
                                    <div class="stat-text">Cas Résolus</div>
                                </div>
                            </div>
                            <div class="impact-stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-details">
                                    <div class="stat-value" id="stat-donors"
                                        data-target="<?= $stats['total_donors'] ?? 0 ?>">0</div>
                                    <div class="stat-text">Donateurs Actifs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="impact-right">
                        <div class="testimonial-card">
                            <div class="quote-icon">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <p class="testimonial-text">
                                "Grâce à votre générosité, ma fille a pu recevoir les soins dont elle avait
                                désespérément
                                besoin. Vous nous avez redonné l'espoir."
                            </p>
                            <div class="testimonial-author">
                                <div class="author-avatar">
                                    <img src="https://i.pravatar.cc/150?img=47" alt="Fatma">
                                </div>
                                <div class="author-info">
                                    <div class="author-name">Fatma B.</div>
                                    <div class="author-role">Mère bénéficiaire</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="about-section">
            <div class="container">
                <div class="about-grid">
                    <div class="about-content">
                        <div class="section-badge">Notre Mission</div>
                        <h2 class="about-title">Combattre dans l'Ombre pour Ceux qui Souffrent</h2>
                        <p class="about-text">
                            Depuis le <strong>21 juillet 2021</strong>, l'Association UniVersElle Ariana œuvre
                            quotidiennement pour redonner dignité et espoir aux plus vulnérables de notre société.
                        </p>
                        <p class="about-text">
                            Nous sommes une organisation humanitaire <strong>apolitique</strong> dédiée à quatre causes
                            essentielles :
                        </p>
                        <div class="about-causes">
                            <div class="cause-item">
                                <div class="cause-icon">
                                    <i class="fas fa-child"></i>
                                </div>
                                <div class="cause-text">
                                    <h4>Enfants Démunis</h4>
                                    <p>Sans accès à l'éducation ou aux soins</p>
                                </div>
                            </div>
                            <div class="cause-item">
                                <div class="cause-icon">
                                    <i class="fas fa-heartbeat"></i>
                                </div>
                                <div class="cause-text">
                                    <h4>Personnes Âgées & Malades</h4>
                                    <p>Abandonnées et sans ressources</p>
                                </div>
                            </div>
                            <div class="cause-item">
                                <div class="cause-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="cause-text">
                                    <h4>Sans-Abri (SDF)</h4>
                                    <p>Survivant sans ressources</p>
                                </div>
                            </div>
                            <div class="cause-item">
                                <div class="cause-icon">
                                    <i class="fas fa-hand-holding-heart"></i>
                                </div>
                                <div class="cause-text">
                                    <h4>Orphelins</h4>
                                    <p>Privés de sécurité affective</p>
                                </div>
                            </div>
                        </div>
                        <div class="about-cta">
                            <button class="btn-primary" onclick="window.location.href='#cases'">En Savoir Plus</button>
                            <button class="btn-outline" onclick="openLoginModal()">Rejoindre l'Équipe</button>
                        </div>
                    </div>
                    <div class="about-visual">
                        <div class="visual-card card-large">
                            <img src="https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=600"
                                alt="Solidarité">
                        </div>
                        <div class="visual-card card-small-1">
                            <img src="https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=400" alt="Enfants">
                        </div>
                        <div class="visual-card card-small-2">
                            <img src="https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=400" alt="Aide">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Events Section -->
        <section id="events" class="events-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Événements à Venir</h2>
                    <p class="section-subtitle">Rejoignez-nous lors de nos prochaines actions solidaires</p>
                </div>
                <div class="events-grid">
                    <?php if (empty($upcomingEvents)): ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                            <p>Aucun événement à venir pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcomingEvents as $event):
                            $date = strtotime($event['event_date']);
                            $day = date('d', $date);
                            $month = date('M', $date);
                            $months = ['Jan' => 'JAN', 'Feb' => 'FÉV', 'Mar' => 'MAR', 'Apr' => 'AVR', 'May' => 'MAI', 'Jun' => 'JUIN', 'Jul' => 'JUIL', 'Aug' => 'AOÛT', 'Sep' => 'SEP', 'Oct' => 'OCT', 'Nov' => 'NOV', 'Dec' => 'DÉC'];
                            $monthFr = $months[$month] ?? $month;
                            ?>
                            <div class="event-card">
                                <div class="event-date">
                                    <div class="date-day"><?= $day ?></div>
                                    <div class="date-month"><?= $monthFr ?></div>
                                </div>
                                <div class="event-content">
                                    <h3 class="event-title"><?= htmlspecialchars($event['title']) ?></h3>
                                    <p class="event-description">
                                        <?= htmlspecialchars(substr($event['description'], 0, 100)) . (strlen($event['description']) > 100 ? '...' : '') ?>
                                    </p>
                                    <div class="event-meta">
                                        <span><i class="fas fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($event['location']) ?></span>
                                        <span><i class="fas fa-clock"></i> <?= date('H:i', $date) ?></span>
                                    </div>
                                    <button class="btn-event"
                                        onclick="window.location.href='event-details.php?id=<?= $event['id'] ?>'">Participer</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="cta-background"></div>
            <div class="container">
                <div class="cta-content">
                    <h2 class="cta-title">Votre talent numérique au service de la solidarité.</h2>
                    <p class="cta-subtitle">VLa technologie au service de l'urgence sociale.</p>
                    <div class="cta-buttons">
                        <button class="btn-cta-primary" onclick="window.location.href='#cases'">
                            <span>Faire un Don Maintenant</span>
                            <i class="fas fa-heart"></i>
                        </button>
                        <button class="btn-cta-secondary" onclick="window.location.href='#contact'">
                            <span>Devenir Bénévole</span>
                            <i class="fas fa-hands-helping"></i>
                        </button>
                    </div>
                    <div class="cta-quote">
                        <i class="fas fa-quote-left"></i>
                        <p>"Le numérique au cœur de l'action solidaire"</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="contact-section">
            <div class="container">
                <div class="contact-grid">
                    <div class="contact-info">
                        <h2 class="contact-title">Contactez-Nous</h2>
                        <p class="contact-description">
                            Notre équipe est disponible pour répondre à vos questions et vous accompagner dans votre
                            démarche solidaire.
                        </p>
                        <div class="contact-details">
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-text">
                                    <h4>Téléphone</h4>
                                    <p>95 403 001</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-text">
                                    <h4>Email</h4>
                                    <p>universellecelluleariana@gmail.com</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-text">
                                    <h4>Localisation</h4>
                                    <p>Ariana, Tunisie</p>
                                </div>
                            </div>
                        </div>
                        <div class="social-links">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                    <div class="contact-form-container">
                        <form class="contact-form">
                            <div class="form-group">
                                <input type="text" placeholder="Votre Nom" required>
                            </div>
                            <div class="form-group">
                                <input type="email" placeholder="Votre Email" required>
                            </div>
                            <div class="form-group">
                                <input type="tel" placeholder="Votre Téléphone">
                            </div>
                            <div class="form-group">
                                <select>
                                    <option>Sujet du Message</option>
                                    <option>Question Générale</option>
                                    <option>Devenir Bénévole</option>
                                    <option>Proposer un Cas</option>
                                    <option>Partenariat</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea placeholder="Votre Message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn-submit">
                                <span>Envoyer le Message</span>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <a href="#home">
                        <h3 class="footer-title">UniVersElle Ariana</h3>
                    </a>
                    <p class="footer-description">
                        Association humanitaire dédiée à redonner espoir et dignité aux plus vulnérables.
                    </p>
                    <div class="footer-badge">
                        <i class="fas fa-certificate"></i>
                        <span>+300,000 vies touchées depuis 2021</span>
                    </div>
                </div>
                <div class="footer-section">
                    <h4 class="footer-section-title">Navigation</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Accueil</a></li>
                        <li><a href="#cases">Cas Sociaux</a></li>
                        <li><a href="#about">À Propos</a></li>
                        <li><a href="#events">Événements</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4 class="footer-section-title">Catégories</h4>
                    <ul class="footer-links">
                        <li><a href="#">Santé</a></li>
                        <li><a href="#">Handicap</a></li>
                        <li><a href="#">Éducation</a></li>
                        <li><a href="#">Enfants</a></li>
                        <li><a href="#">Rénovation</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4 class="footer-section-title">S'impliquer</h4>
                    <ul class="footer-links">
                        <li><a href="#">Faire un Don</a></li>
                        <li><a href="#">Devenir Bénévole</a></li>
                        <li><a href="#">Proposer un Cas</a></li>
                        <li><a href="#">Partenariats</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 UniVersElle Ariana. Tous droits réservés.</p>
                <div class="footer-legal">
                    <a href="#">Mentions Légales</a>
                    <span>•</span>
                    <a href="#">Politique de Confidentialité</a>
                    <span>•</span>
                    <a href="#">CGU</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Interactvitity Script -->
    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-overlay" onclick="closeModal()"></div>
        <div class="modal-container">
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-header">
                <h2>Connexion</h2>
                <p>Bienvenue sur UniVersElle Ariana</p>
            </div>
            <div class="modal-tabs">
                <button class="tab-btn active" data-role="donor" onclick="switchLoginTab('donor')">
                    <i class="fas fa-user"></i>
                    <span>Donateur</span>
                </button>
                <button class="tab-btn" data-role="association" onclick="switchLoginTab('association')">
                    <i class="fas fa-handshake"></i>
                    <span>Association</span>
                </button>
                <button class="tab-btn" data-role="admin" onclick="switchLoginTab('admin')">
                    <i class="fas fa-shield-halved"></i>
                    <span>Admin</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="loginForm" action="controller/AuthController.php" method="POST">
                    <input type="hidden" name="role" id="loginRole" value="donor">
                    <div class="form-group">
                        <label for="loginEmail">Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="loginEmail" name="email" placeholder="votre@email.com" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="loginPassword" name="password" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Se souvenir de moi</span>
                        </label>
                        <a href="#" class="forgot-link">Mot de passe oublié ?</a>
                    </div>
                    <button type="submit" class="btn-submit-login">
                        <span>Se connecter</span>
                        <i class="fas fa-sign-in-alt"></i>
                    </button>
                    <!-- Face ID Toggle Switch (The "t" feature) -->
                    <div class="face-toggle-wrapper">
                        <div class="face-toggle-info">
                            <i class="fas fa-face-smile"></i>
                            <div class="face-toggle-text">
                                <span>Authentification Faciale</span>
                                <small>Sécurisé par Face-API.js</small>
                            </div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="btnFaceLogin">
                            <span class="slider"></span>
                        </label>
                    </div>
                </form>
                <div class="modal-footer">
                    <p>Pas encore de compte ? <a href="#" id="openRegister">S'inscrire</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-overlay" onclick="closeRegisterModal()"></div>
        <div class="modal-container">
            <button class="modal-close" onclick="closeRegisterModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-header">
                <h2>Créer un Compte</h2>
                <p>Rejoignez notre communauté solidaire</p>
            </div>
            <div class="modal-body">
                <div class="modal-tabs">
                    <button class="tab-btn active" data-role="donor" onclick="switchRegisterTab('donor')">
                        <i class="fas fa-user"></i>
                        <span>Donateur</span>
                    </button>
                    <button class="tab-btn" data-role="association" onclick="switchRegisterTab('association')">
                        <i class="fas fa-handshake"></i>
                        <span>Association</span>
                    </button>
                    <button class="tab-btn" data-role="admin" onclick="switchRegisterTab('admin')">
                        <i class="fas fa-shield-halved"></i>
                        <span>Admin</span>
                    </button>
                </div>
                <form id="registerForm" method="POST">
                    <input type="hidden" name="role" id="registerRole" value="donor">
                    <input type="hidden" name="face_descriptor" id="faceDescriptorInput">

                    <!-- Common Fields -->
                    <div class="form-group">
                        <label for="registerFullName">Nom Complet</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="registerFullName" name="full_name" placeholder="Votre nom complet"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="registerEmail">Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="registerEmail" name="email" placeholder="votre@email.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="registerPassword">Mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="registerPassword" name="password" placeholder="••••••••" required
                                minlength="6">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="registerPasswordConfirm">Confirmer le mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="registerPasswordConfirm" name="password_confirm"
                                placeholder="••••••••" required minlength="6">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="registerPhone">Téléphone</label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="registerPhone" name="phone" placeholder="+216 XX XXX XXX">
                        </div>
                    </div>

                    <!-- Association-specific fields -->
                    <div id="associationFields" style="display: none;">
                        <div class="form-group">
                            <label for="registerAssociationName">Nom de l'Association</label>
                            <div class="input-wrapper">
                                <i class="fas fa-building"></i>
                                <input type="text" id="registerAssociationName" name="association_name"
                                    placeholder="Nom de votre association">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="registerDescription">Description</label>
                            <div class="input-wrapper">
                                <i class="fas fa-align-left"></i>
                                <textarea id="registerDescription" name="description"
                                    placeholder="Décrivez votre association et sa mission" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="registerWebsite">Site Web (optionnel)</label>
                            <div class="input-wrapper">
                                <i class="fas fa-globe"></i>
                                <input type="url" id="registerWebsite" name="website_url"
                                    placeholder="https://votre-site.com">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="registerAddress">Adresse</label>
                        <div class="input-wrapper">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" id="registerAddress" name="address" placeholder="Votre adresse">
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="terms" required>
                            <span>J'accepte les <a href="#" class="forgot-link">conditions d'utilisation</a></span>
                        </label>
                    </div>

                    <button type="submit" class="btn-submit-login">
                        <span>S'inscrire</span>
                        <i class="fas fa-user-plus"></i>
                    </button>
                    <!-- Face ID Toggle Switch (The "t" feature) -->
                    <div class="face-toggle-wrapper">
                        <div class="face-toggle-info">
                            <i class="fas fa-face-smile"></i>
                            <div class="face-toggle-text">
                                <span>Enregistrer mon visage</span>
                                <small>Pour une connexion simplifiée</small>
                            </div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="btnFaceRegister"
                                onchange="this.checked ? registerMyFace() : null">
                            <span class="slider"></span>
                        </label>
                    </div>
                </form>
                <div class="modal-footer">
                    <p>Vous avez déjà un compte ? <a href="#" id="openLogin">Se connecter</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Case Details Modal -->
    <div id="caseModal" class="modal">
        <div class="modal-overlay" onclick="closeCaseModal()"></div>
        <div class="modal-container case-detail-container">
            <button class="modal-close" onclick="closeCaseModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="case-detail-content">
                <div class="case-detail-image">
                    <img id="modalCaseImage" src="" alt="">
                    <div id="modalCaseBadge" class="case-badge"></div>
                </div>
                <div class="case-detail-info">
                    <div class="case-detail-header">
                        <div class="case-category">
                            <i id="modalCaseIcon" class="fas fa-heartbeat"></i>
                            <span id="modalCaseCategory">Santé</span>
                        </div>
                        <h2 id="modalCaseTitle"></h2>
                    </div>
                    <div class="case-detail-body">
                        <p id="modalCaseDescription"></p>
                        <div class="case-progress-detailed">
                            <div class="progress-info">
                                <span>Progression de la collecte</span>
                                <span id="modalCasePercent">0%</span>
                            </div>
                            <div class="progress-bar">
                                <div id="modalCaseProgressFill" class="progress-fill" style="width: 0%"></div>
                            </div>
                            <div class="progress-amounts">
                                <div class="amount-item">
                                    <span class="label">Collecté</span>
                                    <span id="modalCaseRaised" class="value">0 TND</span>
                                </div>
                                <div class="amount-item">
                                    <span class="label">Objectif</span>
                                    <span id="modalCaseGoal" class="value">0 TND</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="case-detail-footer">
                        <div class="case-meta">
                            <span><i class="far fa-eye"></i> <span id="modalCaseViews">0</span> vues</span>
                            <span><i class="far fa-calendar"></i> Publié le <span id="modalCaseDate"></span></span>
                        </div>
                        <button class="btn-donate-modal" id="modalDonateBtn">
                            <i class="fas fa-heart"></i>
                            <span>Soutenir sur Cha9a9a</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Canvas/Video Container (Hidden) -->
    <div id="ai-container"
        style="position: fixed; top: 10px; right: 10px; width: 150px; height: 110px; z-index: 9999; border-radius: 10px; overflow: hidden; background: #000; border: 2px solid var(--primary-color); display: none;">
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

    <!-- Gesture Visual Feedback -->
    <div id="gesture-indicator" class="gesture-indicator"></div>
    <div id="virtual-cursor" class="virtual-cursor"></div>

    <!-- accessibility AI Assistant Chatbot -->
    <div id="chatbot-widget" class="chatbot-widget">
        <div class="chatbot-header">
            <div class="chatbot-ai-icon">
                <i class="fas fa-robot"></i>
                <div class="ai-pulse"></div>
            </div>
            <div class="chatbot-title">
                <h4>Assistant AI</h4>
                <p>Besoin d'aide ? Dites "Bonjour"</p>
            </div>
            <button class="chatbot-close" onclick="toggleChatbot()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="chatbot-messages" class="chatbot-messages">
            <div class="message ai-message">
                Bonjour ! Je suis l'assistant vocal de Maratech. Comment puis-je vous aider aujourd'hui ?
            </div>
        </div>
        <div class="chatbot-footer">
            <button id="chatbot-voice-btn" class="chatbot-voice-btn" title="Activer la commande vocale">
                <i class="fas fa-microphone"></i>
            </button>
            <div class="chatbot-input">
                <input type="text" id="chatbot-input-field" placeholder="Posez une question...">
                <button id="chatbot-send-btn"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
    <button id="chatbot-trigger" class="chatbot-trigger" onclick="toggleChatbot()">
        <i class="fas fa-comment-dots"></i>
    </button>

    <!-- MediaPipe dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>

    <!-- App Scripts -->
    <script src="script.js?v=3.0"></script>
    <script src="accessibility.js?v=1.0"></script>
    <script src="face-api.min.js"></script>
    <script src="face-auth.js?v=4.0"></script>
    <script src="gestures.js?v=2.0"></script>
    <script src="chatbot.js?v=1.0"></script>
</body>

</html>