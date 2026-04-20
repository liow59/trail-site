<?php

class HelloAsso {
    private $clientId;
    private $clientSecret;
    private $apiUrl;
    private $organizationSlug;
    private $formSlug;
    private $accessToken;

    public function __construct() {
        $this->clientId = $_ENV['HELLOASSO_CLIENT_ID'] ?? '';
        $this->clientSecret = $_ENV['HELLOASSO_CLIENT_SECRET'] ?? '';
        $this->apiUrl = $_ENV['HELLOASSO_API_URL'] ?? 'https://api.helloasso.com/v5';
        $this->organizationSlug = $_ENV['HELLOASSO_ORGANIZATION_SLUG'] ?? '';
        $this->formSlug = $_ENV['HELLOASSO_FORM_SLUG'] ?? '';
    }

    private function getAccessToken() {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $ch = curl_init($this->apiUrl . '/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $this->accessToken = $data['access_token'] ?? null;

        return $this->accessToken;
    }

    public function createCheckoutIntent($participantData) {
        $token = $this->getAccessToken();
        if (!$token) {
            throw new Exception('Impossible d\'obtenir le token API HelloAsso');
        }

        // Calculer le montant total
        $coursePrice = $participantData['course_price'];
        $mealTotal = ($participantData['repas_poulet'] * 10) + 
                     ($participantData['repas_saucisse'] * 12) + 
                     ($participantData['repas_nuggets'] * 8);
        $totalAmount = $coursePrice + $mealTotal;

        // Préparer les items de paiement
        $items = [];
        
        // Item pour la course
        $items[] = [
            'name' => 'Inscription ' . $participantData['course'],
            'priceCategory' => 'Fixed',
            'amount' => $coursePrice * 100, // En centimes
            'type' => 'Payment'
        ];

        // Items pour les repas
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

        // Créer le checkout intent
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
            throw new Exception('Erreur API HelloAsso: ' . $response);
        }

        $data = json_decode($response, true);
        return $data;
    }
}
