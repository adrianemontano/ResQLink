@extends('layouts.dashboard', ['title' => 'Users | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>User Accounts</h1>
            <p>Admin-managed dispatcher and volunteer accounts.</p>
        </div>
        <a class="button" href="{{ route('admin.users.create') }}">Create User</a>
    </div>

    <section class="card">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $managedUser)
                    <tr>
                        <td>{{ $managedUser->name }}</td>
                        <td>{{ $managedUser->username }}</td>
                        <td>{{ $managedUser->email }}</td>
                        <td><span class="badge">{{ $managedUser->role?->name }}</span></td>
                        <td>
                            <span class="badge {{ $managedUser->is_active ? 'active' : 'inactive' }}">
                                {{ $managedUser->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="actions" style="margin-top: 0;">
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
                        <td colspan="6">No dispatcher or volunteer accounts have been created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $users->links() }}
        </div>
    </section>
@endsection
