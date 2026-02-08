<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégorie | UniVersElle Ariana</title>
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>

<body class="category-detail-page">
    <!-- Navbar (Shared) -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <span class="logo-icon"><i class="fas fa-hand-holding-heart"></i></span>
                <span class="logo-text">UniVersElle<span>Ariana</span></span>
            </a>
            <div class="nav-links">
                <a href="index.php">Accueil</a>
                <a href="index.html#cases" class="active">Cas Sociaux</a>
                <a href="index.html#events">Événements</a>
                <a href="index.html#about">À Propos</a>
            </div>
            <div class="nav-actions">
                <button class="btn-search"><i class="fas fa-search"></i></button>
                <div class="auth-buttons">
                    <button class="btn-login" onclick="window.location.href='../login.html'">Connexion</button>
                    <button class="btn-register" onclick="window.location.href='../register.html'">Rejoindre</button>
                </div>
                <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
            </div>
        </div>
    </nav>

    <main>
        <!-- Category Hero Section -->
        <section class="category-hero">
            <div class="container">
                <div class="hero-content">
                    <div class="category-badge-lg" id="categoryBadge">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h1 class="category-title" id="categoryTitle">Soins médicaux et traitements urgents</h1>
                    <p class="category-subtitle" id="categorySubtitle">24 cas actifs ont besoin de votre aide</p>

                    <!-- Real-time Counter -->
                    <div class="realtime-counter-bar">
                        <div class="counter-item">
                            <span class="dot pulse-red"></span>
                            <span id="activeCountText">24 personnes attendent une aide médicale</span>
                        </div>
                        <div class="counter-divider"></div>
                        <div class="counter-item">
                            <span class="icon-success"><i class="fas fa-check-circle"></i></span>
                            <span id="resolvedCountText">3 opérations financées cette semaine</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 1: Top Urgent Cases -->
        <section class="urgent-highlight-section">
            <div class="container">
                <div class="section-header-compact">
                    <h2 class="sub-section-title"><i class="fas fa-fire-alt"></i> Cas les plus urgents de cette
                        catégorie</h2>
                </div>
                <div class="urgent-highlight-grid" id="urgentGrid">
                    <!-- Placeholder for Top 3 Urgent Cases -->
                    <!-- Cards here will have .urgent-featured class with red glow -->
                </div>
            </div>
        </section>

        <!-- Section 2: Advanced Filters -->
        <section class="advanced-filters-section">
            <div class="container">
                <div class="filter-panel glass-card">
                    <div class="filter-group">
                        <label><i class="fas fa-sort-amount-down"></i> Tri par :</label>
                        <select id="sortFilter" class="filter-select">
                            <option value="urgency">Urgence</option>
                            <option value="amount_desc">Montant (Décroissant)</option>
                            <option value="amount_asc">Montant (Croissant)</option>
                            <option value="date">Plus récent</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-info-circle"></i> Statut :</label>
                        <div class="status-pills">
                            <button class="pill active" data-status="all">Tous</button>
                            <button class="pill" data-status="urgent">🔴 Urgent</button>
                            <button class="pill" data-status="in_progress">🟡 En cours</button>
                            <button class="pill" data-status="completed">🟢 Terminé</button>
                        </div>
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-map-marker-alt"></i> Localisation :</label>
                        <select id="regionFilter" class="filter-select">
                            <option value="all">Toutes les régions</option>
                            <option value="Tunis">Tunis</option>
                            <option value="Ariana">Ariana</option>
                            <option value="Ben Arous">Ben Arous</option>
                            <option value="Manouba">Manouba</option>
                            <option value="Sousse">Sousse</option>
                            <option value="Sfax">Sfax</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>
                    <div class="filter-group search-group">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" id="categorySearch" placeholder="Rechercher dans cette catégorie...">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Case List -->
        <section class="category-cases-section">
            <div class="container">
                <div class="cases-results-grid" id="categoryCasesGrid">
                    <!-- Case cards populated by JS -->
                </div>
                <!-- Pagination -->
                <div id="paginationContainer" class="pagination-wrapper">
                    <button class="btn-show-more" id="btnShowMore">Voir plus de cas</button>
                </div>
            </div>
        </section>

        <!-- Section 4: Secondary CTA -->
        <section class="category-footer-cta">
            <div class="container">
                <div class="cta-banner glass-card">
                    <div class="cta-side">
                        <i class="fas fa-building-ngo"></i>
                        <div>
                            <h4>Vous représentez une association ?</h4>
                            <p>Publiez un cas médical urgent pour obtenir du soutien.</p>
                            <a href="../register.html?role=association" class="cta-link">Publier un cas <i
                                    class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="cta-divider"></div>
                    <div class="cta-side">
                        <i class="fas fa-user-md"></i>
                        <div>
                            <h4>Devenir bénévole médical</h4>
                            <p>Mettez vos compétences au service de ceux qui en ont besoin.</p>
                            <a href="index.html#contact" class="cta-link">Rejoindre l'équipe <i
                                    class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer (Shared) -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2024 UniVersElle Ariana - Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="script.js"></script>
</body>

</html>