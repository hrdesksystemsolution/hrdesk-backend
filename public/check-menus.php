<?php

header('Content-Type: application/json');

try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=hrdesk_forbes',
        'root',
        'root'
    );
    
    // Get all menus with their structure
    $stmt = $pdo->query('SELECT mnuno, mnuname, filename, preference, mnu_type, dashboard FROM mnuinfo ORDER BY preference ASC LIMIT 30');
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => count($menus),
        'menus' => $menus
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
