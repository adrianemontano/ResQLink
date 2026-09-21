@extends('layouts.dashboard', ['title' => 'Submit Incident | ResQLink'])

@push('styles')
    @vite('resources/css/volunteer-report.css')
    <style>.volunteer-map { background: #fff !important; background-image: none !important; }</style>
@endpush

@push('scripts')
    @vite('resources/js/volunteer-report.js')
@endpush

@section('content')
    <div class="volunteer-topbar"><div><h1>Submit Incident</h1><p>Provide accurate details. Dispatchers will review the preliminary assessment.</p></div></div>
    @if (session('incident')) @php($incident = session('incident'))<div class="alert success">Incident <strong>INC-{{ str_pad((string) $incident->id, 4, '0', STR_PAD_LEFT) }}</strong> was submitted. Status: <strong>{{ $incident->status }}</strong>. Preliminary severity: <strong>{{ $incident->severity }}</strong>.</div>@endif
    @if ($errors->any())<div class="alert error">Please correct the highlighted fields before submitting.</div>@endif
    <div class="volunteer-report-grid">
        <form class="card volunteer-form-card" method="POST" action="{{ route('volunteer.incidents.store') }}">
            @csrf
            <div class="volunteer-section-head">Incident details</div>
            <div class="volunteer-field-row"><x-volunteer-field label="Category" name="category" type="select" :options="['Flood', 'Earthquake', 'Landslide', 'Fire']" required /><x-volunteer-field label="Barangay" name="barangay" placeholder="e.g. Lahug" required /></div>
            <div class="volunteer-field-row"><x-volunteer-field label="Nearest landmark" name="nearest_landmark" placeholder="e.g. Robinsons Cybergate" required /><x-volunteer-field label="Affected persons" name="affected_population" type="number" min="0" placeholder="Estimated count" required /></div>
            <div class="volunteer-section-head volunteer-section-head-spaced">Location &amp; severity <span>Set on the map →</span></div>
            <div class="volunteer-field-row"><x-volunteer-field label="Latitude" name="latitude" type="number" step="any" readonly placeholder="Pin on map" /><x-volunteer-field label="Longitude" name="longitude" type="number" step="any" readonly placeholder="Pin on map" /></div>
            <x-volunteer-field label="Impact radius (metres)" name="impact_radius" type="number" step="any" min="0.01" readonly placeholder="Set radius on map" />
            <p class="volunteer-synced-note">ⓘ These fields fill in automatically once you pin the incident on the map.</p>
            <x-volunteer-field label="Notes" name="notes" type="textarea" placeholder="Anything dispatchers should know before they respond" />
            <div class="volunteer-form-footer"><button class="button" type="submit" data-submit-incident disabled>Submit Incident Report</button></div>
        </form>
        <section class="card volunteer-map-card"><div class="volunteer-map-head"><h2>Mark the incident</h2><span>Tap to place a pin, drag the slider to set radius</span></div><div class="volunteer-map-wrap" data-volunteer-map><div class="volunteer-map-search"><input type="search" placeholder="Search a barangay" data-barangay-search aria-label="Search a local barangay"></div><div class="volunteer-map" data-map-canvas aria-label="Offline Cebu City barangay map"></div><div class="volunteer-no-pin" data-no-pin>⌖<span>Tap anywhere on the map to place the incident location</span></div><div class="volunteer-map-controls"><div><strong>Impact radius</strong><span><b data-radius-value>300</b> m</span></div><input type="range" min="100" max="3000" step="50" value="300" data-radius-slider><small>Local map data • no external tiles required</small></div></div></section>
    </div>
@endsection
