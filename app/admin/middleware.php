<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';

/**
 * Middleware d'authentification admin
 * À inclure en haut de chaque page admin
 */

function requireAdmin(): void
{
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: /admin/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }

    // Vérifier expiration de session (2h)
    if (isset($_SESSION['admin_last_activity']) && time() - $_SESSION['admin_last_activity'] > 7200) {
        session_destroy();
        header('Location: /admin/login.php?expired=1');
        exit;
    }

    $_SESSION['admin_last_activity'] = time();
}

function adminLogout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    header('Location: /admin/login.php');
    exit;
}
