@php
    $profile = $managedUser->volunteerProfile;
    $documents = $profile->documents->keyBy('document_type');
@endphp

<h2>Volunteer Documents</h2>
<p>
    Verification:
    <span class="badge status-{{ $profile->verification_status }}">{{ $profile->verification_status }}</span>
</p>

<table>
    <thead>
        <tr>
            <th>Document</th>
            <th>File</th>
            <th>Review</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach (\App\Models\VolunteerDocument::TYPES as $type => $label)
            @php($document = $documents->get($type))
            <tr>
                <td>{{ $label }}</td>
                @if ($document)
                    <td>{{ $document->original_filename }} ({{ $document->mime_type }})</td>
                    <td>
                        <span class="badge status-{{ $document->review_status }}">{{ $document->review_status }}</span>
                        @if ($document->reviewed_at)
                            <small>{{ $document->reviewer?->name }}, {{ $document->reviewed_at->format('Y-m-d H:i') }}</small>
                        @endif
                    </td>
                    <td>
                        <div class="actions" style="margin-top: 0;">
                            <a class="button secondary" href="{{ route('admin.users.documents.show', [$managedUser, $document]) }}">Download</a>
                            @foreach (['approved' => 'Approve', 'rejected' => 'Reject'] as $reviewStatus => $reviewLabel)
                                <form method="POST" action="{{ route('admin.users.documents.review', [$managedUser, $document]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="review_status" value="{{ $reviewStatus }}">
                                    <button type="submit" class="{{ $reviewStatus === 'rejected' ? 'danger' : 'secondary' }}">{{ $reviewLabel }}</button>
                                </form>
                            @endforeach
                        </div>
                    </td>
                @else
                    <td colspan="3">Not uploaded</td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>

<form method="POST" action="{{ route('admin.users.verification', $managedUser) }}">
    @csrf
    @method('PATCH')
    <button type="submit">{{ $profile->verification_status === 'verified' ? 'Mark Pending' : 'Verify Volunteer' }}</button>
</form>

<h3>Upload Document</h3>
<form method="POST" action="{{ route('admin.users.documents.store', $managedUser) }}" enctype="multipart/form-data">
    @csrf
    <div class="field">
        <label for="document_type">Document type</label>
        <select id="document_type" name="document_type" required>
            @foreach (\App\Models\VolunteerDocument::TYPES as $type => $label)
                <option value="{{ $type }}" @selected(old('document_type') === $type) @disabled($documents->has($type))>{{ $label }}</option>
            @endforeach
        </select>
        @error('document_type')
            <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label for="document">File (PDF, JPG, or PNG, up to 5 MB)</label>
        <input id="document" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required>
        @error('document')
            <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

    <div class="actions">
        <button type="submit">Upload Document</button>
    </div>
</form>
