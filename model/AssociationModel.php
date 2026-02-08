<?php
require_once __DIR__ . '/../view/config.php';

class AssociationModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function getAllAssociations()
    {
        $stmt = $this->pdo->query("SELECT * FROM associations ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getAssociationById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM associations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAssociationByUserId($userId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM associations WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function createAssociation($data)
    {
        $sql = "INSERT INTO associations (user_id, name, description, email, phone, address, website_url, logo_url, verified, registration_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['user_id'],
            $data['name'],
            $data['description'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['website_url'],
            $data['logo_url'],
            1,
            $data['registration_number'] ?? null
        ]);
    }

    public function updateAssociation($id, $data)
    {
        $sql = "UPDATE associations SET name=?, description=?, email=?, phone=?, address=?, website_url=?, logo_url=?, registration_number=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['website_url'],
            $data['logo_url'],
            $data['registration_number'] ?? null,
            $id
        ]);
    }

    public function deleteAssociation($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM associations WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function verifyAssociation($id)
    {
        $stmt = $this->pdo->prepare("UPDATE associations SET verified = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAssociationStats($associationId)
    {
        $stats = ['total_cases' => 0, 'active_cases' => 0, 'resolved_cases' => 0, 'total_raised' => 0, 'total_views' => 0, 'total_donors' => 0];

        $stats['total_cases'] = $this->pdo->query("SELECT COUNT(*) FROM cases WHERE association_id = $associationId")->fetchColumn() ?: 0;
        $stats['active_cases'] = $this->pdo->query("SELECT COUNT(*) FROM cases WHERE association_id = $associationId AND status = 'active'")->fetchColumn() ?: 0;
        $stats['resolved_cases'] = $this->pdo->query("SELECT COUNT(*) FROM cases WHERE association_id = $associationId AND status = 'resolved'")->fetchColumn() ?: 0;
        $stats['total_raised'] = $this->pdo->query("SELECT SUM(progress_amount) FROM cases WHERE association_id = $associationId")->fetchColumn() ?: 0;
        $stats['total_views'] = $this->pdo->query("SELECT SUM(views) FROM cases WHERE association_id = $associationId")->fetchColumn() ?: 0;

        $sql = "SELECT COUNT(DISTINCT d.user_id) FROM donations d JOIN cases c ON d.case_id = c.id WHERE c.association_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$associationId]);
        $stats['total_donors'] = $stmt->fetchColumn() ?: 0;

        return $stats;
    }
}
?>