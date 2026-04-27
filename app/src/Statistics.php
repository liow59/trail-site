<?php

class Statistics {
    private $clientId;
    private $clientSecret;
    private $apiUrl;
    private $organizationSlug;
    private $formSlug;
    private $accessToken;
    
    // Configuration des courses (total places)
    private $racesConfig = [
        '3km'   => ['total' => 50,  'price' => 0,  'label' => '3 km'],
        '7.5km' => ['total' => 100, 'price' => 10, 'label' => '7.5 km'],
        '15km'  => ['total' => 100, 'price' => 15, 'label' => '15 km']
    ];
    
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
        
        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $this->accessToken = $data['access_token'] ?? null;

        return $this->accessToken;
    }
    
    private function apiGet($endpoint, $params = []) {
        $token = $this->getAccessToken();
        if (!$token) return null;
        
        $url = $this->apiUrl . $endpoint;
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        error_log("HelloAsso API error ($httpCode): $response");
        return null;
    }
    
    public function getRaceStats() {
        $races = [];
        foreach ($this->racesConfig as $key => $config) {
            $races[$key] = [
                'total' => $config['total'],
                'price' => $config['price'],
                'label' => $config['label'],
                'registered' => 0,
                'remaining' => $config['total'],
                'percentage' => 0
            ];
        }
        
        try {
            // Récupérer TOUS les items vendus (pagination)
            $allItems = [];
            $pageIndex = 1;
            $pageSize = 100;
            
            do {
                $data = $this->apiGet(
                    '/organizations/' . $this->organizationSlug . '/forms/Event/' . $this->formSlug . '/items',
                    [
                        'pageIndex' => $pageIndex,
                        'pageSize' => $pageSize,
                        'itemStates' => 'Processed,Registered',
                        'withDetails' => 'true'
                    ]
                );
                
                if (!$data || !isset($data['data'])) break;
                
                $items = $data['data'];
                $allItems = array_merge($allItems, $items);
                
                // Vérifier s'il y a une page suivante
                $totalPages = $data['pagination']['totalPages'] ?? 1;
                $pageIndex++;
                
            } while ($pageIndex <= $totalPages);
            
            // Compter les inscriptions par course
            foreach ($allItems as $item) {
                $itemName = strtolower($item['name'] ?? '');
                $tierName = strtolower($item['tierName'] ?? '');
                $searchIn = $itemName . ' ' . $tierName;
                
                foreach ($races as $key => &$race) {
                    // Chercher dans le nom de l'item ou du tier
                    if (strpos($searchIn, strtolower($key)) !== false) {
                        $race['registered']++;
                        break;
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log('Erreur récupération stats HelloAsso: ' . $e->getMessage());
        }
        
        // Calculer remaining et percentage
        foreach ($races as $key => &$race) {
            $race['remaining'] = max(0, $race['total'] - $race['registered']);
            $race['percentage'] = round(($race['registered'] / $race['total']) * 100, 1);
        }
        
        return $races;
    }
    
    // Debug : voir la réponse brute de l'API
    public function debugItems() {
        $data = $this->apiGet(
            '/organizations/' . $this->organizationSlug . '/forms/Event/' . $this->formSlug . '/items',
            [
                'pageSize' => 20,
                'withDetails' => 'true'
            ]
        );
        return $data;
    }
}
