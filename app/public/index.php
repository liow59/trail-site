<?php 
require_once __DIR__ . '/../src/bootstrap.php';

$stats = new Statistics();
$raceStats = $stats->getRaceStats();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trail de la Vogue Challaisienne 2026</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <span class="badge">▲ 1ÈRE ÉDITION</span>
    <h1><span style="display:inline-block; font-size:0.7em; margin-right:0.3em; color:var(--lime);">▲</span>TRAIL<span style="display:inline-block; font-size:0.7em; margin-left:0.3em; color:var(--lime);">▲</span><br><span class="text-lime">DE LA</span><br>VOGUE<br>CHALLAISIENNE</h1>
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
        <p style="font-family:'Bebas Neue',sans-serif; font-size:1.3rem; color:var(--lime); letter-spacing:0.05em;">Parking de la Halle</p>
        <p style="font-size:0.85rem; color:var(--sand);">381 rue de la Mairie, 01630 Challex</p>
      </div>
    </div>
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1368.9!2d5.975!3d46.181!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2s381%20Rue%20de%20la%20Mairie%2C%2001630%20Challex!5e1!3m2!1sfr!2sfr!4v1234567890" width="100%" height="300" style="border:0; display:block;" allowfullscreen="" loading="lazy"></iframe>
    <div style="padding:1.25rem 1.5rem; display:flex; gap:1rem; flex-wrap:wrap; justify-content:center;">
      <a href="https://www.google.com/maps/search/381+Rue+de+la+Mairie+01630+Challex" target="_blank" style="background:var(--lime); color:var(--earth); font-family:'DM Sans',sans-serif; font-weight:600; font-size:0.9rem; padding:0.75rem 1.5rem; border-radius:2px; text-decoration:none; letter-spacing:0.05em;">📍 Ouvrir dans Google Maps</a>
      <a href="https://waze.com/ul?q=381+Rue+de+la+Mairie+Challex&navigate=yes" target="_blank" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:var(--cream); font-family:'DM Sans',sans-serif; font-weight:500; font-size:0.9rem; padding:0.75rem 1.5rem; border-radius:2px; text-decoration:none; letter-spacing:0.05em;">🚗 Ouvrir dans Waze</a>
    </div>
  </div>
</section>

<!-- COURSES PREVIEW -->
<section class="section">
  <p class="section-tag">// Les parcours</p>
  <h2 class="section-title">Trois distances<br>pour tous</h2>
  <div class="races-grid">
    <a href="/inscription.php?course=3km" style="text-decoration:none; color:inherit;">
      <div class="race-card">
        <div class="race-dist" style="font-size:2.5rem; color:var(--sky)">3<small style="font-size:1.5rem">km</small></div>
        <div class="race-type">
          <div class="race-info-item"><span class="icon">🕚</span><span>Départ à 11h00</span></div>
          <div class="race-info-item"><span class="icon">👦</span><span>De 8 à 11 ans</span></div>
          <div class="race-info-item"><span class="icon">👨‍👧</span><span>Accompagnement adulte possible</span></div>
        </div>
        <div class="race-price" style="color:var(--sky)">Gratuit</div>
        <div class="race-spots">
          <div class="spots-bar"><div class="spots-fill" style="width:<?= $raceStats['3km']['percentage'] ?>%; background:var(--sky)"></div></div>
          <span class="spots-text"><?= $raceStats['3km']['registered'] ?> inscrits · <?= $raceStats['3km']['remaining'] ?> / <?= $raceStats['3km']['total'] ?> places</span>
        </div>
      </div>
    </a>
    
    <a href="/inscription.php?course=7.5km" style="text-decoration:none; color:inherit;">
      <div class="race-card">
        <div class="race-dist">7.5<small style="font-size:1.5rem">km</small></div>
        <div class="race-type">
          <div class="race-info-item"><span class="icon">🕙</span><span>Départ à 10h00</span></div>
          <div class="race-info-item"><span class="icon">🏃</span><span>À partir de 12 ans</span></div>
          <div class="race-info-item"><span class="icon">⛰</span><span>150 D+</span></div>
        </div>
        <div class="race-price"><?= $raceStats['7.5km']['price'] ?> €</div>
        <div style="margin-top:0.75rem;">
          <span class="gpx-link" onclick="event.preventDefault(); event.stopPropagation(); openGpxModal('7.5km');">🗺 Voir le parcours</span>
        </div>
        <div class="race-spots">
          <div class="spots-bar"><div class="spots-fill" style="width:<?= $raceStats['7.5km']['percentage'] ?>%"></div></div>
          <span class="spots-text"><?= $raceStats['7.5km']['registered'] ?> inscrits · <?= $raceStats['7.5km']['remaining'] ?> / <?= $raceStats['7.5km']['total'] ?> places</span>
        </div>
      </div>
    </a>
    
    <a href="/inscription.php?course=15km" style="text-decoration:none; color:inherit;">
      <div class="race-card">
        <div class="race-dist">15<small style="font-size:1.5rem">km</small></div>
        <div class="race-type">
          <div class="race-info-item"><span class="icon">🕘</span><span>Départ à 9h00</span></div>
          <div class="race-info-item"><span class="icon">🏃</span><span>À partir de 16 ans</span></div>
          <div class="race-info-item"><span class="icon">🔄</span><span>2 boucles · 300 D+</span></div>
        </div>
        <div class="race-price"><?= $raceStats['15km']['price'] ?> €</div>
        <div class="race-spots">
          <div class="spots-bar"><div class="spots-fill" style="width:<?= $raceStats['15km']['percentage'] ?>%"></div></div>
          <span class="spots-text"><?= $raceStats['15km']['registered'] ?> inscrits · <?= $raceStats['15km']['remaining'] ?> / <?= $raceStats['15km']['total'] ?> places</span>
        </div>
      </div>
    </a>
  </div>
  <div style="text-align:center; margin-top:2rem;">
    <a href="/inscription.php" class="cta-btn">Choisir ma course →</a>
  </div>
