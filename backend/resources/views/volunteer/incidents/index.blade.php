@extends('layouts.dashboard', ['title' => 'Your Incidents | ResQLink'])

@push('styles')
    @vite('resources/css/volunteer-report.css')
    <style>.volunteer-map { background: #fff !important; background-image: none !important; } .volunteer-hover-popup .maplibregl-popup-content { padding: 5px 7px; font-size: 10px; line-height: 1.25; } .volunteer-hover-popup .maplibregl-popup-tip { display: none; }</style>
    <script>window.RESQLINK_LOCAL_MAP_STYLE_URL = @json(config('local_map.style_url')); window.RESQLINK_LOCAL_MAP_ATTRIBUTION = @json(config('local_map.attribution'));</script>
@endpush

@push('scripts')
    @vite('resources/js/volunteer-report.js')
@endpush

@section('content')
    <div class="volunteer-topbar"><div><h1>Your Incident Reports</h1><p>View and manage the incidents you have submitted.</p></div></div>
    
    @if ($incidents->isEmpty())
        <div class="alert info">
            You have not submitted any incident reports yet.
            <a class="button secondary" href="{{ route('volunteer.incidents.create') }}">Submit your first report</a>
        </div>
    @else
        <div class="volunteer-incidents-list">
            <table class="m-table">
                <thead>
                    <tr>
                        <th>Incident</th>
                        <th>Category</th>
                        <th>Barangay</th>
                        <th>Status</th>
                        <th>Severity</th>
                        <th>Reported</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incidents as $incident)
                        <tr>
                            <td>INC-{{ str_pad((string) $incident->getKey(), 4, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $incident->category ?: 'Unspecified' }}</td>
                            <td>{{ $incident->barangay ?: 'Not specified' }}</td>
                            <td><span class="badge status-{{ strtolower($incident->status ?: 'reported') }}">{{ $incident->status ?: 'Reported' }}</span></td>
                            <td><span class="badge severity-{{ strtolower($incident->severity ?: 'unrated') }}">{{ $incident->severity ?: 'Unrated' }}</span></td>
                            <td>
                                @if ($incident->reported_at)
                                    {{ ($incident->reported_at)->format('M j, Y') }}
                                @else
                                    {{ ($incident->created_at)->format('M j, Y') }}
                                @endif
                            </td>
                            <td>
                                <a class="button tiny" href="{{ route('volunteer.incidents.edit', $incident) }}">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No incidents found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection