<?php
require_once __DIR__ . '/../src/bootstrap.php';

// Lire le body de la requête HelloAsso
$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);

if (!$data) {
    http_response_code(400);
    exit;
}

error_log('Webhook HelloAsso reçu: ' . $rawBody);

try {
    $pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME') . ';charset=utf8mb4',
        getenv('DB_USER'), getenv('DB_PASS')
    );
    
    $mailer = new Mailer();
    
    // HelloAsso envoie différents types d'événements
    $eventType = $data['eventType'] ?? '';
    
    // Traiter uniquement les paiements confirmés
    if (in_array($eventType, ['Order', 'Payment'])) {
        $order = $data['data'] ?? [];
        $orderId = $order['id'] ?? null;
        
        if (!$orderId) exit;
        
        // Récupérer l'inscription en attente depuis la DB
        $stmt = $pdo->prepare("SELECT * FROM inscriptions WHERE order_id = ? OR (statut = 'pending' AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)) ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$orderId]);
        $inscription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$inscription) exit;
        
        // Récupérer le ticket URL depuis HelloAsso
        $ticketUrl = null;
        $items = $order['items'] ?? [];
        foreach ($items as $item) {
            if (!empty($item['ticketUrl'])) {
                $ticketUrl = $item['ticketUrl'];
                break;
            }
        }
        
        // Assigner le dossard
        $dossard = $mailer->assignDossard($inscription['course']);
        
        // Mettre à jour l'inscription
        $stmt = $pdo->prepare("UPDATE inscriptions SET statut='paid', dossard=?, ticket_url=?, order_id=? WHERE id=?");
        $stmt->execute([$dossard, $ticketUrl, $orderId, $inscription['id']]);
        
        // Envoyer l'email de confirmation
        $inscription['dossard'] = $dossard;
        $inscription['ticket_url'] = $ticketUrl;
        $mailer->sendConfirmationEmail($inscription);
    }
    
    http_response_code(200);
    echo 'OK';
    
} catch (Exception $e) {
    error_log('Webhook error: ' . $e->getMessage());
    http_response_code(500);
}
