<?php
require_once __DIR__ . '/../src/bootstrap.php';

// Protection par mot de passe simple
$password = 'Vogue2026';
$auth = $_GET['key'] ?? '';
if ($auth !== $password) {
    http_response_code(403);
    die('<h1>Accès refusé</h1><p>Ajoutez ?key=Vogue2026 à l\'URL</p>');
}

// Récupérer les participants depuis HelloAsso
$ha    = new HelloAsso();
$token = $ha->getAccessToken();
$org   = getenv('HELLOASSO_ORGANIZATION_SLUG');
$form  = getenv('HELLOASSO_FORM_SLUG');
$api   = 'https://api.helloasso.com/v5';

$participants = [];
$continuationToken = null;

do {
    $url = "$api/organizations/$org/forms/Event/$form/items?pageSize=100";
    if ($continuationToken) $url .= '&continuationToken=' . urlencode($continuationToken);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    $data = json_decode(curl_exec($ch), true);
    curl_close($ch);

    foreach ($data['data'] ?? [] as $item) {
        if (($item['state'] ?? '') === 'Canceled') continue;
        $participants[] = $item;
    }

    $continuationToken = $data['pagination']['continuationToken'] ?? null;
    $hasMore = !empty($continuationToken) && count($data['data'] ?? []) === 100;
} while ($hasMore);

// Grouper par course
$groups = [];
foreach ($participants as $p) {
    $label = $p['name'] ?? 'Inconnu';
    if (!isset($groups[$label])) $groups[$label] = [];
    $groups[$label][] = $p;
}

// Ordre d'affichage
$order = ['Course Enfant', 'Course 7.5km', 'Course 15km'];
uksort($groups, function($a, $b) use ($order) {
    $ia = array_search($a, $order);
    $ib = array_search($b, $order);
    if ($ia === false) $ia = 99;
    if ($ib === false) $ib = 99;
    return $ia - $ib;
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Participants 2026 — Trail de la Vogue Challaisienne</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="page-header">
  <a href="/" class="back-link">← Retour</a>
  <h1>Participants 2026</h1>
  <p><?= array_sum(array_map('count', $groups)) ?> inscrits au total</p>
</div>

<section class="section">
<?php foreach ($groups as $label => $items):
    $colors = ['Course Enfant'=>'var(--sky)', 'Course 7.5km'=>'var(--lime)', 'Course 15km'=>'#e07850'];
    $color = $colors[$label] ?? 'var(--sand)';
?>
  <div style="margin-bottom:2.5rem;">
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:0.75rem;">
      <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:<?= $color ?>;margin:0;"><?= htmlspecialchars($label) ?></h2>
      <span style="font-family:'DM Mono',monospace;font-size:0.85rem;color:var(--sand);background:rgba(255,255,255,0.05);padding:0.2rem 0.6rem;border-radius:2px;"><?= count($items) ?> inscrits</span>
    </div>
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:0.9rem;">
        <thead>
          <tr style="color:var(--lime);font-family:'DM Mono',monospace;font-size:0.75rem;letter-spacing:0.08em;text-transform:uppercase;">
            <th style="padding:0.5rem 1rem;text-align:left;border-bottom:1px solid rgba(255,255,255,0.1);">#</th>
            <th style="padding:0.5rem 1rem;text-align:left;border-bottom:1px solid rgba(255,255,255,0.1);">Prénom</th>
            <th style="padding:0.5rem 1rem;text-align:left;border-bottom:1px solid rgba(255,255,255,0.1);">Nom</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $i => $p):
            $user = $p['user'] ?? [];
            $prenom = $user['firstName'] ?? '—';
            $nom    = strtoupper($user['lastName'] ?? '—');
          ?>
          <tr style="border-bottom:1px solid rgba(255,255,255,0.05);<?= $i % 2 === 0 ? 'background:rgba(255,255,255,0.02)' : '' ?>">
            <td style="padding:0.6rem 1rem;color:var(--sand);font-family:'DM Mono',monospace;"><?= $i + 1 ?></td>
            <td style="padding:0.6rem 1rem;color:var(--cream);"><?= htmlspecialchars($prenom) ?></td>
            <td style="padding:0.6rem 1rem;color:var(--cream);font-weight:600;"><?= htmlspecialchars($nom) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endforeach; ?>
</section>

<footer style="text-align:center;padding:2rem;color:var(--sand);font-size:0.85rem;border-top:1px solid rgba(255,255,255,0.1);margin-top:2rem;">
  <p>© 2026 Vogue Challaisienne · Données en temps réel via HelloAsso</p>
</footer>

</body>
</html>
