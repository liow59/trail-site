<?php require_once __DIR__ . '/../src/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trail 2025 – Courez dans les montagnes</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<!-- HERO -->
<section class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-eyebrow">Édition 2025</span>
        <h1>Trail des Crêtes</h1>
        <p class="hero-subtitle">10km · 23km · 42km — Samedi 12 juillet 2025</p>
        <a href="/inscription.php" class="btn-primary">S'inscrire maintenant</a>
    </div>
    <div class="hero-scroll">↓</div>
</section>

<!-- COURSES -->
<section class="section" id="courses">
    <div class="container">
        <h2 class="section-title">Les courses</h2>
        <div class="cards">
            <div class="card">
                <div class="card-badge">Découverte</div>
                <h3>10 km</h3>
                <p class="card-stat">D+ 400m</p>
                <ul>
                    <li>Accessible à tous</li>
                    <li>Ravitaillement x2</li>
                    <li>Départ 10h00</li>
                </ul>
                <p class="card-price">25 €</p>
                <a href="/inscription.php?course=10km" class="btn-outline">S'inscrire</a>
            </div>
            <div class="card card--featured">
                <div class="card-badge">Populaire</div>
                <h3>23 km</h3>
                <p class="card-stat">D+ 1 100m</p>
                <ul>
                    <li>Semi-technique</li>
                    <li>Ravitaillement x4</li>
                    <li>Départ 8h30</li>
                </ul>
                <p class="card-price">40 €</p>
                <a href="/inscription.php?course=23km" class="btn-primary">S'inscrire</a>
            </div>
            <div class="card">
                <div class="card-badge">Expert</div>
                <h3>42 km</h3>
                <p class="card-stat">D+ 2 600m</p>
                <ul>
                    <li>Technique & engagé</li>
                    <li>Ravitaillement x6</li>
                    <li>Départ 6h00</li>
                </ul>
                <p class="card-price">60 €</p>
                <a href="/inscription.php?course=42km" class="btn-outline">S'inscrire</a>
            </div>
        </div>
    </div>
</section>

<!-- INFOS -->
<section class="section section--dark" id="infos">
    <div class="container">
        <h2 class="section-title">Informations pratiques</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-icon">📍</span>
                <h4>Lieu de départ</h4>
                <p>Place du village<br>74000 Annecy</p>
            </div>
            <div class="info-item">
                <span class="info-icon">📅</span>
                <h4>Date</h4>
                <p>Samedi 12 juillet 2025<br>Retraits dossards dès 7h00</p>
            </div>
            <div class="info-item">
                <span class="info-icon">🏆</span>
                <h4>Récompenses</h4>
                <p>Podium par catégorie<br>Finisher medal pour tous</p>
            </div>
            <div class="info-item">
                <span class="info-icon">🚗</span>
                <h4>Parking gratuit</h4>
                <p>Parking de l'église<br>Navettes disponibles</p>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT -->
<section class="section" id="contact">
    <div class="container container--narrow">
        <h2 class="section-title">Contact</h2>
        <p style="text-align:center">
            Une question ? Écrivez-nous à
            <a href="mailto:<?= htmlspecialchars($_ENV['BREVO_FROM_EMAIL'] ?? 'contact@trail.fr') ?>">
                <?= htmlspecialchars($_ENV['BREVO_FROM_EMAIL'] ?? 'contact@trail.fr') ?>
            </a>
        </p>
    </div>
</section>

<footer class="footer">
    <p>© 2025 Trail des Crêtes · Tous droits réservés</p>
</footer>

</body>
</html>
