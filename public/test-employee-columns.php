<?php
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=hrdesk_forbes',
        'root',
        'root',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Get table columns
    $stmt = $pdo->query('DESCRIBE employee');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    echo "Employee Table Columns:\n";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    echo "</pre>";
    
    // Also show first row
    echo "<h3>Sample Data:</h3>";
    $stmt = $pdo->query('SELECT * FROM employee LIMIT 1');
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($sample);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
