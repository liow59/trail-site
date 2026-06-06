<?php 
require_once __DIR__ . '/../src/bootstrap.php';

$stats = new Statistics();
$formData = $stats->getFormData();

$orderMap = ['Course Enfant', 'Course 7.5km', 'Course 15km'];

$courseExtras = [
    'Course Enfant' => [
        'shortName'=>'3','unit'=>'km', 'color' => 'var(--sky)', 'total' => 30,
        'infos' => [['🕚','Départ à 11h00'],['👦','De 8 à 11 ans'],['👨‍👧','Accompagnement adulte possible']],
        'gpx' => '3km', 'urlParam' => '3km'
    ],
    'Course 7.5km' => [
        'shortName' => '7.5', 'unit' => 'km', 'color' => 'var(--lime)', 'total' => 75,
        'infos' => [['🕙','Départ à 9h30'],['🏃','À partir de 12 ans'],['⛰','150 D+']],
        'gpx' => '7.5km', 'urlParam' => '7.5km'
    ],
    'Course 15km' => [
        'shortName' => '15', 'unit' => 'km', 'color' => '#e07850', 'total' => 75,
        'infos' => [['🕘','Départ à 9h00'],['🏃','À partir de 16 ans'],['🔄','2 boucles · 300 D+']],
        'gpx' => '15km', 'urlParam' => '15km'
    ]
];
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
  #lieu-map { width:100%; height:300px; }
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
</style>
</head>
<body>

<section class="hero" style="min-height:100vh;">
  <div class="hero-content">
    <span class="badge">▲ 1ÈRE ÉDITION</span>
    <h1><span style="display:inline-block;font-size:0.7em;margin-right:0.3em;color:var(--lime);">▲</span>TRAIL<span style="display:inline-block;font-size:0.7em;margin-left:0.3em;color:var(--lime);">▲</span><br><span class="text-lime">DE LA</span><br>VOGUE<br>CHALLAISIENNE</h1>
    <p class="subtitle">COURSE NATURE — 6 SEPTEMBRE 2026</p>
    <div class="hero-date-box">
      <span style="display:inline-flex;align-items:center;margin-right:0.3rem;">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--earth);">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
        <line x1="16" y1="2" x2="16" y2="6"></line>
        <line x1="8" y1="2" x2="8" y2="6"></line>
        <line x1="3" y1="10" x2="21" y2="10"></line>
      </svg>
    </span>
      <span class="date-text">DIMANCHE 6 SEPTEMBRE 2026</span>
    </div>
    <p style="font-family:'DM Mono',sans-serif;font-size:0.85rem;color:var(--sand);letter-spacing:0.12em;margin-bottom:1.5rem;">⏱ Course non chronométrée</p>
    <a href="https://www.helloasso.com/associations/la-vogue-challaisienne/evenements/trail-de-la-vogue-challaisienne-2026" target="_blank" class="cta-btn">S'inscrire maintenant</a>
  </div>
</section>

<!-- COURSES -->
<section class="section">
  <p class="section-tag">// Les parcours</p>
  <h2 class="section-title">Trois distances<br>pour tous</h2>
  <div class="races-grid">
<?php foreach ($orderMap as $orderedLabel):
    $course = null;
    foreach ($formData['courses'] as $c) { if ($c['label'] === $orderedLabel) { $course = $c; break; } }
    if (!$course) continue;
    $extras = $courseExtras[$orderedLabel] ?? null;
    if (!$extras) continue;
    $color = $extras['color'];
    $total = $extras['total'];
    $pct = $total > 0 ? min(round(($course['registered'] / $total) * 100, 1), 100) : 0;
?>
    <a href="https://www.helloasso.com/associations/la-vogue-challaisienne/evenements/trail-de-la-vogue-challaisienne-2026" target="_blank" style="text-decoration:none;color:inherit;">
      <div class="race-card">
        <div class="race-dist" style="font-size:2.5rem;color:<?= $color ?>"><?= $extras['shortName'] ?><small style="font-size:1.5rem"><?= $extras['unit'] ?></small></div>
        <div class="race-type">
