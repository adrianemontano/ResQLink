@extends('layouts.dashboard', ['title' => 'Create User | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Create User</h1>
            <p>Create dispatcher or volunteer accounts. Public registration is disabled.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.users.index') }}">Back to Users</a>
    </div>

    <section class="card">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users.partials.form', ['managedUser' => null])

            <div class="field">
                <label for="password">Temporary Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password">
                @error('password')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm Temporary Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
            </div>

            <div class="actions">
                <button type="submit">Create Account</button>
            </div>
        </form>
    </section>
@endsection
