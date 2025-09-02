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
                    ->orWhere('address', 'LIKE', "%{$search}%")
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

        if ($gender && $gender !== 'Not Specified') {
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
            return; // Don't delete default avatars
        }

        try {
            // Extract path from URL
            $parsedUrl = parse_url($avatarUrl);
            if (!$parsedUrl || !isset($parsedUrl['path'])) {
                return;
            }

            $path = $parsedUrl['path'];

            // Remove /storage/ prefix to get the actual file path
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

        // If it's a base64 data URL
        if (str_starts_with($avatarData, 'data:image/')) {
            try {
                // Extract image data and mime type
                $dataParts = explode(',', $avatarData);
                if (count($dataParts) !== 2) {
                    throw new Exception('Invalid data URL format');
                }

                $imageData = base64_decode($dataParts[1]);
                if ($imageData === false) {
                    throw new Exception('Failed to decode base64 data');
                }

                // Extract mime type and determine extension
                $mimeTypePart = explode(';', explode(':', $dataParts[0])[1])[0];
                $extension = explode('/', $mimeTypePart)[1];
                if ($extension === 'jpeg') {
                    $extension = 'jpg';
                }

                // Generate unique filename
                $imageName = 'resident_' . time() . '_' . uniqid() . '.' . $extension;
                $path = 'resident_images/' . $imageName;

                // Delete old avatar before saving new one
                if ($oldAvatarUrl) {
                    $this->deleteOldAvatar($oldAvatarUrl);
                }

                // Save new image
                Storage::disk('public')->put($path, $imageData);

                return asset('storage/' . $path);
            } catch (Exception $e) {
                Log::error("Failed to process base64 avatar: " . $e->getMessage());
                return $oldAvatarUrl ?: 'https://ionicframework.com/docs/img/demos/avatar.svg';
            }
        }

        // If it's already a valid URL, return as is
        if (filter_var($avatarData, FILTER_VALIDATE_URL)) {
            return $avatarData;
        }

        // If invalid format, return old avatar or default
        return $oldAvatarUrl ?: 'https://ionicframework.com/docs/img/demos/avatar.svg';
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resident.
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
            'avatar' => 'nullable|string',
        ]);

        // Generate unique resident_number
        do {
            $randomNumber = random_int(100000, 999999);
            $residentNumber = 'RES-' . $randomNumber;
            $exists = Resident::where('resident_number', $residentNumber)->exists();
        } while ($exists);

        $data = $request->all();
        $data['resident_number'] = $residentNumber;

        // Process avatar upload
        if ($request->hasFile('avatar')) {
            // Traditional file upload
            $imageName = 'resident_' . time() . '.' . $request->avatar->extension();
            $path = $request->avatar->storeAs('resident_images', $imageName, 'public');
            $data['avatar'] = asset('storage/' . $path);
        } else {
            // Handle base64 data URL or regular URL
            $data['avatar'] = $this->processAvatarUpload($request->avatar);
        }

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

    public function edit(string $id)
    {
        //
    }

    /**
     * Update an existing resident.
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
            'avatar' => 'nullable|string',
        ]);

        $resident = Resident::findOrFail($id);
        $data = $request->all();

        // Handle avatar update
        if ($request->hasFile('avatar')) {
            // Traditional file upload
            $this->deleteOldAvatar($resident->avatar);

            $imageName = 'resident_' . time() . '.' . $request->avatar->extension();
            $path = $request->avatar->storeAs('resident_images', $imageName, 'public');
            $data['avatar'] = asset('storage/' . $path);
        } elseif ($request->has('avatar')) {
            // Handle base64 data URL or regular URL
            $oldAvatar = $resident->avatar;
            $newAvatar = $this->processAvatarUpload($request->avatar, $oldAvatar);

            // Only update if we got a different avatar URL
            if ($newAvatar !== $oldAvatar) {
                $data['avatar'] = $newAvatar;
            }
        }
        // If no avatar in request, keep the current one

        $resident->update($data);

        // Refresh the resident to get updated data
        $resident->refresh();

        return response()->json([
            'message' => 'Resident updated successfully',
            'data' => $resident,
        ], 200);
    }

    public function destroy(string $id)
    {
        $resident = Resident::findOrFail($id);

        // Delete avatar file
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
