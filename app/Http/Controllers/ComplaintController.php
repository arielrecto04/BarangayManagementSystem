<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;
use Illuminate\Support\Facades\Log;

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
        try {
            $validated = $request->validate([
                'complainant_name' => 'required|string',
                'respondent_name' => 'required|string',
                'case_no' => 'required|string',
                'title' => 'required|string',
                'description' => 'required|string',
                'resolution' => 'required|string',
                'filing_date' => 'required|date',
                'complainant_id' => 'required|exists:residents,id',
                'respondent_id' => 'required|exists:residents,id',
                'status' => 'required|string',
                'nature_of_complaint' => 'required|string',
                'incident_datetime' => 'required|date',
                'incident_location' => 'required|string',
                'supporting_documents' => 'nullable|array',
                'supporting_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,docx|max:2048',
                'witness' => 'required|string',
            ]);

            $fileData = [];
            if ($request->hasFile('supporting_documents')) {
                foreach ($request->file('supporting_documents') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $path = $file->store('documents', 'public');

                    // Store both path and original name
                    $fileData[] = [
                        'path' => $path,
                        'name' => $originalName
                    ];
                }
            }

            // Store array of objects with path and name
            $validated['supporting_documents'] = $fileData;

            $complaint = Complaint::create($validated);

            return response()->json([
                'message' => 'Complaint created successfully',
                'data' => $complaint,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating complaint: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while creating the complaint',
                'error' => $e->getMessage()
            ], 500);
        }
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
        try {
            $validated = $request->validate([
                'complainant_name' => 'sometimes|required|string',
                'respondent_name' => 'sometimes|required|string',
                'case_no' => 'sometimes|required|string',
                'title' => 'sometimes|required|string',
                'description' => 'sometimes|required|string',
                'resolution' => 'sometimes|required|string',
                'filing_date' => 'sometimes|required|date',
                'complainant_id' => 'sometimes|required|exists:residents,id',
                'respondent_id' => 'sometimes|required|exists:residents,id',
                'status' => 'sometimes|required|string',
                'nature_of_complaint' => 'sometimes|required|string',
                'incident_datetime' => 'sometimes|required|date',
                'incident_location' => 'sometimes|required|string',
                'supporting_documents' => 'nullable|array',
                'supporting_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,docx|max:2048',
                'existing_documents' => 'nullable|string', // JSON string of existing docs to keep
                'witness' => 'sometimes|required|string',
            ]);

            $complaint = Complaint::findOrFail($id);

            // Handle supporting documents
            $finalDocuments = [];

            // Keep existing documents if specified
            if ($request->has('existing_documents')) {
                $existingDocs = json_decode($request->existing_documents, true);
                if (is_array($existingDocs)) {
                    $finalDocuments = array_merge($finalDocuments, $existingDocs);
                }
            }

            // Add new uploaded files
            if ($request->hasFile('supporting_documents')) {
                foreach ($request->file('supporting_documents') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $path = $file->store('documents', 'public');

                    $finalDocuments[] = [
                        'path' => $path,
                        'name' => $originalName
                    ];
                }
            }

            // Update supporting documents if any changes were made
            if (!empty($finalDocuments) || $request->has('supporting_documents') || $request->has('existing_documents')) {
                $validated['supporting_documents'] = $finalDocuments;
            }

            $complaint->update($validated);

            return response()->json([
                'message' => 'Complaint updated successfully',
                'data' => $complaint,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating complaint: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while updating the complaint',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $complaint = Complaint::findOrFail($id);
            $complaint->delete();

            return response()->json([
                'message' => 'Complaint deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting complaint: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while deleting the complaint',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:Open,In Progress,Resolved'
            ]);

            $complaint = Complaint::findOrFail($id);
            $complaint->status = $request->status;
            $complaint->save();

            return response()->json(['data' => $complaint]);
        } catch (\Exception $e) {
            Log::error('Error updating complaint status: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while updating the status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
