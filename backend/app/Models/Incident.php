<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'volunteer_id', 'reported_by', 'category_id', 'barangay_id', 'severity_id',
    'status_id', 'category', 'barangay', 'nearest_landmark', 'landmark',
    'affected_population', 'persons_count', 'latitude', 'longitude',
    'impact_radius', 'notes', 'reported_at', 'status', 'severity',
])]
class Incident extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'persons_count' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'impact_radius' => 'decimal:2',
            'affected_population' => 'integer',
            'reported_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    /**
     * @return HasMany<IncidentHistory, $this>
     */
    public function history(): HasMany
    {
        return $this->hasMany(IncidentHistory::class)->latest('changed_at');
    }

    protected static function booted(): void
    {
        static::creating(function (self $incident): void {
            $incident->reported_at ??= now();
            $incident->status ??= 'Reported';
        });
    }
}
