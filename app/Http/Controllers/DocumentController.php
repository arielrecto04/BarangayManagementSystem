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
        //
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
            'document' => 'required|file|mimes:pdf,doc,docx|max:2048'
        ]);


        $path = $request->file('document')->storeAs('documents', $request->file('document')->getClientOriginalName());


        $Document = Document::create([
            'file_name' => $request->file('document')->getClientOriginalName(),
            'file_type' => $request->file('document')->getClientOriginalExtension(),
            'file_path' => $path,
            'file_sizes' => $request->file('document')->getSize(),
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
