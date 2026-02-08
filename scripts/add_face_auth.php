<?php
require_once __DIR__ . '/../view/config.php';

try {
    $pdo = config::getConnexion();
    echo "Checking users table schema...\n";

    $columns = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('face_descriptor', $columns)) {
        echo "Adding face_descriptor to users...\n";
        // TEXT is sufficient for storing the JSON string of 128 float values
        $pdo->exec("ALTER TABLE users ADD COLUMN face_descriptor TEXT DEFAULT NULL AFTER profile_image");
        echo "Column added successfully.\n";
    } else {
        echo "Column face_descriptor already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>