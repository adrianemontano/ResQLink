<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeamRequest;
use App\Http\Requests\Admin\UpdateTeamRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'all');
        $statusFilter = in_array($statusFilter, ['all', 'active', 'inactive'], true) ? $statusFilter : 'all';

        $teams = Team::query()
            ->with('teamLead')
            ->withCount('members')
            ->when($statusFilter !== 'all', fn ($query) => $query->where('is_active', $statusFilter === 'active'))
            ->orderBy('name')
            ->paginate(15)
            ->appends(['status' => $statusFilter === 'all' ? null : $statusFilter]);

        return view('admin.teams.index', ['teams' => $teams, 'statusFilter' => $statusFilter]);
    }

    public function create(): View
    {
        return view('admin.teams.create', ['responders' => $this->responders()]);
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request): void {
            $team = Team::query()->create($this->teamAttributes($data, $request));
            $team->members()->sync($data['member_ids']);
        });

        return redirect()->route('admin.teams.index')->with('status', 'Team created.');
    }

    public function edit(Team $team): View
    {
        return view('admin.teams.edit', [
            'team' => $team->load('members'),
            'responders' => $this->responders(),
        ]);
    }

    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $team): void {
            $team->update($this->teamAttributes($data, $request));
            $team->members()->sync($data['member_ids']);
        });

        return redirect()->route('admin.teams.index')->with('status', 'Team updated.');
    }

    public function toggleActivation(Team $team): RedirectResponse
    {
        $team->update(['is_active' => ! $team->is_active]);

        return redirect()->route('admin.teams.index')->with('status', 'Team activation updated.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function teamAttributes(array $data, Request $request): array
    {
        return [
            'name' => $data['name'],
            'barangay' => $data['barangay'] ?? null,
            'team_lead_id' => (int) $data['team_lead_id'],
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * Barangay Responder accounts that can be added to a team, with their current team.
     *
     * @return Collection<int, User>
     */
    private function responders(): Collection
    {
        $query = User::query()
            ->with(['volunteerProfile', 'teams'])
            ->whereRelation('role', 'name', 'volunteer');

        if (Schema::hasColumn('users', 'name')) {
            $query->orderBy('name');
        } else {
            $query->orderBy('first_name')->orderBy('last_name');
        }

        return $query->get();
    }
}
