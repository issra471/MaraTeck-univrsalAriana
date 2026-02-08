<?php
require_once __DIR__ . '/../view/config.php';

class VolunteerModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function getAllVolunteers()
    {
        $sql = "SELECT v.*, u.full_name, u.email FROM volunteers v JOIN users u ON v.user_id = u.id";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function registerVolunteer($userId, $skills, $availability)
    {
        $sql = "INSERT INTO volunteers (user_id, skills, availability, status) VALUES (?, ?, ?, 'active')";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $skills, $availability]);
    }

    public function updateVolunteerStatus($id, $status)
    {
        $stmt = $this->pdo->prepare("UPDATE volunteers SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function updateVolunteer($id, $data)
    {
        $stmt = $this->pdo->prepare("UPDATE volunteers SET skills = ?, availability = ?, status = ? WHERE id = ?");
        return $stmt->execute([$data['skills'], $data['availability'], $data['status'], $id]);
    }

    public function deleteVolunteer($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM volunteers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getVolunteerById($id)
    {
        $sql = "SELECT v.*, u.full_name, u.email FROM volunteers v JOIN users u ON v.user_id = u.id WHERE v.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>