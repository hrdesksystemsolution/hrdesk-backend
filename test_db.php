<?php

// Try different connection methods
$connections = [
    ['host' => 'localhost', 'user' => 'root', 'pass' => ''],
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'user' => 'root', 'pass' => 'root'],
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => 'root'],
];

foreach ($connections as $config) {
    echo "Trying: {$config['host']} / {$config['user']} / ***\n";
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname=hrdesk_forbes",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "✓ Connected successfully!\n";
        
        // Try to fetch a user
  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute(['vishal24']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "✓ User found:\n";
            print_r($user);
            exit(0);
        } else {
            echo "✗ User not found\n";
        }
        break;
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n\n";
    }
}

