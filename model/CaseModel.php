<?php
require_once __DIR__ . '/../view/config.php';

class CaseModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function getAllCases()
    {
        $sql = "SELECT c.*, a.name as association_name FROM cases c 
                LEFT JOIN associations a ON c.association_id = a.id 
                ORDER BY c.created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getCaseById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM cases WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createCase($data)
    {
        $sql = "INSERT INTO cases (association_id, title, description, category, goal_amount, image_url, beneficiary_name, beneficiary_story, status, cha9a9a_link, is_urgent, deadline) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['association_id'],
            $data['title'],
            $data['description'],
            $data['category'],
            $data['goal_amount'],
            $data['image_url'] ?? null,
            $data['beneficiary_name'] ?? null,
            $data['beneficiary_story'] ?? null,
            'active',
            $data['cha9a9a_link'] ?? null,
            $data['is_urgent'] ?? 0,
            $data['deadline'] ?? null
        ]);
    }

    public function updateCase($id, $data)
    {
        $sql = "UPDATE cases SET title=?, description=?, category=?, goal_amount=?, image_url=?, beneficiary_name=?, beneficiary_story=?, status=?, cha9a9a_link=?, is_urgent=?, deadline=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['category'],
            $data['goal_amount'],
            $data['image_url'] ?? null,
            $data['beneficiary_name'] ?? null,
            $data['beneficiary_story'] ?? null,
            $data['status'] ?? 'pending',
            $data['cha9a9a_link'] ?? null,
            $data['is_urgent'] ?? 0,
            $data['deadline'] ?? null,
            $id
        ]);
    }

    public function incrementViews($id)
    {
        $stmt = $this->pdo->prepare("UPDATE cases SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteCase($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM cases WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAssociationCases($associationId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM cases WHERE association_id = ? ORDER BY created_at DESC");
        $stmt->execute([$associationId]);
        return $stmt->fetchAll();
    }

    public function getRecentCases()
    {
        $stmt = $this->pdo->query("SELECT * FROM cases ORDER BY created_at DESC LIMIT 10");
        return $stmt->fetchAll();
    }

    public function getCasesByCategory()
    {
        $stmt = $this->pdo->query("SELECT category, COUNT(*) as count, SUM(progress_amount) as raised FROM cases GROUP BY category");
        return $stmt->fetchAll();
    }

    public function getSavedCases($userId)
    {
        $sql = "SELECT c.* FROM cases c JOIN saved_cases s ON c.id = s.case_id WHERE s.user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function toggleSavedCase($userId, $caseId)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM saved_cases WHERE user_id = ? AND case_id = ?");
        $stmt->execute([$userId, $caseId]);
        if ($stmt->fetch()) {
            $stmt = $this->pdo->prepare("DELETE FROM saved_cases WHERE user_id = ? AND case_id = ?");
            return $stmt->execute([$userId, $caseId]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO saved_cases (user_id, case_id) VALUES (?, ?)");
            return $stmt->execute([$userId, $caseId]);
        }
    }
}
?>