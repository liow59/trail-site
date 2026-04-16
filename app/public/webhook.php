<?php

/**
 * Webhook HelloAsso
 * HelloAsso appelle cette URL à chaque paiement confirmé
 * URL à configurer dans votre espace HelloAsso :
 *   https://votre-domaine.fr/webhook.php
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Trail\Src\Runner;
use Trail\Src\Mailer;
use Trail\Src\HelloAsso;

// Sécurité : accepter uniquement les POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$payload   = file_get_contents('php://input');
$signature = $_SERVER['HTTP_HELLOASSO_SIGNATURE'] ?? '';

// Log pour debug
error_log('[Webhook] Received: ' . substr($payload, 0, 200));

// Valider la signature (optionnel mais recommandé)
// $helloasso = new HelloAsso();
// if (!$helloasso->validateWebhook($payload, $signature)) {
//     http_response_code(401);
//     exit('Invalid signature');
// }

$data = json_decode($payload, true);

if (!$data) {
    http_response_code(400);
    exit('Invalid JSON');
}

$eventType = $data['eventType'] ?? '';
$order     = $data['data'] ?? [];

// On traite uniquement les paiements confirmés
if ($eventType !== 'Payment' && $eventType !== 'Order') {
    http_response_code(200);
    exit('Event ignored');
}

// Extraire l'ID de commande et le montant
$orderId = (string) ($order['id'] ?? $order['orderId'] ?? '');
$montant = (float) (($order['amount']['total'] ?? 0) / 100); // HelloAsso renvoie en centimes

// Retrouver le coureur via l'email dans les metadata HelloAsso
$payerEmail = $order['payer']['email'] ?? '';
$payerFirst = $order['payer']['firstName'] ?? '';
$payerLast  = $order['payer']['lastName'] ?? '';

if (!$orderId) {
    error_log('[Webhook] No orderId found');
    http_response_code(200);
    exit('No orderId');
}

try {
    $runnerModel = new Runner();

    // Chercher le coureur par order_id (si déjà lié) ou par email
    $runner = $runnerModel->findByOrderId($orderId);

    if (!$runner && $payerEmail) {
        // Chercher par email + statut en_attente
        $db   = \Trail\Src\Database::getInstance();
        $stmt = $db->prepare("
            SELECT * FROM runners
            WHERE email = :email AND statut = 'en_attente'
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([':email' => $payerEmail]);
        $runner = $stmt->fetch() ?: null;
    }

    if (!$runner) {
        error_log("[Webhook] Runner not found for order {$orderId} / email {$payerEmail}");
        http_response_code(200);
        exit('Runner not found');
    }

    // Mettre à jour le statut
    $runnerModel->updatePayment((int) $runner['id'], $orderId, $montant, 'payé');

    // Récupérer les données à jour
    $updatedRunner = $runnerModel->findById((int) $runner['id']);

    // Envoyer l'email de confirmation
    $mailer = new Mailer();
    $mailer->sendConfirmation($updatedRunner);

    error_log("[Webhook] ✅ Runner #{$runner['id']} confirmed — order {$orderId}");

    http_response_code(200);
    echo json_encode(['status' => 'ok', 'runner_id' => $runner['id']]);

} catch (\Exception $e) {
    error_log('[Webhook] Error: ' . $e->getMessage());
    http_response_code(500);
    exit('Internal error');
}
