<?php
require_once __DIR__ . '/../model/UserModel.php';

class FaceAuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function handleRequest()
    {
        header('Content-Type: application/json');
        session_start();

        $action = $_GET['action'] ?? '';

        try {
            switch ($action) {
                case 'register_face':
                    $this->registerFace();
                    break;
                case 'get_descriptor':
                    $this->getDescriptor();
                    break;
                case 'login_with_face':
                    $this->loginWithFace();
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function registerFace()
    {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('User not logged in');
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['descriptor'])) {
            throw new Exception('No descriptor provided');
        }

        // Save descriptor as JSON string
        $descriptor = json_encode($input['descriptor']);
        $userId = $_SESSION['user_id'];

        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("UPDATE users SET face_descriptor = ? WHERE id = ?");
        $stmt->execute([$descriptor, $userId]);

        echo json_encode(['success' => true, 'message' => 'Visage enregistré avec succès']);
    }

    private function getDescriptor()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';

        if (!$email) {
            throw new Exception('Email required');
        }

        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT id, password_hash, face_descriptor FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['face_descriptor']) {
            echo json_encode([
                'success' => true,
                'descriptor' => json_decode($user['face_descriptor']),
                'user_id' => $user['id'] // Send ID to use in final login step
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Aucune donnée faciale trouvée pour cet utilisateur']);
        }
    }

    private function loginWithFace()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $input['user_id'] ?? null;

        if (!$userId) {
            throw new Exception('User ID required');
        }

        // In a real app, we would verify a signed token from the client or do the matching server-side (python).
        // For this PHP setup, we trust the client's match for the demo.

        $user = $this->userModel->getUserById($userId);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['association_id'] = $user['id']; // Simplification for demo

            echo json_encode(['success' => true, 'redirect' => '../view/dashboard/' . ($user['role'] === 'admin' ? 'admin' : ($user['role'] === 'association' ? 'association' : 'donor')) . '-dashboard.php']);
        } else {
            throw new Exception('Login failed');
        }
    }
}

// Route
if (basename($_SERVER['PHP_SELF']) === 'FaceAuthController.php') {
    require_once __DIR__ . '/../view/config.php'; // Ensure config is loaded
    $controller = new FaceAuthController();
    $controller->handleRequest();
}
?>