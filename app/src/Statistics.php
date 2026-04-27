<?php

class Statistics {
    private $clientId;
    private $clientSecret;
    private $apiUrl;
    private $organizationSlug;
    private $formSlug;
    private $accessToken;
    
    // Configuration alignée sur HelloAsso
    private $racesConfig = [
        '3km'   => ['total' => 30,  'price' => 0,  'label' => 'Course Enfant',     'tierMatch' => 'course enfant'],
        '7.5km' => ['total' => 75,  'price' => 10, 'label' => 'Course 7.5km',      'tierMatch' => 'course 7.5km'],
        '15km'  => ['total' => 75,  'price' => 15, 'label' => 'Course 15km',        'tierMatch' => 'course 15km'],
        'test'  => ['total' => 10,  'price' => 0.5,'label' => 'Test (payant)',       'tierMatch' => 'test']
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
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
            // Récupérer tous les items vendus avec pagination
            $pageIndex = 1;
            
            do {
                $data = $this->apiGet(
                    '/organizations/' . $this->organizationSlug . '/forms/Event/' . $this->formSlug . '/items',
                    [
                        'pageIndex' => $pageIndex,
                        'pageSize' => 100,
                        'withDetails' => 'true'
                    ]
                );
                
                if (!$data || !isset($data['data'])) break;
                
                foreach ($data['data'] as $item) {
                    $itemName = strtolower($item['name'] ?? '');
                    $state = $item['state'] ?? '';
                    
                    // Ne compter que les items valides
                    if ($state === 'Canceled') continue;
                    
                    // Matcher avec les courses configurées
                    foreach ($races as $key => &$race) {
                        $match = $this->racesConfig[$key]['tierMatch'];
                        if (strpos($itemName, $match) !== false) {
                            $race['registered']++;
                            break;
                        }
                    }
                }
                
                $totalPages = $data['pagination']['totalPages'] ?? 1;
                $pageIndex++;
                
            } while ($pageIndex <= $totalPages && $totalPages > 0);
            
        } catch (Exception $e) {
            error_log('Erreur stats HelloAsso: ' . $e->getMessage());
        }
        
        // Calculer remaining et percentage
        foreach ($races as &$race) {
            $race['remaining'] = max(0, $race['total'] - $race['registered']);
            $race['percentage'] = round(($race['registered'] / $race['total']) * 100, 1);
        }
        
        return $races;
    }
}
