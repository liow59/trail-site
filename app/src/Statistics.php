<?php

class Statistics {
    private $helloasso;
    private $apiUrl;
    private $organizationSlug;
    private $formSlug;
    
    private $racesConfig = [
        '3km'   => ['total' => 30,  'price' => 0,  'label' => 'Course Enfant',  'tierMatch' => 'course enfant'],
        '7.5km' => ['total' => 75,  'price' => 10, 'label' => 'Course 7.5km',   'tierMatch' => 'course 7.5km'],
        '15km'  => ['total' => 75,  'price' => 15, 'label' => 'Course 15km',    'tierMatch' => 'course 15km']
    ];
    
    public function __construct() {
        $this->helloasso = new HelloAsso();
        $this->apiUrl = 'https://api.helloasso.com/v5';
        $this->organizationSlug = getenv('HELLOASSO_ORGANIZATION_SLUG') ?: '';
        $this->formSlug = getenv('HELLOASSO_FORM_SLUG') ?: '';
    }
    
    public function getRaceStats() {
        // Cache les stats pendant 5 minutes
        $cacheFile = '/tmp/helloasso_stats.json';
        
        if (file_exists($cacheFile)) {
            $cache = json_decode(file_get_contents($cacheFile), true);
            if ($cache && $cache['expiry'] > time()) {
                return $cache['data'];
            }
        }
        
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
            $token = $this->helloasso->getAccessToken();
            
            $pageIndex = 1;
            do {
                $url = $this->apiUrl . '/organizations/' . $this->organizationSlug 
                     . '/forms/Event/' . $this->formSlug . '/items'
                     . '?pageIndex=' . $pageIndex . '&pageSize=100&withDetails=true';
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode !== 200) break;
                
                $data = json_decode($response, true);
                if (!$data || !isset($data['data'])) break;
                
                foreach ($data['data'] as $item) {
                    $itemName = strtolower($item['name'] ?? '');
                    $state = $item['state'] ?? '';
                    
                    if ($state === 'Canceled') continue;
                    
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
        
        foreach ($races as &$race) {
            $race['remaining'] = max(0, $race['total'] - $race['registered']);
            $race['percentage'] = round(($race['registered'] / $race['total']) * 100, 1);
        }
        
        // Sauvegarder en cache 5 minutes
        file_put_contents($cacheFile, json_encode([
            'data' => $races,
            'expiry' => time() + 300
        ]));
        
        return $races;
    }
}
