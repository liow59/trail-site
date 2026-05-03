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
    
    /**
     * Récupère les tiers (billets) depuis l'API HelloAsso
     * et les catégorise en courses, repas, tests
     */
    public function getFormData() {
        $cacheFile = '/tmp/helloasso_formdata.json';
        
        if (file_exists($cacheFile)) {
            $cache = json_decode(file_get_contents($cacheFile), true);
            if ($cache && $cache['expiry'] > time()) {
                return $cache['data'];
            }
        }
        
        $result = [
            'courses' => [],
            'meals' => [],
            'tests' => [],
            'title' => '',
            'description' => '',
            'startDate' => '',
            'place' => ''
        ];
        
        try {
            $token = $this->helloasso->getAccessToken();
            
            // 1. Récupérer les infos du formulaire (tiers, lieu, etc.)
            $url = $this->apiUrl . '/organizations/' . $this->organizationSlug 
                 . '/forms/Event/' . $this->formSlug . '/public';
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                return $this->fallbackData();
            }
            
            $form = json_decode($response, true);
            $result['title'] = $form['title'] ?? '';
            $result['description'] = $form['description'] ?? '';
            $result['startDate'] = $form['startDate'] ?? '';
            $result['place'] = $form['place'] ?? [];
            
            // Catégoriser les tiers
            foreach ($form['tiers'] ?? [] as $tier) {
                $label = $tier['label'] ?? '';
                $id = $tier['id'] ?? 0;
                $price = ($tier['price'] ?? 0) / 100; // Centimes -> euros
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
                
                if (stripos($label, 'test') !== false) {
                    $result['tests'][] = $tierData;
                } elseif (stripos($label, 'repas') !== false || stripos($label, 'kebab') !== false) {
                    $result['meals'][] = $tierData;
                } elseif (stripos($label, 'course') !== false) {
                    $result['courses'][] = $tierData;
                }
            }
            
            // 2. Compter les inscrits par tier
            $pageIndex = 1;
            do {
                $itemsUrl = $this->apiUrl . '/organizations/' . $this->organizationSlug 
                          . '/forms/Event/' . $this->formSlug . '/items'
                          . '?pageIndex=' . $pageIndex . '&pageSize=100';
                
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
                    
                    // Compter dans courses
                    foreach ($result['courses'] as &$c) {
                        if ($c['id'] === $tierId) { $c['registered']++; break; }
                    }
                    // Compter dans meals
                    foreach ($result['meals'] as &$m) {
                        if ($m['id'] === $tierId) { $m['registered']++; break; }
                    }
                    // Compter dans tests
                    foreach ($result['tests'] as &$t) {
                        if ($t['id'] === $tierId) { $t['registered']++; break; }
                    }
                }
                
                $totalPages = $data['pagination']['totalPages'] ?? 1;
                $pageIndex++;
            } while ($pageIndex <= $totalPages && $totalPages > 0);
            
        } catch (Exception $e) {
            error_log('Erreur stats HelloAsso: ' . $e->getMessage());
            return $this->fallbackData();
        }
        
        // Cache 5 minutes
        file_put_contents($cacheFile, json_encode([
            'data' => $result,
            'expiry' => time() + 300
        ]));
        
        return $result;
    }
    
    private function fallbackData() {
        return [
            'courses' => [
                ['id' => 0, 'label' => 'Course Enfant', 'price' => 0, 'priceCents' => 0, 'registered' => 0, 'total' => 30, 'remaining' => 30, 'percentage' => 0],
                ['id' => 0, 'label' => 'Course 7.5km', 'price' => 10, 'priceCents' => 1000, 'registered' => 0, 'total' => 75, 'remaining' => 75, 'percentage' => 0],
                ['id' => 0, 'label' => 'Course 15km', 'price' => 15, 'priceCents' => 1500, 'registered' => 0, 'total' => 75, 'remaining' => 75, 'percentage' => 0],
            ],
            'meals' => [],
            'tests' => [],
            'title' => 'Trail de la vogue Challaisienne 2026',
            'description' => '',
            'startDate' => '2026-09-06T09:00:00+02:00',
            'place' => []
        ];
    }
}
