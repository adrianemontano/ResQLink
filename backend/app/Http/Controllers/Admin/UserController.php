<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetUserPasswordRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('role')
            ->whereHas('role', fn ($query) => $query->whereIn('slug', ['dispatcher', 'volunteer']))
            ->orderBy('name')
            ->paginate(15);

        return view('admin.users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => $this->manageableRoles(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        User::query()->create($data);

        return redirect()->route('admin.users.index')->with('status', 'User account created.');
    }

    public function edit(User $user): View
    {
        $this->abortUnlessManageable($user);

        return view('admin.users.edit', [
            'managedUser' => $user->load('role'),
            'roles' => $this->manageableRoles(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->abortUnlessManageable($user);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'User account updated.');
    }

    public function resetPassword(ResetUserPasswordRequest $request, User $user): RedirectResponse
    {
        $this->abortUnlessManageable($user);

        $user->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()->route('admin.users.edit', $user)->with('status', 'Password reset.');
    }

    public function toggleActivation(User $user): RedirectResponse
    {
        $this->abortUnlessManageable($user);

        $user->update(['is_active' => ! $user->is_active]);

        return redirect()->route('admin.users.index')->with('status', 'User activation updated.');
    }

    /**
     * @return Collection<int, Role>
     */
    private function manageableRoles()
    {
        return Role::query()
            ->whereIn('slug', ['dispatcher', 'volunteer'])
            ->orderBy('name')
            ->get();
    }

    private function abortUnlessManageable(User $user): void
    {
        abort_unless($user->hasRole(['dispatcher', 'volunteer']), 404);
    }
}
