<?php
require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json');

$stats = new Statistics();

echo json_encode([
    'raceStats' => $stats->getRaceStats(),
    'rawItems' => $stats->debugItems()
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
