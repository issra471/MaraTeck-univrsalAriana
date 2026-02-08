<?php
require_once __DIR__ . '/../model/CaseModel.php';

class CaseController
{
    private $model;

    public function __construct()
    {
        $this->model = new CaseModel();
    }

    public function index()
    {
        return $this->model->getAllCases();
    }

    public function show($id)
    {
        return $this->model->getCaseById($id);
    }

    public function create($data)
    {
        // Add logic for association ID if missing in data but present in session
        if (!isset($data['association_id']) && isset($_SESSION['association_id'])) {
            $data['association_id'] = $_SESSION['association_id'];
        }
        return $this->model->createCase($data);
    }

    public function update($id, $data)
    {
        return $this->model->updateCase($id, $data);
    }

    public function delete($id)
    {
        return $this->model->deleteCase($id);
    }

    public function getRecent()
    {
        return $this->model->getRecentCases();
    }

    public function toggleSave($userId, $caseId)
    {
        return $this->model->toggleSavedCase($userId, $caseId);
    }
}
?>