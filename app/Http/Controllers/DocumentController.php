<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Document::with('uploadedBy')->latest()->paginate(10);
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
            'document' => 'required|file|max:2048'
        ]);


        $path = $request->document->storeAs('/public/documents', str_replace(' ', '_', $request->document->getClientOriginalName()), 'public');


        $Document = Document::create([
            'file_name' => $request->document->getClientOriginalName(),
            'file_type' => $request->document->getClientOriginalExtension(),
            'file_path' => asset('storage/' . $path),
            'file_sizes' => $request->document->getSize(),
            'uploaded_by' => Auth::user()->id,
        ]);



        return response()->json([
            'message' => 'Document uploaded successfully',
            'document' => $Document
        ], 201);
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
}
