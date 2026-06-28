<div class="field">
    <label for="name">Name</label>
    <input id="name" name="name" value="{{ old('name', $managedUser?->name) }}" autocomplete="name">
    @error('name')
        <div class="error-text">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="username">Username</label>
    <input id="username" name="username" value="{{ old('username', $managedUser?->username) }}" autocomplete="username">
    @error('username')
        <div class="error-text">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" value="{{ old('email', $managedUser?->email) }}" autocomplete="email">
    @error('email')
        <div class="error-text">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="role_id">Role</label>
    <select id="role_id" name="role_id">
        @foreach ($roles as $role)
            <option value="{{ $role->id }}" @selected((int) old('role_id', $managedUser?->role_id) === $role->id)>
                {{ $role->name }}
            </option>
        @endforeach
    </select>
    @error('role_id')
        <div class="error-text">{{ $message }}</div>
    @enderror
</div>

<div class="inline-field">
    <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $managedUser?->is_active ?? true))>
    <label for="is_active">Active account</label>
</div>
