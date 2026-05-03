<?php 
require_once __DIR__ . '/../src/bootstrap.php';

$stats = new Statistics();
$formData = $stats->getFormData();

$error = null;
$success = isset($_GET['success']);
$paymentError = isset($_GET['error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $helloasso = new HelloAsso();
        
        $payer = [
            'prenom' => trim($_POST['prenom'] ?? ''),
            'nom' => trim($_POST['nom'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telephone' => trim($_POST['telephone'] ?? ''),
            'date_naissance' => $_POST['date_naissance'] ?? '',
            'sexe' => $_POST['sexe'] ?? ''
        ];
        
        // Construire les items sélectionnés
        $selectedItems = [];
        
        // Course sélectionnée
        $courseId = intval($_POST['course_tier_id'] ?? 0);
        $courseLabel = $_POST['course_label'] ?? '';
        $courseAmount = intval($_POST['course_amount'] ?? 0);
        
        if ($courseId && $courseAmount > 0) {
            $selectedItems[] = [
                'tierId' => $courseId,
                'label' => $courseLabel,
                'amount' => $courseAmount
            ];
        }
        
        // Repas sélectionnés
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'meal_qty_') === 0 && intval($value) > 0) {
                $mealId = intval(str_replace('meal_qty_', '', $key));
                $mealLabel = $_POST['meal_label_' . $mealId] ?? '';
                $mealPrice = intval($_POST['meal_price_' . $mealId] ?? 0);
                $qty = intval($value);
                
                for ($i = 0; $i < $qty; $i++) {
                    $selectedItems[] = [
                        'tierId' => $mealId,
                        'label' => $mealLabel,
                        'amount' => $mealPrice
                    ];
                }
            }
        }
        
        $checkout = $helloasso->createCheckoutIntent($payer, $selectedItems);
        
        if (isset($checkout['free']) && $checkout['free']) {
            header('Location: /inscription.php?success=1');
            exit;
        }
        
        $checkoutUrl = $checkout['redirectUrl'] ?? null;
        if ($checkoutUrl) {
            header('Location: ' . $checkoutUrl);
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
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
  <p>Choisissez votre course et complétez le formulaire</p>
</div>

<?php if ($success): ?>
<div class="section">
  <div style="background:rgba(168,198,64,0.1); border:1px solid var(--lime); border-radius:4px; padding:2rem; text-align:center; max-width:600px; margin:0 auto;">
    <div style="font-size:3rem; margin-bottom:1rem;">✅</div>
    <h2 style="font-family:'Bebas Neue',sans-serif; font-size:2rem; color:var(--lime); margin-bottom:1rem;">Inscription confirmée !</h2>
    <p style="color:var(--sand); margin-bottom:1.5rem;">Vous recevrez un email de confirmation.</p>
    <a href="/" class="cta-btn">Retour à l'accueil</a>
  </div>
</div>
<?php elseif ($paymentError): ?>
<div class="section">
  <div style="background:rgba(196,68,10,0.15); border:1px solid var(--rust); border-radius:4px; padding:2rem; text-align:center; max-width:600px; margin:0 auto;">
    <div style="font-size:3rem; margin-bottom:1rem;">❌</div>
    <h2 style="font-family:'Bebas Neue',sans-serif; font-size:2rem; color:var(--rust); margin-bottom:1rem;">Paiement annulé</h2>
    <p style="color:var(--sand); margin-bottom:1.5rem;">Le paiement n'a pas pu être finalisé.</p>
    <a href="/inscription.php" class="cta-btn">Réessayer</a>
  </div>
</div>
<?php else: ?>

<section class="section">
  <p class="section-tag">// Étape 1</p>
  <h2 class="section-title">Choisissez<br>votre course</h2>
  
  <?php if ($error): ?>
  <div style="background:rgba(196,68,10,0.15); border:1px solid var(--rust); border-radius:2px; padding:1rem; margin-bottom:2rem; color:#e87a50;">
    ⚠ <?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>
  
  <div class="races-grid">
    <?php foreach ($formData['courses'] as $course): ?>
    <div class="race-card" data-tier-id="<?= $course['id'] ?>" data-price="<?= $course['priceCents'] ?>" data-label="<?= htmlspecialchars($course['label']) ?>">
      <div class="race-check">✓</div>
      <div class="race-dist" style="font-size:2rem; <?= $course['price'] == 0 ? 'color:var(--sky)' : '' ?>"><?= htmlspecialchars($course['label']) ?></div>
      <div class="race-price" style="<?= $course['price'] == 0 ? 'color:var(--sky)' : '' ?>"><?= $course['price'] > 0 ? number_format($course['price'], 2) . ' €' : 'Gratuit' ?></div>
      <?php if ($course['registered'] > 0): ?>
      <div class="race-spots">
        <span class="spots-text"><?= $course['registered'] ?> inscrits</span>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <form class="reg-form" method="POST" id="inscription-form">
    <p class="section-tag" style="margin-top:3rem">// Étape 2 - Vos informations</p>
    <div class="form-row">
      <div class="form-group">
        <label>Prénom *</label>
        <input type="text" name="prenom" id="prenom" required>
      </div>
      <div class="form-group">
        <label>Nom *</label>
        <input type="text" name="nom" id="nom" required>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" id="email" required>
      </div>
      <div class="form-group">
        <label>Téléphone *</label>
        <input type="tel" name="telephone" id="telephone" required>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Date de naissance *</label>
        <input type="date" name="date_naissance" id="date_naissance" required>
      </div>
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
    <p class="section-tag" style="margin-top:2rem">// Étape 3 - Repas</p>
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
    
    <div style="background:rgba(168,198,64,0.08); border:1px solid rgba(168,198,64,0.2); border-radius:4px; padding:1rem; margin:1.5rem 0; font-size:0.85rem; color:var(--sand);">
      🔒 Paiement sécurisé par carte bancaire
    </div>
    
    <button type="submit" class="submit-btn" disabled>Procéder au paiement sécurisé →</button>
  </form>
</section>

<?php endif; ?>

<footer style="text-align:center; padding:2rem; color:var(--sand); font-size:0.85rem; border-top:1px solid rgba(255,255,255,0.1); margin-top:4rem;">
  <p>© 2026 Vogue Challaisienne · <a href="https://www.vogue-challex.fr" style="color:var(--lime); text-decoration:none;">www.vogue-challex.fr</a></p>
</footer>

<script>
// Données des repas depuis PHP
var mealsData = <?= json_encode(array_map(function($m) { 
    return ['id' => $m['id'], 'price' => $m['price'], 'priceCents' => $m['priceCents']]; 
}, $formData['meals'])) ?>;

var selectedTierId = null;
var selectedPrice = 0;
var selectedPriceCents = 0;

// Pré-sélection depuis URL
var urlParams = new URLSearchParams(window.location.search);
var preselect = urlParams.get('course');

var preselectionMap = {
  '3km': 'Course Enfant',
  '7.5km': 'Course 7.5km',
  '15km': 'Course 15km'
};

document.querySelectorAll('.race-card').forEach(function(card) {
  var tierId = parseInt(card.dataset.tierId);
  var priceCents = parseInt(card.dataset.price);
  var label = card.dataset.label;
  
  // Pré-sélection
  if (preselect && preselectionMap[preselect] === label) {
    selectCard(card, tierId, priceCents, label);
  }
  
  card.addEventListener('click', function() {
    selectCard(card, tierId, priceCents, label);
  });
});

function selectCard(card, tierId, priceCents, label) {
  document.querySelectorAll('.race-card').forEach(function(c) { c.classList.remove('selected'); });
  card.classList.add('selected');
  selectedTierId = tierId;
  selectedPriceCents = priceCents;
  selectedPrice = priceCents / 100;
  document.getElementById('course_tier_id').value = tierId;
  document.getElementById('course_label').value = label;
  document.getElementById('course_amount').value = priceCents;
  updatePrices();
  checkFormValidity();
}

// Boutons repas
document.querySelectorAll('.qty-btn').forEach(function(btn) {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    var mealId = btn.dataset.mealId;
    var input = document.querySelector('input[name="meal_qty_' + mealId + '"]');
    var value = parseInt(input.value) || 0;
    if (btn.classList.contains('qty-plus')) value++;
    else if (btn.classList.contains('qty-minus') && value > 0) value--;
    input.value = value;
    updatePrices();
  });
});

