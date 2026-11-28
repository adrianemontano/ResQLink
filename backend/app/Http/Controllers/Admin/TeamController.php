<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResponseTeam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $requestedPerPage = $request->query('per_page');
        $perPage = in_array($requestedPerPage, ['10', '25', '50'], true)
            ? (int) $requestedPerPage
            : 10;

        $teams = ResponseTeam::query()
            ->withCount('incidents')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.teams.index', compact('teams', 'perPage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:response_teams,name'],
            'code' => ['nullable', 'string', 'max:32', 'unique:response_teams,code'],
            'type' => ['required', 'string', 'max:80'],
            'team_leader' => ['nullable', 'string', 'max:120'],
            'members' => ['nullable', 'string', 'max:1000'],
            'contact_number' => ['nullable', 'string', 'max:30'],
        ]);

        $data['is_active'] = true;

        ResponseTeam::query()->create($data);

        return redirect()->route('admin.teams.index')->with('status', 'Response team created.');
    }

    public function toggle(ResponseTeam $team): RedirectResponse
    {
        $team->update(['is_active' => ! $team->is_active]);

        return redirect()->route('admin.teams.index')->with('status', 'Response team status updated.');
    }
}
