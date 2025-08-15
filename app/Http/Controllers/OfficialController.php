<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Official;

class OfficialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $officials = Official::all();
        return response()->json([
            'data' => $officials
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'description' => 'nullable|string',
            'terms' => 'nullable|string',
            'no_of_per_term' => 'nullable|integer',
            'elected_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'resident_id' => 'nullable|integer|exists:residents,id',
        ]);

        $official = Official::create($validated);

        return response()->json([
            'message' => 'Official created successfully',
            'data' => $official,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Official::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Fixed validation rules - made most fields nullable to match frontend behavior
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'description' => 'nullable|string',
            'terms' => 'nullable|string',
            'no_of_per_term' => 'nullable|integer',
            'elected_date' => 'nullable|date',
            'start_date' => 'nullable|date', // Added missing field
            'end_date' => 'nullable|date',
            'resident_id' => 'nullable|integer|exists:residents,id',
        ]);

        $official = Official::findOrFail($id);
        $official->update($validated); // Use validated data instead of $request->all()

        return response()->json([
            'message' => 'Official updated successfully',
            'data' => $official,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $official = Official::findOrFail($id);
        $official->delete();

        return response()->json([
            'message' => 'Official deleted successfully',
        ], 200);
    }
}
