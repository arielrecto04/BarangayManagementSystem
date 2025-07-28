<?php

namespace App\Http\Controllers;

use App\Models\Blotter;
use Illuminate\Http\Request;

class BlotterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Blotter::paginate(10); // Return paginated blotters
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $request->validate([
        'blotter_no' => 'required',
        'filing_date' => 'required|date',
        'title_case' => 'required',
        'nature_of_case' => 'required',
        'complainants' => 'required',
        'respondents' => 'required',
        'place' => 'required',
        'datetime_of_incident' => 'required|date',
        'blotter_type' => 'required',
        'barangay_case_no' => 'required',
    ]);

    $blotter = Blotter::create($request->all());

    return response()->json([
        'message' => 'Blotter created successfully',
        'data' => $blotter,
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Blotter::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'blotter_id' => 'required',
            'title' => 'required',
            'resident' => 'required',
            'status' => 'required',
            'date' => 'required',
        ]);

        $blotter = Blotter::findOrFail($id);
        $blotter->update($request->all());

        return response()->json([
            'message' => 'Blotter updated successfully',
            'data' => $blotter,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $blotter = Blotter::findOrFail($id);
        $blotter->delete();

        return response()->json([
            'message' => 'Blotter deleted successfully',
        ], 200);
    }
}