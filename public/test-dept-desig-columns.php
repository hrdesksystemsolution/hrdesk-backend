<?php
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=hrdesk_forbes',
        'root',
        'root',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Get department table columns
    echo "<h3>Department Table Columns:</h3>";
    $stmt = $pdo->query('DESCRIBE department');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    echo "</pre>";
    
    // Get designation table columns
    echo "<h3>Designation Table Columns:</h3>";
    $stmt = $pdo->query('DESCRIBE designation');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    echo "</pre>";
    
    // Show sample data
    echo "<h3>Sample Department:</h3>";
    $stmt = $pdo->query('SELECT * FROM department LIMIT 1');
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($sample);
    echo "</pre>";
    
    echo "<h3>Sample Designation:</h3>";
    $stmt = $pdo->query('SELECT * FROM designation LIMIT 1');
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($sample);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
