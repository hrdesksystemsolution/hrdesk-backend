<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=hrdesk_forbes', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query menus
    $stmt = $pdo->query('SELECT mnuno, mnuname, filename, preference, mnu_type, dashboard FROM mnuinfo ORDER BY mnuno LIMIT 30');
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count by range
    $stmt2 = $pdo->query('SELECT 
        SUM(CASE WHEN mnuno BETWEEN 1 AND 99 THEN 1 ELSE 0 END) as system,
        SUM(CASE WHEN mnuno BETWEEN 100 AND 200 THEN 1 ELSE 0 END) as master,
        SUM(CASE WHEN mnuno BETWEEN 201 AND 300 THEN 1 ELSE 0 END) as time_office,
        SUM(CASE WHEN mnuno BETWEEN 301 AND 400 THEN 1 ELSE 0 END) as attendance_leave,
        SUM(CASE WHEN mnuno BETWEEN 401 AND 500 THEN 1 ELSE 0 END) as salary_wages,
        SUM(CASE WHEN mnuno BETWEEN 501 AND 600 THEN 1 ELSE 0 END) as payroll,
        SUM(CASE WHEN mnuno BETWEEN 601 AND 700 THEN 1 ELSE 0 END) as income_tax,
        SUM(CASE WHEN mnuno BETWEEN 701 AND 800 THEN 1 ELSE 0 END) as visitor
    FROM mnuinfo');
    $counts = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    // Query a test user
    $stmt3 = $pdo->query('SELECT id, username, fullname, mnuaccess FROM users LIMIT 1');
    $testUser = $stmt3->fetch(PDO::FETCH_ASSOC);
    
    echo "=== MENU DATA ===\n";
    echo json_encode($menus, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== MENU COUNTS BY RANGE ===\n";
    echo json_encode($counts, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== TEST USER ===\n";
    echo json_encode($testUser, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString();
}
?>