<?php foreach ($extras['infos'] as $info): ?>
          <div class="race-info-item"><span class="icon"><?= $info[0] ?></span><span><?= $info[1] ?></span></div>
<?php endforeach; ?>
        </div>
        <div class="race-price" style="color:<?= $color ?>"><?= $course['price'] > 0 ? $course['price'] . ' €' : 'Gratuit' ?></div>
        <div style="margin-top:0.75rem;">
          <span class="gpx-link" style="color:<?= $color ?>;" onclick="event.preventDefault();event.stopPropagation();openGpxPopup('<?= $extras['gpx'] ?>');">🗺 Voir le parcours</span>
        </div>
        <div class="race-spots">
          <div class="spots-bar"><div class="spots-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div></div>
          <span class="spots-text"><?= $course['registered'] ?> inscrits / <?= $total ?> places</span>
        </div>
      </div>
    </a>
<?php endforeach; ?>
  </div>
</section>

<!-- REPAS -->
<section class="section">
  <p class="section-tag">// Restauration</p>
  <h2 class="section-title">Repas d'après<br>course</h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(480px,1fr));gap:1.5rem;margin-bottom:2rem;">
<?php foreach ($formData['meals'] as $meal): ?>
    <a href="https://www.helloasso.com/associations/la-vogue-challaisienne/evenements/trail-de-la-vogue-challaisienne-2026" target="_blank" style="text-decoration:none;color:inherit;">
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:4px;padding:1.75rem;display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--cream);letter-spacing:0.05em;">🥙 <?= htmlspecialchars(str_replace(' - Frites', '', $meal['label'])) ?></div>
        <div style="font-size:0.85rem;color:var(--sand);margin-top:0.25rem;"><?= stripos($meal['label'], 'enfant') !== false || stripos($meal['label'], 'nugget') !== false ? 'Nuggets · Frites · Glace' : 'Frites · Salade · Tomate · Oignon' ?></div>
      </div>
      <div style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--lime);"><?= number_format($meal['price'],2) ?> €</div>
    </div>
    </a>
<?php endforeach; ?>
  </div>
  <p style="text-align:center;color:var(--sand);font-size:0.85rem;">🍽 Repas d'après course disponible à la commande lors de l'inscription</p>
</section>

<!-- LIEU -->
<section class="section">
  <p class="section-tag">// Lieu de départ</p>
  <h2 class="section-title">Où nous<br>trouver ?</h2>
  <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:4px;overflow:hidden;margin-bottom:1.5rem;">
    <div style="padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border-bottom:1px solid rgba(255,255,255,0.07);">
      <span style="font-size:1.5rem;">📍</span>
      <div>
        <p style="font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:var(--lime);letter-spacing:0.05em;">Parking de la Halle</p>
        <p style="font-size:1rem;color:var(--sand);">381 Rue de la Mairie, 01630 Challex, France</p>
      </div>
    </div>
    <div id="lieu-map"></div>
    <div style="padding:1.25rem 1.5rem;display:flex;gap:1rem;flex-wrap:wrap;justify-content:center;">
      <a href="https://www.google.com/maps?q=5XMF%2B6C+Challex" target="_blank" style="background:var(--lime);color:var(--earth);font-family:'DM Sans',sans-serif;font-weight:600;font-size:0.9rem;padding:0.75rem 1.5rem;border-radius:2px;text-decoration:none;letter-spacing:0.05em;">📍 Ouvrir dans Google Maps</a>
      <a href="https://waze.com/ul?ll=46.18186,5.97386&navigate=yes" target="_blank" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:var(--cream);font-family:'DM Sans',sans-serif;font-weight:500;font-size:0.9rem;padding:0.75rem 1.5rem;border-radius:2px;text-decoration:none;letter-spacing:0.05em;">🚗 Ouvrir dans Waze</a>
    </div>
  </div>
</section>

