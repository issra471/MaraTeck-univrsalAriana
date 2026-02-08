<?php
require_once __DIR__ . '/../model/EventModel.php';

class EventController
{
    private $model;

    public function __construct()
    {
        $this->model = new EventModel();
    }

    public function index()
    {
        return $this->model->getAllEvents();
    }

    public function create($data)
    {
        // Use ID from data (form) if available, otherwise session, otherwise default (which might check for admin rights later)
        $associationId = $data['association_id'] ?? $_SESSION['association_id'] ?? null;

        if (!$associationId) {
            throw new Exception("Association ID is required");
        }

        return $this->model->createEvent($associationId, $data);
    }

    public function update($id, $data)
    {
        return $this->model->updateEvent($id, $data);
    }

    public function delete($id)
    {
        return $this->model->deleteEvent($id);
    }

    public function getUpcoming($userId)
    {
        return $this->model->getUpcomingEventsForUser($userId);
    }
}
?>