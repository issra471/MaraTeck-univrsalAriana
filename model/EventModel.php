<?php
require_once __DIR__ . '/../view/config.php';

class EventModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function getAllEvents()
    {
        $stmt = $this->pdo->query("SELECT e.*, a.name as association_name FROM events e LEFT JOIN associations a ON e.association_id = a.id ORDER BY event_date ASC");
        return $stmt->fetchAll();
    }

    public function getEventById($id)
    {
        $stmt = $this->pdo->prepare("SELECT e.*, a.name as association_name FROM events e LEFT JOIN associations a ON e.association_id = a.id WHERE e.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createEvent($associationId, $data)
    {
        $sql = "INSERT INTO events (association_id, title, description, event_date, location, max_attendees) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $associationId,
            $data['title'],
            $data['description'],
            $data['event_date'],
            $data['location'],
            $data['max_attendees']
        ]);
    }

    public function updateEvent($id, $data)
    {
        $sql = "UPDATE events SET title=?, description=?, event_date=?, location=?, max_attendees=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['event_date'],
            $data['location'],
            $data['max_attendees'],
            $id
        ]);
    }

    public function deleteEvent($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM events WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAssociationEvents($associationId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM events WHERE association_id = ? ORDER BY event_date ASC");
        $stmt->execute([$associationId]);
        return $stmt->fetchAll();
    }

    public function getUpcomingEventsForUser($userId)
    {
        $sql = "SELECT e.* FROM events e 
                WHERE e.event_date >= NOW() 
                ORDER BY e.event_date ASC LIMIT 5";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function registerForEvent($userId, $eventId)
    {
        $sql = "INSERT INTO event_attendees (user_id, event_id) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $eventId]);
    }

    public function getEventAttendees($eventId)
    {
        $sql = "SELECT u.* FROM users u JOIN event_attendees ea ON u.id = ea.user_id WHERE ea.event_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }
}
?>