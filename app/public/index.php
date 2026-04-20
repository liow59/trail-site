<?php require_once __DIR__ . '/../src/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trail de la Vogue Challaisienne 2026</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <span class="badge">▲ 1ÈRE ÉDITION</span>
    <h1>TRAIL<br><span class="text-lime">DE LA</span><br>VOGUE<br>CHALLAISIENNE</h1>
    <p class="subtitle">COURSE NATURE — INSCRIPTIONS OUVERTES 2026</p>
    <div class="hero-date-box">
      <span class="date-icon">📅</span>
      <span class="date-text">DIMANCHE 6 SEPTEMBRE 2026</span>
    </div>
    <p style="font-family:'DM Mono',sans-serif; font-size:0.85rem; color:var(--sand); letter-spacing:0.12em; margin-bottom:1.5rem;">⏱ Course non chronométrée</p>
    <a href="/inscription.php" class="cta-btn">S'inscrire maintenant</a>
  </div>
  <div class="scroll-hint">
    <span>↓ Découvrir</span>
  </div>
</section>

<!-- LIEU -->
<section class="section">
  <p class="section-tag">// Lieu de départ</p>
  <h2 class="section-title">Où nous<br>trouver ?</h2>
  <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:4px; overflow:hidden; margin-bottom:1.5rem;">
    <div style="padding:1.25rem 1.5rem; display:flex; align-items:center; gap:1rem; border-bottom:1px solid rgba(255,255,255,0.07);">
      <span style="font-size:1.5rem;">📍</span>
      <div>
        <p style="font-family:'Bebas Neue',sans-serif; font-size:1.3rem; color:var(--lime); letter-spacing:0.05em;">Challex — La Halle</p>
        <p style="font-size:0.85rem; color:var(--sand);">381 Rue de la Mairie, 01630 Challex</p>
      </div>
    </div>
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2737.8!2d5.9728!3d46.182!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDbCsDEwJzU1LjIiTiA1wrA1OCcyMi4xIkU!5e0!3m2!1sfr!2sfr!4v1234567890" width="100%" height="300" style="border:0; display:block;" allowfullscreen="" loading="lazy"></iframe>
    <div style="padding:1.25rem 1.5rem; display:flex; gap:1rem; flex-wrap:wrap; justify-content:center;">
      <a href="https://www.google.com/maps/search/La+Halle+Challex+381+Rue+de+la+Mairie+01630" target="_blank" style="background:var(--lime); color:var(--earth); font-family:'DM Sans',sans-serif; font-weight:600; font-size:0.9rem; padding:0.75rem 1.5rem; border-radius:2px; text-decoration:none; letter-spacing:0.05em;">📍 Ouvrir dans Google Maps</a>
      <a href="https://waze.com/ul?ll=46.182,5.9728&navigate=yes" target="_blank" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:var(--cream); font-family:'DM Sans',sans-serif; font-weight:500; font-size:0.9rem; padding:0.75rem 1.5rem; border-radius:2px; text-decoration:none; letter-spacing:0.05em;">🚗 Ouvrir dans Waze</a>
    </div>
  </div>
</section>

<!-- COURSES PREVIEW -->
<section class="section">
  <p class="section-tag">// Les parcours</p>
  <h2 class="section-title">Trois distances<br>pour tous</h2>
  <div class="races-grid">
    <div class="race-card-preview">
      <div class="race-dist" style="font-size:2.5rem; color:var(--sky)">3<small style="font-size:1.5rem">km</small></div>
      <div class="race-type">
        <div class="race-info-item"><span class="icon">🕚</span><span>Départ à 11h00</span></div>
        <div class="race-info-item"><span class="icon">👦</span><span>De 8 à 11 ans</span></div>
      </div>
      <div class="race-price" style="color:var(--sky)">Gratuit</div>
    </div>
    <div class="race-card-preview">
      <div class="race-dist">7.5<small style="font-size:1.5rem">km</small></div>
      <div class="race-type">
        <div class="race-info-item"><span class="icon">🕙</span><span>Départ à 10h00</span></div>
        <div class="race-info-item"><span class="icon">⛰</span><span>150 D+</span></div>
      </div>
      <div class="race-price">10 €</div>
    </div>
    <div class="race-card-preview">
      <div class="race-dist">15<small style="font-size:1.5rem">km</small></div>
      <div class="race-type">
        <div class="race-info-item"><span class="icon">🕘</span><span>Départ à 9h00</span></div>
        <div class="race-info-item"><span class="icon">🔄</span><span>2 boucles · 300 D+</span></div>
      </div>
      <div class="race-price">15 €</div>
    </div>
  </div>
  <div style="text-align:center; margin-top:2rem;">
    <a href="/inscription.php" class="cta-btn">Choisir ma course →</a>
  </div>
</section>

<footer style="text-align:center; padding:2rem; color:var(--sand); font-size:0.85rem; border-top:1px solid rgba(255,255,255,0.1); margin-top:4rem;">
  <p>© 2026 Trail de la Vogue Challaisienne · Tous droits réservés</p>
</footer>

</body>
</html>