function updatePrices() {
  var courseEur = selectedPrice || 0;
  document.getElementById('course-price').textContent = courseEur.toFixed(2) + ' €';
  
  var mealTotal = 0;
  mealsData.forEach(function(meal) {
    var qty = parseInt(document.querySelector('input[name="meal_qty_' + meal.id + '"]').value) || 0;
    mealTotal += qty * meal.price;
  });
  document.getElementById('meal-price').textContent = mealTotal.toFixed(2) + ' €';
  
  var total = courseEur + mealTotal;
  document.getElementById('total-price').textContent = total.toFixed(2) + ' €';
  
  // Changer le texte du bouton
  var btn = document.querySelector('.submit-btn');
  if (total <= 0 && selectedTierId) {
    btn.textContent = 'Confirmer mon inscription gratuite →';
  } else {
    btn.textContent = 'Procéder au paiement sécurisé (' + total.toFixed(2) + ' €) →';
  }
}

function checkFormValidity() {
  var prenom = document.getElementById('prenom').value.trim();
  var nom = document.getElementById('nom').value.trim();
  var email = document.getElementById('email').value.trim();
  var telephone = document.getElementById('telephone').value.trim();
  var dateNaissance = document.getElementById('date_naissance').value;
  var sexe = document.getElementById('sexe').value;
  var allFilled = selectedTierId && prenom && nom && email && telephone && dateNaissance && sexe;
  document.querySelector('.submit-btn').disabled = !allFilled;
}

document.querySelectorAll('input, select').forEach(function(el) {
  el.addEventListener('input', checkFormValidity);
  el.addEventListener('change', checkFormValidity);
});

document.getElementById('inscription-form').addEventListener('submit', function() {
  var btn = document.querySelector('.submit-btn');
  btn.textContent = 'Redirection...';
  btn.disabled = true;
});

updatePrices();
checkFormValidity();
</script>
</body>
</html>
