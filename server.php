<?php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// CORS headers for all responses
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE,  OPTIONS, PATCH');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit('');
}

// Test endpoint before Laravel bootstrap
if ($uri === '/api/test') {
    http_response_code(200);
    echo json_encode(['message' => 'API is working!', 'timestamp' => date('Y-m-d H:i:s')]);
    exit;
}

// Login endpoint - direct PHP implementation
if ($uri === '/api/auth/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
            exit;
        }
        
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        if (!$username || !$password) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Username and password required']);
            exit;
        }

        // Simple database connection without Laravel ORM
        $pdo = new PDO(
            'mysql:host=127.0.0.1;port=3306;dbname=hrdesk_forbes;charset=utf8mb4',
            'root',
            'root',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
            exit;
        }

        // Verify password - check if password column exists and handle both hashed and plain passwords
        $passwordMatch = false;
        if (isset($user['password'])) {
            // Try password_verify first (for hashed passwords)
            if (password_verify($password, $user['password'])) {
                $passwordMatch = true;
            } elseif ($user['password'] === $password) {
                // Fall back to plain text comparison for backwards compatibility
                $passwordMatch = true;
            }
        }

        if (!$passwordMatch) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
            exit;
        }

        // Generate a simple JWT token
        $secret = 'AIiuDxACcUhzdXmg+wG5VG4oj9uX6s03w7boqKxSP0g=';
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode([
            'sub' => $user['id'],
            'iat' => time(),
            'exp' => time() + (60 * 60),  // 1 hour
            'data' => [
                'username' => $user['username'] ?? '',
                'id' => $user['id'] ?? 0
            ]
        ]);

        $headerEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $payloadEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true)));

        $token = "$headerEncoded.$payloadEncoded.$signature";

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'] ?? 0,
                'username' => $user['username'] ?? '',
                'emailid' => $user['emailid'] ?? '',
                'fullname' => $user['fullname'] ?? '',
                'mnuaccess' => $user['mnuaccess'] ?? '',
                'dashboard_access' => $user['dashboard_access'] ?? '',
            ]
        ]);
        exit;
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

try {
    require_once __DIR__.'/public/index.php';
} catch (\Throwable $e) {
    // Log the error
    error_log("Laravel Error: " . $e->getMessage());
    error_log($e->getTraceAsString());
    
    // Return JSON error response
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'type' => get_class($e),
    ]);
}
