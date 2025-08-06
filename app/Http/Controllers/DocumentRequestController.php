<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use Illuminate\Support\Facades\DB;

class DocumentRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DocumentRequest::latest()->paginate(10);
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



        $request->validate([
            'document_type' => 'required',
            'remarks' => 'required',
            'requestor_name' => 'required',
            'requestor_contact' => 'required',
            'requestor_address' => 'nullable',
            'requestor_email' => 'required',
        ]);

        $documentRequest = DocumentRequest::create([
            'document_type' => $request->document_type,
            'requestor_email' => $request->requestor_email,
            'remarks' => $request->remarks,
            'requestor_name' => $request->requestor_name,
            'requestor_contact_number' => $request->requestor_contact,
            'requestor_address' => $request->requestor_address,
        ]);


        return $documentRequest;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function statistic()
    {
        $stats = DocumentRequest::query()
            ->select('document_type', 'status', DB::raw('count(*) as count'))
            ->groupBy('document_type', 'status')
            ->get();

        // 2. Group the results by 'document_type' using collections (very fast)
        $groupedStats = $stats->groupBy('document_type');

        // 3. Map the grouped data into your desired final structure
        $totalByDocumentType = $groupedStats->map(function ($statusCollection, $documentType) {

            // The $statusCollection is a collection of all statuses for the current document type
            // e.g., [{status: 'approved', count: 10}, {status: 'rejected', count: 2}]

            return [
                'document_type' => $documentType,
                'count' => $statusCollection->sum('count'), // Calculate total by summing the group counts
                'total_requests_by_status' => $statusCollection->map(function ($item) {
                    return [
                        'status' => $item->status,
                        'count' => $item->count,
                    ];
                })->values(), // Use ->values() to reset keys for a clean array
            ];
        })->values();


        return response()->json([
            'total_by_document_type' => $totalByDocumentType,
            'total_requests' => DocumentRequest::count(),
            'total_requests_by_status' => DocumentRequest::groupBy('status')->count(),
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->search;
        $documentRequests = DocumentRequest::where('requestor_name', 'like', "%{$search}%")
            ->orWhere('requestor_email', 'like', "%{$search}%")
            ->orWhere('requestor_contact_number', 'like', "%{$search}%")
            ->orWhere('requestor_address', 'like', "%{$search}%")
            ->orWhere('remarks', 'like', "%{$search}%")
            ->orWhere('document_type', 'like', "%{$search}%")
            ->latest()
            ->paginate(10);

        return $documentRequests;
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed',
        ]);

        $documentRequest = DocumentRequest::findOrFail($id);
        $documentRequest->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Document request status updated successfully.',
            'document_request' => $documentRequest
        ]);
    }
}
