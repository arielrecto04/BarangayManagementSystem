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
        $blotters = Blotter::with(['complainant', 'respondent'])->paginate(10);
        return response()->json([
            'data' => $blotters->map(function ($blotter) {
                return [
                    'id' => $blotter->id,
                    'blotter_no' => $blotter->blotter_no,
                    'filing_date' => $blotter->filing_date,
                    'title_case' => $blotter->title_case,
                    'nature_of_case' => $blotter->nature_of_case,
                    'complainants_id' => str_pad($blotter->complainants_id, 4, '0', STR_PAD_LEFT),
                    'respondents_id' =>  str_pad($blotter->respondents_id, 4, '0', STR_PAD_LEFT),
                    'place' => $blotter->place,
                    'datetime_of_incident' => $blotter->datetime_of_incident,
                    'blotter_type' => $blotter->blotter_type,
                    'barangay_case_no' => $blotter->barangay_case_no,
                    'status' => $blotter->status,
                    'description' => $blotter->description,
                    'witness' => $blotter->witness,

                ];
            }),
            'current_page' => $blotters->currentPage(),
            'last_page' => $blotters->lastPage(),
            'per_page' => $blotters->perPage(),
            'total' => $blotters->total()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'blotter_no' => 'required',
        'filing_date' => 'required|date',
        'title_case' => 'required',
        'nature_of_case' => 'required',
        'complainants_type' => 'required',
        'complainants_id' => 'required',
        'respondents_type' => 'required',
        'respondents_id' => 'required',
        'place' => 'required',
        'datetime_of_incident' => 'required|date',
        'blotter_type' => 'required',
        'barangay_case_no' => 'required',
        'total_cases' => 'required',
        'status' => 'required|in:Open,In Progress,Resolved',
        'description' => 'required|string|max:1000',
        'witness' => 'required|string|max:255'
    ]);

    $blotter = Blotter::create($validated);

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
        $validated = $request->validate([
            'blotter_no' => 'required',
            'filing_date' => 'required',
            'title_case' => 'required',
            'nature_of_case' => 'required',
            'complainants_type' => 'required',
            'complainants_id' => 'required',
            'respondents_type' => 'required',
            'respondents_id' => 'required',
            'place' => 'required',
            'datetime_of_incident' => 'required',
            'blotter_type' => 'required',
            'barangay_case_no' => 'required',
            'total_cases' => 'nullable',
            'status' => 'required|in:Open,In Progress,Resolved',
            'description' => 'required|string|max:1000',
            'witness' => 'required|string|max:255'
        ]);

        $blotter = Blotter::findOrFail($id);
        $blotter->update($validated);

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