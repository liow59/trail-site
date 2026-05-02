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
<title>Trail de la Vogue Challaisienne 2026 — www.vogue-challex.fr</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
  .gpx-overlay { display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.92); overflow-y:auto; }
  .gpx-overlay.active { display:block; }
  .gpx-inner { max-width:1000px; margin:0 auto; padding:1rem; min-height:100vh; }
  .gpx-header { display:flex; justify-content:space-between; align-items:center; padding:0.5rem 0 1rem; }
  .gpx-header h2 { font-family:'Bebas Neue',sans-serif; font-size:1.8rem; color:var(--lime); }
  .gpx-close { background:none; border:1px solid rgba(255,255,255,0.2); color:var(--cream); padding:0.5rem 1rem; cursor:pointer; border-radius:2px; font-size:1.2rem; }
  .gpx-close:hover { background:var(--lime); color:var(--earth); }
  #gpx-map { width:100%; height:50vh; border-radius:4px; margin-bottom:1rem; }
  .gpx-stats { display:flex; gap:2rem; justify-content:center; flex-wrap:wrap; padding:0.5rem 0 1rem; }
  .gpx-stat { text-align:center; }
  .gpx-stat-value { font-family:'Bebas Neue',sans-serif; font-size:2rem; color:var(--lime); }
  .gpx-stat-label { font-size:0.75rem; color:var(--sand); }
  .gpx-section-label { font-family:'DM Mono',monospace; font-size:0.75rem; color:var(--lime); letter-spacing:0.12em; text-transform:uppercase; margin-bottom:0.5rem; }
  #gpx-elevation-container { width:100%; height:180px; }
  #gpx-elevation { width:100%; height:100%; }
  @media(max-width:768px) {
    #gpx-map { height:40vh; }
    #gpx-elevation-container { height:150px; }
    .gpx-stats { gap:1rem; }
    .gpx-stat-value { font-size:1.5rem; }
  }
</style>
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
    <div style="margin-top:2.5rem; font-size:0.7rem; letter-spacing:0.15em; color:rgba(255,255,255,0.4); text-transform:uppercase; animation:bounce 2s infinite;">↓ Découvrir</div>
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
        <p style="font-family:'Bebas Neue',sans-serif; font-size:1.8rem; color:var(--lime); letter-spacing:0.05em;">Parking de la Halle</p>
        <p style="font-size:1rem; color:var(--sand);">381 Rue de la Mairie, 01630 Challex, France</p>
      </div>
    </div>
    <iframe src="https://maps.google.com/maps?q=46.181861,5.973861&t=k&z=17&output=embed" width="100%" height="300" style="border:0; display:block;" allowfullscreen="" loading="lazy"></iframe>
    <div style="padding:1.25rem 1.5rem; display:flex; gap:1rem; flex-wrap:wrap; justify-content:center;">
      <a href="https://www.google.com/maps?q=46.181861,5.973861" target="_blank" style="background:var(--lime); color:var(--earth); font-family:'DM Sans',sans-serif; font-weight:600; font-size:0.9rem; padding:0.75rem 1.5rem; border-radius:2px; text-decoration:none; letter-spacing:0.05em;">📍 Ouvrir dans Google Maps</a>
      <a href="https://waze.com/ul?ll=46.181861,5.973861&navigate=yes" target="_blank" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:var(--cream); font-family:'DM Sans',sans-serif; font-weight:500; font-size:0.9rem; padding:0.75rem 1.5rem; border-radius:2px; text-decoration:none; letter-spacing:0.05em;">🚗 Ouvrir dans Waze</a>
    </div>
  </div>
</section>

