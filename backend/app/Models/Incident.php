<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

#[Fillable([
    'volunteer_id', 'reported_by', 'category_id', 'barangay_id', 'severity_id',
    'status_id', 'dispatched_team_id', 'external_team_name', 'category', 'barangay', 'nearest_landmark', 'landmark',
    'affected_population', 'persons_count', 'latitude', 'longitude',
    'impact_radius', 'notes', 'reported_at', 'received_at', 'dispatched_at', 'completed_at',
    'status', 'severity',
])]
class Incident extends Model
{
    use HasFactory;

    /** Canonical workflow status written when a volunteer submits a report. */
    public const STATUS_REPORTED = 'Reported';

    /** Terminal workflow status treated as an archived/closed record. */
    public const STATUS_COMPLETED = 'Completed';

    /**
     * Add stable display labels from the normalized reference tables.
     *
     * @param Builder<Incident> $query
     * @return Builder<Incident>
     */
    public function scopeWithReferenceLabels(Builder $query): Builder
    {
        return $query
            ->leftJoin('incident_categories', 'incident_categories.id', '=', 'incidents.category_id')
            ->leftJoin('barangays', 'barangays.id', '=', 'incidents.barangay_id')
            ->leftJoin('severity_levels', 'severity_levels.id', '=', 'incidents.severity_id')
            ->leftJoin('incident_statuses', 'incident_statuses.id', '=', 'incidents.status_id')
            ->select([
                'incidents.*',
                'incident_categories.name as category',
                'barangays.name as barangay',
                'severity_levels.name as severity',
                'incident_statuses.name as status',
            ]);
    }

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
            'received_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'completed_at' => 'datetime',
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
     * @return BelongsTo<ResponseTeam, $this>
     */
    public function dispatchedTeam(): BelongsTo
    {
        return $this->belongsTo(ResponseTeam::class, 'dispatched_team_id');
    }

    /**
     * @return BelongsToMany<ResponseTeam, $this>
     */
    public function responseTeams(): BelongsToMany
    {
        return $this->belongsToMany(ResponseTeam::class, 'incident_response_teams')
            ->withPivot('dispatched_at', 'cancelled_at', 'status', 'dispatched_by', 'cancelled_by', 'cancel_reason')
            ->withTimestamps();
    }

    /**
     * @return HasMany<IncidentHistory, $this>
     */
    public function history(): HasMany
    {
        return $this->hasMany(IncidentHistory::class)->latest('changed_at');
    }

    /**
     * @return HasMany<IncidentPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(IncidentPhoto::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $incident): void {
            $incident->reported_at ??= now();
            $incident->status ??= 'Reported';
        });
    }
}
