<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AllowanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Test endpoint
Route::get('/test', function () {
    try {
        $menuTest = \Illuminate\Support\Facades\DB::table('hrdesk_forbes.mnuinfo')->limit(5)->get();
        return response()->json([
            'message' => 'API is working!', 
            'timestamp' => now(),
            'menus_found' => count($menuTest),
            'menus_sample' => $menuTest
        ])->header('Content-Type', 'application/json');
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'API is working!',
            'timestamp' => now(),
            'error' => $e->getMessage()
        ])->header('Content-Type', 'application/json');
    }
});

// Temporary unprotected menus endpoint for testing
Route::get('/menus-test', function () {
    try {
        $menus = \Illuminate\Support\Facades\DB::table('hrdesk_forbes.mnuinfo')
            ->where('dashboard', 1)
            ->orderBy('preference', 'asc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $menus->toArray()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

// Public menus route — no auth required, returns flat list for client-side grouping
Route::get('/menus', function () {
    try {
        $menus = DB::table('hrdesk_forbes.mnuinfo')
            ->orderBy('preference', 'asc')
            ->get(['mnuno', 'mnuname', 'filename', 'mnu_type', 'dashboard'])
            ->toArray();

        return response()->json([
            'success' => true,
            'data'    => $menus,
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
});

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('attendance', AttendanceController::class);
    Route::apiResource('allowances', AllowanceController::class);
    
    // Menu routes - using closures to avoid controller issues
    Route::get('/menus/search', function (Request $request) {
        try {
            $searchTerm = $request->input('search', '');
            if (empty($searchTerm)) {
                return response()->json(['success' => true, 'data' => []]);
            }
            $results = DB::table('hrdesk_forbes.mnuinfo')
                ->where('mnuname', 'LIKE', "%{$searchTerm}%")
                ->orderBy('preference', 'asc')
                ->get(['mnuno', 'mnuname', 'filename', 'mnu_type', 'dashboard']);
            return response()->json(['success' => true, 'data' => $results->toArray()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    });

    Route::get('/menus/{id}/submenus', function ($id) {
        try {
            $submenus = DB::table('hrdesk_forbes.mnuinfo')
                ->orderBy('preference', 'asc')
                ->get(['mnuno', 'mnuname', 'filename', 'mnu_type', 'dashboard']);
            return response()->json(['success' => true, 'data' => $submenus->toArray()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    });


    Route::get('/dashboard/summary', function (Request $request) {
        return response()->json([
            'total_employees' => \App\Models\Employee::count(),
            'present_today' => \App\Models\Attendance::where('date', today())->where('status', 'present')->count(),
            'pending_approvals' => 10,
            'payroll_status' => 'Active'
        ]);
    });
});
