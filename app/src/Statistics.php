<?php

class Statistics {
    private $pdo;
    
    public function __construct() {
        $host = getenv('DB_HOST') ?: 'trail_mysql';
        $dbname = getenv('DB_NAME') ?: 'trail';
        $user = getenv('DB_USER') ?: 'trail';
        $pass = getenv('DB_PASS') ?: 'Bionicman.40';
        
        $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    }
    
    public function getRaceStats() {
        $races = [
            '3km' => ['total' => 50, 'registered' => 0],
            '7.5km' => ['total' => 100, 'registered' => 0],
            '15km' => ['total' => 100, 'registered' => 0]
        ];
        
        // Compter les inscrits par course
        $stmt = $this->pdo->query("SELECT course, COUNT(*) as count FROM runners GROUP BY course");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (isset($races[$row['course']])) {
                $races[$row['course']]['registered'] = (int)$row['count'];
            }
        }
        
        // Calculer les places restantes et le pourcentage
        foreach ($races as $course => &$data) {
            $data['remaining'] = $data['total'] - $data['registered'];
            $data['percentage'] = ($data['registered'] / $data['total']) * 100;
        }
        
        return $races;
    }
}
