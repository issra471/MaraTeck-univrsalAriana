<?php
require_once __DIR__ . '/../view/config.php';

class MessageModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function getAllMessages()
    {
        return $this->pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
    }

    public function createMessage($data)
    {
        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data['name'], $data['email'], $data['subject'], $data['message']]);
    }

    public function updateMessageStatus($id, $status)
    {
        $stmt = $this->pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function updateMessage($id, $data)
    {
        // Allow updating content if needed, though rare for logs
        $stmt = $this->pdo->prepare("UPDATE contact_messages SET status = ?, message = ? WHERE id = ?");
        return $stmt->execute([$data['status'], $data['message'], $id]);
    }

    public function deleteMessage($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getMessageById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getLatestContactMessages()
    {
        return $this->pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 10")->fetchAll();
    }
}
?>