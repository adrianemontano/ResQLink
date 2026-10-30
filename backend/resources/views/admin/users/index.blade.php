@extends('layouts.dashboard', ['title' => 'Users | ResQLink'])

@section('content')

    <div class="header-row">
        <div>
            <h1>User Accounts</h1>
            <p>Admin-managed administrator, dispatcher, and Barangay Responder accounts.</p>
        </div>
        <a class="button" href="{{ route('admin.users.create') }}">Create User</a>
    </div>

    <form class="card filter-row" method="GET" action="{{ route('admin.users.index') }}">
        <label>Role
            <select name="role">
                <option value="all" @selected($roleFilter === 'all')>All</option>
                <option value="admin" @selected($roleFilter === 'admin')>Admin</option>
                <option value="barangay-responder" @selected($roleFilter === 'barangay-responder')>Barangay Responder</option>
                <option value="dispatcher" @selected($roleFilter === 'dispatcher')>Dispatcher</option>
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="all" @selected($statusFilter === 'all')>All</option>
                <option value="active" @selected($statusFilter === 'active')>Active</option>
                <option value="inactive" @selected($statusFilter === 'inactive')>Inactive</option>
            </select>
        </label>
        <button type="submit" style="padding:0 var(--space-2);">Filter</button>
        <a href="{{ route('admin.users.index') }}">Clear</a>
    </form>

    <section class="card">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Account</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $managedUser)
                    @php
                        $roleName = $managedUser->role?->name;
                        $displayRole = match ($roleName) {
                            'admin' => 'Admin',
                            'dispatcher' => 'Dispatcher',
                            'volunteer' => 'Barangay Responder',
                            default => ucfirst((string) $roleName),
                        };
                    @endphp
                    <tr>
                        <td>{{ $managedUser->name }}</td>
                        <td>{{ $managedUser->username }}</td>
                        <td>{{ $managedUser->email }}</td>
                        <td><span class="badge">{{ $displayRole }}</span></td>
                        <td>
                            <span class="badge {{ $managedUser->is_active ? 'active' : 'inactive' }}">
                                {{ $managedUser->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="actions inline">
                                <a class="button secondary" href="{{ route('admin.users.edit', $managedUser) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.users.activation', $managedUser) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="{{ $managedUser->is_active ? 'danger' : 'secondary' }}">
                                        {{ $managedUser->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No managed user accounts have been created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="records-pagination">
            {{ $users->links() }}
        </div>
    </section>

@endsection
