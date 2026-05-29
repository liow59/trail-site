<?php
session_start();
require_once __DIR__ . '/../src/bootstrap.php';

$stats = new Statistics();
$formData = $stats->getFormData();

$orderMap = ['Course Test (payant)', 'Course Enfant', 'Course 7.5km', 'Course 15km'];
$courseExtras = [
    'Course Test (payant)' => ['shortName'=>'TEST','unit'=>'','color'=>'var(--rust)','total'=>5,'infos'=>[['🧪','Test paiement'],['💳','0.50 €']],'urlParam'=>'test'],
    'Course Enfant' => ['shortName'=>'3','unit'=>'km','color'=>'var(--sky)','total'=>30,'infos'=>[['🕚','Départ à 11h00'],['👦','De 8 à 11 ans'],['👨‍👧','Accompagnement adulte possible']],'urlParam'=>'3km'],
    'Course 7.5km'  => ['shortName'=>'7.5','unit'=>'km','color'=>'var(--lime)','total'=>75,'infos'=>[['🕙','Départ à 10h00'],['🏃','À partir de 12 ans'],['⛰','150 D+']],'urlParam'=>'7.5km'],
    'Course 15km'   => ['shortName'=>'15','unit'=>'km','color'=>'#e07850','total'=>75,'infos'=>[['🕘','Départ à 9h00'],['🏃','À partir de 16 ans'],['🔄','2 boucles · 300 D+']],'urlParam'=>'15km']
];

