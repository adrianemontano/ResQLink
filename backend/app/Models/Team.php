<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = ['name', 'barangay', 'team_lead_id', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function teamLead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_lead_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * @return HasMany<IncidentDispatch, $this>
     */
    public function dispatches(): HasMany
    {
        return $this->hasMany(IncidentDispatch::class);
    }

    public function isInBarangay(?string $barangay): bool
    {
        return $barangay !== null && $this->barangay !== null
            && strcasecmp(trim($this->barangay), trim($barangay)) === 0;
    }

    /**
     * Label for team dropdowns when dispatching to an incident in $barangay.
     */
    public function dispatchLabel(?string $barangay): string
    {
        return $this->name
            .($this->barangay ? " ({$this->barangay})" : '')
            .($this->teamLead ? ' — Lead: '.$this->teamLead->name : '')
            .($this->isInBarangay($barangay) ? ' — same barangay' : '');
    }

    /**
     * @param  Builder<Team>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Teams not currently out on a call: no uncancelled dispatch to an
     * incident that is still open. A team frees up when that incident is
     * completed or its dispatch is cancelled.
     *
     * @param  Builder<Team>  $query
     */
    public function scopeAvailableForDispatch(Builder $query): void
    {
        $query->where('is_active', true)
            ->whereDoesntHave('dispatches', fn (Builder $dispatches) => $dispatches
                ->whereNull('cancelled_at')
                ->whereHas('incident', fn (Builder $incident) => $incident->where('status', '!=', 'Completed')));
    }
}
