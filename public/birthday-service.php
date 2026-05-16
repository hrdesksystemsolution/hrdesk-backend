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
    
    $action = $_GET['action'] ?? 'getCurrentMonth';
    
    if ($action === 'getCurrentMonth') {
        // Get current month and year
        $currentMonth = date('m');
        $currentYear = date('Y');
        
        // Query to get employees with birthdays in current month
        $stmt = $pdo->prepare('
            SELECT 
                e.id,
                e.empno,
                e.initial,
                e.fname,
                e.mname,
                e.lastname,
                e.birthdate,
                CONCAT(e.initial, " ", e.fname, " ", COALESCE(e.mname, ""), " ", e.lastname) as employee_name,
                CONCAT(e.fname, " ", e.lastname) as display_name,
                COALESCE(d.department, "N/A") as department_name,
                COALESCE(des.designation, "N/A") as designation_name,
                e.profile_image as employee_photo
            FROM employee e
            LEFT JOIN department d ON e.department = d.id
            LEFT JOIN designation des ON e.designation = des.id
            WHERE MONTH(e.birthdate) = :month 
                AND (e.emp_status IS NULL OR e.emp_status = "" OR e.emp_status NOT LIKE "%Left%")
            ORDER BY DAY(e.birthdate) ASC, e.fname ASC
        ');
        
        $stmt->execute([':month' => $currentMonth]);
        $birthdays = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the response
        $formattedBirthdays = [];
        foreach ($birthdays as $emp) {
            $formattedBirthdays[] = [
                'id' => $emp['id'],
                'empno' => $emp['empno'],
                'employee_name' => trim($emp['employee_name']),
                'display_name' => $emp['display_name'],
                'initial' => $emp['initial'],
                'fname' => $emp['fname'],
                'mname' => $emp['mname'],
                'lastname' => $emp['lastname'],
                'birthdate' => $emp['birthdate'],
                'birth_date' => date('d-m', strtotime($emp['birthdate'])),
                'age' => date('Y') - date('Y', strtotime($emp['birthdate'])),
                'department' => $emp['department_name'],
                'designation' => $emp['designation_name'],
                'photo' => $emp['employee_photo']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'month' => $currentMonth,
            'year' => $currentYear,
            'total' => count($formattedBirthdays),
            'data' => $formattedBirthdays,
            'message' => 'Birthday list for ' . date('F Y')
        ]);
        
    } elseif ($action === 'getByMonth') {
        // Get birthdays for specific month
        $month = $_GET['month'] ?? date('m');
        
        $stmt = $pdo->prepare('
            SELECT 
                e.id,
                e.empno,
                e.initial,
                e.fname,
                e.mname,
                e.lastname,
                e.birthdate,
                CONCAT(e.initial, " ", e.fname, " ", COALESCE(e.mname, ""), " ", e.lastname) as employee_name,
                CONCAT(e.fname, " ", e.lastname) as display_name,
                COALESCE(d.department, "N/A") as department_name,
                COALESCE(des.designation, "N/A") as designation_name,
                e.profile_image as employee_photo
            FROM employee e
            LEFT JOIN department d ON e.department = d.id
            LEFT JOIN designation des ON e.designation = des.id
            WHERE MONTH(e.birthdate) = :month 
                AND (e.emp_status IS NULL OR e.emp_status = "" OR e.emp_status NOT LIKE "%Left%")
            ORDER BY DAY(e.birthdate) ASC, e.fname ASC
        ');
        
        $stmt->execute([':month' => $month]);
        $birthdays = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the response
        $formattedBirthdays = [];
        foreach ($birthdays as $emp) {
            $formattedBirthdays[] = [
                'id' => $emp['id'],
                'empno' => $emp['empno'],
                'employee_name' => trim($emp['employee_name']),
                'display_name' => $emp['display_name'],
                'initial' => $emp['initial'],
                'fname' => $emp['fname'],
                'mname' => $emp['mname'],
                'lastname' => $emp['lastname'],
                'birthdate' => $emp['birthdate'],
                'birth_date' => date('d-m', strtotime($emp['birthdate'])),
                'age' => date('Y') - date('Y', strtotime($emp['birthdate'])),
                'department' => $emp['department_name'],
                'designation' => $emp['designation_name'],
                'photo' => $emp['employee_photo']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'month' => $month,
            'total' => count($formattedBirthdays),
            'data' => $formattedBirthdays
        ]);
        
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
        'error' => $e->getMessage()
    ]);
}
?>
