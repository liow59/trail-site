<?php

require_once __DIR__ . '/../src/bootstrap.php';

use Trail\Src\Runner;
use Trail\Src\HelloAsso;
use Trail\Src\Mailer;

$errors  = [];
$success = false;

$preselectedCourse = in_array($_GET['course'] ?? '', ['3km', '7.5km', '15km'])
    ? $_GET['course']
    : '7.5km';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide. Rechargez la page.';
    }

    $required = ['nom', 'prenom', 'email', 'date_naissance', 'course'];
    foreach ($required as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $errors[] = "Le champ « {$field} » est obligatoire.";
        }
    }

    if (empty($errors)) {
        $email  = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $course = $_POST['course'];

        if (!$email) {
            $errors[] = 'Adresse email invalide.';
        }

        if (!in_array($course, ['3km', '7.5km', '15km'])) {
            $errors[] = 'Course invalide.';
        }
    }

    if (empty($errors)) {
        $runner = new Runner();

        if ($runner->emailAlreadyRegistered($email, $course)) {
            $errors[] = 'Cette adresse email est déjà inscrite pour cette course.';
        }
    }

    if (empty($errors)) {
        try {
            $repas_poulet   = max(0, (int)($_POST['repas_poulet'] ?? 0));
            $repas_saucisse = max(0, (int)($_POST['repas_saucisse'] ?? 0));
            $repas_nuggets  = max(0, (int)($_POST['repas_nuggets'] ?? 0));
            $total_repas    = ($repas_poulet * 10) + ($repas_saucisse * 12) + ($repas_nuggets * 8);

            $runnerId = $runner->create([
                'nom'             => $_POST['nom'],
                'prenom'          => $_POST['prenom'],
                'email'           => $email,
                'telephone'       => $_POST['telephone'] ?? null,
                'date_naissance'  => $_POST['date_naissance'],
                'course'          => $course,
                'club'            => $_POST['club'] ?? null,
                'repas_poulet'    => $repas_poulet,
                'repas_saucisse'  => $repas_saucisse,
                'repas_nuggets'   => $repas_nuggets,
                'total_repas'     => $total_repas,
            ]);

            $runnerData  = $runner->findById($runnerId);
            $helloasso   = new HelloAsso();
            $paymentUrl  = $helloasso->getPaymentUrl(
                $runnerId,
                $email,
                $_POST['prenom'],
                $_POST['nom']
            );

            $mailer = new Mailer();
            $mailer->sendPendingPayment($runnerData, $paymentUrl);

            header('Location: ' . $paymentUrl);
            exit;

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $errors[] = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$tarifs = ['3km' => 0, '7.5km' => 10, '15km' => 15];
$repasMenu = [
    'poulet'   => ['label' => 'Poulet frites', 'prix' => 10],
    'saucisse' => ['label' => 'Saucisse polenta', 'prix' => 12],
    'nuggets'  => ['label' => 'Nuggets', 'prix' => 8],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — Trail de la Vogue Challaisienne 2026</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="page-header">
    <a href="/" class="logo">La Vogue Challaisienne</a>
</header>

<main class="form-page">
    <div class="container container--narrow">
        <h1>Inscription</h1>
        <p class="form-intro">Remplissez le formulaire ci-dessous. Vous serez redirigé(e) vers HelloAsso pour le paiement sécurisé.</p>

        <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="/inscription.php" class="form" id="inscriptionForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <fieldset>
                <legend>Informations personnelles</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom"
                               value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                               required autocomplete="given-name">
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom"
                               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                               required autocomplete="family-name">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required autocomplete="email">
                    </div>
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone"
                               value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>"
                               autocomplete="tel">
                    </div>
                </div>
                <div class="form-group">
                    <label for="date_naissance">Date de naissance *</label>
                    <input type="date" id="date_naissance" name="date_naissance"
                           value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>"
                           required>
                </div>
                <div class="form-group">
                    <label for="club">Club (facultatif)</label>
                    <input type="text" id="club" name="club"
                           value="<?= htmlspecialchars($_POST['club'] ?? '') ?>">
                </div>
            </fieldset>

            <fieldset>
                <legend>Choix de la course</legend>
                <div class="course-selector">
                    <?php foreach ($tarifs as $dist => $prix): ?>
                    <label class="course-option <?= ($preselectedCourse === $dist || ($_POST['course'] ?? '') === $dist) ? 'selected' : '' ?>">
                        <input type="radio" name="course" value="<?= $dist ?>"
                               <?= ($preselectedCourse === $dist || ($_POST['course'] ?? '') === $dist) ? 'checked' : '' ?>>
                        <span class="course-label">
                            <strong><?= $dist ?></strong>
                            <em><?= $prix > 0 ? $prix . ' €' : 'Gratuit' ?></em>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <fieldset>
                <legend>Choix du repas (facultatif)</legend>
                <p style="font-size:.88rem;color:var(--muted);margin-bottom:16px;">Commandez vos repas pour le jour de la course. Le montant sera ajouté au prix de l'inscription.</p>

                <?php foreach ($repasMenu as $key => $repas): ?>
                <div class="repas-row">
                    <div class="repas-info">
                        <strong><?= $repas['label'] ?></strong>
                        <span class="repas-prix"><?= $repas['prix'] ?> €</span>
                    </div>
                    <div class="repas-qty">
                        <button type="button" class="qty-btn qty-minus" data-target="repas_<?= $key ?>">−</button>
                        <input type="number" name="repas_<?= $key ?>" id="repas_<?= $key ?>"
                               value="<?= (int)($_POST['repas_' . $key] ?? 0) ?>"
                               min="0" max="10" class="qty-input" data-prix="<?= $repas['prix'] ?>">
                        <button type="button" class="qty-btn qty-plus" data-target="repas_<?= $key ?>">+</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </fieldset>

            <div class="price-summary" id="priceSummary">
                <div class="price-line">
                    <span>Course</span>
                    <strong id="prixCourse"><?= $tarifs[$preselectedCourse] ?> €</strong>
                </div>
                <div class="price-line" id="repasLine" style="display:none;">
                    <span>Repas</span>
                    <strong id="prixRepas">0 €</strong>
                </div>
                <div class="price-total">
                    <span>Total à payer</span>
                    <strong id="prixTotal"><?= $tarifs[$preselectedCourse] ?> €</strong>
                </div>
                <small>(paiement sécurisé via HelloAsso)</small>
            </div>

            <div class="form-footer">
                <p class="legal">En soumettant ce formulaire, vous acceptez nos
                    <a href="#">conditions de participation</a> et notre
                    <a href="#">politique de confidentialité</a>.
                </p>
                <button type="submit" class="btn-primary btn--large btn--full">
                    Continuer vers le paiement →
                </button>
            </div>
        </form>
    </div>
</main>

<script>
const tarifs = <?= json_encode($tarifs) ?>;

function updateTotal() {
    const course = document.querySelector('input[name="course"]:checked');
    const prixCourse = course ? tarifs[course.value] : 0;

    let prixRepas = 0;
    document.querySelectorAll('.qty-input').forEach(input => {
        prixRepas += parseInt(input.value || 0) * parseInt(input.dataset.prix);
    });

    document.getElementById('prixCourse').textContent = prixCourse > 0 ? prixCourse + ' €' : 'Gratuit';
    document.getElementById('prixRepas').textContent = prixRepas + ' €';
    document.getElementById('repasLine').style.display = prixRepas > 0 ? 'flex' : 'none';
    document.getElementById('prixTotal').textContent = (prixCourse + prixRepas) + ' €';
}

document.querySelectorAll('input[name="course"]').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.course-option').forEach(el => el.classList.remove('selected'));
        radio.closest('.course-option').classList.add('selected');
        updateTotal();
    });
});

document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        let val = parseInt(input.value || 0);
        if (btn.classList.contains('qty-plus') && val < 10) val++;
        if (btn.classList.contains('qty-minus') && val > 0) val--;
        input.value = val;
        updateTotal();
    });
});

document.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('change', updateTotal);
});

updateTotal();
</script>

</body>
</html>
