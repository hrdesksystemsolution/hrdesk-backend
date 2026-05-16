<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use Illuminate\Http\Request;

class AllowanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Allowance::query();

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        return response()->json($query->paginate(20));
    }

    public function show($id)
    {
        $allowance = Allowance::find($id);
        if (!$allowance) {
            return response()->json(['message' => 'Allowance not found'], 404);
        }
        return response()->json($allowance);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'allowance_type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'financial_year' => 'required|string',
        ]);

        $allowance = Allowance::create($validated);
        return response()->json($allowance, 201);
    }

    public function update(Request $request, $id)
    {
        $allowance = Allowance::find($id);
        if (!$allowance) {
            return response()->json(['message' => 'Allowance not found'], 404);
        }

        $validated = $request->validate([
            'amount' => 'numeric|min:0',
            'status' => 'in:active,inactive',
        ]);

        $allowance->update($validated);
        return response()->json($allowance);
    }

    public function destroy($id)
    {
        $allowance = Allowance::find($id);
        if (!$allowance) {
            return response()->json(['message' => 'Allowance not found'], 404);
        }

        $allowance->delete();
        return response()->json(['message' => 'Allowance deleted']);
    }
}
