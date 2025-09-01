<?php

namespace App\Http\Controllers;

use App\Models\Blotter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
                    'complainants_resident_number' => optional($blotter->complainant)->resident_number,
                    'respondents_id' =>  str_pad($blotter->respondents_id, 4, '0', STR_PAD_LEFT),
                    'respondents_resident_number' => optional($blotter->respondent)->resident_number,
                    'incident_location' => $blotter->incident_location,
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
        try {
            Log::info('Blotter store request received');
            Log::info('Request data: ' . json_encode($request->all()));

            $validated = $request->validate([
                'blotter_no' => 'required',
                'filing_date' => 'required|date',
                'title_case' => 'required',
                'nature_of_case' => 'required',
                'complainants_name' => 'required',
                'complainants_id' => 'required|exists:residents,id',
                'respondents_name' => 'required',
                'respondents_id' => 'required|exists:residents,id',
                'incident_location' => 'required',
                'datetime_of_incident' => 'required|date',
                'blotter_type' => 'required',
                'barangay_case_no' => 'required',
                'total_cases' => 'nullable',
                'status' => 'required|in:Open,In Progress,Resolved',
                'description' => 'required|string|max:1000',
                'witness' => 'nullable|string|max:255',
                'supporting_documents' => 'nullable|array',
                'supporting_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,docx|max:2048',
            ]);

            Log::info('Validation passed');

            // FIXED: Handle file uploads properly
            $fileData = [];
            if ($request->hasFile('supporting_documents')) {
                Log::info('Processing ' . count($request->file('supporting_documents')) . ' files');

                foreach ($request->file('supporting_documents') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $path = $file->store('documents', 'public');

                    $fileInfo = [
                        'name' => $originalName,
                        'path' => $path,
                        'mime_type' => $file->getClientMimeType(),
                    ];

                    $fileData[] = $fileInfo;
                    Log::info('File processed: ' . json_encode($fileInfo));
                }

                // Store as JSON string
                $validated['supporting_documents'] = json_encode($fileData);
                Log::info('Final supporting_documents JSON: ' . $validated['supporting_documents']);
            } else {
                // FIXED: Explicitly set to null when no files
                $validated['supporting_documents'] = null;
                Log::info('No files uploaded, setting supporting_documents to null');
            }

            $blotter = Blotter::create($validated);

            // FIXED: Return consistent response format with processed documents
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

            // Prepare response data
            $responseData = $blotter->toArray();
            $responseData['supporting_documents'] = $documents;

            Log::info('Blotter created successfully with ID: ' . $blotter->id);
            Log::info('Response supporting_documents: ' . json_encode($documents));

            return response()->json([
                'message' => 'Blotter created successfully',
                'data' => $responseData,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed: ' . json_encode($e->errors()));
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating blotter: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'An error occurred while creating the blotter',
                'error' => $e->getMessage()
            ], 500);
        }
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
                'complainants_resident_number' => optional($blotter->complainant)->resident_number,
                'respondents_id' => $blotter->respondents_id,
                'respondents_resident_number' => optional($blotter->respondent)->resident_number,
                'incident_location' => $blotter->incident_location,
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
            'filing_date' => 'required|date',
            'title_case' => 'required',
            'nature_of_case' => 'required',
            'complainants_name' => 'required',
            'complainants_id' => 'required|exists:residents,id',
            'respondents_name' => 'required',
            'respondents_id' => 'required|exists:residents,id',
            'incident_location' => 'required',
            'datetime_of_incident' => 'required|date',
            'blotter_type' => 'required',
            'barangay_case_no' => 'required',
            'total_cases' => 'nullable',
            'status' => 'required|in:Open,In Progress,Resolved',
            'description' => 'required|string|max:1000',
            'witness' => 'nullable|string|max:255',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,docx|max:2048',
            'existing_documents' => 'nullable|string',
        ]);

        $blotter = Blotter::findOrFail($id);

        // FIXED: Handle file uploads properly
        $finalDocuments = [];

        // Process existing documents - ALWAYS check this field when provided
        if ($request->has('existing_documents')) {
            $existingDocsJson = $request->existing_documents;
            Log::info('Blotter existing documents received: ' . $existingDocsJson);

            if (!empty($existingDocsJson)) {
                $existingDocs = json_decode($existingDocsJson, true);
                if (is_array($existingDocs)) {
                    $finalDocuments = $existingDocs;
                    Log::info('Blotter processed existing documents: ' . count($finalDocuments) . ' files');
                } else {
                    Log::warning('Invalid existing_documents JSON format for blotter');
                }
            } else {
                Log::info('Empty existing_documents - all existing blotter files will be removed');
            }
        } else {
            // If existing_documents is not provided, keep current documents
            if (!empty($blotter->supporting_documents)) {
                $currentDocs = json_decode($blotter->supporting_documents, true);
                if (is_array($currentDocs)) {
                    $finalDocuments = $currentDocs;
                }
            }
            Log::info('No existing_documents field - keeping current blotter documents');
        }

        // Add new uploaded files
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
            Log::info('Added ' . count($request->file('supporting_documents')) . ' new files to blotter');
        }

        // FIXED: Always update supporting_documents when the field is being managed
        if ($request->has('existing_documents') || $request->hasFile('supporting_documents')) {
            if (!empty($finalDocuments)) {
                $validated['supporting_documents'] = json_encode($finalDocuments);
            } else {
                $validated['supporting_documents'] = null; // Explicitly set to null when empty
            }
            Log::info('Final blotter documents count: ' . count($finalDocuments));
        }

        $blotter->update($validated);

        // Return the updated blotter with processed documents for response
        $documents = [];
        if (!empty($blotter->fresh()->supporting_documents)) {
            $decoded = json_decode($blotter->fresh()->supporting_documents, true);
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

        $responseData = $blotter->fresh()->toArray(); // Get fresh data from DB
        $responseData['supporting_documents'] = $documents;

        Log::info('Blotter updated. Supporting documents in DB: ' . json_encode($blotter->fresh()->supporting_documents));

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
