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
        'name' => 'required|string',
        'position' => 'required|string',
        'description' => 'required|string',
        'terms' => 'required|string',
        'no_of_per_term' => 'required|integer',
        'elected_date' => 'required|date',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'resident_id' => 'required|string',
    ]);

    $official = Official::create($validated);

    return response()->json([
        'message' => 'Official created successfully',
        'data' => $official,
    ], 201); // ✅ Send success response
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
         $request->validate([
            'name' => 'required',
            'description' => 'required',
            'position' => 'required',
            'terms' => 'required',
            'no_of_per_term' => 'required',
            'elected_date' => 'required',
            'end_date' => 'required',
            'resident_id' => 'required',
            
        ]);

        $official = Official::findOrFail($id);
        $official->update($request->all());

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