<!-- ACCES & PARKINGS -->
<section class="section" style="padding-top:0;">
  <p class="section-tag">// Accès & Parkings</p>
  <h2 class="section-title">Se garer</h2>
  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1rem;">
    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-radius:4px; padding:1.25rem;">
      <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.5rem;">
        <span style="font-size:1.3rem;">🅿️</span>
        <strong style="color:var(--lime); font-size:0.95rem;">Parking de l'Église</strong>
      </div>
      <p style="font-size:0.85rem; color:var(--sand); margin-bottom:0.75rem;">Challex, Église, 01630 Challex</p>
      <a href="https://www.google.com/maps/search/Eglise+Challex+01630" target="_blank" style="font-size:0.8rem; color:var(--lime); text-decoration:underline;">Voir sur la carte →</a>
    </div>
    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-radius:4px; padding:1.25rem;">
      <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.5rem;">
        <span style="font-size:1.3rem;">🅿️</span>
        <strong style="color:var(--lime); font-size:0.95rem;">Parking Terrain de Foot</strong>
      </div>
      <p style="font-size:0.85rem; color:var(--sand); margin-bottom:0.75rem;">30-136 Rue de la Craz, 01630 Challex</p>
      <a href="https://www.google.com/maps/search/30+Rue+de+la+Craz+Challex+01630" target="_blank" style="font-size:0.8rem; color:var(--lime); text-decoration:underline;">Voir sur la carte →</a>
    </div>
    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-radius:4px; padding:1.25rem;">
      <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.5rem;">
        <span style="font-size:1.3rem;">🅿️</span>
        <strong style="color:var(--lime); font-size:0.95rem;">Parking Rue de la Mairie</strong>
      </div>
      <p style="font-size:0.85rem; color:var(--sand); margin-bottom:0.75rem;">236-310 Rue de la Mairie, 01630 Challex</p>
      <a href="https://www.google.com/maps/search/236+Rue+de+la+Mairie+Challex+01630" target="_blank" style="font-size:0.8rem; color:var(--lime); text-decoration:underline;">Voir sur la carte →</a>
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
        <div style="margin-top:0.75rem;">
          <span class="gpx-link" onclick="event.preventDefault(); event.stopPropagation(); openGpxPopup('3km');">🗺 Voir le parcours</span>
        </div>
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
          <span class="gpx-link" onclick="event.preventDefault(); event.stopPropagation(); openGpxPopup('7.5km');">🗺 Voir le parcours</span>
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
        <div style="margin-top:0.75rem;">
          <span class="gpx-link" onclick="event.preventDefault(); event.stopPropagation(); openGpxPopup('15km');">🗺 Voir le parcours</span>
        </div>
        <div class="race-spots">
          <div class="spots-bar"><div class="spots-fill" style="width:<?= $raceStats['15km']['percentage'] ?>%"></div></div>
          <span class="spots-text"><?= $raceStats['15km']['registered'] ?> inscrits · <?= $raceStats['15km']['remaining'] ?> / <?= $raceStats['15km']['total'] ?> places</span>
        </div>
      </div>
    </a>
  </div>
</section>

<!-- POPUP GPX -->
<div class="gpx-overlay" id="gpx-overlay">
  <div class="gpx-inner">
    <div class="gpx-header">
      <h2 id="gpx-title">▲ Parcours</h2>
      <button class="gpx-close" onclick="closeGpxPopup()">✕</button>
    </div>
    <div id="gpx-map"></div>
    <div class="gpx-stats" id="gpx-stats">Chargement...</div>
    <p class="gpx-section-label">// Profil altimétrique</p>
    <div id="gpx-elevation-container">
      <canvas id="gpx-elevation"></canvas>
    </div>
  </div>
</div>

<!-- CONTACT -->
<section class="section" style="text-align:center;">
  <p class="section-tag">// Contact</p>
  <h2 class="section-title">Une question ?</h2>
  <p style="color:var(--sand); margin-bottom:1rem; font-size:1rem;">Contactez-nous par email</p>
  <a href="mailto:contact@vogue-challex.fr" style="font-family:'Bebas Neue',sans-serif; font-size:1.8rem; color:var(--lime); text-decoration:none; letter-spacing:0.05em;">contact@vogue-challex.fr</a>
</section>

