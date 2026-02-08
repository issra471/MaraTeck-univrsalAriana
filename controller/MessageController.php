<?php
require_once __DIR__ . '/../model/MessageModel.php';

class MessageController
{
    private $model;

    public function __construct()
    {
        $this->model = new MessageModel();
    }

    public function index()
    {
        return $this->model->getAllMessages();
    }

    public function show($id)
    {
        return $this->model->getMessageById($id);
    }

    public function create($data)
    {
        return $this->model->createMessage($data);
    }

    public function update($id, $data)
    {
        return $this->model->updateMessage($id, $data);
    }

    public function delete($id)
    {
        return $this->model->deleteMessage($id);
    }
}
?>