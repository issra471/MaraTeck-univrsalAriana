<?php
require_once __DIR__ . '/../model/AssociationModel.php';

class AssociationController
{
    private $model;

    public function __construct()
    {
        $this->model = new AssociationModel();
    }

    public function index()
    {
        return $this->model->getAllAssociations();
    }

    public function show($id)
    {
        return $this->model->getAssociationById($id);
    }

    public function create($data)
    {
        return $this->model->createAssociation($data);
    }

    public function update($id, $data)
    {
        return $this->model->updateAssociation($id, $data);
    }

    public function delete($id)
    {
        return $this->model->deleteAssociation($id);
    }

    public function verify($id)
    {
        return $this->model->verifyAssociation($id);
    }

    public function getStats($id)
    {
        return $this->model->getAssociationStats($id);
    }
}
?>