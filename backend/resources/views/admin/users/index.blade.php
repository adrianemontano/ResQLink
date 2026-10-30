@extends('layouts.dashboard', ['title' => 'Users | ResQLink'])

@section('content')
    @php
        $filters = [
            ['key' => 'all', 'label' => 'All'],
            ['key' => 'admin', 'label' => 'Admin'],
            ['key' => 'barangay-responder', 'label' => 'Barangay Responder'],
            ['key' => 'dispatcher', 'label' => 'Dispatcher'],
        ];
        $statusFilters = [
            ['key' => 'all', 'label' => 'All'],
            ['key' => 'active', 'label' => 'Active'],
            ['key' => 'inactive', 'label' => 'Inactive'],
        ];
    @endphp

    <div class="header-row">
        <div>
            <h1>User Accounts</h1>
            <p>Admin-managed administrator, dispatcher, and Barangay Responder accounts.</p>
        </div>
        <a class="button" href="{{ route('admin.users.create') }}">Create User</a>
    </div>

    <section class="card">
        <div class="user-role-filters" style="display:flex; flex-wrap:wrap; gap:0.5rem; align-items:center; margin-bottom:1.5rem;">
            <span class="sidebar-section-label" style="font-size:0.875rem; padding:0; margin-right:0.25rem; color:var(--text-muted);">Role:</span>
            <select name="role" id="roleFilter" onchange="applyFilter(this, 'role')" style="width:auto; padding:0.5rem 0.9rem; border:1px solid var(--border); border-radius:var(--radius-sm); background:#fff;">
                @foreach ($filters as $filter)
                    <option value="{{ $filter['key'] }}" @selected($roleFilter === $filter['key'])>{{ $filter['label'] }}</option>
                @endforeach
            </select>
            <span class="sidebar-section-label" style="font-size:0.875rem; padding:0; margin-left:0.75rem; margin-right:0.25rem; color:var(--text-muted);">Status:</span>
            <select name="status" id="statusFilter" onchange="applyFilter(this, 'status')" style="width:auto; padding:0.5rem 0.9rem; border:1px solid var(--border); border-radius:var(--radius-sm); background:#fff;">
                @foreach ($statusFilters as $filter)
                    <option value="{{ $filter['key'] }}" @selected($statusFilter === $filter['key'])>{{ $filter['label'] }}</option>
                @endforeach
            </select>
        </div>

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

    <script>
        function applyFilter(select, param) {
            const value = select.value;
            const url = new URL(window.location.href);
            if (value === 'all') {
                url.searchParams.delete(param);
            } else {
                url.searchParams.set(param, value);
            }
            window.location.href = url.toString();
        }
    </script>
@endsection
