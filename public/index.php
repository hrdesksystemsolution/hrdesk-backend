<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Parse .env file manually (getenv() doesn't read .env files)
$env = array();
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $env[trim($key)] = trim($val);
        }
    }
}

$db_host    = isset($env['DB_HOST'])     ? $env['DB_HOST']     : '127.0.0.1';
$db_name    = isset($env['DB_DATABASE']) ? $env['DB_DATABASE'] : 'hrdesk1_eeipl';
$db_user    = isset($env['DB_USERNAME']) ? $env['DB_USERNAME'] : 'hrdesk1_user1';
$db_pass    = isset($env['DB_PASSWORD']) ? $env['DB_PASSWORD'] : '';
$jwt_secret = isset($env['JWT_SECRET'])  ? $env['JWT_SECRET']  : 'fallback_secret_key';

function getDB($host, $name, $user, $pass) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Exception $e) {
        jsonResponse(array('success' => false, 'message' => 'DB error: ' . $e->getMessage()), 500);
    }
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

function generateToken($user, $secret) {
    $header  = rtrim(strtr(base64_encode(json_encode(array('typ'=>'JWT','alg'=>'HS256'))), '+/', '-_'), '=');
    $payload = rtrim(strtr(base64_encode(json_encode(array(
        'sub'      => $user['id'],
        'username' => $user['username'],
        'iat'      => time(),
        'exp'      => time() + 86400
    ))), '+/', '-_'), '=');
    $sig = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true)), '+/', '-_'), '=');
    return "$header.$payload.$sig";
}

function verifyToken($token, $secret) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    $sig = rtrim(strtr(base64_encode(hash_hmac('sha256', "$parts[0].$parts[1]", $secret, true)), '+/', '-_'), '=');
    if ($sig !== $parts[2]) return false;
    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
    if (!$payload || $payload['exp'] < time()) return false;
    return $payload;
}

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = str_replace('/hrdesk_new/service', '', $uri);
$method = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true);
if (!$body) $body = array();

// Test route
if ($uri === '/api/test' && $method === 'GET') {
    jsonResponse(array('success' => true, 'message' => 'API working!', 'php' => PHP_VERSION));
}

// Login
if ($uri === '/api/auth/login' && $method === 'POST') {
    $username = isset($body['username']) ? trim($body['username']) : '';
    $password = isset($body['password']) ? $body['password'] : '';

    if (!$username || !$password) {
        jsonResponse(array('success' => false, 'message' => 'Username and password required'), 422);
    }

    $pdo  = getDB($db_host, $db_name, $db_user, $db_pass);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute(array($username));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        jsonResponse(array('success' => false, 'message' => 'Invalid username or password'), 401);
    }

    $valid = password_verify($password, $user['password'])
          || $user['password'] === $password
          || $user['password'] === md5($password);

    if (!$valid) {
        jsonResponse(array('success' => false, 'message' => 'Invalid username or password'), 401);
    }

    $token = generateToken($user, $jwt_secret);
    jsonResponse(array(
        'success' => true,
        'token'   => $token,
        'user'    => array(
            'id'               => $user['id'],
            'username'         => $user['username'],
            'emailid'          => isset($user['emailid']) ? $user['emailid'] : '',
            'fullname'         => isset($user['fullname']) ? $user['fullname'] : '',
            'mnuaccess'        => isset($user['mnuaccess']) ? $user['mnuaccess'] : '',
            'dashboard'        => isset($user['dashboard']) ? $user['dashboard'] : 0,
            'dashboard_access' => isset($user['dashboard_access']) ? $user['dashboard_access'] : '',
        )
    ));
}

// Logout
if ($uri === '/api/auth/logout' && $method === 'POST') {
    jsonResponse(array('success' => true, 'message' => 'Logged out'));
}

// Auth check for protected routes
// Apache often strips Authorization header — use multiple fallbacks
$authHeader = '';
if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
} elseif (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    if (!empty($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    }
}
$token      = str_replace('Bearer ', '', $authHeader);
$payload    = verifyToken($token, $jwt_secret);

if (strpos($uri, '/api/') === 0 && !$payload) {
    jsonResponse(array('success' => false, 'message' => 'Unauthorized'), 401);
}

// Menus
if ($uri === '/api/menus' && $method === 'GET') {
    $pdo  = getDB($db_host, $db_name, $db_user, $db_pass);
    $stmt = $pdo->query("SELECT mnuno, mnuname, filename, mnu_type, dashboard FROM mnuinfo ORDER BY preference ASC");
    jsonResponse(array('success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)));
}

jsonResponse(array('success' => false, 'message' => 'Route not found: ' . $uri), 404);