<!-- PARKINGS -->
<section class="section" style="padding-top:0;">
  <p class="section-tag">// Accès & Parkings</p>
  <h2 class="section-title">Se garer</h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1rem;">
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:4px;padding:1.25rem;">
      <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem;"><span style="font-size:1.3rem;">🅿️</span><strong style="color:var(--lime);font-size:0.95rem;">Parking de l'Église</strong></div>
      <p style="font-size:0.85rem;color:var(--sand);margin-bottom:0.75rem;">Challex, Église, 01630 Challex</p>
      <a href="https://www.google.com/maps?q=5XMG%2B8G+Challex" target="_blank" style="font-size:0.8rem;color:var(--lime);text-decoration:underline;">Voir sur la carte →</a>
    </div>
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:4px;padding:1.25rem;">
      <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem;"><span style="font-size:1.3rem;">🅿️</span><strong style="color:var(--lime);font-size:0.95rem;">Parking de la Halle</strong></div>
      <p style="font-size:0.85rem;color:var(--sand);margin-bottom:0.75rem;">Parking de la Halle, 01630 Challex</p>
      <a href="https://www.google.com/maps?q=5XMF%2B6C+Challex" target="_blank" style="font-size:0.8rem;color:var(--lime);text-decoration:underline;">Voir sur la carte →</a>
    </div>
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:4px;padding:1.25rem;">
      <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem;"><span style="font-size:1.3rem;">🅿️</span><strong style="color:var(--lime);font-size:0.95rem;">Parking Rue de la Mairie</strong></div>
      <p style="font-size:0.85rem;color:var(--sand);margin-bottom:0.75rem;">236-310 Rue de la Mairie, 01630 Challex</p>
      <a href="https://www.google.com/maps?q=5XJF%2BR4+Challex" target="_blank" style="font-size:0.8rem;color:var(--lime);text-decoration:underline;">Voir sur la carte →</a>
    </div>
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:4px;padding:1.25rem;">
      <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem;"><span style="font-size:1.3rem;">🅿️</span><strong style="color:var(--lime);font-size:0.95rem;">Parking Rue de la Craz</strong></div>
      <p style="font-size:0.85rem;color:var(--sand);margin-bottom:0.75rem;">Rue de la Craz, 01630 Challex</p>
      <a href="https://www.google.com/maps?q=5XJC%2BPP+Challex" target="_blank" style="font-size:0.8rem;color:var(--lime);text-decoration:underline;">Voir sur la carte →</a>
    </div>
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
    <div id="gpx-elevation-container"><canvas id="gpx-elevation"></canvas></div>
  </div>
</div>

<!-- INFOS -->
<section class="section">
  <p class="section-tag">// Informations pratiques</p>
  <h2 class="section-title">Plus<br>d'infos</h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.25rem;">
    
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:4px;padding:1.5rem;">
      <div style="font-size:1.5rem;margin-bottom:0.75rem;">🎽</div>
      <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.2rem;color:var(--lime);margin-bottom:0.5rem;">Retrait des dossards</h3>
      <p style="color:var(--sand);font-size:0.9rem;line-height:1.6;">À partir de <strong style="color:var(--cream);">8h15</strong> sur place.<br><br>Retrait anticipé possible le <strong style="color:var(--cream);">samedi de 14h30 à 17h30</strong> sur place.</p>
      <p style="color:var(--rust);font-size:0.85rem;margin-top:0.75rem;font-weight:600;">⚠ IMPORTANT : Retrait du dossard au minimum <strong>15 minutes</strong> avant le début de votre course.</p>
    </div>

    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:4px;padding:1.5rem;">
      <div style="font-size:1.5rem;margin-bottom:0.75rem;">💧</div>
      <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.2rem;color:var(--lime);margin-bottom:0.5rem;">Ravitaillement</h3>
      <p style="color:var(--sand);font-size:0.9rem;line-height:1.6;">
        <span style="color:var(--sky);">3km</span> — Ravitaillement à l'arrivée<br>
        <span style="color:var(--lime);">7.5km</span> — Ravitaillement à l'arrivée<br>
        <span style="color:#e07850;">15km</span> — Ravitaillement à 7.5km + arrivée
      </p>
    </div>

    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:4px;padding:1.5rem;">
      <div style="font-size:1.5rem;margin-bottom:0.75rem;">🏆</div>
      <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.2rem;color:var(--lime);margin-bottom:0.5rem;">Remise des prix</h3>
      <p style="color:var(--sand);font-size:0.9rem;line-height:1.6;">Cérémonie de remise des prix à <strong style="color:var(--cream);">12h00</strong> sur le lieu d'arrivée.</p>
    </div>

    <div style="background:rgba(168,198,64,0.08);border:1px solid rgba(168,198,64,0.25);border-radius:4px;padding:1.5rem;">
      <div style="font-size:1.5rem;margin-bottom:0.75rem;">🥙</div>
      <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.2rem;color:var(--lime);margin-bottom:0.5rem;">Repas d'après course</h3>
      <p style="color:var(--sand);font-size:0.9rem;line-height:1.6;">Profitez d'un super repas d'après course, disponible <strong style="color:var(--cream);">sur commande lors de votre inscription</strong>. Un moment convivial pour célébrer votre performance !</p>
    </div>

  </div>
