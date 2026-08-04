@extends('layouts.dashboard', ['title' => 'Reports | ResQLink'])

@php
    $categoryPalette = ['var(--blue)', 'var(--red)', 'var(--navy)', 'var(--amber)', 'var(--green)'];
    $statusColors = [
        'pending' => 'var(--red)',
        'received' => 'var(--amber)',
        'dispatched' => 'var(--green)',
    ];
@endphp

@section('content')
    <div class="header-row">
        <div>
            <h1>System Reports</h1>
            <p>Summary statistics and incident analytics.</p>
        </div>
    </div>

    <div class="report-stat-grid">
        <div class="report-stat">
            <div class="report-stat-val">{{ $totalIncidents }}</div>
            <div class="report-stat-label">Total Incidents</div>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-title">Incidents by Category</div>
        @forelse ($byCategory as $index => $row)
            @php $percent = $totalIncidents > 0 ? round($row->total / $totalIncidents * 100) : 0; @endphp
            <div class="bar-row">
                <div class="bar-label">{{ $row->category }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ max($percent, 6) }}%; background: {{ $categoryPalette[$index % count($categoryPalette)] }};">{{ $row->total }}</div>
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
                <div class="bar-label">{{ ucfirst($row->status) }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ max($percent, 6) }}%; background: {{ $statusColors[$row->status] ?? 'var(--navy)' }};">{{ $row->total }}</div>
                </div>
            </div>
        @empty
            <p>No incident data yet.</p>
        @endforelse
    </div>
@endsection
