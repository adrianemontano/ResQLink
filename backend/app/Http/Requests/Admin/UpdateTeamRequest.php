<?php

namespace App\Http\Requests\Admin;

use App\Models\Team;

class UpdateTeamRequest extends StoreTeamRequest
{
    protected function teamId(): ?int
    {
        $team = $this->route('team');

        return $team instanceof Team ? $team->id : null;
    }
}