</section>

<!-- CONTACT -->
<section class="section" style="text-align:center;">
  <p class="section-tag">// Contact</p>
  <h2 class="section-title">Une question ?</h2>
  <p style="color:var(--sand);margin-bottom:1rem;">Contactez-nous par email</p>
  <a href="mailto:contact@vogue-challex.fr" style="font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:var(--lime);text-decoration:none;letter-spacing:0.05em;">contact@vogue-challex.fr</a>
</section>

<footer style="text-align:center;padding:2rem;color:var(--sand);font-size:0.85rem;border-top:1px solid rgba(255,255,255,0.1);margin-top:2rem;">
  <p>© 2026 Vogue Challaisienne · <a href="https://www.vogue-challex.fr" style="color:var(--lime);text-decoration:none;">www.vogue-challex.fr</a></p>
  <p style="margin-top:0.5rem;"><a href="/participants2026.php?key=Vogue2026" style="color:var(--sand);text-decoration:none;font-size:0.8rem;opacity:0.6;">📋 Liste des participants</a></p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// CARTE LIEU
var lieuMap = L.map('lieu-map');
L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',{attribution:'Esri',maxZoom:18}).addTo(lieuMap);
lieuMap.setView([46.1828, 5.9729], 17);
L.circleMarker([46.1828, 5.9729],{radius:12,color:'#a8c640',fillColor:'#a8c640',fillOpacity:1,weight:3}).addTo(lieuMap).bindPopup('<b>Parking de la Halle</b><br>381 Rue de la Mairie').openPopup();

// GPX
var gpxMap = null, gpxChart = null;
var gpxFiles = {'3km':'/gpx/COURSE_3km.gpx','7.5km':'/gpx/COURSE_7_5km.gpx','15km':'/gpx/COURSE_15km.gpx'};

function openGpxPopup(course) {
  document.getElementById('gpx-overlay').classList.add('active');
  document.body.style.overflow = 'hidden';
  document.getElementById('gpx-title').textContent = '▲ Parcours ' + course;
  if (gpxMap) { gpxMap.remove(); gpxMap = null; }
  if (gpxChart) { gpxChart.destroy(); gpxChart = null; }
  setTimeout(function() { loadGpx(course); }, 100);
}

function closeGpxPopup() {
  document.getElementById('gpx-overlay').classList.remove('active');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) { if (e.key==='Escape') closeGpxPopup(); });
document.getElementById('gpx-overlay').addEventListener('click', function(e) { if (e.target===this) closeGpxPopup(); });

