@extends('layouts.dashboard', ['title' => 'Reports | ResQLink'])

@php
    $categoryPalette = ['var(--blue)', 'var(--red)', 'var(--navy)', 'var(--amber)', 'var(--green)'];
    $statusColors = [
        'reported' => 'var(--red)',
        'received' => 'var(--amber)',
        'dispatched' => 'var(--green)',
        'completed' => 'var(--blue)',
    ];
@endphp

@section('content')
    <div class="header-row">
        <div>
            <h1>System Reports</h1>
            <p>Incident summaries calculated from persisted records.</p>
        </div>
    </div>

    <form class="card" method="GET" action="{{ route('admin.reports.index') }}">
        <label for="report-period">Reporting period</label>
        <select id="report-period" name="period">
            <option value="daily" @selected($period === 'daily')>Daily</option>
            <option value="weekly" @selected($period === 'weekly')>Weekly</option>
            <option value="monthly" @selected($period === 'monthly')>Monthly</option>
        </select>
        <label for="report-date">Date in selected period</label>
        <input id="report-date" type="date" name="date" value="{{ $date }}">
        <button type="submit">Generate report</button>
        <p>
            Report window: [{{ $from->format('Y-m-d H:i') }}, {{ $until->format('Y-m-d H:i') }})
            {{ $timezone }}. Weekly periods run Monday through Sunday.
        </p>
    </form>

    <div class="report-stat-grid">
        <div class="report-stat">
            <div class="report-stat-val">{{ $totalIncidents }}</div>
            <div class="report-stat-label">Total incidents</div>
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
                    <div class="bar-fill" style="width: {{ max($percent, 6) }}%; background: {{ $categoryPalette[$index % count($categoryPalette)] }};">
                        {{ $row->total }} ({{ $percent }}%)
                    </div>
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
                <div class="bar-label">{{ $row->label }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ max($percent, 6) }}%; background: {{ $statusColors[strtolower($row->label)] ?? 'var(--navy)' }};">
                        {{ $row->total }} ({{ $percent }}%)
                    </div>
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
