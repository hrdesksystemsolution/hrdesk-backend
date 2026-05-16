<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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
    
    $action = $_GET['action'] ?? 'getMenus';
    
    if ($action === 'getMenus') {
        // Get all menus
        $stmt = $pdo->prepare('
            SELECT mnuno, mnuname, filename, preference, mnu_type, dashboard
            FROM mnuinfo 
            WHERE dashboard = 1 
            ORDER BY preference ASC, mnuname ASC
        ');
        $stmt->execute();
        $allMenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Define category keywords for intelligent grouping
        $categoryKeywords = [
            'Visitor' => ['visitor', 'gatepass', 'outpass'],
            'Attendance/Leave' => ['attendance', 'leave', 'punch', 'sanction', 'lock', 'coff'],
            'Payroll/Salary' => ['payroll', 'salary', 'wages', 'salary calulation', 'calculation'],
            'Income Tax' => ['income', 'income tax', 'tax', 'tds', 'exemption', 'allowance exemption'],
            'Time Office' => ['time detail', 'monthly status', 'late coming', 'absent',],
            'Masters' => ['shift', 'company', 'category', 'section', 'department', 'designation', 'employee', 'holiday', 'bank', 'grade', 'level', 'allowance', 'deduction'],
            'Reports' => ['report', 'summary', 'register', 'cost', 'location'],
            'Settings' => ['setting', 'menu setting', 'process', 'register imei', 'hotel', 'upload'],
        ];
        
        // Group menus intelligently
        $groupedMenus = [];
        $used = [];
        $categoryGroups = [];
        
        // Initialize category groups
        foreach ($categoryKeywords as $categoryName => $keywords) {
            $categoryGroups[$categoryName] = [
                'mnuno' => 'cat_' . strtolower(str_replace('/', '_', $categoryName)),
                'mnuname' => $categoryName,
                'filename' => '',
                'preference' => -1,
                'mnu_type' => 'CATEGORY',
                'dashboard' => 1,
                'submenus' => [],
                'isCategory' => true
            ];
        }
        
        // First pass: Try to match menus to existing parent-child relationships
        foreach ($allMenus as $menu) {
            if (isset($used[$menu['mnuno']])) continue;
            
            $parentMenu = [
                'mnuno' => $menu['mnuno'],
                'mnuname' => $menu['mnuname'],
                'filename' => $menu['filename'],
                'preference' => $menu['preference'],
                'mnu_type' => $menu['mnu_type'],
                'dashboard' => $menu['dashboard'],
                'submenus' => []
            ];
            
            // Check if this menu has related submenu items
            $menuNameBase = strtolower(trim($menu['mnuname']));
            $hasChildren = false;
            
            if (strlen($menuNameBase) > 3) {
                foreach ($allMenus as $potential_child) {
                    if ($potential_child['mnuno'] == $menu['mnuno'] || isset($used[$potential_child['mnuno']])) {
                        continue;
                    }
                    
                    $childNameLower = strtolower(trim($potential_child['mnuname']));
                    
                    // Detect parent-child by name matching
                    if (strpos($childNameLower, $menuNameBase) !== false) {
                        $parentMenu['submenus'][] = [
                            'mnuno' => $potential_child['mnuno'],
                            'mnuname' => $potential_child['mnuname'],
                            'filename' => $potential_child['filename']
                        ];
                        $used[$potential_child['mnuno']] = true;
                        $hasChildren = true;
                    }
                }
            }
            
            // If this menu doesn't fit a direct parent-child relationship, 
            // it might be categorized in category grouping (second pass)
            if (!$hasChildren) {
                // Don't add yet - we'll categorize it in pass 2
                continue;
            }
            
            $groupedMenus[] = $parentMenu;
            $used[$menu['mnuno']] = true;
        }
        
        // Second pass: Group remaining menus by category keywords
        foreach ($allMenus as $menu) {
            if (!isset($used[$menu['mnuno']])) {
                $menuNameLower = strtolower($menu['mnuname']);
                $assigned = false;
                
                // Match menu to category
                foreach ($categoryKeywords as $categoryName => $keywords) {
                    foreach ($keywords as $keyword) {
                        if (strpos($menuNameLower, strtolower($keyword)) !== false) {
                            $categoryGroups[$categoryName]['submenus'][] = [
                                'mnuno' => $menu['mnuno'],
                                'mnuname' => $menu['mnuname'],
                                'filename' => $menu['filename']
                            ];
                            $used[$menu['mnuno']] = true;
                            $assigned = true;
                            break 2; // Break out of both loops
                        }
                    }
                }
                
                // If no category matched, add to Miscellaneous
                if (!$assigned) {
                    if (!isset($categoryGroups['System'])) {
                        $categoryGroups['System'] = [
                            'mnuno' => 'cat_system',
                            'mnuname' => 'System',
                            'filename' => '',
                            'preference' => 999,
                            'mnu_type' => 'CATEGORY',
                            'dashboard' => 1,
                            'submenus' => [],
                            'isCategory' => true
                        ];
                    }
                    $categoryGroups['System']['submenus'][] = [
                        'mnuno' => $menu['mnuno'],
                        'mnuname' => $menu['mnuname'],
                        'filename' => $menu['filename']
                    ];
                    $used[$menu['mnuno']] = true;
                }
            }
        }
        
        // Add explicit parent menus first
        $allGroupedMenus = array_merge($groupedMenus, array_filter($categoryGroups, function($cat) {
            return !empty($cat['submenus']);
        }));
        
        // Sort by preference, then by name
        usort($allGroupedMenus, function($a, $b) {
            $prefDiff = ($a['preference'] ?? 999) - ($b['preference'] ?? 999);
            if ($prefDiff != 0) return $prefDiff;
            return strcmp($a['mnuname'], $b['mnuname']);
        });
        
        echo json_encode([
            'success' => true,
            'data' => array_values($allGroupedMenus),
            'total' => count($groupedMenus)
        ], JSON_UNESCAPED_SLASHES);
        
    } elseif ($action === 'search') {
        $searchTerm = $_GET['search'] ?? '';
        
        if (empty($searchTerm)) {
            echo json_encode(['success' => true, 'data' => []]);
            exit();
        }
        
        $stmt = $pdo->prepare('
            SELECT * FROM mnuinfo 
            WHERE mnuname LIKE ? AND dashboard = 1 
            ORDER BY preference ASC
        ');
        $stmt->execute(["%{$searchTerm}%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $results
        ], JSON_UNESCAPED_SLASHES);
    }
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
