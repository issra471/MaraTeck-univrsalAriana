<?php
require_once __DIR__ . '/../view/config.php';

try {
    $pdo = config::getConnexion();
    echo "Checking database schema...\n";

    // 1. Check associations table
    $columns = $pdo->query("DESCRIBE associations")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('registration_number', $columns)) {
        echo "Adding registration_number to associations...\n";
        $pdo->exec("ALTER TABLE associations ADD COLUMN registration_number VARCHAR(100) AFTER address");
    }
    if (!in_array('verified', $columns)) {
        echo "Adding verified to associations...\n";
        $pdo->exec("ALTER TABLE associations ADD COLUMN verified BOOLEAN DEFAULT FALSE AFTER logo_url");
    }
    if (!in_array('logo_url', $columns)) {
        echo "Adding logo_url to associations...\n";
        $pdo->exec("ALTER TABLE associations ADD COLUMN logo_url VARCHAR(255) AFTER website_url");
    }
    if (!in_array('website_url', $columns)) {
        echo "Adding website_url to associations...\n";
        $pdo->exec("ALTER TABLE associations ADD COLUMN website_url VARCHAR(255) AFTER address");
    }

    // 2. Check cases table
    $caseColumns = $pdo->query("DESCRIBE cases")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('deadline', $caseColumns)) {
        echo "Adding deadline to cases...\n";
        $pdo->exec("ALTER TABLE cases ADD COLUMN deadline DATETIME AFTER is_urgent");
    }
    if (!in_array('photos_urls', $caseColumns)) {
        echo "Adding photos_urls to cases...\n";
        $pdo->exec("ALTER TABLE cases ADD COLUMN photos_urls JSON AFTER image_url");
    }

    // Check status column enum values if needed, but difficult to check via describe reliably in all DBs. 
    // Assuming status exists.

    // 3. Check donations table
    $donationColumns = $pdo->query("DESCRIBE donations")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('is_anonymous', $donationColumns)) {
        echo "Adding is_anonymous to donations...\n";
        $pdo->exec("ALTER TABLE donations ADD COLUMN is_anonymous BOOLEAN DEFAULT FALSE AFTER transaction_id");
    }
    if (!in_array('message', $donationColumns)) {
        echo "Adding message to donations...\n";
        $pdo->exec("ALTER TABLE donations ADD COLUMN message TEXT AFTER is_anonymous");
    }

    echo "Schema check completed.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>