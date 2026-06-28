@extends('layouts.dashboard', ['title' => 'Edit User | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Edit User</h1>
            <p>Update account details, status, role, or reset the password.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.users.index') }}">Back to Users</a>
    </div>

    <div class="split">
        <section class="card">
            <form method="POST" action="{{ route('admin.users.update', $managedUser) }}">
                @csrf
                @method('PUT')
                @include('admin.users.partials.form')

                <div class="actions">
                    <button type="submit">Save Changes</button>
                </div>
            </form>
        </section>

        <section class="card">
            <h2>Reset Password</h2>
            <p>Set a new temporary password for this account.</p>

            <form method="POST" action="{{ route('admin.users.password', $managedUser) }}">
                @csrf
                @method('PATCH')

                <div class="field">
                    <label for="password">New Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password">
                    @error('password')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                </div>

                <div class="actions">
                    <button type="submit">Reset Password</button>
                </div>
            </form>
        </section>
    </div>
@endsection
