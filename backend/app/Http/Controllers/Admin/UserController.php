<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetUserPasswordRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\VolunteerProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\VolunteerDocument;

class UserController extends Controller
{
    public function index(): View
    {
        $query = User::query()
            ->with('role')
            ->whereHas('role', fn ($query) => $query->whereIn('name', ['admin', 'dispatcher', 'volunteer']));

        if (Schema::hasColumn('users', 'name')) {
            $query->orderBy('name');
        } else {
            $query->orderBy('first_name')->orderBy('last_name');
        }

        $users = $query->paginate(15);

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
        $this->normalizeNameFields($data);

        $role = Role::query()->findOrFail($data['role_id']);
        $barangay = $data['barangay'] ?? null;
        unset($data['barangay']);

        DB::transaction(function () use ($data, $role, $barangay): void {
            $user = User::query()->create($data);

            if ($role->name === 'volunteer') {
                VolunteerProfile::query()->create([
                    'user_id' => $user->id,
                    'barangay' => $barangay,
                    'verification_status' => 'pending',
                ]);
            }
        });

        return redirect()->route('admin.users.index')->with('status', 'User account created.');
    }

    public function edit(User $user): View
    {
        $this->abortUnlessManageable($user);

        return view('admin.users.edit', [
            'managedUser' => $user->load(['role', 'volunteerProfile.documents']),
            'roles' => $this->manageableRoles(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->abortUnlessManageable($user);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $this->normalizeNameFields($data);

        $role = Role::query()->findOrFail($data['role_id']);
        $barangay = $data['barangay'] ?? $user->volunteerProfile?->barangay;
        unset($data['barangay']);

        DB::transaction(function () use ($data, $role, $barangay, $user): void {
            $user->update($data);

            if ($role->name === 'volunteer') {
                $user->volunteerProfile()->updateOrCreate(
                    [],
                    [
                        'barangay' => $barangay,
                        'verification_status' => $user->volunteerProfile?->verification_status ?? 'pending',
                    ],
                );
            } else {
                $user->volunteerProfile()->delete();
            }
        });

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

    public function uploadDocument(Request $request, User $user): RedirectResponse
    {
        $this->abortUnlessManageable($user);
        abort_unless($user->hasRole('volunteer') && $user->volunteerProfile, 404);
        $data = $request->validate([
            'document_type' => ['required', 'in:endorsement_letter,barangay_clearance,certificate_of_residency'],
            'document' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);
        abort_if($user->volunteerProfile->documents()->where('document_type', $data['document_type'])->exists(), 422, 'This document type already exists.');
        $file = $request->file('document');
        VolunteerDocument::query()->create([
            'volunteer_profile_id' => $user->volunteerProfile->id,
            'document_type' => $data['document_type'],
            'file_path' => $file->store('volunteer-documents'),
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
        ]);
        return back()->with('status', 'Volunteer document uploaded.');
    }

    public function toggleVerification(User $user): RedirectResponse
    {
        $this->abortUnlessManageable($user);
        abort_unless($user->hasRole('volunteer') && $user->volunteerProfile, 404);
        $profile = $user->volunteerProfile;
        $verified = $profile->verification_status !== 'verified';
        $profile->update([
            'verification_status' => $verified ? 'verified' : 'pending',
            'verified_at' => $verified ? now() : null,
        ]);
        return back()->with('status', 'Volunteer verification updated.');
    }

    /**
     * @return Collection<int, Role>
     */
    private function manageableRoles()
    {
        return Role::query()
            ->whereIn('name', ['admin', 'dispatcher', 'volunteer'])
            ->orderBy('name')
            ->get();
    }

    private function abortUnlessManageable(User $user): void
    {
        abort_unless($user->hasRole(['admin', 'dispatcher', 'volunteer']), 404);
    }

    /** @param array<string, mixed> $data */
    private function normalizeNameFields(array &$data): void
    {
        if (Schema::hasColumn('users', 'name')) {
            return;
        }

        $parts = preg_split('/\s+/', trim((string) ($data['name'] ?? '')), 2);
        $data['first_name'] = $parts[0] ?? '';
        $data['last_name'] = $parts[1] ?? null;
        unset($data['name']);
    }
}
