<?php require_once __DIR__ . '/../src/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trail de la Vogue Challaisienne</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav class="navbar" id="navbar">
    <a href="/" class="nav-logo">La Vogue Challaisienne</a>
    <button class="nav-toggle" id="navToggle" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
    <ul class="nav-links" id="navLinks">
        <li><a href="#courses">Courses</a></li>
        <li><a href="#infos">Infos</a></li>
        <li><a href="#contact">Contact</a></li>
        <li><a href="/inscription.php" class="nav-cta">S'inscrire</a></li>
    </ul>
</nav>

<section class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-eyebrow">Édition 2025</span>
        <h1>Trail<br><span class="text-lime">de la<br>Vogue</span><br>Challaisienne</h1>
        <p class="hero-subtitle">10km · 23km · 42km — Samedi 12 juillet 2025</p>
        <a href="/inscription.php" class="btn-primary btn--large">S'inscrire maintenant</a>
    </div>
    <div class="hero-scroll">Découvrir</div>
</section>

<section class="section section--earth" id="courses">
    <div class="container">
        <span class="section-label reveal">Les parcours</span>
        <h2 class="section-title reveal">Choisissez votre aventure</h2>
        <p class="section-desc reveal">Trois distances, un même terrain de jeu exceptionnel. Sentiers techniques, crêtes panoramiques et chemins de vignes.</p>
        <div class="cards">
            <div class="card">
                <span class="card-badge">Découverte</span>
                <h3>10 km</h3>
                <p class="card-stat">D+ 400m</p>
                <ul>
                    <li>Accessible à tous</li>
                    <li>Ravitaillement ×2</li>
                    <li>Départ 10h00</li>
                </ul>
                <p class="card-price">25 €</p>
                <a href="/inscription.php?course=10km" class="btn-outline btn--full">S'inscrire</a>
            </div>
            <div class="card card--featured">
                <span class="card-badge">Populaire</span>
                <h3>23 km</h3>
                <p class="card-stat">D+ 1 100m</p>
                <ul>
                    <li>Semi-technique</li>
                    <li>Ravitaillement ×4</li>
                    <li>Départ 8h30</li>
                </ul>
                <p class="card-price">40 €</p>
                <a href="/inscription.php?course=23km" class="btn-primary btn--full">S'inscrire</a>
            </div>
            <div class="card">
                <span class="card-badge">Expert</span>
                <h3>42 km</h3>
                <p class="card-stat">D+ 2 600m</p>
                <ul>
                    <li>Technique & engagé</li>
                    <li>Ravitaillement ×6</li>
                    <li>Départ 6h00</li>
                </ul>
                <p class="card-price">60 €</p>
                <a href="/inscription.php?course=42km" class="btn-outline btn--full">S'inscrire</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--forest" id="infos">
    <div class="container">
        <span class="section-label reveal">Informations</span>
        <h2 class="section-title reveal">Le jour J</h2>
        <p class="section-desc reveal">Tout ce qu'il faut savoir pour préparer votre course dans les meilleures conditions.</p>
        <div class="info-grid">
            <div class="info-item reveal">
                <div class="info-icon">📍</div>
                <h4>Lieu de départ</h4>
                <p>Place du village<br>74000 Annecy</p>
            </div>
            <div class="info-item reveal">
                <div class="info-icon">📅</div>
                <h4>Date</h4>
                <p>Samedi 12 juillet 2025<br>Retraits dossards dès 7h00</p>
            </div>
            <div class="info-item reveal">
                <div class="info-icon">🏆</div>
                <h4>Récompenses</h4>
                <p>Podium par catégorie<br>Médaille finisher pour tous</p>
            </div>
            <div class="info-item reveal">
                <div class="info-icon">🚗</div>
                <h4>Parking gratuit</h4>
                <p>Parking de l'église<br>Navettes disponibles</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="contact">
    <div class="container container--narrow">
        <span class="section-label reveal">Contact</span>
        <h2 class="section-title reveal">Une question ?</h2>
        <p class="reveal" style="text-align:center;color:var(--muted);margin-top:20px;">
            Écrivez-nous à
            <a href="mailto:<?= htmlspecialchars($_ENV['BREVO_FROM_EMAIL'] ?? 'contact@trail.fr') ?>">
                <?= htmlspecialchars($_ENV['BREVO_FROM_EMAIL'] ?? 'contact@trail.fr') ?>
            </a>
        </p>
    </div>
</section>

<footer class="footer">
    <p>© 2025 Trail de la Vogue Challaisienne · Tous droits réservés</p>
</footer>

<script src="/assets/js/main.js"></script>
</body>
</html>
