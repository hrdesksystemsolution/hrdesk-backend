<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=hrdesk_forbes',
        'root',
        'root',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // List all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in database:\n";
    print_r($tables);
    
    // Check if company table exists
    if (in_array('company', $tables)) {
        echo "\nCompany table structure:\n";
        $stmt = $pdo->query("DESCRIBE company");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($columns);
        
        echo "\nSample company data:\n";
        $stmt = $pdo->query("SELECT * FROM company LIMIT 5");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($data);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
