<?php

require_once __DIR__ . '/../src/bootstrap.php';

use Trail\Src\Runner;
use Trail\Src\Mailer;

$runnerId = (int) ($_GET['runner_id'] ?? 0);
$status   = $_GET['status'] ?? 'error';
$runner   = null;

if ($runnerId > 0) {
    $runnerModel = new Runner();
    $runner      = $runnerModel->findById($runnerId);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $status === 'success' ? 'Inscription confirmée' : 'Erreur de paiement' ?> – Trail 2025</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="page-header">
    <a href="/" class="logo">🏔️ Trail des Crêtes</a>
</header>

<main class="callback-page">
    <div class="container container--narrow">

        <?php if ($status === 'success' && $runner): ?>
        <div class="callback-card callback-card--success">
            <div class="callback-icon">✅</div>
            <h1>Inscription confirmée !</h1>
            <p>
                Bonjour <strong><?= htmlspecialchars($runner['prenom']) ?></strong>,<br>
                votre inscription sur la <strong><?= htmlspecialchars($runner['course']) ?></strong> est enregistrée.
            </p>
            <div class="callback-recap">
                <div class="recap-row">
                    <span>Nom</span>
                    <strong><?= htmlspecialchars($runner['prenom'] . ' ' . $runner['nom']) ?></strong>
                </div>
                <div class="recap-row">
                    <span>Course</span>
                    <strong><?= htmlspecialchars($runner['course']) ?></strong>
                </div>
                <div class="recap-row">
                    <span>N° dossard provisoire</span>
                    <strong>#<?= str_pad((string)$runner['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                </div>
            </div>
            <p class="callback-note">
                📧 Un email de confirmation a été envoyé à
                <strong><?= htmlspecialchars($runner['email']) ?></strong>.
            </p>
            <a href="/" class="btn-primary">Retour à l'accueil</a>
        </div>

        <?php elseif ($status === 'error'): ?>
        <div class="callback-card callback-card--error">
            <div class="callback-icon">❌</div>
            <h1>Paiement non finalisé</h1>
            <p>
                Votre paiement n'a pas été complété. Votre dossier est conservé pendant 24h.
            </p>
            <?php if ($runner): ?>
            <p class="callback-note">
                📧 Un email avec le lien de paiement a été envoyé à
                <strong><?= htmlspecialchars($runner['email']) ?></strong>.
            </p>
            <?php endif; ?>
            <div class="callback-actions">
                <?php if ($runner): ?>
                <a href="/inscription.php" class="btn-primary">Réessayer</a>
                <?php endif; ?>
                <a href="/" class="btn-outline">Retour à l'accueil</a>
            </div>
        </div>

        <?php else: ?>
        <div class="callback-card callback-card--error">
            <div class="callback-icon">⚠️</div>
            <h1>Lien invalide</h1>
            <p>Ce lien n'est pas valide. Contactez-nous si vous avez besoin d'aide.</p>
            <a href="/" class="btn-primary">Retour à l'accueil</a>
        </div>
        <?php endif; ?>

    </div>
</main>

</body>
</html>
