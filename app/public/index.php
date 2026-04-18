<?php require_once __DIR__ . '/../src/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trail de la Vogue Challex 2026</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav class="navbar" id="navbar">
    <a href="/" class="nav-logo">La Vogue Challex</a>
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
        <span class="hero-eyebrow">▲ Saison 2026</span>
        <h1>Trail<br><span class="text-lime">de la</span><br>Vogue<br><span class="text-lime">Challex</span></h1>
        <p class="hero-subtitle">Course nature — Inscriptions ouvertes 2026</p>
        <div class="hero-info">
            <p class="hero-date">📅 Dimanche 6 septembre 2026</p>
            <p class="hero-detail">⏱ Course non chronométrée</p>
        </div>
        <a href="/inscription.php" class="btn-hero">S'inscrire maintenant</a>
    </div>
    <div class="hero-scroll">↓ Découvrir</div>
</section>

<section class="section section--earth" id="courses">
    <div class="container">
        <span class="section-label reveal">Les parcours</span>
        <h2 class="section-title reveal">Choisissez votre aventure</h2>
        <p class="section-desc reveal">Trois distances pour tous les âges, au cœur des chemins de vignes et sentiers de Challex.</p>
        <div class="cards">
            <div class="card">
                <span class="card-badge">Découverte</span>
                <h3>3 km</h3>
                <p class="card-stat">Course enfants</p>
                <ul>
                    <li>Départ à 11h00</li>
                    <li>De 8 à 11 ans</li>
                    <li>Accompagnement adulte possible</li>
                </ul>
                <p class="card-price">Gratuit</p>
                <div class="card-places">50 places</div>
                <a href="/inscription.php?course=3km" class="btn-outline btn--full">S'inscrire</a>
            </div>
            <div class="card card--featured">
                <span class="card-badge">Populaire</span>
                <h3>7.5 km</h3>
                <p class="card-stat">150 D+</p>
                <ul>
                    <li>Départ à 10h00</li>
                    <li>À partir de 12 ans</li>
                    <li>Ravitaillement sur parcours</li>
                </ul>
                <p class="card-price">10 €</p>
                <div class="card-places">100 places</div>
                <a href="/inscription.php?course=7.5km" class="btn-primary btn--full">S'inscrire</a>
            </div>
            <div class="card">
                <span class="card-badge">Expert</span>
                <h3>15 km</h3>
                <p class="card-stat">300 D+ · 2 boucles</p>
                <ul>
                    <li>Départ à 9h00</li>
                    <li>À partir de 16 ans</li>
                    <li>Parcours technique</li>
                </ul>
                <p class="card-price">15 €</p>
                <div class="card-places">100 places</div>
                <a href="/inscription.php?course=15km" class="btn-outline btn--full">S'inscrire</a>
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
                <p>Challex — La Halle<br>381 Rue de la Mairie<br>01630 Challex</p>
            </div>
            <div class="info-item reveal">
                <div class="info-icon">📅</div>
                <h4>Date</h4>
                <p>Dimanche 6 septembre 2026<br>Retraits dossards sur place</p>
            </div>
            <div class="info-item reveal">
                <div class="info-icon">🏆</div>
                <h4>Récompenses</h4>
                <p>Podium par catégorie<br>Médaille finisher pour tous</p>
            </div>
            <div class="info-item reveal">
                <div class="info-icon">🚗</div>
                <h4>Parking gratuit</h4>
                <p>Parking à proximité<br>de La Halle</p>
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
    <p>© 2026 Trail de la Vogue Challex · Tous droits réservés</p>
</footer>

<script src="/assets/js/main.js"></script>
</body>
</html>
