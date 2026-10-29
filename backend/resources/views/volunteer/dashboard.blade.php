@extends('layouts.dashboard', ['title' => 'Barangay Responder Dashboard | ResQLink'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/volunteer-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/volunteer-dashboard-responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/volunteer-dashboard-analytics.css') }}">
@endpush

@section('content')
    <div class="header-row volunteer-dashboard-heading">
        <div>
            <span class="volunteer-eyebrow">Barangay Responder overview</span>
            <h1>Your response impact</h1>
            <p>A clear view of the incidents you have reported and the difference you are making.</p>
        </div>
        <a class="button" href="{{ route('volunteer.incidents.create') }}">Report an incident</a>
    </div>

    <div class="volunteer-dashboard">
        <section class="volunteer-hero" aria-labelledby="volunteer-hero-title">
            <div class="volunteer-hero-copy">
                <span class="volunteer-hero-kicker">Every report moves response forward</span>
                <h2 id="volunteer-hero-title">Your community counts on clear information.</h2>
                <p>Share what is happening. Dispatchers use your reports to understand local needs and coordinate support.</p>
                <a class="volunteer-hero-link" href="{{ route('volunteer.incidents.create') }}">
                    Create a new report <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
            <div class="volunteer-hero-total" aria-label="{{ $totalReports }} reports submitted by you">
                <span>Your reports</span>
                <strong>{{ number_format($totalReports) }}</strong>
                <small>submitted to date</small>
            </div>
            <span class="volunteer-hero-mark" aria-hidden="true">R</span>
        </section>

        <section class="volunteer-metrics" aria-label="Your incident summary">
            <article class="volunteer-metric-card metric-total">
                <span class="volunteer-metric-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M7 3.75h7l4 4v12.5H7a2 2 0 0 1-2-2v-12.5a2 2 0 0 1 2-2Z"/><path d="M14 4v4h4M9 13h6M9 16.5h6"/></svg>
                </span>
                <span class="volunteer-metric-label">Total reports</span>
                <strong>{{ number_format($totalReports) }}</strong>
                <small>All incidents you have reported</small>
            </article>
            <article class="volunteer-metric-card metric-active">
                <span class="volunteer-metric-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.25"/><path d="M12 7.5v5l3 1.75"/></svg>
                </span>
                <span class="volunteer-metric-label">Open reports</span>
                <strong>{{ number_format($activeReports) }}</strong>
                <small>Still moving through response</small>
            </article>
            <article class="volunteer-metric-card metric-complete">
                <span class="volunteer-metric-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.25"/><path d="m8.5 12 2.25 2.25L15.75 9"/></svg>
                </span>
                <span class="volunteer-metric-label">Completed</span>
                <strong>{{ number_format($completedReports) }}</strong>
                <small>Marked complete by the response team</small>
            </article>
        </section>

        <section class="volunteer-analytics-grid" aria-label="Incident analytics">
            <article class="volunteer-analytics-card volunteer-trend-card">
                <div class="volunteer-card-heading">
                    <div>
                        <span class="volunteer-eyebrow">Your activity</span>
                        <h2>Reporting trend</h2>
                    </div>
                    <span class="volunteer-period-label">Last 6 months</span>
                </div>
                <div class="volunteer-chart" role="img" aria-label="Monthly reports in the last six months: @foreach ($reportTrend as $month) {{ $month['label'] }} {{ $month['total'] }}; @endforeach">
                    @foreach ($reportTrend as $month)
                        <div class="volunteer-chart-column">
                            <span class="volunteer-chart-value">{{ $month['total'] }}</span>
                            <div class="volunteer-chart-track">
                                <span class="volunteer-chart-bar" style="--bar-height: {{ $month['total'] > 0 ? (int) round($month['total'] / $trendTotal * 100) : 0 }}%"></span>
                            </div>
                            <span class="volunteer-chart-label">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="volunteer-analytics-card volunteer-category-card">
                <div class="volunteer-card-heading">
                    <div>
                        <span class="volunteer-eyebrow">What you report</span>
                        <h2>Incident categories</h2>
                    </div>
                </div>
                @php
                    $categoryColors = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)'];
                    $categoryStops = [];
                    $categoryProgress = 0;
                    foreach ($categoryBreakdown as $index => $category) {
                        $segmentEnd = $totalReports > 0 ? $categoryProgress + ($category['count'] / $totalReports * 100) : $categoryProgress;
                        $categoryStops[] = $categoryColors[$index % count($categoryColors)] . ' ' . $categoryProgress . '% ' . $segmentEnd . '%';
                        $categoryProgress = $segmentEnd;
                    }
                @endphp
                @if ($categoryBreakdown->isNotEmpty())
                    <div class="volunteer-category-layout">
                        <div class="volunteer-category-donut" style="--category-chart: conic-gradient({{ implode(', ', $categoryStops) }})" role="img" aria-label="Your incident reports by category">
                            <span>{{ number_format($totalReports) }}</span>
                            <small>Total</small>
                        </div>
                        <div class="volunteer-category-legend">
                            @foreach ($categoryBreakdown as $index => $category)
                                <div class="volunteer-category-item">
                                    <span class="volunteer-category-dot" style="--category-color: {{ $categoryColors[$index % count($categoryColors)] }}" aria-hidden="true"></span>
                                    <span>{{ $category['name'] }}</span>
                                    <strong>{{ $category['count'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="volunteer-empty-state">
                        <span aria-hidden="true">+</span>
                        <p>Your category breakdown will appear after your first report.</p>
                    </div>
                @endif
            </article>
        </section>

        <section class="volunteer-analytics-card volunteer-recent-card">
            <div class="volunteer-card-heading">
                <div>
                    <span class="volunteer-eyebrow">Your activity</span>
                    <h2>Recent reports</h2>
                </div>
                <a class="volunteer-text-link" href="{{ route('volunteer.incidents.create') }}">Submit another <span aria-hidden="true">&rarr;</span></a>
            </div>
            @forelse ($recentIncidents as $incident)
                <article class="volunteer-recent-row">
                    <span class="volunteer-recent-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 20s7-4.25 7-10a7 7 0 1 0-14 0c0 5.75 7 10 7 10Z"/><circle cx="12" cy="10" r="2.25"/></svg>
                    </span>
                    <div class="volunteer-recent-main">
                        <strong>{{ $incident->category ?: 'Incident report' }}</strong>
                        <span>INC-{{ str_pad((string) $incident->getKey(), 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <span class="volunteer-status-badge status-{{ strtolower($incident->status ?: 'reported') }}">{{ $incident->status ?: 'Reported' }}</span>
                    <time datetime="{{ ($incident->reported_at ?? $incident->created_at)->toDateString() }}">
                        {{ ($incident->reported_at ?? $incident->created_at)->format('M j, Y') }}
                    </time>
                </article>
            @empty
                <div class="volunteer-empty-state volunteer-recent-empty">
                    <span aria-hidden="true">+</span>
                    <p>You have not submitted an incident yet. Your first report will show up here.</p>
                    <a class="button secondary" href="{{ route('volunteer.incidents.create') }}">Submit your first report</a>
                </div>
            @endforelse
        </section>
    </div>
@endsection
