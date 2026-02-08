<?php
require_once __DIR__ . '/../model/UserModel.php';

class UserController
{
    private $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function login($email, $password)
    {
        return $this->model->authenticate($email, $password);
    }

    public function index()
    {
        return $this->model->getAllUsers();
    }

    public function show($id)
    {
        return $this->model->getUserById($id);
    }

    public function create($data)
    {
        return $this->model->createUser($data);
    }

    public function update($id, $data)
    {
        return $this->model->updateUser($id, $data);
    }

    public function delete($id)
    {
        return $this->model->deleteUser($id);
    }

    public function updateProfile($userId, $data)
    {
        return $this->model->updateUserProfile($userId, $data);
    }
}
?>