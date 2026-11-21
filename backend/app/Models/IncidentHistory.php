<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['incident_id', 'entry_type', 'category', 'status_id', 'changed_by', 'notes', 'changed_at'])]
class IncidentHistory extends Model
{
    public const ENTRY_TYPE_STATUS_CHANGE = 'status_change';
    public const ENTRY_TYPE_UPDATE = 'update';

    public const UPDATE_CATEGORIES = [
        'Resource requested',
        'Resource arrived',
        'Situation update',
        'Coordination',
        'Other',
    ];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(IncidentStatus::class, 'status_id');
    }
}