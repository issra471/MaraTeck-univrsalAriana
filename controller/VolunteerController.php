<?php
require_once __DIR__ . '/../model/VolunteerModel.php';

class VolunteerController
{
    private $model;

    public function __construct()
    {
        $this->model = new VolunteerModel();
    }

    public function index()
    {
        return $this->model->getAllVolunteers();
    }

    public function show($id)
    {
        return $this->model->getVolunteerById($id);
    }

    public function create($data)
    {
        // $data usually comes from $_POST, containing user_id, skills, availability
        // Ensure user_id is present, maybe from session if not in data
        if (!isset($data['user_id']) && isset($_SESSION['user_id'])) {
            $data['user_id'] = $_SESSION['user_id'];
        }
        return $this->model->registerVolunteer($data['user_id'], $data['skills'], $data['availability']);
    }

    public function update($id, $data)
    {
        return $this->model->updateVolunteer($id, $data);
    }

    public function delete($id)
    {
        return $this->model->deleteVolunteer($id);
    }
}
?>