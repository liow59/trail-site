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

    /**
     * Créer un coureur en attente de paiement
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO runners (nom, prenom, email, telephone, date_naissance, course, taille_tshirt, club, ip_address)
            VALUES (:nom, :prenom, :email, :telephone, :date_naissance, :course, :taille_tshirt, :club, :ip_address)
        ");

        $stmt->execute([
            ':nom'             => htmlspecialchars(trim($data['nom'])),
            ':prenom'          => htmlspecialchars(trim($data['prenom'])),
            ':email'           => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
            ':telephone'       => $data['telephone'] ?? null,
            ':date_naissance'  => $data['date_naissance'] ?? null,
            ':course'          => $data['course'],
            ':taille_tshirt'   => $data['taille_tshirt'] ?? null,
            ':club'            => $data['club'] ?? null,
            ':ip_address'      => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Mettre à jour le statut après paiement HelloAsso
     */
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

    /**
     * Récupérer un coureur par ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM runners WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Récupérer un coureur par order HelloAsso
     */
    public function findByOrderId(string $orderId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM runners WHERE helloasso_order_id = :order_id");
        $stmt->execute([':order_id' => $orderId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Liste tous les coureurs (admin)
     */
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

    /**
     * Statistiques pour le dashboard admin
     */
    public function getStats(): array
    {
        $stats = $this->db->query("
            SELECT
                COUNT(*) as total,
                SUM(statut = 'payé') as payes,
                SUM(statut = 'en_attente') as en_attente,
                SUM(statut = 'annulé') as annules,
                SUM(CASE WHEN statut = 'payé' THEN montant ELSE 0 END) as total_encaisse,
                SUM(course = '10km') as total_10km,
                SUM(course = '23km') as total_23km,
                SUM(course = '42km') as total_42km
            FROM runners
        ")->fetch();

        return $stats;
    }

    /**
     * Annuler une inscription
     */
    public function cancel(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE runners SET statut = 'annulé' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Vérifier si email déjà inscrit pour cette course
     */
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
