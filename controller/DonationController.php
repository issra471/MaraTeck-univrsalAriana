<?php
require_once __DIR__ . '/../model/DonationModel.php';

class DonationController
{
    private $model;

    public function __construct()
    {
        $this->model = new DonationModel();
    }

    public function index()
    {
        return $this->model->getAllDonations();
    }

    public function show($id)
    {
        return $this->model->getDonationById($id);
    }

    public function create($data)
    {
        return $this->model->createDonation($data);
    }

    public function update($id, $data)
    {
        return $this->model->updateDonation($id, $data);
    }

    public function delete($id)
    {
        return $this->model->deleteDonation($id);
    }

    public function getUserHistory($userId)
    {
        return $this->model->getUserDonations($userId);
    }

    public function getImpact($userId)
    {
        return $this->model->calculateDonationImpact($userId);
    }
}
?>