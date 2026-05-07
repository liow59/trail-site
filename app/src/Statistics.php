<?php

class Statistics {
    private $helloasso;
    private $apiUrl;
    private $organizationSlug;
    private $formSlug;
    
    public function __construct() {
        $this->helloasso = new HelloAsso();
        $this->apiUrl = 'https://api.helloasso.com/v5';
        $this->organizationSlug = getenv('HELLOASSO_ORGANIZATION_SLUG') ?: '';
        $this->formSlug = getenv('HELLOASSO_FORM_SLUG') ?: '';
    }
    
    public function getFormData() {
        $cacheFile = '/tmp/helloasso_formdata.json';
        
        if (file_exists($cacheFile)) {
            $cache = json_decode(file_get_contents($cacheFile), true);
            if ($cache && $cache['expiry'] > time()) {
                return $cache['data'];
            }
        }
        
        $result = ['courses' => [], 'meals' => [], 'tests' => [], 'title' => '', 'startDate' => '', 'place' => []];
        
        try {
            $token = $this->helloasso->getAccessToken();
            
            // 1. Récupérer les tiers du formulaire
            $url = $this->apiUrl . '/organizations/' . $this->organizationSlug . '/forms/Event/' . $this->formSlug . '/public';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) return $this->fallbackData();
            
            $form = json_decode($response, true);
            $result['title'] = $form['title'] ?? '';
            $result['startDate'] = $form['startDate'] ?? '';
            $result['place'] = $form['place'] ?? [];
            
            // Indexer les tiers par ID
            $tiersById = [];
            foreach ($form['tiers'] ?? [] as $tier) {
                $label = $tier['label'] ?? '';
                $id = $tier['id'] ?? 0;
                $price = ($tier['price'] ?? 0) / 100;
                $priceCents = $tier['price'] ?? 0;
                
                $tierData = [
                    'id' => $id,
                    'label' => $label,
                    'price' => $price,
                    'priceCents' => $priceCents,
                    'registered' => 0,
                    'total' => 0,
                    'remaining' => 0,
                    'percentage' => 0
                ];
                
                $tiersById[$id] = $tierData;
                
                if (stripos($label, 'test') !== false) {
                    $result['tests'][] = &$tiersById[$id];
                } elseif (stripos($label, 'repas') !== false || stripos($label, 'kebab') !== false) {
                    $result['meals'][] = &$tiersById[$id];
                } elseif (stripos($label, 'course') !== false) {
                    $result['courses'][] = &$tiersById[$id];
                }
            }
            
            // 2. Compter les inscrits par tierId (pagination)
            $continuationToken = null;
            do {
                $itemsUrl = $this->apiUrl . '/organizations/' . $this->organizationSlug 
                          . '/forms/Event/' . $this->formSlug . '/items?pageSize=100';
                if ($continuationToken) {
                    $itemsUrl .= '&continuationToken=' . urlencode($continuationToken);
                }
                
                $ch = curl_init($itemsUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode !== 200) break;
                
                $data = json_decode($response, true);
                if (!$data || !isset($data['data'])) break;
                
                foreach ($data['data'] as $item) {
                    $tierId = $item['tierId'] ?? 0;
                    $state = $item['state'] ?? '';
                    if ($state === 'Canceled') continue;
                    if (isset($tiersById[$tierId])) {
                        $tiersById[$tierId]['registered']++;
                    }
                }
                
                // Pagination avec continuationToken
                $continuationToken = $data['pagination']['continuationToken'] ?? null;
                $hasMore = !empty($continuationToken) && count($data['data']) === 100;
                
            } while ($hasMore);
            
            // 3. Calculer remaining et percentage
            foreach ($tiersById as &$tier) {
                if ($tier['total'] > 0) {
                    $tier['remaining'] = max(0, $tier['total'] - $tier['registered']);
                    $tier['percentage'] = round(($tier['registered'] / $tier['total']) * 100, 1);
                } else {
                    $tier['remaining'] = 0;
                    $tier['percentage'] = 0;
                }
            }
            
        } catch (Exception $e) {
            error_log('Erreur stats HelloAsso: ' . $e->getMessage());
            return $this->fallbackData();
        }
        
        // Cache 2 minutes
        file_put_contents($cacheFile, json_encode([
            'data' => $result,
            'expiry' => time() + 120
        ]));
        
        return $result;
    }
    
    private function fallbackData() {
        return [
            'courses' => [
                ['id' => 20476860, 'label' => 'Course Enfant', 'price' => 0,  'priceCents' => 0,    'registered' => 0, 'total' => 30, 'remaining' => 30, 'percentage' => 0],
                ['id' => 20476845, 'label' => 'Course 7.5km',  'price' => 10, 'priceCents' => 1000, 'registered' => 0, 'total' => 75, 'remaining' => 75, 'percentage' => 0],
                ['id' => 20476854, 'label' => 'Course 15km',   'price' => 15, 'priceCents' => 1500, 'registered' => 0, 'total' => 75, 'remaining' => 75, 'percentage' => 0],
            ],
            'meals' => [], 'tests' => [], 'title' => '', 'startDate' => '', 'place' => []
        ];
    }
}