$error      = null;
$success    = isset($_GET['success']);
$payErr     = isset($_GET['error']);
$showWidget = isset($_GET['widget']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $prenom       = trim($_POST['prenom'] ?? '');
        $nom          = trim($_POST['nom'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $telephone    = trim($_POST['telephone'] ?? '');
        $dateNaissRaw = $_POST['date_naissance'] ?? '';
        $dateNaiss    = $dateNaissRaw ? date('Y-m-d', strtotime($dateNaissRaw)) : null;
        if ($dateNaiss === '1970-01-01') $dateNaiss = null;
        $sexe         = $_POST['sexe'] ?? '';
        $courseLabel  = $_POST['course_label'] ?? '';
        $courseTierId = intval($_POST['course_tier_id'] ?? 0);
        $courseAmount = intval($_POST['course_amount'] ?? 0);

        $payer = ['prenom' => $prenom, 'nom' => $nom, 'email' => $email, 'telephone' => $telephone, 'date_naissance' => $dateNaiss, 'sexe' => $sexe];

        $selectedItems = [];
        $repasData     = [];

        if ($courseTierId) {
            $selectedItems[] = ['tierId' => $courseTierId, 'label' => $courseLabel, 'amount' => $courseAmount];
        }

        foreach ($_POST as $key => $value) {
            if (strpos($key, 'meal_qty_') === 0 && intval($value) > 0) {
                $mealId    = intval(str_replace('meal_qty_', '', $key));
                $mealLabel = $_POST['meal_label_' . $mealId] ?? '';
                $mealPrice = intval($_POST['meal_price_' . $mealId] ?? 0);
                $qty       = intval($value);
                for ($i = 0; $i < $qty; $i++) {
                    $selectedItems[] = ['tierId' => $mealId, 'label' => $mealLabel, 'amount' => $mealPrice];
                }
                $repasData[] = ['label' => $mealLabel, 'qty' => $qty, 'amount' => $mealPrice * $qty];
            }
        }

        $totalCents = array_sum(array_column($selectedItems, 'amount'));
        $statut     = $totalCents <= 0 ? 'free' : 'pending';

        $pdo  = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME') . ';charset=utf8mb4', getenv('DB_USER'), getenv('DB_PASS'));
        $stmt = $pdo->prepare('INSERT INTO inscriptions (prenom, nom, email, telephone, date_naissance, sexe, course, tier_id, repas, total_cents, statut) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$prenom, $nom, $email, $telephone, $dateNaiss, $sexe, $courseLabel, $courseTierId, json_encode($repasData), $totalCents, $statut]);
        $inscriptionId = $pdo->lastInsertId();

        // Créer checkout HelloAsso (gratuit ou payant)
        $helloasso = new HelloAsso();
        $checkout  = $helloasso->createCheckoutIntent($payer, $selectedItems);

        if (!empty($checkout['id'])) {
            $pdo->prepare("UPDATE inscriptions SET order_id=? WHERE id=?")->execute([$checkout['id'], $inscriptionId]);
        }

        // Si checkout réussi (payant ou gratuit via API)
        $checkoutUrl = $checkout['redirectUrl'] ?? null;
        if ($checkoutUrl) { header('Location: ' . $checkoutUrl); exit; }

        // Fallback gratuit : widget intégré
        if ($totalCents <= 0) {
            $_SESSION['inscription'] = ['prenom' => $prenom, 'nom' => $nom, 'email' => $email, 'course' => $courseLabel, 'inscriptionId' => $inscriptionId];
            header('Location: /inscription.php?widget=1');
            exit;
        }

        throw new Exception('URL de paiement non reçue');

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$inscData  = $_SESSION['inscription'] ?? [];
$widgetUrl = 'https://www.helloasso.com/associations/la-vogue-challaisienne/evenements/trail-de-la-vogue-challaisienne-2026/widget';
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
  <p><?= $showWidget ? 'Confirmez votre inscription' : 'Remplissez le formulaire puis procédez au paiement' ?></p>
</div>

<?php if ($success): ?>
<div class="section">
  <div style="background:rgba(168,198,64,0.1);border:1px solid var(--lime);border-radius:4px;padding:2rem;text-align:center;max-width:600px;margin:0 auto;">
    <div style="font-size:3rem;margin-bottom:1rem;">✅</div>
    <h2 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--lime);margin-bottom:1rem;">Inscription confirmée !</h2>
    <p style="color:var(--sand);margin-bottom:1.5rem;">Un email de confirmation avec votre numéro de dossard vous a été envoyé.</p>
    <a href="/" class="cta-btn">Retour à l'accueil</a>
  </div>
</div>

<?php elseif ($payErr): ?>
<div class="section">
  <div style="background:rgba(196,68,10,0.15);border:1px solid var(--rust);border-radius:4px;padding:2rem;text-align:center;max-width:600px;margin:0 auto;">
    <div style="font-size:3rem;margin-bottom:1rem;">❌</div>
    <h2 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--rust);margin-bottom:1rem;">Paiement annulé</h2>
    <p style="color:var(--sand);margin-bottom:1.5rem;">Le paiement n'a pas pu être finalisé.</p>
    <a href="/inscription.php" class="cta-btn">Réessayer</a>
  </div>
</div>

<?php elseif ($showWidget): ?>
<section class="section">
  <div style="background:rgba(168,198,64,0.08);border:1px solid rgba(168,198,64,0.2);border-radius:4px;padding:1rem;margin-bottom:1.5rem;font-size:0.85rem;color:var(--sand);">
    ✅ Bonjour <strong style="color:var(--cream)"><?= htmlspecialchars($inscData['prenom'] ?? '') ?> <?= htmlspecialchars($inscData['nom'] ?? '') ?></strong> — confirmez votre inscription à la <strong style="color:var(--lime)"><?= htmlspecialchars($inscData['course'] ?? '') ?></strong>
  </div>
  <div style="border-radius:8px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);">
    <?php
    $tierId = $inscData['tierId'] ?? 0;
    $widgetParams = http_build_query([
        'firstName' => $inscData['prenom'] ?? '',
        'lastName'  => $inscData['nom'] ?? '',
        'email'     => $inscData['email'] ?? '',
    ]);
    if ($tierId) {
        $widgetParams .= '&defaultTierCount[' . $tierId . ']=1';
    }
    ?>
    <iframe
      id="helloasso-widget"
      src="<?= $widgetUrl ?>?<?= $widgetParams ?>"
      style="width:100%;min-height:750px;border:none;display:block;"
      allowtransparency="true"
      scrolling="auto">
    </iframe>
    <script>
    // Écouter les messages HelloAsso pour détecter la fin d'inscription
    window.addEventListener('message', function(e) {
        if (e.origin.indexOf('helloasso') !== -1) {
            if (e.data && (e.data.type === 'resize' || e.data.action === 'scrollTop')) return;
            console.log('HelloAsso message:', e.data);
            // Rediriger vers succès si paiement confirmé
            if (e.data && e.data.type === 'payment-success') {
                window.location.href = '/inscription.php?success=1';
            }
        }
    });
    </script>
  </div>
</section>

<?php else: ?>
<section class="section">

  <?php if ($error): ?>
  <div style="background:rgba(196,68,10,0.15);border:1px solid var(--rust);border-radius:2px;padding:1rem;margin-bottom:2rem;color:#e87a50;">
    ⚠ <?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>

  <form class="reg-form" method="POST" id="inscription-form">
    <p class="section-tag">// Étape 1 — Votre course</p>
    <div class="races-grid" style="margin-bottom:2rem;">
<?php
// Fusionner courses + tests pour la boucle
$allCourses = array_merge($formData['courses'], $formData['tests']);
foreach ($orderMap as $orderedLabel):
    $course = null;
    foreach ($allCourses as $c) { if ($c['label'] === $orderedLabel) { $course = $c; break; } }
    if (!$course) continue;
    $extras = $courseExtras[$orderedLabel] ?? null;
    if (!$extras) continue;
    $color = $extras['color'];
    $total = $extras['total'];
    $pct   = $total > 0 ? min(round(($course['registered'] / $total) * 100, 1), 100) : 0;
?>
      <div class="race-card" data-tier-id="<?= $course['id'] ?>" data-price="<?= $course['priceCents'] ?>" data-label="<?= htmlspecialchars($course['label']) ?>">
        <div class="race-check">✓</div>
        <div class="race-dist" style="font-size:2.5rem;color:<?= $color ?>"><?= $extras['shortName'] ?><small style="font-size:1.5rem"><?= $extras['unit'] ?></small></div>
        <div class="race-type">
<?php foreach ($extras['infos'] as $info): ?>
          <div class="race-info-item"><span class="icon"><?= $info[0] ?></span><span><?= $info[1] ?></span></div>
<?php endforeach; ?>
        </div>
        <div class="race-price" style="color:<?= $color ?>"><?= $course['price'] > 0 ? $course['price'] . ' €' : 'Gratuit' ?></div>
        <div class="race-spots">
          <div class="spots-bar"><div class="spots-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div></div>
          <span class="spots-text"><?= $course['registered'] ?> inscrits / <?= $total ?> places</span>
        </div>
      </div>
<?php endforeach; ?>
    </div>

    <p class="section-tag">// Étape 2 — Vos informations</p>
    <div class="form-row">
      <div class="form-group"><label>Prénom *</label><input type="text" name="prenom" id="prenom" required></div>
      <div class="form-group"><label>Nom *</label><input type="text" name="nom" id="nom" required></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Email *</label><input type="email" name="email" id="email" required></div>
      <div class="form-group"><label>Téléphone *</label><input type="tel" name="telephone" id="telephone" required></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Date de naissance *</label><input type="date" name="date_naissance" id="date_naissance" required></div>
      <div class="form-group">
        <label>Sexe *</label>
        <select name="sexe" id="sexe" required>
          <option value="">—</option>
          <option value="M">Homme</option>
          <option value="F">Femme</option>
        </select>
      </div>
    </div>

    <?php if (!empty($formData['meals'])): ?>
    <p class="section-tag" style="margin-top:2rem">// Étape 3 — Repas de fin de course</p>
    <div class="repas-grid">
      <?php foreach ($formData['meals'] as $meal): ?>
      <div class="repas-row">
        <div class="repas-info">
          <strong><?= htmlspecialchars($meal['label']) ?></strong>
          <span class="repas-prix"><?= number_format($meal['price'], 2) ?> €</span>
        </div>
        <div class="repas-qty">
          <button type="button" class="qty-btn qty-minus" data-meal-id="<?= $meal['id'] ?>">−</button>
          <input type="number" name="meal_qty_<?= $meal['id'] ?>" class="qty-input" value="0" min="0" readonly>
          <input type="hidden" name="meal_label_<?= $meal['id'] ?>" value="<?= htmlspecialchars($meal['label']) ?>">
          <input type="hidden" name="meal_price_<?= $meal['id'] ?>" value="<?= $meal['priceCents'] ?>">
          <button type="button" class="qty-btn qty-plus" data-meal-id="<?= $meal['id'] ?>">+</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="price-summary">
      <div class="price-line"><span>Course</span><span id="course-price">— €</span></div>
      <div class="price-line"><span>Repas</span><span id="meal-price">0 €</span></div>
      <div class="price-total"><span>Total</span><span id="total-price">— €</span></div>
    </div>

    <input type="hidden" name="course_tier_id" id="course_tier_id">
    <input type="hidden" name="course_label" id="course_label">
    <input type="hidden" name="course_amount" id="course_amount" value="0">

    <div style="background:rgba(168,198,64,0.08);border:1px solid rgba(168,198,64,0.2);border-radius:4px;padding:1rem;margin:1.5rem 0;font-size:0.85rem;color:var(--sand);">
      🔒 Paiement sécurisé
    </div>

    <button type="submit" class="submit-btn" disabled>Procéder →</button>
  </form>
</section>
<?php endif; ?>

<footer style="text-align:center;padding:2rem;color:var(--sand);font-size:0.85rem;border-top:1px solid rgba(255,255,255,0.1);margin-top:4rem;">
  <p>© 2026 Vogue Challaisienne · <a href="https://www.vogue-challex.fr" style="color:var(--lime);text-decoration:none;">www.vogue-challex.fr</a></p>
</footer>

<script>
var mealsData=<?= json_encode(array_map(function($m){return['id'=>$m['id'],'price'=>$m['price'],'priceCents'=>$m['priceCents']];},$formData['meals'])) ?>;
var selectedTierId=null,selectedPrice=0;
var urlParams=new URLSearchParams(window.location.search);
var preselect=urlParams.get('course');
var preselectionMap={'3km':'Course Enfant','7.5km':'Course 7.5km','15km':'Course 15km'};

document.querySelectorAll('.race-card').forEach(function(card){
  var tierId=parseInt(card.dataset.tierId);
  var priceCents=parseInt(card.dataset.price);
  var label=card.dataset.label;
  if(preselect&&preselectionMap[preselect]===label) selectCard(card,tierId,priceCents,label);
  card.addEventListener('click',function(){selectCard(card,tierId,priceCents,label);});
});

function selectCard(card,tierId,priceCents,label){
  document.querySelectorAll('.race-card').forEach(function(c){c.classList.remove('selected');});
  card.classList.add('selected');
  selectedTierId=tierId; selectedPrice=priceCents/100;
  document.getElementById('course_tier_id').value=tierId;
  document.getElementById('course_label').value=label;
  document.getElementById('course_amount').value=priceCents;
  updatePrices(); checkFormValidity();
}

document.querySelectorAll('.qty-btn').forEach(function(btn){
  btn.addEventListener('click',function(e){
    e.preventDefault();
    var mealId=btn.dataset.mealId;
    var input=document.querySelector('input[name="meal_qty_'+mealId+'"]');
    var value=parseInt(input.value)||0;
    if(btn.classList.contains('qty-plus')) value++;
    else if(btn.classList.contains('qty-minus')&&value>0) value--;
    input.value=value; updatePrices();
  });
});

function updatePrices(){
  document.getElementById('course-price').textContent=(selectedPrice||0).toFixed(2)+' €';
  var mealTotal=0;
  mealsData.forEach(function(meal){
    var qty=parseInt(document.querySelector('input[name="meal_qty_'+meal.id+'"]').value)||0;
    mealTotal+=qty*meal.price;
  });
  document.getElementById('meal-price').textContent=mealTotal.toFixed(2)+' €';
  var total=(selectedPrice||0)+mealTotal;
  document.getElementById('total-price').textContent=total.toFixed(2)+' €';
  var btn=document.querySelector('.submit-btn');
  if(!btn) return;
  btn.textContent=total<=0&&selectedTierId?'Confirmer mon inscription gratuite →':'Procéder au paiement ('+total.toFixed(2)+' €) →';
}

function checkFormValidity(){
  var btn=document.querySelector('.submit-btn');
  if(!btn) return;
  var ok=selectedTierId
    &&document.getElementById('prenom').value.trim()
    &&document.getElementById('nom').value.trim()
    &&document.getElementById('email').value.trim()
    &&document.getElementById('telephone').value.trim()
    &&document.getElementById('date_naissance').value
    &&document.getElementById('sexe').value;
  btn.disabled=!ok;
}

document.querySelectorAll('input,select').forEach(function(el){
  el.addEventListener('input',checkFormValidity);
  el.addEventListener('change',checkFormValidity);
});

var form=document.getElementById('inscription-form');
if(form) form.addEventListener('submit',function(){
  var btn=document.querySelector('.submit-btn');
  btn.textContent='⏳ Traitement...'; btn.disabled=true;
});

updatePrices(); checkFormValidity();
</script>
</body>
</html>
