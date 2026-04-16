<?php

require_once __DIR__ . '/middleware.php';
requireAdmin();

use Trail\Src\Runner;

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $runnerId  = (int) ($_POST['runner_id'] ?? 0);
    $runnerModel = new Runner();

    if ($action === 'cancel' && $runnerId) {
        $runnerModel->cancel($runnerId);
        header('Location: /admin/dashboard.php?msg=cancelled');
        exit;
    }

    if ($action === 'logout') {
        adminLogout();
    }
}

// Export CSV
if (isset($_GET['export'])) {
    $runnerModel = new Runner();
    $filters     = ['course' => $_GET['course'] ?? '', 'statut' => $_GET['statut'] ?? ''];
    $runners     = $runnerModel->getAll($filters['course'], $filters['statut']);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="inscriptions_' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Naissance', 'Course', 'T-shirt', 'Club', 'Statut', 'Montant', 'N° HelloAsso', 'Date inscription'], ';');
    foreach ($runners as $r) {
        fputcsv($out, [
            $r['id'], $r['prenom'], $r['nom'], $r['email'],
            $r['telephone'], $r['date_naissance'], $r['course'],
            $r['taille_tshirt'], $r['club'], $r['statut'],
            $r['montant'], $r['helloasso_order_id'], $r['created_at']
        ], ';');
    }
    fclose($out);
    exit;
}

// Filtres
$filterCourse = $_GET['course'] ?? '';
$filterStatut = $_GET['statut'] ?? '';
$search       = trim($_GET['q'] ?? '');

$runnerModel = new Runner();
$stats       = $runnerModel->getStats();
$runners     = $runnerModel->getAll($filterCourse, $filterStatut);

// Recherche texte
if ($search) {
    $runners = array_filter($runners, fn($r) =>
        stripos($r['nom'], $search) !== false ||
        stripos($r['prenom'], $search) !== false ||
        stripos($r['email'], $search) !== false
    );
}

$message = match($_GET['msg'] ?? '') {
    'cancelled' => 'Inscription annulée.',
    default     => ''
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin – Trail 2025</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-body">

<nav class="admin-nav">
    <a href="/admin/dashboard.php" class="admin-nav-logo">🏔️ Admin Trail</a>
    <form method="POST">
        <button type="submit" name="action" value="logout" class="btn-outline btn--sm">Déconnexion</button>
    </form>
</nav>

<main class="admin-main">
    <div class="container">

        <?php if ($message): ?>
        <div class="alert alert--success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total'] ?></div>
                <div class="stat-label">Inscrits total</div>
            </div>
            <div class="stat-card stat-card--green">
                <div class="stat-value"><?= $stats['payes'] ?></div>
                <div class="stat-label">Paiements confirmés</div>
            </div>
            <div class="stat-card stat-card--orange">
                <div class="stat-value"><?= $stats['en_attente'] ?></div>
                <div class="stat-label">En attente</div>
            </div>
            <div class="stat-card stat-card--blue">
                <div class="stat-value"><?= number_format((float)$stats['total_encaisse'], 0, ',', ' ') ?> €</div>
                <div class="stat-label">Total encaissé</div>
            </div>
        </div>

        <!-- PAR COURSE -->
        <div class="stats-grid stats-grid--3">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total_10km'] ?></div>
                <div class="stat-label">10 km</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total_23km'] ?></div>
                <div class="stat-label">23 km</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total_42km'] ?></div>
                <div class="stat-label">42 km</div>
            </div>
        </div>

        <!-- FILTRES -->
        <form method="GET" class="filter-bar">
            <input type="text" name="q" placeholder="Rechercher nom, prénom, email…"
                   value="<?= htmlspecialchars($search) ?>" class="filter-search">

            <select name="course" onchange="this.form.submit()">
                <option value="">Toutes les courses</option>
                <?php foreach (['10km','23km','42km'] as $c): ?>
                <option value="<?= $c ?>" <?= $filterCourse === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
            </select>

            <select name="statut" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                <?php foreach (['payé','en_attente','annulé'] as $s): ?>
                <option value="<?= $s ?>" <?= $filterStatut === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-primary btn--sm">Filtrer</button>
            <a href="?export=1&course=<?= urlencode($filterCourse) ?>&statut=<?= urlencode($filterStatut) ?>"
               class="btn-outline btn--sm">⬇ Export CSV</a>
        </form>

        <!-- TABLEAU -->
        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Coureur</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>T-shirt</th>
                        <th>Statut</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($runners)): ?>
                    <tr><td colspan="9" style="text-align:center;padding:30px;color:#999">Aucun résultat</td></tr>
                    <?php endif; ?>
                    <?php foreach ($runners as $r): ?>
                    <tr class="<?= $r['statut'] === 'annulé' ? 'row--cancelled' : '' ?>">
                        <td><?= str_pad((string)$r['id'], 4, '0', STR_PAD_LEFT) ?></td>
                        <td><strong><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></strong>
                            <?php if ($r['club']): ?>
                            <br><small><?= htmlspecialchars($r['club']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($r['email']) ?></td>
                        <td><span class="badge badge--course"><?= $r['course'] ?></span></td>
                        <td><?= $r['taille_tshirt'] ?></td>
                        <td>
                            <span class="badge badge--<?= $r['statut'] === 'payé' ? 'success' : ($r['statut'] === 'en_attente' ? 'warning' : 'danger') ?>">
                                <?= $r['statut'] ?>
                            </span>
                        </td>
                        <td><?= $r['montant'] ? number_format((float)$r['montant'], 2, ',', ' ') . ' €' : '–' ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                        <td>
                            <?php if ($r['statut'] !== 'annulé'): ?>
                            <form method="POST" onsubmit="return confirm('Annuler cette inscription ?')">
                                <input type="hidden" name="runner_id" value="<?= $r['id'] ?>">
                                <button type="submit" name="action" value="cancel"
                                        class="btn-danger btn--sm">Annuler</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p class="table-count"><?= count($runners) ?> résultat(s)</p>

    </div>
</main>

</body>
</html>
