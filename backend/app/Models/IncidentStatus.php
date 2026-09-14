<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncidentStatus extends Model
{
    public function histories(): HasMany
    {
        return $this->hasMany(IncidentHistory::class, 'status_id');
    }
}