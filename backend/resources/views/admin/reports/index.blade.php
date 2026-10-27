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
    $daysWithIncidents = collect($byFrequency)->where('total', '>', 0)->count();
@endphp

@section('content')
    <div class="header-row">
        <div>
            <h1>System Reports</h1>
            <p>Incident summaries calculated from persisted records.</p>
        </div>
    </div>

    <form class="card report-filter-row" method="GET" action="{{ route('admin.reports.index') }}">
        <label for="report-period">Reporting period</label>
        <select id="report-period" name="period">
            <option value="daily" @selected($period === 'daily')>Daily</option>
            <option value="weekly" @selected($period === 'weekly')>Weekly</option>
            <option value="monthly" @selected($period === 'monthly')>Monthly</option>
        </select>
        <label for="report-date">Date in selected period</label>
        <input id="report-date" type="date" name="date" value="{{ $date }}">
        <button type="submit">Generate Report</button>
        <p>
            Report window: [{{ $from->format('Y-m-d H:i') }}, {{ $until->format('Y-m-d H:i') }})
            {{ $timezone }}. Weekly periods run Monday through Sunday.
        </p>
    </form>

    <div class="report-stat-grid">
        <div class="report-stat">
            <div class="report-stat-val">{{ $totalIncidents }}</div>
            <div class="report-stat-label">Total Incidents</div>
        </div>
        @foreach ($statusLabels as $status => $label)
            @php $statusTotal = $byStatus->firstWhere('label', $status)?->total ?? 0; @endphp
            <div class="report-stat">
                <div class="report-stat-val">{{ $statusTotal }}</div>
                <div class="report-stat-label">{{ $label }}</div>
            </div>
        @endforeach
        <div class="report-stat">
            <div class="report-stat-val">{{ $daysWithIncidents }}</div>
            <div class="report-stat-label">Days With Incidents</div>
        </div>
    </div>

    @unless ($hasIncidents)
        <p class="card" role="status">No incident data in this period. All totals are zero.</p>
    @endunless

    <section class="chart-card" aria-label="Incidents by category">
        <h2 class="chart-title">Incidents by category</h2>
        @forelse ($byCategory as $index => $row)
            @php $percent = $totalIncidents > 0 ? round($row->total / $totalIncidents * 100) : 0; @endphp
            <div class="bar-row">
                <div class="bar-label">{{ $row->label }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ max($percent, 6) }}%; background: {{ $categoryPalette[$index % count($categoryPalette)] }};"></div>
                    <strong class="bar-value">{{ $row->total }} ({{ $percent }}%)</strong>
                </div>
            </div>
        @empty
            <p>No category totals for this period.</p>
        @endforelse
    </section>

    <section class="chart-card" aria-label="Incidents by status">
        <h2 class="chart-title">Incidents by status</h2>
        @forelse ($byStatus as $row)
            @php $percent = $totalIncidents > 0 ? round($row->total / $totalIncidents * 100) : 0; @endphp
            <div class="bar-row">
                <div class="bar-label">{{ $statusLabels[$row->label] ?? $row->label }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ max($percent, 6) }}%; background: {{ $statusColors[$row->label] ?? 'var(--rosewood)' }};"></div>
                    <strong class="bar-value">{{ $row->total }} ({{ $percent }}%)</strong>
                </div>
            </div>
        @empty
            <p>No status totals for this period.</p>
        @endforelse
    </section>

    <section class="chart-card" aria-label="Incidents by barangay">
        <h2 class="chart-title">Incidents by barangay</h2>
        @forelse ($byBarangay as $row)
            <p>{{ $row->label }}: {{ $row->total }}</p>
        @empty
            <p>No barangay totals for this period.</p>
        @endforelse
    </section>

    <section class="chart-card" aria-label="Incident frequency by date">
        <h2 class="chart-title">Incident frequency by date</h2>
        @foreach ($byFrequency as $row)
            <p>{{ $row['date'] }}: {{ $row['total'] }}</p>
        @endforeach
    </section>
@endsection
