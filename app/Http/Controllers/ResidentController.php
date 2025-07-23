<?php

namespace App\Http\Controllers;
use App\Models\Resident;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
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
            'First_Name'=> 'required',
            'Last_Name'=> 'required',
            'Birthday' => 'required',
            'Age' => 'required',
            'Gender' => 'required',
            'Address' => 'required',
            'Contact_Number' => 'required',
            'Family_Member' => 'required',
            'Emergency_Contact'=> 'required',
        ]);

        Resident::create([
            'First_Name'=> $request->First_Name,
            'Last_Name' => $request->Last_Name,
            'Birthday'  => $request->Birthday,
            'Age'  => $request->Age,
            'Gender'  => $request->Gender,
            'Address'  => $request->Address,
            'Contact_Number' => $request->Contact_Number,
            'Family_Member' => $request->Family_Member,
            'Emergency_Contact'=> $request->Emergency_Contact,
        ]);
        
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
