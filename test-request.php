<?php

// Load Laravel
require_once __DIR__ . '/bootstrap/app.php';

// Get the application instance
$app = require_once __DIR__ . '/bootstrap/app.php';

// Get the request service
$request = $app['request'];

// Set up a test POST request to /api/auth/login
$request = $app['request']::create('/api/auth/login', 'POST', [], [], [], [
    'HTTP_CONTENT_TYPE' => 'application/json',
], json_encode(['username' => 'vishal24', 'password' => '1234guruji'])
);

$app['request'] = $request;

// Bootstrap the application
$app->bootstrapWith([
    \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
    \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
    \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
    \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
    \Illuminate\Foundation\Bootstrap\BootProviders::class,
]);

// Get the kernel and handle the request
$kernel = $app[\Illuminate\Contracts\Http\Kernel::class];
$response = $kernel->handle($request);

echo "Response Status: " . $response->status() . "\n";
echo "Response Content-Type: " . $response->headers->get('Content-Type') . "\n";
echo "Response Body:\n";
echo $response->getContent();
echo "\n";
