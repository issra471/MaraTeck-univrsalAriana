<?php
require_once __DIR__ . '/../view/config.php';

class DonationModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function getAllDonations()
    {
        $sql = "SELECT d.*, u.full_name, c.title as case_title FROM donations d 
                LEFT JOIN users u ON d.user_id = u.id 
                LEFT JOIN cases c ON d.case_id = c.id 
                ORDER BY d.created_at DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function createDonation($data)
    {
        $sql = "INSERT INTO donations (case_id, user_id, amount, payment_method, status, transaction_id, is_anonymous, message) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['case_id'],
            $data['user_id'],
            $data['amount'],
            $data['payment_method'],
            'completed',
            $data['transaction_id'],
            $data['is_anonymous'] ?? 0, // New
            $data['message'] ?? null // New
        ]);
    }

    public function updateDonation($id, $data)
    {
        $sql = "UPDATE donations SET status=?, amount=?, payment_method=?, message=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['status'],
            $data['amount'],
            $data['payment_method'],
            $data['message'] ?? null,
            $id
        ]);
    }

    public function deleteDonation($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM donations WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getDonationById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM donations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getUserDonations($userId)
    {
        $stmt = $this->pdo->prepare("SELECT d.*, c.title as case_title FROM donations d LEFT JOIN cases c ON d.case_id = c.id WHERE d.user_id = ? ORDER BY d.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getRecentDonations()
    {
        $sql = "SELECT d.*, u.full_name, c.title as case_title FROM donations d 
                LEFT JOIN users u ON d.user_id = u.id 
                LEFT JOIN cases c ON d.case_id = c.id 
                ORDER BY d.created_at DESC LIMIT 15";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function calculateDonationImpact($userId)
    {
        $impact = ['total_donated' => 0, 'total_donations' => 0, 'cases_supported' => 0];
        $impact['total_donated'] = $this->pdo->query("SELECT SUM(amount) FROM donations WHERE user_id = $userId")->fetchColumn() ?: 0;
        $impact['total_donations'] = $this->pdo->query("SELECT COUNT(*) FROM donations WHERE user_id = $userId")->fetchColumn() ?: 0;
        $impact['cases_supported'] = $this->pdo->query("SELECT COUNT(DISTINCT case_id) FROM donations WHERE user_id = $userId")->fetchColumn() ?: 0;
        return $impact;
    }

    public function getAssociationDonations($associationId)
    {
        $sql = "SELECT d.*, u.full_name, c.title as case_title 
                FROM donations d 
                JOIN cases c ON d.case_id = c.id 
                LEFT JOIN users u ON d.user_id = u.id 
                WHERE c.association_id = ? 
                ORDER BY d.created_at DESC LIMIT 20";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$associationId]);
        return $stmt->fetchAll();
    }
}
?>