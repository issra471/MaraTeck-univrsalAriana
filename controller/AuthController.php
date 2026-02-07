<?php
/**
 * AuthController.php
 * Handles user authentication (Login/Logout/Register)
 */
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/AssociationModel.php';

session_start();

class AuthController
{
    private $userModel;
    private $assocModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->assocModel = new AssociationModel();
    }

    public function handleRequest()
    {
        $action = $_GET['action'] ?? 'login';

        switch ($action) {
            case 'login':
                $this->login();
                break;
            case 'register':
                $this->register();
                break;
            case 'logout':
                $this->logout();
                break;
            default:
                header("Location: ../view/FrontOffice/index.html");
                break;
        }
    }

    private function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'donor'; // Role passed from tab selection

            $user = $this->userModel->authenticate($email, $password);

            if ($user) {
                // Set core session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_image'] = $user['profile_image'];

                // Special handling for associations
                if ($user['role'] === 'association') {
                    $assoc = $this->assocModel->getAssociationByUserId($user['id']);
                    if ($assoc) {
                        $_SESSION['association_id'] = $assoc['id'];
                    }
                }

                // Response for AJAX or Redirect
                if ($this->isAjax()) {
                    echo json_encode(['success' => true, 'message' => 'Connexion réussie', 'role' => $user['role']]);
                } else {
                    $this->redirectByRole($user['role']);
                }
            } else {
                if ($this->isAjax()) {
                    echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
                } else {
                    header("Location: ../view/FrontOffice/index.html?error=invalid_credentials");
                }
            }
        }
    }

    private function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['full_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            $phone = $_POST['phone'] ?? null;
            $address = $_POST['address'] ?? null;
            $role = $_POST['role'] ?? 'donor';

            // Validation
            if (empty($fullName) || empty($email) || empty($password)) {
                if ($this->isAjax()) {
                    echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
                }
                return;
            }

            if ($password !== $passwordConfirm) {
                if ($this->isAjax()) {
                    echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas']);
                }
                return;
            }

            if (strlen($password) < 6) {
                if ($this->isAjax()) {
                    echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères']);
                }
                return;
            }

            // Check if email already exists
            try {
                $existingUser = $this->userModel->getUserByEmail($email);
                if ($existingUser) {
                    if ($this->isAjax()) {
                        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
                    }
                    return;
                }
            } catch (Exception $e) {
                // Email doesn't exist, continue
            }

            // Create user
            $userData = [
                'full_name' => $fullName,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'phone' => $phone,
                'address' => $address,
                'bio' => null,
                'profile_image' => null,
                'face_descriptor' => $_POST['face_descriptor'] ?? null
            ];

            try {
                $userCreated = $this->userModel->createUser($userData);

                if ($userCreated) {
                    // Get the newly created user
                    $newUser = $this->userModel->getUserByEmail($email);

                    // If role is association, create association record
                    if ($role === 'association' && $newUser) {
                        $associationName = $_POST['association_name'] ?? $fullName;
                        $description = $_POST['description'] ?? '';
                        $websiteUrl = $_POST['website_url'] ?? null;

                        $associationData = [
                            'user_id' => $newUser['id'],
                            'name' => $associationName,
                            'description' => $description,
                            'email' => $email,
                            'phone' => $phone,
                            'address' => $address,
                            'website_url' => $websiteUrl,
                            'logo_url' => null,
                            'verified' => 0
                        ];

                        $this->assocModel->createAssociation($associationData);
                    }

                    // Auto-login after registration
                    $_SESSION['user_id'] = $newUser['id'];
                    $_SESSION['user_name'] = $newUser['full_name'];
                    $_SESSION['user_role'] = $newUser['role'];

                    if ($newUser['role'] === 'association') {
                        $assoc = $this->assocModel->getAssociationByUserId($newUser['id']);
                        if ($assoc) {
                            $_SESSION['association_id'] = $assoc['id'];
                        }
                    }

                    if ($this->isAjax()) {
                        echo json_encode(['success' => true, 'message' => 'Inscription réussie', 'role' => $newUser['role']]);
                    } else {
                        $this->redirectByRole($newUser['role']);
                    }
                } else {
                    if ($this->isAjax()) {
                        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du compte']);
                    }
                }
            } catch (Exception $e) {
                if ($this->isAjax()) {
                    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
                }
            }
        }
    }

    private function logout()
    {
        session_destroy();
        header("Location: ../view/FrontOffice/index.html");
    }

    private function redirectByRole($role)
    {
        switch ($role) {
            case 'admin':
            case 'moderator':
                header("Location: ../view/dashboard/admin-dashboard.php");
                break;
            case 'association':
                header("Location: ../view/FrontOffice/index.php");
                break;
            default:
                header("Location: ../view/FrontOffice/index.php?status=logged_in");
                break;
        }
    }

    private function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}

$auth = new AuthController();
$auth->handleRequest();
