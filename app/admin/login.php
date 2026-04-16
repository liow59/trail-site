<?php

require_once __DIR__ . '/../../src/bootstrap.php';

$error = '';

// Déjà connecté
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    $validUser = $_ENV['ADMIN_USER'] ?? 'admin';
    $validPass = $_ENV['ADMIN_PASS'] ?? '';

    if ($user === $validUser && password_verify($pass, password_hash($validPass, PASSWORD_DEFAULT))
        || ($user === $validUser && $pass === $validPass)) {
        $_SESSION['admin_logged_in']    = true;
        $_SESSION['admin_last_activity'] = time();

        $redirect = $_GET['redirect'] ?? '/admin/dashboard.php';
        // Sécurité : on ne redirige que vers des URLs internes
        if (!str_starts_with($redirect, '/')) {
            $redirect = '/admin/dashboard.php';
        }

        header('Location: ' . $redirect);
        exit;
    }

    $error = 'Identifiants incorrects.';
    sleep(1); // Anti brute-force minimal
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Connexion</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-body">

<main class="admin-login">
    <div class="login-card">
        <div class="login-logo">🏔️</div>
        <h1>Administration</h1>
        <p class="login-subtitle">Trail des Crêtes 2025</p>

        <?php if (isset($_GET['expired'])): ?>
        <div class="alert alert--warning">Session expirée. Reconnectez-vous.</div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="form">
            <div class="form-group">
                <label for="username">Identifiant</label>
                <input type="text" id="username" name="username"
                       autocomplete="username" autofocus required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password"
                       autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn-primary btn--full">Se connecter</button>
        </form>
    </div>
</main>

</body>
</html>
