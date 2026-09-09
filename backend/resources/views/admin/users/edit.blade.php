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

        @if ($managedUser->hasRole('volunteer') && $managedUser->volunteerProfile)
            <section class="card">
                <h2>Volunteer Documents</h2>
                @forelse ($managedUser->volunteerProfile->documents as $document)
                    <p>{{ ucwords(str_replace('_', ' ', $document->document_type)) }} — {{ $document->review_status }}</p>
                @empty <p>No documents uploaded.</p> @endforelse
                <p>Verification: {{ $managedUser->volunteerProfile->verification_status }}</p>
                <form method="POST" action="{{ route('admin.users.verification', $managedUser) }}">
                    @csrf @method('PATCH')
                    <button type="submit">{{ $managedUser->volunteerProfile->verification_status === 'verified' ? 'Mark Pending' : 'Verify Volunteer' }}</button>
                </form>
                <form method="POST" action="{{ route('admin.users.documents.store', $managedUser) }}" enctype="multipart/form-data">
                    @csrf
                    <select name="document_type" required>
                        <option value="endorsement_letter">Endorsement Letter</option>
                        <option value="barangay_clearance">Barangay Clearance</option>
                        <option value="certificate_of_residency">Certificate of Residency</option>
                    </select>
                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required>
                    <button type="submit">Upload Document</button>
                </form>
            </section>
        @endif
    </div>
@endsection
