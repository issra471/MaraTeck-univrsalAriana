<?php
require_once __DIR__ . '/../view/config.php';

class UserModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function authenticate($email, $password)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    public function getAllUsers()
    {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getUserById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getUserByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function createUser($data)
    {
        $sql = "INSERT INTO users (full_name, email, password_hash, role, phone, address, bio, profile_image, face_descriptor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        try {
            return $stmt->execute([
                $data['full_name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'] ?? 'user',
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['bio'] ?? null,
                $data['profile_image'] ?? null,
                $data['face_descriptor'] ?? null
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new Exception("Cet email est déjà utilisé.");
            }
            throw $e;
        }
    }

    public function updateUser($id, $data)
    {
        $sql = "UPDATE users SET full_name=?, email=?, role=?, phone=?, address=?, bio=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        try {
            return $stmt->execute([
                $data['full_name'],
                $data['email'],
                $data['role'],
                $data['phone'],
                $data['address'],
                $data['bio'],
                $id
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new Exception("Cet email est déjà utilisé par un autre utilisateur.");
            }
            throw $e;
        }
    }

    public function deleteUser($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateUserProfile($userId, $data)
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $val) {
            $fields[] = "$key = ?";
            $params[] = $val;
        }

        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}
?>