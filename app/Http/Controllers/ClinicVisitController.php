<?php

namespace App\Http\Controllers;

use App\Models\ClinicVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClinicVisitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Eager load relationships for efficiency
        $query = ClinicVisit::with([
            'resident:id,first_name,last_name', // Only get the columns you need
            'healthWorker:id,name' // Assuming your User model has a 'name' attribute
        ]);

        // 2. Add search functionality
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->whereHas('resident', function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%");
            });
        }

        // 3. Sort by the most recent visit first and paginate
        return $query->latest('visit_date')->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'visit_date' => 'required|date',
            'reason_for_visit' => 'required|string',
            'diagnosis' => 'nullable|string',
            'treatment_notes' => 'nullable|string',
            'prescription' => 'nullable|string',
        ]);

        // Automatically assign the logged-in user as the health worker
        $validated['health_worker_id'] = Auth::id();

        $clinicVisit = ClinicVisit::create($validated);

        return response()->json($clinicVisit, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ClinicVisit $clinicVisit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClinicVisit $clinicVisit)
    {
        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'visit_date' => 'required|date',
            'reason_for_visit' => 'required|string',
            'diagnosis' => 'nullable|string',
            'treatment_notes' => 'nullable|string',
            'prescription' => 'nullable|string',
        ]);

        $clinicVisit->update($validated);

        return response()->json($clinicVisit);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClinicVisit $clinicVisit)
    {
        $clinicVisit->delete();

        return response()->json(null, 204);
    }
}
