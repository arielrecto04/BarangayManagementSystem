<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Complaint::paginate(10);
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
            'complainant_name' => 'required|string',
            'respondent_name' => 'required|string',
            'case_no' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
            'resolution' => 'nullable|string',
            'date' => 'required|date',
            'filing_date' => 'required|date',
            'complainant_id' => 'required|exists:residents,id',
            'respondent_id' => 'required|exists:residents,id',
            'status' => 'nullable|string',
        ]);
         $complaint = Complaint::create($validated);

        return response()->json([
            'message' => 'Complaint created successfully',
            'data' => $complaint,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Complaint::findOrFail($id);
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
    'complainant_name' => 'sometimes|string',
    'respondent_name' => 'sometimes|string',
    'case_no' => 'sometimes|string',
    'title' => 'sometimes|string',
    'description' => 'sometimes|string',
    'resolution' => 'nullable|string',
    'date' => 'sometimes|date',
    'filing_date' => 'sometimes|date',
    'status' => 'nullable|string',
        ]);
        $complaint = Complaint::findOrFail($id);
        $complaint->update($request->all());

        return response()->json([
            'message' => 'Complaint updated successfully',
            'data' => $complaint,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->delete();

        return response()->json([
            'message' => 'Complaint deleted successfully',
        ], 200);
    }
}
