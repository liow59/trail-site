<?php

require_once __DIR__ . '/../src/bootstrap.php';

use Trail\Src\Runner;
use Trail\Src\HelloAsso;
use Trail\Src\Mailer;

$errors  = [];
$success = false;

// Pré-sélection de la course depuis l'URL
$preselectedCourse = in_array($_GET['course'] ?? '', ['10km', '23km', '42km'])
    ? $_GET['course']
    : '23km';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide. Rechargez la page.';
    }

    // Validation des champs
    $required = ['nom', 'prenom', 'email', 'date_naissance', 'course', 'taille_tshirt'];
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

        if (!in_array($course, ['10km', '23km', '42km'])) {
            $errors[] = 'Course invalide.';
        }
    }

    if (empty($errors)) {
        $runner = new Runner();

        // Vérifier inscription en double
        if ($runner->emailAlreadyRegistered($email, $course)) {
            $errors[] = 'Cette adresse email est déjà inscrite pour cette course.';
        }
    }

    if (empty($errors)) {
        try {
            $runnerId = $runner->create([
                'nom'            => $_POST['nom'],
                'prenom'         => $_POST['prenom'],
                'email'          => $email,
                'telephone'      => $_POST['telephone'] ?? null,
                'date_naissance' => $_POST['date_naissance'],
                'course'         => $course,
                'taille_tshirt'  => $_POST['taille_tshirt'],
                'club'           => $_POST['club'] ?? null,
            ]);

            $runnerData  = $runner->findById($runnerId);
            $helloasso   = new HelloAsso();
            $paymentUrl  = $helloasso->getPaymentUrl(
                $runnerId,
                $email,
                $_POST['prenom'],
                $_POST['nom']
            );

            // Envoyer l'email avec le lien de paiement
            $mailer = new Mailer();
            $mailer->sendPendingPayment($runnerData, $paymentUrl);

            // Rediriger vers HelloAsso
            header('Location: ' . $paymentUrl);
            exit;

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $errors[] = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}

// Générer un token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$tarifs = ['10km' => 25, '23km' => 40, '42km' => 60];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription – Trail 2025</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="page-header">
    <a href="/" class="logo">🏔️ Trail des Crêtes</a>
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
                           max="<?= date('Y-m-d', strtotime('-16 years')) ?>" required>
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
                            <em><?= $prix ?> €</em>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div id="priceSummary" class="price-summary">
                    Montant : <strong id="priceDisplay"><?= $tarifs[$preselectedCourse] ?> €</strong>
                    <small>(paiement sécurisé via HelloAsso)</small>
                </div>

                <div class="form-group">
                    <label for="taille_tshirt">Taille T-shirt *</label>
                    <select id="taille_tshirt" name="taille_tshirt" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach (['XS','S','M','L','XL','XXL'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($_POST['taille_tshirt'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>

            <div class="form-footer">
                <p class="legal">En soumettant ce formulaire, vous acceptez nos
                    <a href="#">conditions de participation</a> et notre
                    <a href="#">politique de confidentialité</a>.
                </p>
                <button type="submit" class="btn-primary btn--large">
                    Continuer vers le paiement →
                </button>
            </div>
        </form>
    </div>
</main>

<script>
const tarifs = <?= json_encode($tarifs) ?>;
document.querySelectorAll('input[name="course"]').forEach(radio => {
    radio.addEventListener('change', () => {
        document.getElementById('priceDisplay').textContent = tarifs[radio.value] + ' €';
        document.querySelectorAll('.course-option').forEach(el => el.classList.remove('selected'));
        radio.closest('.course-option').classList.add('selected');
    });
});
</script>

</body>
</html>
