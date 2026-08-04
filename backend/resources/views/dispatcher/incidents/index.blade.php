@extends('layouts.dashboard', ['title' => 'Incidents | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Incident Management</h1>
            <p>Live queue of emergencies reported by volunteers.</p>
        </div>
    </div>

    <section class="card">
        <table>
            <thead>
                <tr>
                    <th>Incident ID</th>
                    <th>Reporter</th>
                    <th>Category</th>
                    <th>Persons</th>
                    <th>Barangay</th>
                    <th>Reported</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($incidents as $incident)
                    <tr>
                        <td>INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $incident->reporter?->name ?? '—' }}</td>
                        <td>{{ $incident->category }}</td>
                        <td>{{ $incident->persons_count }}</td>
                        <td>{{ $incident->barangay }}</td>
                        <td>{{ $incident->created_at->diffForHumans() }}</td>
                        <td>
                            <span class="badge status-{{ $incident->status }}">{{ ucfirst($incident->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No incidents have been reported yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $incidents->links() }}
        </div>
    </section>
@endsection
