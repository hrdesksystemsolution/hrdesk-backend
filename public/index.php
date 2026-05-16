<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Laravel Front Controller
|--------------------------------------------------------------------------
|
| Laravel - A PHP Framework For Web Artisans
|
| This file is the front controller for the application and will load
| all of the framework components. After loading the framework, we
| will create the application and make a request to the framework.
|
*/

define('LARAVEL_START', microtime(true));

try {
    // Register the auto loader.
    require __DIR__.'/../vendor/autoload.php';

    // Bootstrap Laravel and handle the request...
    $app = require_once __DIR__.'/../bootstrap/app.php';

    $kernel = $app[\Illuminate\Contracts\Http\Kernel::class];

    $response = $kernel->handle(
        $request = \Illuminate\Http\Request::capture()
    );

    $response->send();

    $kernel->terminate($request, $response);
} catch (\Throwable $e) {
    // Log the error
    error_log("Laravel Error: " . $e->getMessage());
    error_log($e->getTraceAsString());
    
    // Return JSON error response
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'type' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}