</section>

<!-- MODAL GPX -->
<div id="gpx-modal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.85); padding:1rem;">
  <div style="position:relative; width:100%; height:100%; max-width:1000px; margin:0 auto; display:flex; flex-direction:column;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:1rem 0;">
      <h3 style="font-family:'Bebas Neue',sans-serif; font-size:1.8rem; color:var(--lime);" id="gpx-modal-title">Parcours 7.5km</h3>
      <button onclick="closeGpxModal()" style="background:none; border:none; color:var(--cream); font-size:2rem; cursor:pointer;">✕</button>
    </div>
    <div id="gpx-map" style="flex:1; border-radius:4px; min-height:400px;"></div>
    <div id="gpx-stats" style="display:flex; gap:2rem; padding:1rem 0; justify-content:center; flex-wrap:wrap;"></div>
  </div>
</div>

<footer style="text-align:center; padding:2rem; color:var(--sand); font-size:0.85rem; border-top:1px solid rgba(255,255,255,0.1); margin-top:4rem;">
  <p>© 2026 Trail de la Vogue Challaisienne · Tous droits réservés</p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.7.0/gpx.min.js"></script>
<script>
const gpxFiles = {
  '7.5km': '/assets/gpx/parcours-7.5km.gpx'
};

let gpxMap = null;

function openGpxModal(course) {
  const modal = document.getElementById('gpx-modal');
  const title = document.getElementById('gpx-modal-title');
  const statsDiv = document.getElementById('gpx-stats');
  
  modal.style.display = 'block';
  document.body.style.overflow = 'hidden';
  title.textContent = 'Parcours ' + course;
  
  // Détruire l'ancienne carte si elle existe
  if (gpxMap) {
    gpxMap.remove();
    gpxMap = null;
  }
  
  // Créer la carte
  gpxMap = L.map('gpx-map');
  
  // Fond satellite
  L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Esri',
    maxZoom: 18
  }).addTo(gpxMap);
  
  // Charger le GPX
  new L.GPX(gpxFiles[course], {
    async: true,
    marker_options: {
      startIconUrl: null,
      endIconUrl: null,
      shadowUrl: null,
      wptIconUrls: {}
    },
    polyline_options: {
      color: '#a8c640',
      weight: 4,
      opacity: 0.9,
      lineCap: 'round'
    }
  }).on('loaded', function(e) {
    var gpx = e.target;
    gpxMap.fitBounds(gpx.getBounds(), { padding: [30, 30] });
    
    // Ajouter marqueur départ/arrivée
    var start = gpx.get_start_point();
    if (start) {
      L.circleMarker([start.lat, start.lng], {
        radius: 8, color: '#a8c640', fillColor: '#a8c640', fillOpacity: 1, weight: 2
      }).addTo(gpxMap).bindPopup('<b>Départ / Arrivée</b><br>Parking de la Halle');
    }
    
    // Stats
    var dist = (gpx.get_distance() / 1000).toFixed(1);
    var eleGain = Math.round(gpx.get_elevation_gain());
    var eleLoss = Math.round(gpx.get_elevation_loss());
    var eleMin = Math.round(gpx.get_elevation_min());
    var eleMax = Math.round(gpx.get_elevation_max());
    
    statsDiv.innerHTML = 
      '<div style="text-align:center;"><div style="font-family:Bebas Neue,sans-serif; font-size:2rem; color:var(--lime);">' + dist + ' km</div><div style="font-size:0.75rem; color:var(--sand);">Distance</div></div>' +
      '<div style="text-align:center;"><div style="font-family:Bebas Neue,sans-serif; font-size:2rem; color:var(--lime);">+ ' + eleGain + ' m</div><div style="font-size:0.75rem; color:var(--sand);">Dénivelé +</div></div>' +
      '<div style="text-align:center;"><div style="font-family:Bebas Neue,sans-serif; font-size:2rem; color:var(--lime);">- ' + eleLoss + ' m</div><div style="font-size:0.75rem; color:var(--sand);">Dénivelé -</div></div>' +
      '<div style="text-align:center;"><div style="font-family:Bebas Neue,sans-serif; font-size:2rem; color:var(--cream);">' + eleMin + ' - ' + eleMax + ' m</div><div style="font-size:0.75rem; color:var(--sand);">Altitude</div></div>';
  }).on('error', function(e) {
    statsDiv.innerHTML = '<p style="color:var(--rust);">Erreur chargement du parcours</p>';
  }).addTo(gpxMap);
}

function closeGpxModal() {
  document.getElementById('gpx-modal').style.display = 'none';
  document.body.style.overflow = '';
  if (gpxMap) {
    gpxMap.remove();
    gpxMap = null;
  }
}

// Fermer avec Escape
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeGpxModal();
});
</script>
</body>
</html>
