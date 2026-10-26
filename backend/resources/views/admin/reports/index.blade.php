@extends('layouts.dashboard', ['title' => 'Reports | ResQLink'])

@php
    use App\Models\Incident;

    $categoryPalette = ['var(--rosewood)', 'var(--sangria)', 'var(--maximum-red)', 'var(--tangelo)', 'var(--philippine-orange)'];
    $statusColors = [
        'Reported' => 'var(--maximum-red)',
        'Received' => 'var(--tangelo)',
        'Dispatched' => 'var(--philippine-orange)',
        Incident::STATUS_COMPLETED => 'var(--success)',
    ];
    $statusLabels = [
        'Reported' => 'Active — Reported',
        'Received' => 'Active — Received',
        'Dispatched' => 'Active — Dispatched',
        Incident::STATUS_COMPLETED => 'Archived — Completed',
    ];
@endphp

@section('content')
    <div class="header-row">
        <div>
            <h1>System Reports</h1>
            <p>Summary statistics and incident analytics.</p>
        </div>
    </div>

    <form class="card report-filter-row" method="GET">
        <select name="period"><option value="daily" @selected($period === 'daily')>Daily</option><option value="weekly" @selected($period === 'weekly')>Weekly</option><option value="monthly" @selected($period === 'monthly')>Monthly</option></select>
        <input type="date" name="date" value="{{ $date }}">
        <button type="submit">Generate Report</button>
        <p>Showing {{ $from->format('M d, Y H:i') }} to {{ $to->format('M d, Y H:i') }}</p>
    </form>

    <div class="report-stat-grid">
        <div class="report-stat">
            <div class="report-stat-val">{{ $totalIncidents }}</div>
            <div class="report-stat-label">Total Incidents</div>
        </div>
        @foreach ($statusLabels as $status => $label)
            @php $statusTotal = $byStatus->firstWhere('status', $status)?->total ?? 0; @endphp
            <div class="report-stat">
                <div class="report-stat-val">{{ $statusTotal }}</div>
                <div class="report-stat-label">{{ $label }}</div>
            </div>
        @endforeach
        <div class="report-stat">
            <div class="report-stat-val">{{ $byFrequency->count() }}</div>
            <div class="report-stat-label">Days With Incidents</div>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-title">Incidents by Category</div>
        @forelse ($byCategory as $index => $row)
            @php $percent = $totalIncidents > 0 ? round($row->total / $totalIncidents * 100) : 0; @endphp
            <div class="bar-row">
                <div class="bar-label">{{ $row->category }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ max($percent, 6) }}%; background: {{ $categoryPalette[$index % count($categoryPalette)] }};"></div>
                    <strong class="bar-value">{{ $row->total }}</strong>
                </div>
            </div>
        @empty
            <p>No incident data yet.</p>
        @endforelse
    </div>

    <div class="chart-card">
        <div class="chart-title">Incidents by Status</div>
        @forelse ($byStatus as $row)
            @php $percent = $totalIncidents > 0 ? round($row->total / $totalIncidents * 100) : 0; @endphp
            <div class="bar-row">
                <div class="bar-label">{{ $statusLabels[$row->status] ?? $row->status }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ max($percent, 6) }}%; background: {{ $statusColors[$row->status] ?? 'var(--rosewood)' }};"></div>
                    <strong class="bar-value">{{ $row->total }}</strong>
                </div>
            </div>
        @empty
            <p>No incident data yet.</p>
        @endforelse
    </div>

    <div class="chart-card"><div class="chart-title">Incidents by Barangay</div>
        @forelse ($byBarangay as $row)<p>{{ $row->barangay ?: 'Unknown' }}: {{ $row->total }}</p>@empty<p>No incident data in this period.</p>@endforelse
    </div>
    <div class="chart-card"><div class="chart-title">Frequency by Date</div>
        @forelse ($byFrequency as $row)<p>{{ $row->report_date }}: {{ $row->total }}</p>@empty<p>No incident data in this period.</p>@endforelse
    </div>
@endsection
