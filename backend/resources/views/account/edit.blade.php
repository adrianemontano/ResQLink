@extends('layouts.dashboard', ['title' => 'My Account | ResQLink'])

@section('content')
    <div class="header-row">
        <div><h1>My Account</h1><p>Update your profile and password.</p></div>
    </div>

    <section class="card account-card">
        <h2>My Profile</h2>
        <form method="POST" action="{{ route($routePrefix.'.account.update') }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <label>Name<input name="name" value="{{ old('name', $user->name) }}" required>@error('name')<small>{{ $message }}</small>@enderror</label>
                <label>Username<input name="username" value="{{ old('username', $user->username) }}" required>@error('username')<small>{{ $message }}</small>@enderror</label>
                <label>Email<input type="email" name="email" value="{{ old('email', $user->email) }}" required>@error('email')<small>{{ $message }}</small>@enderror</label>
                <label>Contact number<input name="contact_number" value="{{ old('contact_number', $user->contact_number) }}">@error('contact_number')<small>{{ $message }}</small>@enderror</label>
            </div>
            <div class="actions"><button class="button secondary dispatcher-view-button" type="submit">Save profile</button></div>
        </form>
    </section>

    <section class="card account-card">
        <h2>Change Password</h2>
        <form method="POST" action="{{ route($routePrefix.'.account.password') }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <label>New password<input type="password" name="password" minlength="8" autocomplete="new-password" required>@error('password')<small>{{ $message }}</small>@enderror</label>
                <label>Confirm new password<input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required></label>
            </div>
            <div class="actions"><button class="button secondary dispatcher-view-button" type="submit">Update password</button></div>
        </form>
    </section>
@endsection
