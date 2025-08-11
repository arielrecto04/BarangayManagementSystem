<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the page and per_page parameters from the request
        $perPage = $request->get('per_page', 15); // Default to 15 items per page
        $page = $request->get('page', 1);

        // You can also add search and filtering here
        $search = $request->get('search');
        $ageRange = $request->get('age_range');
        $gender = $request->get('gender');

        $query = Resident::query();

        // Add search functionality
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('address', 'LIKE', "%{$search}%")
                    ->orWhere('contact_number', 'LIKE', "%{$search}%");
            });
        }

        // Add age range filtering
        if ($ageRange) {
            switch ($ageRange) {
                case '18-25':
                    $query->whereBetween('age', [18, 25]);
                    break;
                case '26-35':
                    $query->whereBetween('age', [26, 35]);
                    break;
                case '36-45':
                    $query->whereBetween('age', [36, 45]);
                    break;
            }
        }

        // Add gender filtering
        if ($gender && $gender !== 'Not Specified') {
            $query->where('gender', $gender);
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        // Paginate the results
        $residents = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $residents->items(),
            'current_page' => $residents->currentPage(),
            'per_page' => $residents->perPage(),
            'total' => $residents->total(),
            'last_page' => $residents->lastPage(),
            'from' => $residents->firstItem(),
            'to' => $residents->lastItem(),
            'links' => [
                'first' => $residents->url(1),
                'last' => $residents->url($residents->lastPage()),
                'prev' => $residents->previousPageUrl(),
                'next' => $residents->nextPageUrl(),
            ]
        ]);
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
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => "nullable|email|max:255|unique:residents,email",
            'birthday' => 'required|date',
            'age' => 'required|integer|min:0|max:150',
            'gender' => 'required|string|in:Male,Female',
            'address' => 'required|string|max:500',
            'contact_number' => 'required|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'family_member' => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'avatar' => 'nullable|url',
        ]);

        // Generate a unique resident_number in the format RES-XXXXXX
        do {
            $randomNumber = random_int(100000, 999999);
            $residentNumber = 'RES-' . $randomNumber;
            $exists = Resident::where('resident_number', $residentNumber)->exists();
        } while ($exists);

        // Merge the generated resident_number into request data
        $data = $request->all();
        $data['resident_number'] = $residentNumber;

        $resident = Resident::create($data);


        return response()->json([
            'message' => 'Resident created successfully',
            'data' => $resident,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $resident = Resident::findOrFail($id);

        return response()->json([
            'data' => $resident
        ]);
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
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'birthday' => 'required|date',
            'email' => "nullable|email|max:255|unique:residents,email,{$id}",
            'age' => 'required|integer|min:0|max:150',
            'gender' => 'required|string|in:Male,Female',
            'address' => 'required|string|max:500',
            'contact_number' => 'required|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'family_member' => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'avatar' => 'nullable|url',
        ]);

        $resident = Resident::findOrFail($id);
        $resident->update($request->all());

        return response()->json([
            'message' => 'Resident updated successfully',
            'data' => $resident,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $resident = Resident::findOrFail($id);
        $resident->delete();

        return response()->json([
            'message' => 'Resident deleted successfully',
        ], 200);
    }

    public function getResidentByNumber($number)
    {
        return Resident::where('resident_number', $number)->firstOrFail();
    }
}
