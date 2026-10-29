@extends('layouts.dashboard', ['title' => 'Users | ResQLink'])

@section('content')
    @php
        $filters = [
            ['key' => 'all', 'label' => 'All'],
            ['key' => 'admin', 'label' => 'Admin'],
            ['key' => 'barangay-responder', 'label' => 'Barangay Responder'],
            ['key' => 'dispatcher', 'label' => 'Dispatcher'],
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
        <div class="user-role-filters" style="display:flex; flex-wrap:wrap; gap:0.75rem; margin-bottom:1.5rem;">
            @foreach ($filters as $filter)
                @php
                    $isActive = $roleFilter === $filter['key'];
                    $queryString = $filter['key'] === 'all' ? [] : ['role' => $filter['key']];
                @endphp
                <a
                    href="{{ route('admin.users.index', $queryString) }}"
                    class="button {{ $isActive ? '' : 'secondary' }}"
                    style="padding:0.5rem 0.9rem;"
                    @if ($isActive) aria-current="page" @endif
                >
                    {{ $filter['label'] }}
                </a>
            @endforeach
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
@endsection
