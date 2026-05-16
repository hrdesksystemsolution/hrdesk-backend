<?php

try {
    // Test database connection
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=hrdesk_forbes',
        'root',
        'root',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Query the mnuinfo table - just like the MenuController does
    $stmt = $pdo->prepare('SELECT * FROM mnuinfo WHERE dashboard = 1 ORDER BY preference ASC LIMIT 20');
    $stmt->execute();
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Also show column names
    $tableCheck = $pdo->prepare('DESCRIBE mnuinfo');
    $tableCheck->execute();
    $columns = $tableCheck->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Database connection successful',
        'table_columns' => array_map(function($col) { return $col['Field']; }, $columns),
        'menus_count' => count($menus),
        'menus_sample' => $menus
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
} catch (\Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
