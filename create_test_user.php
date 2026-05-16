<?php

// Try to connect to database directly
try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=hrdesk_forbes",
        'root',
        'root',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Connected to database successfully!\n";

    // Check existing users
    $stmt = $pdo->query("SELECT username, emailid, fullname FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\nExisting users:\n";
    foreach ($users as $user) {
        echo "- Username: {$user['username']}, Email: {$user['emailid']}, Name: {$user['fullname']}\n";
    }

    // Try to find a user with a simple password
    $stmt = $pdo->prepare("SELECT username, password FROM users WHERE username = ?");
    $stmt->execute(['vishal24']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Create a test user with known password
    $testUsername = 'testuser';
    $testPassword = 'test123'; // Plain text password
    $testEmail = 'test@example.com';
    $testFullname = 'Test User';

    // Check if test user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$testUsername]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingUser) {
        // Create test user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, emailid, fullname) VALUES (?, ?, ?, ?)");
        $stmt->execute([$testUsername, $testPassword, $testEmail, $testFullname]);
        echo "\nCreated test user: username='testuser', password='test123'\n";
    } else {
        echo "\nTest user already exists: username='testuser', password='test123'\n";
    }

    echo "\nYou can now login with:\n";
    echo "- Username: testuser, Password: test123\n";
    echo "- Username: vishal24, Password: 1234guruji (if plain text)\n";

} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}