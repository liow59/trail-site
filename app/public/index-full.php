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
        'infos' => [['🕙','Départ à 10h00'],['🏃','À partir de 12 ans'],['⛰','150 D+']],
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
      <span class="date-icon">📅</span>
      <span class="date-text">DIMANCHE 6 SEPTEMBRE 2026</span>
    </div>
    <p style="font-family:'DM Mono',sans-serif;font-size:0.85rem;color:var(--sand);letter-spacing:0.12em;margin-bottom:1.5rem;">⏱ Course non chronométrée</p>
    <a href="https://www.helloasso.com/associations/la-vogue-challaisienne/evenements/trail-de-la-vogue-challaisienne-2026" target="_blank" class="cta-btn">S'inscrire maintenant</a>
  </div>
</section>