<footer style="text-align:center; padding:2rem; color:var(--sand); font-size:0.85rem; border-top:1px solid rgba(255,255,255,0.1); margin-top:2rem;">
  <p>© 2026 Vogue Challaisienne · <a href="https://www.vogue-challex.fr" style="color:var(--lime); text-decoration:none;">www.vogue-challex.fr</a> · Tous droits réservés</p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
var gpxMap = null;
var gpxChart = null;
var gpxLoadedCourse = null;

// Tous les parcours pointent vers le même GPX pour l'instant
var gpxFiles = {
  '3km': '/gpx/Course_7_5.gpx',
  '7.5km': '/gpx/Course_7_5.gpx',
  '15km': '/gpx/Course_7_5.gpx'
};

var gpxColors = {
  '3km': '#87b8c4',
  '7.5km': '#87b8c4',
  '15km': '#87b8c4'
};

function openGpxPopup(course) {
  var overlay = document.getElementById('gpx-overlay');
  overlay.classList.add('active');
  document.body.style.overflow = 'hidden';
  document.getElementById('gpx-title').textContent = '▲ Parcours ' + course;
  
  // Détruire l'ancienne carte
  if (gpxMap) { gpxMap.remove(); gpxMap = null; }
  if (gpxChart) { gpxChart.destroy(); gpxChart = null; }
  
  setTimeout(function() { loadGpx(course); }, 100);
}

function closeGpxPopup() {
  document.getElementById('gpx-overlay').classList.remove('active');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeGpxPopup();
});

document.getElementById('gpx-overlay').addEventListener('click', function(e) {
  if (e.target === this) closeGpxPopup();
});

