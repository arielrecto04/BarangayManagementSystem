<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Certificate::with('resident');

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('certificate_type')) {
            $query->where('certificate_type', $request->certificate_type);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('resident_id')) {
            $query->where('resident_id', $request->resident_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('certificate_number', 'like', "%{$search}%")
                    ->orWhereHas('resident', function ($resQuery) use ($search) {
                        $resQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%");
                    });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Return view or JSON based on request
        if ($request->expectsJson()) {
            $perPage = $request->get('per_page', 15);
            $certificates = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $certificates,
                'summary' => [
                    'total' => Certificate::count(),
                    'pending' => Certificate::pending()->count(),
                    'approved' => Certificate::approved()->count(),
                    'released' => Certificate::released()->count(),
                    'cancelled' => Certificate::cancelled()->count(),
                    'unpaid' => Certificate::unpaid()->count(),
                ]
            ]);
        }

        // For web interface
        $certificates = $query->paginate(10);
        return view('certificates.index', compact('certificates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'resident_id' => 'required|exists:residents,id',
            'certificate_type' => [
                'required',
                Rule::in(array_keys(Certificate::CERTIFICATE_TYPES))
            ],
            'purpose' => 'nullable|string|max:1000',
            'additional_details' => 'nullable|string|max:2000',
            'fee_amount' => 'nullable|numeric|min:0|max:999999.99',
            'payment_status' => [
                'nullable',
                Rule::in(array_keys(Certificate::PAYMENT_STATUSES))
            ],
            'requested_by' => 'nullable|string|max:255',
            'valid_until' => 'nullable|date|after:today',
            'certificate_data' => 'nullable|array',
            'remarks' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if purpose is required for this certificate type
        $certificateType = $request->certificate_type;
        if (in_array($certificateType, Certificate::REQUIRES_PURPOSE) && empty($request->purpose)) {
            $error = 'Purpose is required for this certificate type.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $error,
                    'errors' => ['purpose' => [$error]]
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['purpose' => $error])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $certificateData = $validator->validated();

            // Set default values
            $certificateData['status'] = Certificate::STATUS_PENDING;
            $certificateData['payment_status'] = $certificateData['payment_status'] ?? Certificate::PAYMENT_UNPAID;

            $certificate = Certificate::create($certificateData);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Certificate request created successfully',
                    'data' => $certificate->load('resident')
                ], 201);
            }

            return redirect()->route('certificates.show', $certificate)
                ->with('success', 'Certificate request created successfully');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create certificate request',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to create certificate request')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Certificate $certificate)
    {
        $certificate->load('resident');

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $certificate
            ]);
        }

        return view('certificates.show', compact('certificate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Certificate $certificate)
    {
        $validator = Validator::make($request->all(), [
            'status' => [
                'sometimes',
                Rule::in(array_keys(Certificate::STATUSES))
            ],
            'purpose' => 'sometimes|nullable|string|max:1000',
            'additional_details' => 'sometimes|nullable|string|max:2000',
            'fee_amount' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'payment_status' => [
                'sometimes',
                Rule::in(array_keys(Certificate::PAYMENT_STATUSES))
            ],
            'date_issued' => 'sometimes|nullable|date',
            'date_released' => 'sometimes|nullable|date|after_or_equal:date_issued',
            'issued_by' => 'sometimes|nullable|string|max:255',
            'released_by' => 'sometimes|nullable|string|max:255',
            'remarks' => 'sometimes|nullable|string|max:1000',
            'certificate_data' => 'sometimes|nullable|array',
            'valid_until' => 'sometimes|nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if purpose is required and validate
        if ($request->has('purpose') && $certificate->requiresPurpose() && empty($request->purpose)) {
            $error = 'Purpose is required for this certificate type.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $error,
                    'errors' => ['purpose' => [$error]]
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['purpose' => $error])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $updateData = $validator->validated();

            // Auto-set dates based on status changes
            if ($request->has('status')) {
                switch ($request->status) {
                    case Certificate::STATUS_APPROVED:
                        if (!$certificate->date_issued) {
                            $updateData['date_issued'] = now()->toDateString();
                        }
                        break;
                    case Certificate::STATUS_RELEASED:
                        if (!$certificate->date_issued) {
                            $updateData['date_issued'] = now()->toDateString();
                        }
                        if (!$certificate->date_released) {
                            $updateData['date_released'] = now()->toDateString();
                        }
                        break;
                }
            }

            $certificate->update($updateData);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Certificate updated successfully',
                    'data' => $certificate->fresh()->load('resident')
                ]);
            }

            return redirect()->route('certificates.show', $certificate)
                ->with('success', 'Certificate updated successfully');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update certificate',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to update certificate')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certificate $certificate)
    {
        try {
            $certificate->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Certificate deleted successfully'
                ]);
            }

            return redirect()->route('certificates.index')
                ->with('success', 'Certificate deleted successfully');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete certificate',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to delete certificate');
        }
    }

    /**
     * Show the form for creating a new certificate.
     */
    public function create()
    {
        $residents = Resident::orderBy('last_name')->orderBy('first_name')->get();
        $certificateTypes = Certificate::CERTIFICATE_TYPES;
        $categories = Certificate::CATEGORIES;

        return view('certificates.create', compact('residents', 'certificateTypes', 'categories'));
    }

    /**
     * Show the form for editing the certificate.
     */
    public function edit(Certificate $certificate)
    {
        $certificate->load('resident');
        $residents = Resident::orderBy('last_name')->orderBy('first_name')->get();
        $certificateTypes = Certificate::CERTIFICATE_TYPES;
        $categories = Certificate::CATEGORIES;
        $statuses = Certificate::STATUSES;
        $paymentStatuses = Certificate::PAYMENT_STATUSES;

        return view('certificates.edit', compact(
            'certificate',
            'residents',
            'certificateTypes',
            'categories',
            'statuses',
            'paymentStatuses'
        ));
    }

    /**
     * Approve a certificate.
     */
    public function approve(Request $request, Certificate $certificate)
    {
        $validator = Validator::make($request->all(), [
            'issued_by' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (!$certificate->isPending()) {
            $error = 'Only pending certificates can be approved';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $error
                ], 400);
            }

            return redirect()->back()->with('error', $error);
        }

        DB::beginTransaction();
        try {
            $certificate->update([
                'status' => Certificate::STATUS_APPROVED,
                'date_issued' => now()->toDateString(),
                'issued_by' => $request->issued_by,
                'remarks' => $request->remarks,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Certificate approved successfully',
                    'data' => $certificate->fresh()->load('resident')
                ]);
            }

            return redirect()->back()
                ->with('success', 'Certificate approved successfully');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve certificate',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to approve certificate');
        }
    }

    /**
     * Release a certificate.
     */
    public function release(Request $request, Certificate $certificate)
    {
        $validator = Validator::make($request->all(), [
            'released_by' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (!$certificate->isApproved()) {
            $error = 'Only approved certificates can be released';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $error
                ], 400);
            }

            return redirect()->back()->with('error', $error);
        }

        // Check if payment is required and paid
        if ($certificate->fee_amount > 0 && !$certificate->isPaid() && !$certificate->isWaived()) {
            $error = 'Certificate fee must be paid before release';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $error
                ], 400);
            }

            return redirect()->back()->with('error', $error);
        }

        DB::beginTransaction();
        try {
            $certificate->update([
                'status' => Certificate::STATUS_RELEASED,
                'date_released' => now()->toDateString(),
                'released_by' => $request->released_by,
                'remarks' => $request->remarks,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Certificate released successfully',
                    'data' => $certificate->fresh()->load('resident')
                ]);
            }

            return redirect()->back()
                ->with('success', 'Certificate released successfully');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to release certificate',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to release certificate');
        }
    }

    /**
     * Cancel a certificate.
     */
    public function cancel(Request $request, Certificate $certificate)
    {
        $validator = Validator::make($request->all(), [
            'remarks' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($certificate->isReleased()) {
            $error = 'Released certificates cannot be cancelled';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $error
                ], 400);
            }

            return redirect()->back()->with('error', $error);
        }

        DB::beginTransaction();
        try {
            $certificate->update([
                'status' => Certificate::STATUS_CANCELLED,
                'remarks' => $request->remarks,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Certificate cancelled successfully',
                    'data' => $certificate->fresh()->load('resident')
                ]);
            }

            return redirect()->back()
                ->with('success', 'Certificate cancelled successfully');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel certificate',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to cancel certificate');
        }
    }

    /**
     * Mark certificate fee as paid.
     */
    public function markAsPaid(Request $request, Certificate $certificate)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($certificate->isPaid()) {
            $error = 'Certificate fee is already paid';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $error
                ], 400);
            }

            return redirect()->back()->with('error', $error);
        }

        DB::beginTransaction();
        try {
            $updateData = [
                'payment_status' => Certificate::PAYMENT_PAID,
                'remarks' => $request->remarks,
            ];

            // Store payment information in certificate_data
            $certificateData = $certificate->certificate_data ?? [];
            $certificateData['payment'] = [
                'method' => $request->payment_method,
                'reference' => $request->payment_reference,
                'paid_at' => now()->toISOString(),
                'paid_by' => Auth::user()?->name ?? 'System',
            ];
            $updateData['certificate_data'] = $certificateData;

            $certificate->update($updateData);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Certificate fee marked as paid',
                    'data' => $certificate->fresh()->load('resident')
                ]);
            }

            return redirect()->back()
                ->with('success', 'Certificate fee marked as paid');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to mark certificate as paid',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to mark certificate as paid');
        }
    }

    /**
     * Get certificate statistics.
     */
    public function statistics(Request $request)
    {
        $query = Certificate::query();

        // Apply date filters
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $statistics = [
            'total' => (clone $query)->count(),
            'by_status' => [
                'pending' => (clone $query)->pending()->count(),
                'approved' => (clone $query)->approved()->count(),
                'released' => (clone $query)->released()->count(),
                'cancelled' => (clone $query)->cancelled()->count(),
            ],
            'by_payment_status' => [
                'unpaid' => (clone $query)->unpaid()->count(),
                'paid' => (clone $query)->paid()->count(),
                'waived' => (clone $query)->where('payment_status', Certificate::PAYMENT_WAIVED)->count(),
            ],
            'revenue' => [
                'total' => (clone $query)->where('payment_status', Certificate::PAYMENT_PAID)->sum('fee_amount'),
                'waived' => (clone $query)->where('payment_status', Certificate::PAYMENT_WAIVED)->sum('fee_amount'),
                'pending' => (clone $query)->where('payment_status', Certificate::PAYMENT_UNPAID)->sum('fee_amount'),
            ]
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        }

        return view('certificates.statistics', compact('statistics'));
    }

    /**
     * Get certificate types with metadata.
     */
    public function getCertificateTypes()
    {
        $types = [];

        foreach (Certificate::CERTIFICATE_TYPES as $key => $name) {
            $types[] = [
                'key' => $key,
                'name' => $name,
                'category' => Certificate::getCategoryForType($key),
                'category_name' => Certificate::CATEGORIES[Certificate::getCategoryForType($key)] ?? 'Uncategorized',
                'requires_purpose' => in_array($key, Certificate::REQUIRES_PURPOSE),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'types' => $types,
                'categories' => Certificate::CATEGORIES,
                'statuses' => Certificate::STATUSES,
                'payment_statuses' => Certificate::PAYMENT_STATUSES,
            ]
        ]);
    }
}
