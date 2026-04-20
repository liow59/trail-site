<?php

class HelloAsso {
    private $clientId;
    private $clientSecret;
    private $apiUrl;
    private $organizationSlug;
    private $formSlug;
    private $accessToken;

    public function __construct() {
        $this->clientId = getenv('HELLOASSO_CLIENT_ID') ?: $_ENV['HELLOASSO_CLIENT_ID'] ?? '';
        $this->clientSecret = getenv('HELLOASSO_CLIENT_SECRET') ?: $_ENV['HELLOASSO_CLIENT_SECRET'] ?? '';
        $this->apiUrl = getenv('HELLOASSO_API_URL') ?: $_ENV['HELLOASSO_API_URL'] ?? 'https://api.helloasso.com/v5';
        $this->organizationSlug = getenv('HELLOASSO_ORGANIZATION_SLUG') ?: $_ENV['HELLOASSO_ORGANIZATION_SLUG'] ?? '';
        $this->formSlug = getenv('HELLOASSO_FORM_SLUG') ?: $_ENV['HELLOASSO_FORM_SLUG'] ?? '';
        
        // Debug - À RETIRER après test
        error_log("Client ID: " . substr($this->clientId, 0, 10) . "...");
        error_log("Client Secret: " . (empty($this->clientSecret) ? 'VIDE' : 'OK'));
    }

    private function getAccessToken() {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $tokenUrl = $this->apiUrl . '/oauth2/token';
        
        $postData = http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials'
        ]);

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Debug
        error_log("Token URL: " . $tokenUrl);
        error_log("HTTP Code: " . $httpCode);
        error_log("Response: " . $response);
        
        if ($curlError) {
            throw new Exception('Erreur cURL: ' . $curlError);
        }

        if ($httpCode !== 200) {
            throw new Exception('Erreur authentification HelloAsso (HTTP ' . $httpCode . '): ' . $response);
        }

        $data = json_decode($response, true);
        $this->accessToken = $data['access_token'] ?? null;
        
        if (!$this->accessToken) {
            throw new Exception('Token non reçu: ' . json_encode($data));
        }

        return $this->accessToken;
    }

    public function createCheckoutIntent($participantData) {
        $token = $this->getAccessToken();

        $coursePrice = $participantData['course_price'];
        $mealTotal = ($participantData['repas_poulet'] * 10) + 
                     ($participantData['repas_saucisse'] * 12) + 
                     ($participantData['repas_nuggets'] * 8);
        $totalAmount = $coursePrice + $mealTotal;

        $items = [];
        
        $items[] = [
            'name' => 'Inscription ' . $participantData['course'],
            'priceCategory' => 'Fixed',
            'amount' => $coursePrice * 100,
            'type' => 'Payment'
        ];

        if ($participantData['repas_poulet'] > 0) {
            $items[] = [
                'name' => 'Poulet frites x' . $participantData['repas_poulet'],
                'priceCategory' => 'Fixed',
                'amount' => ($participantData['repas_poulet'] * 10) * 100,
                'type' => 'Payment'
            ];
        }
        if ($participantData['repas_saucisse'] > 0) {
            $items[] = [
                'name' => 'Saucisse polenta x' . $participantData['repas_saucisse'],
                'priceCategory' => 'Fixed',
                'amount' => ($participantData['repas_saucisse'] * 12) * 100,
                'type' => 'Payment'
            ];
        }
        if ($participantData['repas_nuggets'] > 0) {
            $items[] = [
                'name' => 'Nuggets x' . $participantData['repas_nuggets'],
                'priceCategory' => 'Fixed',
                'amount' => ($participantData['repas_nuggets'] * 8) * 100,
                'type' => 'Payment'
            ];
        }

        $payload = [
            'totalAmount' => $totalAmount * 100,
            'initialAmount' => $totalAmount * 100,
            'itemName' => 'Trail de la Vogue Challaisienne 2026',
            'backUrl' => 'https://' . $_SERVER['HTTP_HOST'] . '/inscription.php?success=1',
            'errorUrl' => 'https://' . $_SERVER['HTTP_HOST'] . '/inscription.php?error=1',
            'returnUrl' => 'https://' . $_SERVER['HTTP_HOST'] . '/inscription.php?success=1',
            'containsDonation' => false,
            'payer' => [
                'firstName' => $participantData['prenom'],
                'lastName' => $participantData['nom'],
                'email' => $participantData['email'],
                'dateOfBirth' => $participantData['date_naissance'],
                'phoneNumber' => $participantData['telephone']
            ],
            'items' => $items,
            'metadata' => [
                'course' => $participantData['course'],
                'sexe' => $participantData['sexe'],
                'repas_poulet' => $participantData['repas_poulet'],
                'repas_saucisse' => $participantData['repas_saucisse'],
                'repas_nuggets' => $participantData['repas_nuggets']
            ]
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

        $data = json_decode($response, true);
        return $data;
    }
}