function loadGpx(course) {
  var color = gpxColors[course] || '#87b8c4';
  
  gpxMap = L.map('gpx-map');

  L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Esri', maxZoom: 18
  }).addTo(gpxMap);

  fetch(gpxFiles[course])
    .then(function(r) { return r.text(); })
    .then(function(gpxText) {
      var parser = new DOMParser();
      var xml = parser.parseFromString(gpxText, 'text/xml');
      
      var trkpts = xml.getElementsByTagNameNS('http://www.topografix.com/GPX/1/1', 'trkpt');
      if (trkpts.length === 0) trkpts = xml.getElementsByTagName('trkpt');
      
      var allLatLngs = [];
      var elevations = [];
      var distances = [0];
      var totalDist = 0;
      
      for (var i = 0; i < trkpts.length; i++) {
        var pt = trkpts[i];
        var lat = parseFloat(pt.getAttribute('lat'));
        var lon = parseFloat(pt.getAttribute('lon'));
        allLatLngs.push([lat, lon]);
        
        var ele = pt.getElementsByTagNameNS('http://www.topografix.com/GPX/1/1', 'ele')[0];
        if (!ele) ele = pt.getElementsByTagName('ele')[0];
        if (ele) elevations.push(parseFloat(ele.textContent));
        else elevations.push(0);
        
        if (i > 0) {
          totalDist += gpxMap.distance(allLatLngs[i-1], allLatLngs[i]);
          distances.push(totalDist);
        }
      }
      
      if (allLatLngs.length === 0) {
        document.getElementById('gpx-stats').innerHTML = '<p style="color:#c4440a;">Aucun point trouvé</p>';
        return;
      }
      
      // Simplifier
      var simplified = [];
      var step = Math.max(1, Math.floor(allLatLngs.length / 500));
      for (var i = 0; i < allLatLngs.length; i += step) {
        simplified.push(allLatLngs[i]);
      }
      simplified.push(allLatLngs[allLatLngs.length - 1]);
      
      // Tracé avec flèches de direction
      var polyline = L.polyline(simplified, {
        color: color, weight: 4, opacity: 0.9, lineCap: 'round'
      }).addTo(gpxMap);
      
      // Ajouter des flèches de direction tous les 30 points
      for (var i = 30; i < simplified.length - 1; i += 30) {
        var p1 = simplified[i];
        var p2 = simplified[i + 1];
        var angle = Math.atan2(p2[1] - p1[1], p2[0] - p1[0]) * (180 / Math.PI);
        
        L.marker(p1, {
          icon: L.divIcon({
            className: '',
            html: '<div style="color:' + color + '; font-size:16px; transform:rotate(' + (90 - angle) + 'deg); text-shadow:0 0 3px rgba(0,0,0,0.8);">▸</div>',
            iconSize: [16, 16],
            iconAnchor: [8, 8]
          })
        }).addTo(gpxMap);
      }
      
      gpxMap.fitBounds(L.latLngBounds(simplified), { padding: [30, 30] });
      
      // Marqueur départ
      L.circleMarker(allLatLngs[0], {
        radius: 10, color: color, fillColor: color, fillOpacity: 1, weight: 3
      }).addTo(gpxMap).bindPopup('<b>Départ / Arrivée</b><br>Parking de la Halle').openPopup();
      
      // Stats
      var eleGain = 0, eleLoss = 0;
      for (var i = 1; i < elevations.length; i++) {
        var diff = elevations[i] - elevations[i-1];
        if (diff > 0) eleGain += diff;
        else eleLoss += Math.abs(diff);
      }
      
      var eleMin = Math.round(Math.min.apply(null, elevations));
      var eleMax = Math.round(Math.max.apply(null, elevations));
      
      document.getElementById('gpx-stats').innerHTML = 
        '<div class="gpx-stat"><div class="gpx-stat-value">' + (totalDist / 1000).toFixed(1) + ' km</div><div class="gpx-stat-label">Distance</div></div>' +
        '<div class="gpx-stat"><div class="gpx-stat-value">+ ' + Math.round(eleGain) + ' m</div><div class="gpx-stat-label">Dénivelé +</div></div>' +
        '<div class="gpx-stat"><div class="gpx-stat-value">- ' + Math.round(eleLoss) + ' m</div><div class="gpx-stat-label">Dénivelé -</div></div>' +
        '<div class="gpx-stat"><div class="gpx-stat-value">' + eleMin + ' - ' + eleMax + ' m</div><div class="gpx-stat-label">Altitude</div></div>';
      
      // Profil
      var profileStep = Math.max(1, Math.floor(elevations.length / 300));
      var profileEle = [];
      var profileDist = [];
      
      for (var i = 0; i < elevations.length; i += profileStep) {
        profileEle.push(Math.round(elevations[i]));
        profileDist.push((distances[i] / 1000).toFixed(2));
      }
      
      var ctx = document.getElementById('gpx-elevation').getContext('2d');
      gpxChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: profileDist,
          datasets: [{
            data: profileEle,
            borderColor: color,
            backgroundColor: color.replace(')', ', 0.15)').replace('rgb', 'rgba'),
            borderWidth: 2,
            fill: true,
            pointRadius: 0,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: color,
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 2,
            tension: 0.3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#1a1208',
              borderColor: color,
              borderWidth: 1,
              titleColor: color,
              bodyColor: '#f4ede0',
              callbacks: {
                title: function(items) { return items[0].label + ' km'; },
                label: function(item) { return item.raw + ' m'; }
              }
            }
          },
          scales: {
            x: {
              title: { display: true, text: 'Distance (km)', color: '#d4b896', font: { size: 11 } },
              ticks: { color: '#d4b896', maxTicksLimit: 8, font: { size: 10 } },
              grid: { color: 'rgba(255,255,255,0.05)' }
            },
            y: {
              title: { display: true, text: 'Altitude (m)', color: '#d4b896', font: { size: 11 } },
              ticks: { color: '#d4b896', font: { size: 10 } },
              grid: { color: 'rgba(255,255,255,0.08)' }
            }
          }
        }
      });
    })
    .catch(function(err) {
      document.getElementById('gpx-stats').innerHTML = '<p style="color:#c4440a;">Erreur: ' + err.message + '</p>';
    });
}
</script>
</body>
</html>
