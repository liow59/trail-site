<?php
require_once __DIR__ . '/../src/bootstrap.php';

$HELLOASSO_URL = 'https://www.helloasso.com/associations/la-vogue-challaisienne/evenements/trail-de-la-vogue-challaisienne-2026';

$stats    = new Statistics();
$formData = $stats->getFormData();

$courses = [
    'Course Enfant' => ['shortName'=>'3.5','unit'=>'km','color'=>'var(--sky)','total'=>30,'price'=>'Gratuit','infos'=>'Course Enfant · 8-11 ans'],
    'Course 7.5km'  => ['shortName'=>'7.5','unit'=>'km','color'=>'var(--lime)','total'=>75,'price'=>'10 €','infos'=>'À partir de 12 ans · 150 D+'],
    'Course 15km'   => ['shortName'=>'15','unit'=>'km','color'=>'#e07850','total'=>75,'price'=>'15 €','infos'=>'À partir de 16 ans · 300 D+'],
];

// Récupérer les compteurs depuis HelloAsso
$counts = [];
foreach ($formData['courses'] as $c) {
    $counts[$c['label']] = ['registered' => $c['registered'], 'total' => 0];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription - Trail de la Vogue Challaisienne 2026</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="page-header">
  <a href="/" class="back-link">← Retour à l'accueil</a>
  <h1>Inscription</h1>
  <p>Dimanche 6 Septembre 2026 — Challex</p>
</div>

<section class="section" style="max-width:800px;margin:0 auto;">

  <p class="section-tag">// Les parcours</p>
  <div class="races-grid" style="margin-bottom:2.5rem;">
<?php foreach ($courses as $label => $c):
    $registered = $counts[$label]['registered'] ?? 0;
    $total      = $c['total'];
    $remaining  = max(0, $total - $registered);
    $pct        = min(round(($registered / $total) * 100, 1), 100);
    $full       = $remaining <= 0;
?>
    <div class="race-card" style="<?= $full ? 'opacity:0.6;' : '' ?>">
      <div class="race-dist" style="font-size:2.5rem;color:<?= $c['color'] ?>"><?= $c['shortName'] ?><small style="font-size:1.5rem"><?= $c['unit'] ?></small></div>
      <div style="color:var(--sand);font-size:0.85rem;margin-bottom:0.75rem;"><?= $c['infos'] ?></div>
      <div class="race-price" style="color:<?= $c['color'] ?>"><?= $c['price'] ?></div>
      <div class="race-spots" style="margin-top:1rem;">
        <div class="spots-bar"><div class="spots-fill" style="width:<?= $pct ?>%;background:<?= $c['color'] ?>"></div></div>
        <span class="spots-text">
          <?php if ($full): ?>
            ❌ Complet
          <?php else: ?>
            <?= $registered ?> inscrits · <strong style="color:var(--cream)"><?= $remaining ?> places restantes</strong> / <?= $total ?>
          <?php endif; ?>
        </span>
      </div>
    </div>
<?php endforeach; ?>
  </div>

  <?php if (!empty($formData['meals'])): ?>
  <div style="background:rgba(168,198,64,0.05);border:1px solid rgba(168,198,64,0.2);border-radius:4px;padding:1.25rem;margin-bottom:2rem;">
    <p style="font-family:'DM Mono',sans-serif;font-size:0.75rem;color:var(--lime);letter-spacing:0.1em;margin-bottom:0.75rem;">// REPAS DE FIN DE COURSE</p>
    <?php foreach ($formData['meals'] as $meal): ?>
    <div style="display:flex;justify-content:space-between;color:var(--sand);font-size:0.9rem;margin-top:0.5rem;">
      <span>🥙 <?= htmlspecialchars($meal['label']) ?></span>
      <span style="color:var(--lime);font-weight:600;"><?= number_format($meal['price'],2) ?> €</span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div style="text-align:center;">
    <a href="<?= $HELLOASSO_URL ?>" target="_blank" class="cta-btn" style="display:inline-block;font-size:1.1rem;padding:1rem 2.5rem;">
      S'inscrire maintenant →
    </a>
    <p style="color:var(--sand);font-size:0.8rem;margin-top:1rem;opacity:0.7;">🔒 Inscription et paiement sécurisés</p>
  </div>

</section>

<footer style="text-align:center;padding:2rem;color:var(--sand);font-size:0.85rem;border-top:1px solid rgba(255,255,255,0.1);margin-top:4rem;">
  <p>© 2026 Vogue Challaisienne · <a href="https://www.vogue-challex.fr" style="color:var(--lime);text-decoration:none;">www.vogue-challex.fr</a></p>
</footer>

</body>
</html>
