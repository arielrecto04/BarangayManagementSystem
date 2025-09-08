<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class ResidentController extends Controller
{
    /**
     * Get validation rules for resident
     */
    private function getValidationRules($id = null)
    {
        $emailRule = $id ? "nullable|email|max:255|unique:residents,email,{$id}" : "nullable|email|max:255|unique:residents,email";

        return [
            // Basic Information
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|in:Jr,Sr,III,IV,V',
            'email' => $emailRule,
            'birthday' => 'required|date',
            'age' => 'required|integer|min:0|max:150',
            'gender' => 'required|in:Male,Female',

            // Address
            'house_no' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:20',

            // Contact Information
            'contact_number' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'family_member' => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'avatar' => 'nullable|string',

            // Demographic & Residency Details
            'place_of_birth' => 'nullable|string|max:255',
            'civil_status' => 'nullable|in:Single,Married,Widowed,Divorced,Separated,Common Law',
            'citizenship' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'years_of_residency' => 'nullable|integer|min:0',
            'voter_status' => 'nullable|in:Registered,Not Registered,Suspended,Transferred',
            'voter_precinct_number' => 'nullable|string|max:255',

            // Government & ID References
            'valid_id_type' => 'nullable|in:PhilHealth ID,UMID,Driver\'s License,Passport,Postal ID,Voter\'s ID,TIN ID,Senior Citizen ID,PWD ID,Barangay ID,Student ID,Company ID,Other',
            'valid_id_number' => 'nullable|string|max:255',
            'sss_number' => 'nullable|string|max:255',
            'philhealth_number' => 'nullable|string|max:255',
            'tin_number' => 'nullable|string|max:255',
            'pagibig_number' => 'nullable|string|max:255',

            // Employment & Education
            'occupation' => 'nullable|string|max:255',
            'educational_attainment' => 'nullable|in:No Formal Education,Elementary Level,Elementary Graduate,High School Level,High School Graduate,Senior High School Graduate,Vocational Graduate,College Level,College Graduate,Post Graduate',
            'monthly_income' => 'nullable|numeric|min:0',
            'employer_name' => 'nullable|string|max:255',
            'employer_address' => 'nullable|string|max:255',

            // Barangay-Specific Flags
            'is_pwd' => 'boolean',
            'pwd_id_number' => 'nullable|string|max:255',
            'disability_type' => 'nullable|string|max:255',
            'is_senior_citizen' => 'boolean',
            'senior_citizen_id_number' => 'nullable|string|max:255',
            'is_4ps_beneficiary' => 'boolean',
            '4ps_household_id' => 'nullable|string|max:255',
            'is_registered_voter' => 'boolean',
            'is_solo_parent' => 'boolean',
            'solo_parent_id_number' => 'nullable|string|max:255',
            'is_indigenous' => 'boolean',
            'indigenous_group' => 'nullable|string|max:255',

            // Additional Barangay Flags
            'is_ofw' => 'boolean',
            'ofw_country' => 'nullable|string|max:255',
            'is_teen_parent' => 'boolean',
            'is_lactating_mother' => 'boolean',
            'is_pregnant' => 'boolean',

            // Status and Notes
            'resident_status' => 'nullable|in:Active,Inactive,Deceased,Transferred,Temporary',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'notes' => 'nullable|string',
            'date_registered' => 'nullable|date',
            'registered_by' => 'nullable|string|max:255',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        $search = $request->get('search');
        $ageRange = $request->get('age_range');
        $gender = $request->get('gender');

        $query = Resident::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('house_no', 'LIKE', "%{$search}%")
                    ->orWhere('street', 'LIKE', "%{$search}%")
                    ->orWhere('barangay', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%")
                    ->orWhere('province', 'LIKE', "%{$search}%")
                    ->orWhere('contact_number', 'LIKE', "%{$search}%");
            });
        }

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

        if ($gender) {
            $query->where('gender', $gender);
        }

        $query->orderBy('created_at', 'desc');
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
     * Helper method to delete old avatar file
     */
    private function deleteOldAvatar($avatarUrl)
    {
        if (!$avatarUrl || str_contains($avatarUrl, 'ionicframework.com')) {
            return;
        }

        try {
            $parsedUrl = parse_url($avatarUrl);
            if (!$parsedUrl || !isset($parsedUrl['path'])) {
                return;
            }

            $path = $parsedUrl['path'];

            if (str_starts_with($path, '/storage/')) {
                $filePath = str_replace('/storage/', '', $path);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                    Log::info("Deleted old avatar: " . $filePath);
                }
            }
        } catch (Exception $e) {
            Log::error("Failed to delete old avatar: " . $e->getMessage());
        }
    }

    /**
     * Helper method to process avatar upload
     */
    private function processAvatarUpload($avatarData, $oldAvatarUrl = null)
    {
        if (!$avatarData) {
            return 'https://ionicframework.com/docs/img/demos/avatar.svg';
        }

        if (str_starts_with($avatarData, 'data:image/')) {
            try {
                $dataParts = explode(',', $avatarData);
                if (count($dataParts) !== 2) {
                    throw new Exception('Invalid data URL format');
                }

                $imageData = base64_decode($dataParts[1]);
                if ($imageData === false) {
                    throw new Exception('Failed to decode base64 data');
                }

                $mimeTypePart = explode(';', explode(':', $dataParts[0])[1])[0];
                $extension = explode('/', $mimeTypePart)[1];
                if ($extension === 'jpeg') {
                    $extension = 'jpg';
                }

                $imageName = 'resident_' . time() . '_' . uniqid() . '.' . $extension;
                $path = 'resident_images/' . $imageName;

                if ($oldAvatarUrl) {
                    $this->deleteOldAvatar($oldAvatarUrl);
                }

                Storage::disk('public')->put($path, $imageData);

                return asset('storage/' . $path);
            } catch (Exception $e) {
                Log::error("Failed to process base64 avatar: " . $e->getMessage());
                return $oldAvatarUrl ?: 'https://ionicframework.com/docs/img/demos/avatar.svg';
            }
        }

        if (filter_var($avatarData, FILTER_VALIDATE_URL)) {
            return $avatarData;
        }

        return $oldAvatarUrl ?: 'https://ionicframework.com/docs/img/demos/avatar.svg';
    }

    public function create() {}

    /**
     * Store a newly created resident.
     */
    public function store(Request $request)
    {
        $request->validate($this->getValidationRules());

        do {
            $randomNumber = random_int(100000, 999999);
            $residentNumber = 'RES-' . $randomNumber;
            $exists = Resident::where('resident_number', $residentNumber)->exists();
        } while ($exists);

        $data = $request->all();
        $data['resident_number'] = $residentNumber;

        $data['avatar'] = $request->hasFile('avatar')
            ? asset('storage/' . $request->avatar->storeAs('resident_images', 'resident_' . time() . '.' . $request->avatar->extension(), 'public'))
            : $this->processAvatarUpload($request->avatar);

        $resident = Resident::create($data);

        return response()->json([
            'message' => 'Resident created successfully',
            'data' => $resident,
        ], 201);
    }

    public function show(string $id)
    {
        $resident = Resident::findOrFail($id);

        return response()->json([
            'data' => $resident
        ]);
    }

    public function edit(string $id) {}

    /**
     * Update an existing resident.
     */
    public function update(Request $request, string $id)
    {
        $resident = Resident::findOrFail($id);

        $request->validate($this->getValidationRules($id));

        $data = $request->all();

        if ($request->hasFile('avatar')) {
            $this->deleteOldAvatar($resident->avatar);
            $data['avatar'] = asset('storage/' . $request->avatar->storeAs('resident_images', 'resident_' . time() . '.' . $request->avatar->extension(), 'public'));
        } elseif ($request->has('avatar')) {
            $data['avatar'] = $this->processAvatarUpload($request->avatar, $resident->avatar);
        }

        $resident->update($data);
        $resident->refresh();

        return response()->json([
            'message' => 'Resident updated successfully',
            'data' => $resident,
        ], 200);
    }

    public function destroy(string $id)
    {
        $resident = Resident::findOrFail($id);
        $this->deleteOldAvatar($resident->avatar);
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
