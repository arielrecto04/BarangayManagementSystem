<?php

namespace App\Http\Controllers;

use App\Models\VaccinationRecord;
use Illuminate\Http\Request;

class VaccinationRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = VaccinationRecord::with('resident:id, first_name, last_name');

        if ($request->has('vaccine_name') && $request->vaccine_name != '') {
            $query->where('vaccine_name', $request->vaccine_name);
        }

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;

            $query->whereHas('resident', function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%");
            });
        }

        return $query->latest('date_administered')->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(VaccinationRecord $vaccinationRecord)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VaccinationRecord $vaccinationRecord)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VaccinationRecord $vaccinationRecord)
    {
        //
    }
}