function loadGpx(course) {
  var color = course==='3km' ? '#87b8c4' : course==='15km' ? '#e07850' : '#a8c640';
  gpxMap = L.map('gpx-map');
  L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',{attribution:'Esri',maxZoom:18}).addTo(gpxMap);
  fetch(gpxFiles[course]).then(function(r){return r.text();}).then(function(gpxText){
    var xml = new DOMParser().parseFromString(gpxText,'text/xml');
    var pts = xml.getElementsByTagNameNS('http://www.topografix.com/GPX/1/1','trkpt');
    if (pts.length===0) pts = xml.getElementsByTagName('trkpt');
    var ll=[], ele=[], dist=[0], total=0;
    for (var i=0;i<pts.length;i++) {
      var lat=parseFloat(pts[i].getAttribute('lat')), lon=parseFloat(pts[i].getAttribute('lon'));
      ll.push([lat,lon]);
      var e=pts[i].getElementsByTagNameNS('http://www.topografix.com/GPX/1/1','ele')[0]||pts[i].getElementsByTagName('ele')[0];
      ele.push(e?parseFloat(e.textContent):0);
      if (i>0){total+=gpxMap.distance(ll[i-1],ll[i]);dist.push(total);}
    }
    if (!ll.length){document.getElementById('gpx-stats').innerHTML='<p style="color:#c4440a;">Aucun point</p>';return;}
    var simp=[],step=Math.max(1,Math.floor(ll.length/500));
    for(var i=0;i<ll.length;i+=step)simp.push(ll[i]);
    simp.push(ll[ll.length-1]);
    L.polyline(simp,{color:color,weight:4,opacity:0.9}).addTo(gpxMap);
    for(var i=30;i<simp.length-1;i+=30){
      var a=Math.atan2(simp[i+1][1]-simp[i][1],simp[i+1][0]-simp[i][0])*(180/Math.PI);
      L.marker(simp[i],{icon:L.divIcon({className:'',html:'<div style="color:'+color+';font-size:16px;transform:rotate('+(90-a)+'deg);text-shadow:0 0 3px rgba(0,0,0,0.8);">▸</div>',iconSize:[16,16],iconAnchor:[8,8]})}).addTo(gpxMap);
    }
    gpxMap.fitBounds(L.latLngBounds(simp),{padding:[30,30]});
    L.circleMarker(ll[0],{radius:10,color:color,fillColor:color,fillOpacity:1,weight:3}).addTo(gpxMap).bindPopup('<b>Départ / Arrivée</b><br>Parking de la Halle').openPopup();
    var g=0,l=0;
    for(var i=1;i<ele.length;i++){var d=ele[i]-ele[i-1];if(d>0)g+=d;else l+=Math.abs(d);}
    document.getElementById('gpx-stats').innerHTML=
      '<div class="gpx-stat"><div class="gpx-stat-value">'+(total/1000).toFixed(1)+' km</div><div class="gpx-stat-label">Distance</div></div>'+
      '<div class="gpx-stat"><div class="gpx-stat-value">+ '+Math.round(g)+' m</div><div class="gpx-stat-label">Dénivelé +</div></div>'+
      '<div class="gpx-stat"><div class="gpx-stat-value">- '+Math.round(l)+' m</div><div class="gpx-stat-label">Dénivelé -</div></div>'+
      '<div class="gpx-stat"><div class="gpx-stat-value">'+Math.round(Math.min.apply(null,ele))+' - '+Math.round(Math.max.apply(null,ele))+' m</div><div class="gpx-stat-label">Altitude</div></div>';
    var ps=Math.max(1,Math.floor(ele.length/300)),pe=[],pd=[];
    for(var i=0;i<ele.length;i+=ps){pe.push(Math.round(ele[i]));pd.push((dist[i]/1000).toFixed(2));}
    var ctx=document.getElementById('gpx-elevation').getContext('2d');
    gpxChart=new Chart(ctx,{type:'line',data:{labels:pd,datasets:[{data:pe,borderColor:color,backgroundColor:'rgba(135,184,196,0.15)',borderWidth:2,fill:true,pointRadius:0,tension:0.3}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{backgroundColor:'#1a1208',borderColor:color,borderWidth:1,titleColor:color,bodyColor:'#f4ede0',callbacks:{title:function(i){return i[0].label+' km';},label:function(i){return i.raw+' m';}}}},scales:{x:{title:{display:true,text:'Distance (km)',color:'#d4b896'},ticks:{color:'#d4b896',maxTicksLimit:8},grid:{color:'rgba(255,255,255,0.05)'}},y:{title:{display:true,text:'Altitude (m)',color:'#d4b896'},ticks:{color:'#d4b896'},grid:{color:'rgba(255,255,255,0.08)'}}}}});
  }).catch(function(err){document.getElementById('gpx-stats').innerHTML='<p style="color:#c4440a;">Erreur: '+err.message+'</p>';});
}
</script>
</body>
</html>
