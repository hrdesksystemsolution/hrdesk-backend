<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Database connection
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=hrdesk_forbes',
        'root',
        'root',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $action = $_GET['action'] ?? 'getAll';
    
    if ($action === 'getAll') {
        // Get all companies
        $stmt = $pdo->prepare('
            SELECT 
                id,
                name as company_name
            FROM company
            ORDER BY name ASC
        ');
        
        $stmt->execute();
        $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the response
        $formattedCompanies = [];
        foreach ($companies as $company) {
            $formattedCompanies[] = [
                'id' => $company['id'],
                'name' => trim($company['company_name']),
                'company_name' => trim($company['company_name'])
            ];
        }
        
        echo json_encode([
            'success' => true,
            'total' => count($formattedCompanies),
            'data' => $formattedCompanies,
            'message' => 'Company list retrieved successfully'
        ]);
        
    } elseif ($action === 'getById') {
        // Get company by ID
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => 'Company ID is required'
            ]);
            exit();
        }
        
        $stmt = $pdo->prepare('
            SELECT 
                id,
                name as company_name
            FROM company
            WHERE id = :id
        ');
        
        $stmt->execute([':id' => $id]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($company) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $company['id'],
                    'name' => trim($company['company_name']),
                    'company_name' => trim($company['company_name'])
                ],
                'message' => 'Company retrieved successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Company not found'
            ]);
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
