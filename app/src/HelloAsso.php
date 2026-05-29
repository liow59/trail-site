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
            if ($cache && $cache['expiry'] > time()) return $cache['token'];
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

        if ($httpCode !== 200) throw new Exception('Erreur authentification HelloAsso (HTTP ' . $httpCode . '): ' . $response);

        $data = json_decode($response, true);
        $token = $data['access_token'] ?? null;
        if (!$token) throw new Exception('Token non reçu');

        file_put_contents($cacheFile, json_encode(['token' => $token, 'expiry' => time() + 1500]));
        return $token;
    }

    public function createCheckoutIntent($payer, $selectedItems) {
        $token = $this->getAccessToken();

        // Sanitizer les noms
        $sanitize = function($str) {
            $str = trim($str);
            $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
            $str = preg_replace('/[^a-zA-Z0-9 \-\.\']/', '', $str);
            return trim($str);
        };

        $payer['prenom'] = $sanitize($payer['prenom']);
        $payer['nom']    = $sanitize($payer['nom']);

        // Calculer le total
        $totalCents = 0;
        $items = [];
        foreach ($selectedItems as $item) {
            $amount = intval($item['amount']);
            if ($amount <= 0) continue;
            $items[] = [
                'name' => $item['label'],
                'priceCategory' => 'Fixed',
                'amount' => $amount,
                'type' => 'Payment'
            ];
            $totalCents += $amount;
        }

        // Inscription gratuite : checkout-intent avec priceCategory Free
        if ($totalCents <= 0) {
            $freeItems = [];
            foreach ($selectedItems as $item) {
                $freeItems[] = [
                    'name'          => $item['label'],
                    'priceCategory' => 'Free',
                    'amount'        => 0,
                    'type'          => 'Registration'
                ];
            }

            $host    = $_SERVER['HTTP_HOST'] ?? 'www.vogue-challex.fr';
            $baseUrl = 'https://www.vogue-challex.fr';

            $payload = [
                'totalAmount'      => 0,
                'initialAmount'    => 0,
                'itemName'         => 'Trail de la Vogue Challaisienne 2026',
                'backUrl'          => $baseUrl . '/inscription.php',
                'errorUrl'         => $baseUrl . '/inscription.php?error=1',
                'returnUrl'        => $baseUrl . '/inscription.php?success=1',
                'containsDonation' => false,
                'payer'            => [
                    'firstName' => $payer['prenom'],
                    'lastName'  => $payer['nom'],
                    'email'     => $payer['email']
                ],
                'items' => $freeItems
            ];

            // Utiliser l'endpoint lié à l'événement pour apparaître dans la billetterie
        $ch = curl_init($this->apiUrl . '/organizations/' . $this->organizationSlug . '/forms/Event/' . $this->formSlug . '/checkout-intents');
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

            // Si checkout-intent fonctionne, rediriger
            if ($httpCode === 200 || $httpCode === 201) {
                $data = json_decode($response, true);
                if (!empty($data['redirectUrl'])) {
                    return $data;
                }
            }

            // Sinon fallback : widget intégré
            return ['free' => true, 'redirectUrl' => null];
        }

        // URL de base
        $baseUrl = 'https://www.vogue-challex.fr';

        $payload = [
            'totalAmount' => $totalCents,
            'initialAmount' => $totalCents,
            'itemName' => 'Trail de la Vogue Challaisienne 2026',
            'backUrl' => $baseUrl . '/inscription.php',
            'errorUrl' => $baseUrl . '/inscription.php?error=1',
            'returnUrl' => $baseUrl . '/inscription.php?success=1',
            'containsDonation' => false,
            'payer' => [
                'firstName' => $payer['prenom'],
                'lastName'  => $payer['nom'],
                'email'     => $payer['email']
            ],
            'items' => $items
        ];

        // Utiliser l'endpoint lié à l'événement pour apparaître dans la billetterie
        $ch = curl_init($this->apiUrl . '/organizations/' . $this->organizationSlug . '/forms/Event/' . $this->formSlug . '/checkout-intents');
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
