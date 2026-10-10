<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['volunteer_profile_id', 'document_type', 'file_path', 'original_filename', 'mime_type', 'review_status', 'reviewed_by', 'reviewed_at'])]
class VolunteerDocument extends Model
{
    /** Required document types, keyed by stored value. */
    public const TYPES = [
        'endorsement_letter' => 'Endorsement Letter from Barangay Captain',
        'barangay_clearance' => 'Barangay Clearance',
        'certificate_of_residency' => 'Certificate of Residency',
    ];

    public const REVIEW_STATUSES = ['pending', 'approved', 'rejected'];

    /** Private disk used for every volunteer document file. */
    public const DISK = 'local';

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->document_type] ?? $this->document_type;
    }

    public function volunteerProfile(): BelongsTo
    {
        return $this->belongsTo(VolunteerProfile::class, 'volunteer_profile_id', 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
