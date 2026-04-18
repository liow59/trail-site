<?php

declare(strict_types=1);

namespace Trail\Src;

class Runner
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO runners (nom, prenom, email, telephone, date_naissance, course, club, repas_poulet, repas_saucisse, repas_nuggets, total_repas, ip_address)
            VALUES (:nom, :prenom, :email, :telephone, :date_naissance, :course, :club, :repas_poulet, :repas_saucisse, :repas_nuggets, :total_repas, :ip_address)
        ");

        $stmt->execute([
            ':nom'             => htmlspecialchars(trim($data['nom'])),
            ':prenom'          => htmlspecialchars(trim($data['prenom'])),
            ':email'           => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
            ':telephone'       => $data['telephone'] ?? null,
            ':date_naissance'  => $data['date_naissance'] ?? null,
            ':course'          => $data['course'],
            ':club'            => $data['club'] ?? null,
            ':repas_poulet'    => (int)($data['repas_poulet'] ?? 0),
            ':repas_saucisse'  => (int)($data['repas_saucisse'] ?? 0),
            ':repas_nuggets'   => (int)($data['repas_nuggets'] ?? 0),
            ':total_repas'     => (float)($data['total_repas'] ?? 0),
            ':ip_address'      => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updatePayment(int $id, string $orderId, float $montant, string $statut = 'payé'): bool
    {
        $stmt = $this->db->prepare("
            UPDATE runners
            SET statut = :statut, helloasso_order_id = :order_id, montant = :montant
            WHERE id = :id
        ");

        return $stmt->execute([
            ':statut'   => $statut,
            ':order_id' => $orderId,
            ':montant'  => $montant,
            ':id'       => $id,
        ]);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM runners WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findByOrderId(string $orderId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM runners WHERE helloasso_order_id = :order_id");
        $stmt->execute([':order_id' => $orderId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getAll(string $course = '', string $statut = ''): array
    {
        $where = [];
        $params = [];

        if ($course) {
            $where[] = "course = :course";
            $params[':course'] = $course;
        }
        if ($statut) {
            $where[] = "statut = :statut";
            $params[':statut'] = $statut;
        }

        $sql = "SELECT * FROM runners";
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getStats(): array
    {
        $stats = $this->db->query("
            SELECT
                COUNT(*) as total,
                SUM(statut = 'payé') as payes,
                SUM(statut = 'en_attente') as en_attente,
                SUM(statut = 'annulé') as annules,
                SUM(CASE WHEN statut = 'payé' THEN montant ELSE 0 END) as total_encaisse,
                SUM(course = '3km') as total_3km,
                SUM(course = '7.5km') as total_7_5km,
                SUM(course = '15km') as total_15km,
                SUM(CASE WHEN statut = 'payé' THEN total_repas ELSE 0 END) as total_repas_encaisse,
                SUM(CASE WHEN statut = 'payé' THEN repas_poulet ELSE 0 END) as nb_poulet,
                SUM(CASE WHEN statut = 'payé' THEN repas_saucisse ELSE 0 END) as nb_saucisse,
                SUM(CASE WHEN statut = 'payé' THEN repas_nuggets ELSE 0 END) as nb_nuggets
            FROM runners
        ")->fetch();

        return $stats;
    }

    public function cancel(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE runners SET statut = 'annulé' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function emailAlreadyRegistered(string $email, string $course): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM runners
            WHERE email = :email AND course = :course AND statut != 'annulé'
        ");
        $stmt->execute([':email' => $email, ':course' => $course]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
