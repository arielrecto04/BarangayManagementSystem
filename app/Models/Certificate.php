<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_number',
        'resident_id',
        'certificate_type',
        'status',
        'purpose',
        'additional_details',
        'fee_amount',
        'payment_status',
        'date_issued',
        'date_released',
        'issued_by',
        'released_by',
        'remarks',
        'certificate_data',
        'requested_by',
        'valid_until',
        'requires_purpose',
        'category',
    ];

    protected $casts = [
        'certificate_data' => 'array',
        'date_issued' => 'date',
        'date_released' => 'date',
        'valid_until' => 'date',
        'fee_amount' => 'decimal:2',
        'requires_purpose' => 'boolean',
    ];

    // Certificate type constants
    public const CERTIFICATE_TYPES = [
        // Personal / Identity / Status
        'barangay_clearance' => 'Barangay Clearance',
        'certificate_of_residency' => 'Certificate of Residency',
        'certificate_of_good_moral_character' => 'Certificate of Good Moral Character',
        'certificate_of_indigency' => 'Certificate of Indigency',
        'certificate_of_solo_parent' => 'Certificate of Solo Parent',
        'certificate_of_senior_citizen_status' => 'Certificate of Senior Citizen Status',
        'certificate_of_disability_pwd_status' => 'Certificate of Disability/PWD Status',

        // Civil / Legal / Endorsement
        'certificate_of_no_pending_case' => 'Certificate of No Pending Case',
        'certificate_of_guardianship' => 'Certificate of Guardianship',
        'certificate_of_non_employment' => 'Certificate of Non-Employment',
        'certificate_of_separation_abandonment' => 'Certificate of Separation/Abandonment',
        'certificate_of_barangay_endorsement' => 'Certificate of Barangay Endorsement',
        'certificate_of_no_objection' => 'Certificate of No Objection',

        // Property / Business / Livelihood
        'certificate_of_barangay_business_operation' => 'Certificate of Barangay Business Operation',
        'certificate_of_house_ownership_occupancy' => 'Certificate of House Ownership/Occupancy',
        'certificate_of_land_ownership_occupancy' => 'Certificate of Land Ownership/Occupancy',
        'certificate_of_livelihood_income_source' => 'Certificate of Livelihood/Income Source',
        'certificate_of_house_transfer_relocation' => 'Certificate of House Transfer/Relocation',

        // Employment / Assistance
        'certificate_of_employment' => 'Certificate of Employment',
        'certificate_of_barangay_support' => 'Certificate of Barangay Support',
        'certificate_of_death_burial_assistance' => 'Certificate of Death/Burial Assistance',
        'certificate_of_calamity_victim' => 'Certificate of Calamity Victim',
        'certificate_of_adoption_foster_care' => 'Certificate of Adoption/Foster Care',

        // Marriage / Family / Community
        'certificate_of_marriage_cohabitation' => 'Certificate of Marriage/Cohabitation',
        'certificate_of_community_participation' => 'Certificate of Community Participation',
        'certificate_of_voter_registration' => 'Certificate of Voter Registration',

        // Other / Special Purpose
        'certificate_for_scholarship_educational_assistance' => 'Certificate for Scholarship/Educational Assistance',
        'certificate_for_travel_abroad_local_travel' => 'Certificate for Travel Abroad/Local Travel',
        'certificate_for_business_permit_application' => 'Certificate for Business Permit Application',

        // Legacy types
        'business_clearance' => 'Business Clearance',
        'travel_permit' => 'Travel Permit',
        'death_certificate' => 'Death Certificate',
        'birth_certificate' => 'Birth Certificate',
    ];

    // Categories for certificate grouping
    public const CATEGORIES = [
        'personal_identity_status' => 'Personal / Identity / Status',
        'civil_legal_endorsement' => 'Civil / Legal / Endorsement',
        'property_business_livelihood' => 'Property / Business / Livelihood',
        'employment_assistance' => 'Employment / Assistance',
        'marriage_family_community' => 'Marriage / Family / Community',
        'other_special_purpose' => 'Other / Special Purpose',
        'legacy' => 'Legacy Certificates',
    ];

    // Certificates that typically require purpose
    public const REQUIRES_PURPOSE = [
        'barangay_clearance',
        'certificate_of_residency',
        'certificate_of_indigency',
        'certificate_of_good_moral_character',
        'certificate_of_no_pending_case',
        'certificate_of_barangay_business_operation',
        'certificate_of_barangay_endorsement',
        'certificate_of_non_employment',
        'certificate_of_calamity_victim',
        'certificate_of_voter_registration',
        'business_clearance',
        'travel_permit',
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_RELEASED = 'released';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_RELEASED => 'Released',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    // Payment status constants
    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_WAIVED = 'waived';

    public const PAYMENT_STATUSES = [
        self::PAYMENT_UNPAID => 'Unpaid',
        self::PAYMENT_PAID => 'Paid',
        self::PAYMENT_WAIVED => 'Waived',
    ];

    /**
     * Relationships
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * Accessors & Mutators
     */
    public function getCertificateTypeNameAttribute(): string
    {
        return self::CERTIFICATE_TYPES[$this->certificate_type] ?? ucwords(str_replace('_', ' ', $this->certificate_type));
    }

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucwords($this->status);
    }

    public function getPaymentStatusNameAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? ucwords($this->payment_status);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeReleased($query)
    {
        return $query->where('status', self::STATUS_RELEASED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('certificate_type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_UNPAID);
    }

    /**
     * Helper methods
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isReleased(): bool
    {
        return $this->status === self::STATUS_RELEASED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function isUnpaid(): bool
    {
        return $this->payment_status === self::PAYMENT_UNPAID;
    }

    public function isWaived(): bool
    {
        return $this->payment_status === self::PAYMENT_WAIVED;
    }

    public function requiresPurpose(): bool
    {
        return in_array($this->certificate_type, self::REQUIRES_PURPOSE) || $this->requires_purpose;
    }

    public function isExpired(): bool
    {
        return $this->valid_until && Carbon::now()->isAfter($this->valid_until);
    }

    public function getDaysUntilExpiration(): ?int
    {
        if (!$this->valid_until) {
            return null;
        }

        return Carbon::now()->diffInDays($this->valid_until, false);
    }

    public function getCategoryName(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Uncategorized';
    }

    /**
     * Generate certificate number
     */
    public static function generateCertificateNumber($type = null): string
    {
        $year = now()->year;
        $month = now()->format('m');

        // Get the last certificate number for this year and month
        $lastCert = self::whereYear('created_at', $year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastCert) {
            // Extract sequence from last certificate number
            $parts = explode('-', $lastCert->certificate_number);
            if (count($parts) >= 3) {
                $sequence = (int) end($parts) + 1;
            }
        }

        $typePrefix = strtoupper(substr($type ?? 'CERT', 0, 4));

        return sprintf('%s-%s%s-%04d', $typePrefix, $year, $month, $sequence);
    }

    /**
     * Boot method to auto-generate certificate number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificate) {
            if (!$certificate->certificate_number) {
                $certificate->certificate_number = self::generateCertificateNumber($certificate->certificate_type);
            }

            // Auto-set category based on certificate type
            if (!$certificate->category) {
                $certificate->category = self::getCategoryForType($certificate->certificate_type);
            }

            // Auto-set requires_purpose flag
            if (!isset($certificate->requires_purpose)) {
                $certificate->requires_purpose = in_array($certificate->certificate_type, self::REQUIRES_PURPOSE);
            }
        });
    }

    /**
     * Get category for certificate type
     */
    public static function getCategoryForType(string $type): string
    {
        $categoryMap = [
            'barangay_clearance' => 'personal_identity_status',
            'certificate_of_residency' => 'personal_identity_status',
            'certificate_of_good_moral_character' => 'personal_identity_status',
            'certificate_of_indigency' => 'personal_identity_status',
            'certificate_of_solo_parent' => 'personal_identity_status',
            'certificate_of_senior_citizen_status' => 'personal_identity_status',
            'certificate_of_disability_pwd_status' => 'personal_identity_status',

            'certificate_of_no_pending_case' => 'civil_legal_endorsement',
            'certificate_of_guardianship' => 'civil_legal_endorsement',
            'certificate_of_non_employment' => 'civil_legal_endorsement',
            'certificate_of_separation_abandonment' => 'civil_legal_endorsement',
            'certificate_of_barangay_endorsement' => 'civil_legal_endorsement',
            'certificate_of_no_objection' => 'civil_legal_endorsement',

            'certificate_of_barangay_business_operation' => 'property_business_livelihood',
            'certificate_of_house_ownership_occupancy' => 'property_business_livelihood',
            'certificate_of_land_ownership_occupancy' => 'property_business_livelihood',
            'certificate_of_livelihood_income_source' => 'property_business_livelihood',
            'certificate_of_house_transfer_relocation' => 'property_business_livelihood',

            'certificate_of_employment' => 'employment_assistance',
            'certificate_of_barangay_support' => 'employment_assistance',
            'certificate_of_death_burial_assistance' => 'employment_assistance',
            'certificate_of_calamity_victim' => 'employment_assistance',
            'certificate_of_adoption_foster_care' => 'employment_assistance',

            'certificate_of_marriage_cohabitation' => 'marriage_family_community',
            'certificate_of_community_participation' => 'marriage_family_community',
            'certificate_of_voter_registration' => 'marriage_family_community',

            'certificate_for_scholarship_educational_assistance' => 'other_special_purpose',
            'certificate_for_travel_abroad_local_travel' => 'other_special_purpose',
            'certificate_for_business_permit_application' => 'other_special_purpose',

            'business_clearance' => 'legacy',
            'travel_permit' => 'legacy',
            'death_certificate' => 'legacy',
            'birth_certificate' => 'legacy',
        ];

        return $categoryMap[$type] ?? 'other_special_purpose';
    }
}
