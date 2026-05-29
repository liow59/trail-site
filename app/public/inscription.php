<?php
session_start();
require_once __DIR__ . '/../src/bootstrap.php';

$stats = new Statistics();
$formData = $stats->getFormData();

$orderMap = ['Course Test (payant)', 'Course Enfant', 'Course 7.5km', 'Course 15km'];
$courseExtras = [
    'Course Test (payant)' => ['shortName'=>'TEST','unit'=>'','color'=>'var(--rust)','total'=>5,'infos'=>[['🧪','Test paiement'],['💳','0.50 €']],'urlParam'=>'test'],
    'Course Enfant' => ['shortName'=>'3','unit'=>'km','color'=>'var(--sky)','total'=>30,'infos'=>[['🕚','Départ à 11h00'],['👦','De 8 à 11 ans'],['👨‍👧','Accompagnement adulte possible']],'urlParam'=>'3km'],
    'Course 7.5km'  => ['shortName'=>'7.5','unit'=>'km','color'=>'var(--lime)','total'=>75,'infos'=>[['🕙','Départ à 10h00'],['🏃','À partir de 12 ans'],['⛰','150 D+']],'urlParam'=>'7.5km'],
    'Course 15km'   => ['shortName'=>'15','unit'=>'km','color'=>'#e07850','total'=>75,'infos'=>[['🕘','Départ à 9h00'],['🏃','À partir de 16 ans'],['🔄','2 boucles · 300 D+']],'urlParam'=>'15km']
];

$error      = null;
$success    = isset($_GET['success']);
$payErr     = isset($_GET['error']);
$showWidget = isset($_GET['widget']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $prenom       = trim($_POST['prenom'] ?? '');
        $nom          = trim($_POST['nom'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $telephone    = trim($_POST['telephone'] ?? '');
        $dateNaissRaw = $_POST['date_naissance'] ?? '';
        $dateNaiss    = $dateNaissRaw ? date('Y-m-d', strtotime($dateNaissRaw)) : null;
        if ($dateNaiss === '1970-01-01') $dateNaiss = null;
        $sexe         = $_POST['sexe'] ?? '';
        $courseLabel  = $_POST['course_label'] ?? '';
        $courseTierId = intval($_POST['course_tier_id'] ?? 0);
        $courseAmount = intval($_POST['course_amount'] ?? 0);

        $payer = ['prenom' => $prenom, 'nom' => $nom, 'email' => $email, 'telephone' => $telephone, 'date_naissance' => $dateNaiss, 'sexe' => $sexe];

        $selectedItems = [];
        $repasData     = [];

        if ($courseTierId) {
            $selectedItems[] = ['tierId' => $courseTierId, 'label' => $courseLabel, 'amount' => $courseAmount];
        }

        foreach ($_POST as $key => $value) {
            if (strpos($key, 'meal_qty_') === 0 && intval($value) > 0) {
                $mealId    = intval(str_replace('meal_qty_', '', $key));
                $mealLabel = $_POST['meal_label_' . $mealId] ?? '';
                $mealPrice = intval($_POST['meal_price_' . $mealId] ?? 0);
                $qty       = intval($value);
                for ($i = 0; $i < $qty; $i++) {
                    $selectedItems[] = ['tierId' => $mealId, 'label' => $mealLabel, 'amount' => $mealPrice];
                }
                $repasData[] = ['label' => $mealLabel, 'qty' => $qty, 'amount' => $mealPrice * $qty];
            }
        }

        $totalCents = array_sum(array_column($selectedItems, 'amount'));
        $statut     = $totalCents <= 0 ? 'free' : 'pending';

        $pdo  = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME') . ';charset=utf8mb4', getenv('DB_USER'), getenv('DB_PASS'));
        $stmt = $pdo->prepare('INSERT INTO inscriptions (prenom, nom, email, telephone, date_naissance, sexe, course, tier_id, repas, total_cents, statut) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$prenom, $nom, $email, $telephone, $dateNaiss, $sexe, $courseLabel, $courseTierId, json_encode($repasData), $totalCents, $statut]);
        $inscriptionId = $pdo->lastInsertId();

        // Toujours passer par le widget HelloAsso
        $_SESSION['inscription'] = [
            'prenom'        => $prenom,
            'nom'           => $nom,
            'email'         => $email,
            'course'        => $courseLabel,
            'tierId'        => $courseTierId,
            'inscriptionId' => $inscriptionId
        ];
        header('Location: /inscription.php?widget=1');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$inscData  = $_SESSION['inscription'] ?? [];
$widgetUrl = 'https://www.helloasso.com/associations/la-vogue-challaisienne/evenements/trail-de-la-vogue-challaisienne-2026/widget';
