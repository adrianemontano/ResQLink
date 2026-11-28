@extends('layouts.dashboard', ['title' => 'Your Incidents | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Your Incident Reports</h1>
            <p>View and manage the incidents you have submitted.</p>
        </div>
        <a class="button" href="{{ route('volunteer.incidents.create') }}">Report an incident</a>
    </div>

    <section class="card">
        @if ($incidents->isEmpty())
            <div class="alert info">
                You have not submitted any incident reports yet.
                <a class="button secondary dispatcher-view-button" href="{{ route('volunteer.incidents.create') }}">Submit your first report</a>
            </div>
        @else
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Incident</th>
                            <th>Category</th>
                            <th>Barangay</th>
                            <th>Status</th>
                            <th>Severity</th>
                            <th>Reported</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($incidents as $incident)
                            <tr>
                                <td>INC-{{ str_pad((string) $incident->getKey(), 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $incident->category ?: 'Unspecified' }}</td>
                                <td>{{ $incident->barangay ?: 'Not specified' }}</td>
                                <td><span class="badge status-{{ strtolower($incident->status ?: 'reported') }}">{{ $incident->status ?: 'Reported' }}</span></td>
                                <td><span class="badge severity-{{ strtolower($incident->severity ?: 'unrated') }}">{{ $incident->severity ?: 'Unrated' }}</span></td>
                                <td>{{ ($incident->reported_at ?? $incident->created_at)->format('M j, Y') }}</td>
                                <td>
                                    <a class="button secondary dispatcher-view-button" href="{{ route('volunteer.incidents.edit', $incident) }}">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-resqlink-pagination :paginator="$incidents" :per-page="$perPage" />
        @endif
    </section>
@endsection
