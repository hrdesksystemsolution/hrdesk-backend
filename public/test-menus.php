<?php

try {
    // Test database connection with correct credentials
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=hrdesk_forbes',
        'root',
        'root',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Query the mnuinfo table
    $stmt = $pdo->prepare('SELECT * FROM mnuinfo LIMIT 10');
    $stmt->execute();
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Database connection successful',
        'table_exists' => true,
        'menus_count' => count($menus),
        'menus_sample' => $menus
    ], JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'table_exists' => false
    ], JSON_PRETTY_PRINT);
}
