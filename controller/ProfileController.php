<?php
session_start();
require_once __DIR__ . '/../model/UserModel.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$action = $_GET['action'] ?? '';
$userModel = new UserModel();
$userId = $_SESSION['user_id'];

if ($action === 'update') {
    $data = [
        'full_name' => $_POST['full_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'bio' => $_POST['bio'] ?? ''
    ];

    $file = $_FILES['avatar'] ?? null;
    $imageUrl = null;

    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Store as relative path from the 'view' folder
            $imageUrl = 'assets/uploads/profiles/' . $filename;
            $data['profile_image'] = $imageUrl;
        }
    }

    $success = $userModel->updateUserProfile($userId, $data);

    if ($success) {
        $_SESSION['user_name'] = $data['full_name'];
        if (isset($data['profile_image'])) {
            $_SESSION['user_image'] = $data['profile_image'];
        }

        echo json_encode([
            'success' => true,
            'message' => 'Profil mis à jour',
            'image_url' => $data['profile_image'] ?? null
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }
}
?>