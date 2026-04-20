<?php

class Statistics {
    private $clientId;
    private $clientSecret;
    private $apiUrl;
    private $organizationSlug;
    private $formSlug;
    private $accessToken;
    
    public function __construct() {
        $this->clientId = getenv('HELLOASSO_CLIENT_ID') ?: '';
        $this->clientSecret = getenv('HELLOASSO_CLIENT_SECRET') ?: '';
        $this->apiUrl = 'https://api.helloasso.com/v5';
        $this->organizationSlug = getenv('HELLOASSO_ORGANIZATION_SLUG') ?: '';
        $this->formSlug = getenv('HELLOASSO_FORM_SLUG') ?: '';
    }
    
    private function getAccessToken() {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $tokenUrl = 'https://api.helloasso.com/oauth2/token';
        
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
        curl_close($ch);

        $data = json_decode($response, true);
        $this->accessToken = $data['access_token'] ?? null;

        return $this->accessToken;
    }
    
    public function getRaceStats() {
        $races = [
            '3km' => ['total' => 50, 'registered' => 0],
            '7.5km' => ['total' => 100, 'registered' => 0],
            '15km' => ['total' => 100, 'registered' => 0]
        ];
        
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                // Fallback sur valeurs par défaut si API ne répond pas
                return $this->calculateStats($races);
            }
            
            // Récupérer les items/commandes depuis HelloAsso
            $url = $this->apiUrl . '/organizations/' . $this->organizationSlug . '/forms/Event/' . $this->formSlug . '/items';
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                $items = $data['data'] ?? [];
                
                // Compter les inscriptions par course
                foreach ($items as $item) {
                    // Le nom de l'item contient la course (ex: "Inscription 3km")
                    $itemName = $item['name'] ?? '';
                    
                    if (strpos($itemName, '3km') !== false) {
                        $races['3km']['registered']++;
                    } elseif (strpos($itemName, '7.5km') !== false) {
                        $races['7.5km']['registered']++;
                    } elseif (strpos($itemName, '15km') !== false) {
                        $races['15km']['registered']++;
                    }
                }
            }
        } catch (Exception $e) {
            // En cas d'erreur, utiliser les valeurs par défaut
            error_log('Erreur récupération stats HelloAsso: ' . $e->getMessage());
        }
        
        return $this->calculateStats($races);
    }
    
    private function calculateStats($races) {
        foreach ($races as $course => &$data) {
            $data['remaining'] = max(0, $data['total'] - $data['registered']);
            $data['percentage'] = ($data['registered'] / $data['total']) * 100;
        }
        return $races;
    }
}
