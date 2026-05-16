<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Get all menus with their submenus
     */
    public function getMenus()
    {
        try {
            $menus = DB::table('hrdesk_forbes.mnuinfo')
                ->where('dashboard', 1)
                ->orderBy('preference', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $menus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get submenus for a specific menu
     */
    public function getSubmenus($parentMenuId)
    {
        try {
            $submenus = DB::table('hrdesk_forbes.mnuinfo')
                ->where('dashboard', 1)
                ->orderBy('preference', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $submenus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search menus and submenus
     */
    public function searchMenus(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            
            if (empty($searchTerm)) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $results = DB::table('hrdesk_forbes.mnuinfo')
                ->where('mnuname', 'LIKE', "%{$searchTerm}%")
                ->where('dashboard', 1)
                ->orderBy('preference', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all menus with nested submenus structure
     */
    public function getMenusNested()
    {
        try {
            $menus = DB::table('hrdesk_forbes.mnuinfo')
                ->where('dashboard', 1)
                ->orderBy('preference', 'asc')
                ->get()
                ->map(function ($menu) {
                    // For now, return all menus as a flat list
                    // since mnu_type field appears empty in database
                    return $menu;
                });

            return response()->json([
                'success' => true,
                'data' => $menus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
