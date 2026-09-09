@extends('layouts.dashboard', ['title' => 'Submit Incident | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Submit Incident</h1>
            <p>Provide accurate details. Dispatchers will review the preliminary assessment.</p>
        </div>
    </div>

    @if (session('incident'))
        @php($incident = session('incident'))
        <div class="alert success">
            Incident <strong>INC-{{ str_pad((string) $incident->id, 4, '0', STR_PAD_LEFT) }}</strong> was submitted.
            Status: <strong>{{ $incident->status }}</strong>. Preliminary severity: <strong>{{ $incident->severity }}</strong>.
        </div>
    @endif

    @if ($errors->any())
        <div class="alert error">Please correct the highlighted fields before submitting.</div>
    @endif

    <form class="card form-grid" method="POST" action="{{ route('volunteer.incidents.store') }}">
        @csrf
        <div class="form-section-title">Incident details</div>
        <label>Category<select name="category" required><option value="">Select category</option>@foreach (['Flood', 'Earthquake', 'Landslide', 'Fire'] as $category)<option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>@endforeach</select>@error('category')<small>{{ $message }}</small>@enderror</label>
        <label>Barangay<input name="barangay" value="{{ old('barangay') }}" maxlength="100" required>@error('barangay')<small>{{ $message }}</small>@enderror</label>
        <label>Nearest landmark<input name="nearest_landmark" value="{{ old('nearest_landmark') }}" maxlength="255" required>@error('nearest_landmark')<small>{{ $message }}</small>@enderror</label>
        <label>Affected persons<input type="number" name="affected_population" value="{{ old('affected_population') }}" min="0" required>@error('affected_population')<small>{{ $message }}</small>@enderror</label>
        <div class="form-section-title">Local location</div>
        <label>Latitude<input type="number" step="any" name="latitude" value="{{ old('latitude') }}" min="-90" max="90" required>@error('latitude')<small>{{ $message }}</small>@enderror</label>
        <label>Longitude<input type="number" step="any" name="longitude" value="{{ old('longitude') }}" min="-180" max="180" required>@error('longitude')<small>{{ $message }}</small>@enderror</label>
        <label>Impact radius (metres)<input type="number" step="any" name="impact_radius" value="{{ old('impact_radius') }}" min="0.01" required>@error('impact_radius')<small>{{ $message }}</small>@enderror</label>
        <label class="full-width">Notes<textarea name="notes" rows="4" maxlength="5000">{{ old('notes') }}</textarea>@error('notes')<small>{{ $message }}</small>@enderror</label>
        <div class="full-width form-actions"><button class="button" type="submit">Submit Incident Report</button></div>
    </form>
@endsection
