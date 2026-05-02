<?php

class HelloAsso {
    private $clientId;
    private $clientSecret;
    private $apiUrl;
    private $organizationSlug;
    private $formSlug;

    public function __construct() {
        $this->clientId = getenv('HELLOASSO_CLIENT_ID') ?: '';
        $this->clientSecret = getenv('HELLOASSO_CLIENT_SECRET') ?: '';
        $this->apiUrl = 'https://api.helloasso.com/v5';
        $this->organizationSlug = getenv('HELLOASSO_ORGANIZATION_SLUG') ?: '';
        $this->formSlug = getenv('HELLOASSO_FORM_SLUG') ?: '';
    }

    public function getAccessToken() {
        $cacheFile = '/tmp/helloasso_token.json';
        
        if (file_exists($cacheFile)) {
            $cache = json_decode(file_get_contents($cacheFile), true);
            if ($cache && $cache['expiry'] > time()) {
                return $cache['token'];
            }
        }

        $ch = curl_init('https://api.helloasso.com/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Erreur authentification HelloAsso (HTTP ' . $httpCode . '): ' . $response);
        }

        $data = json_decode($response, true);
        $token = $data['access_token'] ?? null;

        if (!$token) {
            throw new Exception('Token non reçu');
        }

        file_put_contents($cacheFile, json_encode([
            'token' => $token,
            'expiry' => time() + 1500
        ]));

        return $token;
    }

    public function createCheckoutIntent($participantData) {
        $token = $this->getAccessToken();

        $coursePrice = floatval($participantData['course_price']);
        $mealTotal = ($participantData['repas_poulet'] * 10) + 
                     ($participantData['repas_saucisse'] * 12) + 
                     ($participantData['repas_nuggets'] * 8);
        $totalAmount = $coursePrice + $mealTotal;

        // HelloAsso n'accepte pas les montants à 0
        if ($totalAmount <= 0) {
            return ['redirectUrl' => null, 'free' => true];
        }

        // Montant en centimes (entier)
        $totalCents = intval(round($totalAmount * 100));

        $items = [];
        
        if ($coursePrice > 0) {
            $items[] = [
                'name' => 'Inscription ' . $participantData['course'],
                'priceCategory' => 'Fixed',
                'amount' => intval(round($coursePrice * 100)),
                'type' => 'Payment'
            ];
        }

        if ($participantData['repas_poulet'] > 0) {
            $items[] = [
                'name' => 'Poulet frites x' . $participantData['repas_poulet'],
                'priceCategory' => 'Fixed',
                'amount' => intval($participantData['repas_poulet'] * 10 * 100),
                'type' => 'Payment'
            ];
        }
        if ($participantData['repas_saucisse'] > 0) {
            $items[] = [
                'name' => 'Saucisse polenta x' . $participantData['repas_saucisse'],
                'priceCategory' => 'Fixed',
                'amount' => intval($participantData['repas_saucisse'] * 12 * 100),
                'type' => 'Payment'
            ];
        }
        if ($participantData['repas_nuggets'] > 0) {
            $items[] = [
                'name' => 'Nuggets x' . $participantData['repas_nuggets'],
                'priceCategory' => 'Fixed',
                'amount' => intval($participantData['repas_nuggets'] * 8 * 100),
                'type' => 'Payment'
            ];
        }

        // Vérifier que la somme des items = totalAmount
        $itemsTotal = 0;
        foreach ($items as $item) {
            $itemsTotal += $item['amount'];
        }

        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $baseUrl = $protocol . '://' . $host;

        $payload = [
            'totalAmount' => $itemsTotal,
            'initialAmount' => $itemsTotal,
            'itemName' => 'Trail de la Vogue Challaisienne 2026',
            'backUrl' => $baseUrl . '/inscription.php',
            'errorUrl' => $baseUrl . '/inscription.php?error=1',
            'returnUrl' => $baseUrl . '/inscription.php?success=1',
            'containsDonation' => false,
            'payer' => [
                'firstName' => $participantData['prenom'],
                'lastName' => $participantData['nom'],
                'email' => $participantData['email']
            ],
            'items' => $items
        ];

        $ch = curl_init($this->apiUrl . '/organizations/' . $this->organizationSlug . '/checkout-intents');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 && $httpCode !== 201) {
            throw new Exception('Erreur création paiement (HTTP ' . $httpCode . '): ' . $response);
        }

        return json_decode($response, true);
    }
}
