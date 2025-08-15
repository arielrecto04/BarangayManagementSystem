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
                    'complainant_address' => $blotter->complainant_address,
                    'respondent_address' => $blotter->respondent_address,
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
            'complainants_name' => 'required',
            'complainants_id' => 'required',
            'respondents_name' => 'required',
            'respondents_id' => 'required',
            'complainant_address' => 'required|string|max:500',
            'respondent_address' => 'required|string|max:500',
            'place' => 'required',
            'datetime_of_incident' => 'required|date',
            'blotter_type' => 'required',
            'barangay_case_no' => 'required',
            'total_cases' => 'required',
            'status' => 'required|in:Open,In Progress,Resolved',
            'description' => 'required|string|max:1000',
            'witness' => 'nullable|string|max:255',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,docx|max:2048',
        ]);

        $fileData = [];
        if ($request->hasFile('supporting_documents')) {
            foreach ($request->file('supporting_documents') as $file) {
                $originalName = $file->getClientOriginalName();
                $path = $file->store('documents', 'public');
                $fileData[] = [
                    'name' => $originalName,
                    'path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                ];
            }
            $validated['supporting_documents'] = json_encode($fileData);
        }

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
        $blotter = Blotter::findOrFail($id);
        $documents = [];

        if (!empty($blotter->supporting_documents)) {
            $decoded = json_decode($blotter->supporting_documents, true);
            if (is_array($decoded)) {
                $documents = array_map(function ($doc) {
                    return [
                        'name' => $doc['name'] ?? basename($doc['path'] ?? ''),
                        'url' => isset($doc['path']) ? asset('storage/' . $doc['path']) : null,
                        'mime_type' => $doc['mime_type'] ?? null,
                        'path' => $doc['path'] ?? null,
                    ];
                }, $decoded);
            }
        }

        return response()->json([
            'data' => [
                'id' => $blotter->id,
                'blotter_no' => $blotter->blotter_no,
                'filing_date' => $blotter->filing_date,
                'title_case' => $blotter->title_case,
                'nature_of_case' => $blotter->nature_of_case,
                'complainants_id' => $blotter->complainants_id,
                'respondents_id' => $blotter->respondents_id,
                'complainant_address' => $blotter->complainant_address,
                'respondent_address' => $blotter->respondent_address,
                'place' => $blotter->place,
                'datetime_of_incident' => $blotter->datetime_of_incident,
                'blotter_type' => $blotter->blotter_type,
                'barangay_case_no' => $blotter->barangay_case_no,
                'status' => $blotter->status,
                'description' => $blotter->description,
                'witness' => $blotter->witness,
                'supporting_documents' => $documents
            ]
        ]);
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
            'complainants_name' => 'required',
            'complainants_id' => 'required',
            'respondents_name' => 'required',
            'respondents_id' => 'required',
            'complainant_address' => 'required|string|max:500',
            'respondent_address' => 'required|string|max:500',
            'place' => 'required',
            'datetime_of_incident' => 'required',
            'blotter_type' => 'required',
            'barangay_case_no' => 'required',
            'total_cases' => 'nullable',
            'status' => 'required|in:Open,In Progress,Resolved',
            'description' => 'required|string|max:1000',
            'witness' => 'nullable|string|max:255',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,docx|max:2048',
            'existing_documents' => 'nullable|string', // For existing documents info
        ]);

        $blotter = Blotter::findOrFail($id);

        // Handle file uploads
        $finalDocuments = [];

        // First, handle existing documents that should be kept
        if ($request->has('existing_documents') && !empty($request->existing_documents)) {
            $existingDocs = json_decode($request->existing_documents, true);
            if (is_array($existingDocs)) {
                $finalDocuments = $existingDocs;
            }
        }

        // Then, handle new file uploads
        if ($request->hasFile('supporting_documents')) {
            foreach ($request->file('supporting_documents') as $file) {
                $originalName = $file->getClientOriginalName();
                $path = $file->store('documents', 'public');
                $finalDocuments[] = [
                    'name' => $originalName,
                    'path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                ];
            }
        } else if (!$request->has('existing_documents')) {
            // If no new files and no existing documents specified, keep current documents
            if (!empty($blotter->supporting_documents)) {
                $currentDocs = json_decode($blotter->supporting_documents, true);
                if (is_array($currentDocs)) {
                    $finalDocuments = $currentDocs;
                }
            }
        }

        // Update supporting documents if there are any changes
        if (!empty($finalDocuments)) {
            $validated['supporting_documents'] = json_encode($finalDocuments);
        } else {
            $validated['supporting_documents'] = null;
        }

        $blotter->update($validated);

        // Return the updated blotter with processed documents for response
        $documents = [];
        if (!empty($blotter->supporting_documents)) {
            $decoded = json_decode($blotter->supporting_documents, true);
            if (is_array($decoded)) {
                $documents = array_map(function ($doc) {
                    return [
                        'name' => $doc['name'] ?? basename($doc['path'] ?? ''),
                        'url' => isset($doc['path']) ? asset('storage/' . $doc['path']) : null,
                        'mime_type' => $doc['mime_type'] ?? null,
                        'path' => $doc['path'] ?? null,
                    ];
                }, $decoded);
            }
        }

        $responseData = $blotter->toArray();
        $responseData['supporting_documents'] = $documents;

        return response()->json([
            'message' => 'Blotter updated successfully',
            'data' => $responseData,
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
