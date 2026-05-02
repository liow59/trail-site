<?php 
require_once __DIR__ . '/../src/bootstrap.php';

$stats = new Statistics();
$raceStats = $stats->getRaceStats();

$error = null;
$success = isset($_GET['success']);
$paymentError = isset($_GET['error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $helloasso = new HelloAsso();
        
        $participantData = [
            'prenom' => trim($_POST['prenom'] ?? ''),
            'nom' => trim($_POST['nom'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telephone' => trim($_POST['telephone'] ?? ''),
            'date_naissance' => $_POST['date_naissance'] ?? '',
            'sexe' => $_POST['sexe'] ?? '',
            'course' => $_POST['course'] ?? '',
            'course_price' => (int)($_POST['course_price'] ?? 0),
            'repas_poulet' => (int)($_POST['repas_poulet'] ?? 0),
            'repas_saucisse' => (int)($_POST['repas_saucisse'] ?? 0),
            'repas_nuggets' => (int)($_POST['repas_nuggets'] ?? 0)
        ];
        
        $checkout = $helloasso->createCheckoutIntent($participantData);
        $checkoutUrl = $checkout['redirectUrl'] ?? null;
        
        if (isset($checkout["free"]) && $checkout["free"]) {
            // Inscription gratuite - pas de paiement nécessaire
            header("Location: /inscription.php?success=1");
            exit;
        }
        
        $checkoutUrl = $checkout["redirectUrl"] ?? null;
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
    <h2 style="font-family:'Bebas Neue',sans-serif; font-size:2rem; color:var(--lime); margin-bottom:1rem;">Paiement confirmé !</h2>
    <p style="color:var(--sand); margin-bottom:1.5rem;">Votre inscription est validée. Vous recevrez un email de confirmation.</p>
    <a href="/" class="cta-btn">Retour à l'accueil</a>
  </div>
</div>
<?php elseif ($paymentError): ?>
<div class="section">
  <div style="background:rgba(196,68,10,0.15); border:1px solid var(--rust); border-radius:4px; padding:2rem; text-align:center; max-width:600px; margin:0 auto;">
    <div style="font-size:3rem; margin-bottom:1rem;">❌</div>
    <h2 style="font-family:'Bebas Neue',sans-serif; font-size:2rem; color:var(--rust); margin-bottom:1rem;">Paiement annulé</h2>
    <p style="color:var(--sand); margin-bottom:1.5rem;">Le paiement n'a pas pu être finalisé. Vous pouvez réessayer.</p>
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
    <div class="race-card" data-race="3km" data-price="0">
      <div class="race-check">✓</div>
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
    <div class="race-card" data-race="7.5km" data-price="10">
      <div class="race-check">✓</div>
      <div class="race-dist">7.5<small style="font-size:1.5rem">km</small></div>
      <div class="race-type">
        <div class="race-info-item"><span class="icon">🕙</span><span>Départ à 10h00</span></div>
        <div class="race-info-item"><span class="icon">🏃</span><span>À partir de 12 ans</span></div>
        <div class="race-info-item"><span class="icon">⛰</span><span>150 D+</span></div>
      </div>
      <div class="race-price"><?= $raceStats['7.5km']['price'] ?> €</div>
      <div class="race-spots">
        <div class="spots-bar"><div class="spots-fill" style="width:<?= $raceStats['7.5km']['percentage'] ?>%"></div></div>
        <span class="spots-text"><?= $raceStats['7.5km']['registered'] ?> inscrits · <?= $raceStats['7.5km']['remaining'] ?> / <?= $raceStats['7.5km']['total'] ?> places</span>
      </div>
    </div>
    <div class="race-card" data-race="15km" data-price="15">
      <div class="race-check">✓</div>
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

    <p class="section-tag" style="margin-top:2rem">// Étape 3 - Repas de fin de course</p>
    <div class="repas-grid">
      <div class="repas-row">
        <div class="repas-info"><strong>Poulet frites</strong><span class="repas-prix">10 €</span></div>
        <div class="repas-qty">
          <button type="button" class="qty-btn qty-minus" data-meal="poulet">−</button>
          <input type="number" name="repas_poulet" id="repas_poulet" class="qty-input" value="0" min="0" readonly>
          <button type="button" class="qty-btn qty-plus" data-meal="poulet">+</button>
        </div>
      </div>
      <div class="repas-row">
        <div class="repas-info"><strong>Saucisse polenta</strong><span class="repas-prix">12 €</span></div>
        <div class="repas-qty">
          <button type="button" class="qty-btn qty-minus" data-meal="saucisse">−</button>
          <input type="number" name="repas_saucisse" id="repas_saucisse" class="qty-input" value="0" min="0" readonly>
          <button type="button" class="qty-btn qty-plus" data-meal="saucisse">+</button>
        </div>
      </div>
      <div class="repas-row">
        <div class="repas-info"><strong>Nuggets</strong><span class="repas-prix">8 €</span></div>
        <div class="repas-qty">
          <button type="button" class="qty-btn qty-minus" data-meal="nuggets">−</button>
          <input type="number" name="repas_nuggets" id="repas_nuggets" class="qty-input" value="0" min="0" readonly>
          <button type="button" class="qty-btn qty-plus" data-meal="nuggets">+</button>
        </div>
      </div>
    </div>

    <div class="price-summary">
      <div class="price-line"><span>Course</span><span id="course-price">— €</span></div>
      <div class="price-line"><span>Repas</span><span id="meal-price">0 €</span></div>
      <div class="price-total"><span>Total</span><span id="total-price">— €</span></div>
    </div>

    <input type="hidden" name="course" id="selected-course">
    <input type="hidden" name="course_price" id="selected-course-price" value="0">
    
    <div style="background:rgba(168,198,64,0.08); border:1px solid rgba(168,198,64,0.2); border-radius:4px; padding:1rem; margin:1.5rem 0; font-size:0.85rem; color:var(--sand);">
      🔒 Paiement sécurisé par carte bancaire
    </div>
    
    <button type="submit" class="submit-btn" disabled>Procéder au paiement sécurisé →</button>
  </form>
</section>

<?php endif; ?>

<footer style="text-align:center; padding:2rem; color:var(--sand); font-size:0.85rem; border-top:1px solid rgba(255,255,255,0.1); margin-top:4rem;">
  <p>© 2026 Trail de la Vogue Challaisienne · Tous droits réservés</p>
</footer>

<script>
let selectedRace = null;
let selectedPrice = 0;

const racePrices = {'3km': 0, '7.5km': 10, '15km': 15};
const mealPrices = {'poulet': 10, 'saucisse': 12, 'nuggets': 8};

const urlParams = new URLSearchParams(window.location.search);
const preselectedCourse = urlParams.get('course');

document.querySelectorAll('.race-card').forEach(card => {
  const race = card.dataset.race;
  
  if (preselectedCourse === race) {
    card.classList.add('selected');
    selectedRace = race;
    selectedPrice = parseInt(card.dataset.price);
    document.getElementById('selected-course').value = race;
    document.getElementById('selected-course-price').value = selectedPrice;
    updatePrices();
    checkFormValidity();
  }
  
  card.addEventListener('click', () => {
    document.querySelectorAll('.race-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    selectedRace = race;
    selectedPrice = parseInt(card.dataset.price);
    document.getElementById('selected-course').value = race;
    document.getElementById('selected-course-price').value = selectedPrice;
    updatePrices();
    checkFormValidity();
  });
});

document.querySelectorAll('.qty-btn').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    const meal = btn.dataset.meal;
    const input = document.getElementById('repas_' + meal);
    let value = parseInt(input.value) || 0;
    if (btn.classList.contains('qty-plus')) value++;
    else if (btn.classList.contains('qty-minus') && value > 0) value--;
    input.value = value;
    updatePrices();
  });
});

function updatePrices() {
  document.getElementById('course-price').textContent = (selectedPrice || 0) + ' €';
  let mealTotal = 0;
  Object.keys(mealPrices).forEach(meal => {
    const qty = parseInt(document.getElementById('repas_' + meal).value) || 0;
    mealTotal += qty * mealPrices[meal];
  });
  document.getElementById('meal-price').textContent = mealTotal + ' €';
  document.getElementById('total-price').textContent = ((selectedPrice || 0) + mealTotal) + ' €';
}

function checkFormValidity() {
  const prenom = document.getElementById('prenom').value.trim();
  const nom = document.getElementById('nom').value.trim();
  const email = document.getElementById('email').value.trim();
  const telephone = document.getElementById('telephone').value.trim();
  const dateNaissance = document.getElementById('date_naissance').value;
  const sexe = document.getElementById('sexe').value;
  const allFilled = selectedRace && prenom && nom && email && telephone && dateNaissance && sexe;
  document.querySelector('.submit-btn').disabled = !allFilled;
}

document.querySelectorAll('input, select').forEach(el => {
  el.addEventListener('input', checkFormValidity);
  el.addEventListener('change', checkFormValidity);
});

document.getElementById('inscription-form').addEventListener('submit', function() {
  document.querySelector('.submit-btn').textContent = 'Redirection vers le paiement...';
  document.querySelector('.submit-btn').disabled = true;
});

updatePrices();
checkFormValidity();
</script>
</body>
</html>
